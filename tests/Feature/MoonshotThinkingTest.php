<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Jonaspauleta\PrismMoonshot\Maps\ThinkingMap;
use Jonaspauleta\PrismMoonshot\Moonshot;
use Prism\Prism\Facades\Prism;

beforeEach(function (): void {
    Http::preventStrayRequests();
});

it('omits thinking when no provider option is set', function (): void {
    Http::fake([
        'api.moonshot.ai/v1/chat/completions' => Http::response([
            'id' => 'x',
            'model' => 'kimi-k2.6',
            'choices' => [['index' => 0, 'message' => ['role' => 'assistant', 'content' => 'ok'], 'finish_reason' => 'stop']],
            'usage' => ['prompt_tokens' => 1, 'completion_tokens' => 1],
        ]),
    ]);

    Prism::text()->using(Moonshot::KEY, 'kimi-k2.6')->withPrompt('hi')->asText();

    Http::assertSent(fn (Request $request): bool => ! array_key_exists('thinking', $request->data()));
});

it('sends thinking={type:enabled} when boolean true is passed', function (): void {
    Http::fake([
        'api.moonshot.ai/v1/chat/completions' => Http::response([
            'id' => 'x',
            'model' => 'kimi-k2.6',
            'choices' => [['index' => 0, 'message' => ['role' => 'assistant', 'content' => 'ok'], 'finish_reason' => 'stop']],
            'usage' => ['prompt_tokens' => 1, 'completion_tokens' => 1],
        ]),
    ]);

    Prism::text()
        ->using(Moonshot::KEY, 'kimi-k2.6')
        ->withProviderOptions(['thinking' => true])
        ->withPrompt('hi')
        ->asText();

    Http::assertSent(fn (Request $request): bool => data_get($request->data(), 'thinking') === ['type' => 'enabled']);
});

it('passes through thinking={type:enabled, keep:all} for kimi-k2.6 multi-turn', function (): void {
    Http::fake([
        'api.moonshot.ai/v1/chat/completions' => Http::response([
            'id' => 'x',
            'model' => 'kimi-k2.6',
            'choices' => [['index' => 0, 'message' => ['role' => 'assistant', 'content' => 'ok'], 'finish_reason' => 'stop']],
            'usage' => ['prompt_tokens' => 1, 'completion_tokens' => 1],
        ]),
    ]);

    Prism::text()
        ->using(Moonshot::KEY, 'kimi-k2.6')
        ->withProviderOptions(['thinking' => ['type' => 'enabled', 'keep' => 'all']])
        ->withPrompt('hi')
        ->asText();

    Http::assertSent(fn (Request $request): bool => data_get($request->data(), 'thinking') === [
        'type' => 'enabled',
        'keep' => 'all',
    ]);
});

it('rejects invalid thinking.type values', function (): void {
    ThinkingMap::map(['type' => 'maybe']);
})->throws(InvalidArgumentException::class, "thinking.type must be 'enabled' or 'disabled'");

it('rejects unsupported keep values', function (): void {
    ThinkingMap::map(['type' => 'enabled', 'keep' => 'last']);
})->throws(InvalidArgumentException::class, "keep currently only supports 'all'");

it('returns null for false / null inputs', function (): void {
    expect(ThinkingMap::map(null))->toBeNull();
    expect(ThinkingMap::map(false))->toBeNull();
});
