<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Laravel\Ai\AiManager;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Streaming\Events\ReasoningDelta;
use Laravel\Ai\Streaming\Events\ReasoningEnd;
use Laravel\Ai\Streaming\Events\ReasoningStart;
use Laravel\Ai\Streaming\Events\StreamEnd;
use Laravel\Ai\Streaming\Events\StreamEvent;
use Laravel\Ai\Streaming\Events\StreamStart;
use Laravel\Ai\Streaming\Events\TextDelta;
use Laravel\Ai\Streaming\Events\TextEnd;
use Laravel\Ai\Streaming\Events\TextStart;

beforeEach(function (): void {
    Http::preventStrayRequests();
});

function sseFromChunks(array $chunks): string
{
    $lines = [];

    foreach ($chunks as $chunk) {
        $lines[] = 'data: '.json_encode($chunk);
        $lines[] = '';
    }

    $lines[] = 'data: [DONE]';
    $lines[] = '';

    return implode("\n", $lines);
}

it('streams text deltas from a chat completions SSE response', function (): void {
    $sse = sseFromChunks([
        ['id' => 'x', 'model' => 'kimi-k2.6', 'choices' => [['index' => 0, 'delta' => ['role' => 'assistant']]]],
        ['id' => 'x', 'model' => 'kimi-k2.6', 'choices' => [['index' => 0, 'delta' => ['content' => 'Hello']]]],
        ['id' => 'x', 'model' => 'kimi-k2.6', 'choices' => [['index' => 0, 'delta' => ['content' => ' world'], 'finish_reason' => 'stop']], 'usage' => ['prompt_tokens' => 3, 'completion_tokens' => 2]],
    ]);

    Http::fake([
        'api.moonshot.ai/v1/chat/completions' => Http::response($sse, 200, ['Content-Type' => 'text/event-stream']),
    ]);

    $provider = resolve(AiManager::class)->textProvider('moonshot');

    $events = [];
    $generator = $provider->textGateway()->streamText(
        'inv-1',
        $provider,
        'kimi-k2.6',
        instructions: null,
        messages: [new Message('user', 'Hi')],
    );

    foreach ($generator as $event) {
        $events[] = $event;
    }

    $classes = array_map(fn (StreamEvent $e): string => $e::class, $events);

    expect($classes)->toContain(StreamStart::class)
        ->and($classes)->toContain(TextStart::class)
        ->and($classes)->toContain(TextDelta::class)
        ->and($classes)->toContain(TextEnd::class)
        ->and($classes)->toContain(StreamEnd::class);

    $deltas = array_values(array_filter($events, fn ($e): bool => $e instanceof TextDelta));
    $text = implode('', array_map(fn (TextDelta $e): string => $e->delta, $deltas));

    expect($text)->toBe('Hello world');
});

it('emits reasoning events when the model returns reasoning_content deltas', function (): void {
    $sse = sseFromChunks([
        ['id' => 'x', 'model' => 'kimi-k2-thinking', 'choices' => [['index' => 0, 'delta' => ['role' => 'assistant']]]],
        ['id' => 'x', 'model' => 'kimi-k2-thinking', 'choices' => [['index' => 0, 'delta' => ['reasoning_content' => 'let me think...']]]],
        ['id' => 'x', 'model' => 'kimi-k2-thinking', 'choices' => [['index' => 0, 'delta' => ['reasoning_content' => ' carefully']]]],
        ['id' => 'x', 'model' => 'kimi-k2-thinking', 'choices' => [['index' => 0, 'delta' => ['content' => 'Answer']]]],
        ['id' => 'x', 'model' => 'kimi-k2-thinking', 'choices' => [['index' => 0, 'delta' => [], 'finish_reason' => 'stop']], 'usage' => ['prompt_tokens' => 3, 'completion_tokens' => 4]],
    ]);

    Http::fake([
        'api.moonshot.ai/v1/chat/completions' => Http::response($sse, 200, ['Content-Type' => 'text/event-stream']),
    ]);

    $provider = resolve(AiManager::class)->textProvider('moonshot');

    $events = [];
    foreach ($provider->textGateway()->streamText('inv-2', $provider, 'kimi-k2-thinking', null, [new Message('user', 'Hi')]) as $event) {
        $events[] = $event;
    }

    $classes = array_map(fn (StreamEvent $e): string => $e::class, $events);
    expect($classes)->toContain(ReasoningStart::class)
        ->and($classes)->toContain(ReasoningDelta::class)
        ->and($classes)->toContain(ReasoningEnd::class);

    // ReasoningEnd must come before TextStart.
    $reasoningEndIndex = array_search(ReasoningEnd::class, $classes, true);
    $textStartIndex = array_search(TextStart::class, $classes, true);
    expect($reasoningEndIndex)->toBeLessThan($textStartIndex);

    $reasoning = implode('', array_map(
        fn (ReasoningDelta $e): string => $e->delta,
        array_filter($events, fn ($e): bool => $e instanceof ReasoningDelta),
    ));
    expect($reasoning)->toBe('let me think... carefully');
});
