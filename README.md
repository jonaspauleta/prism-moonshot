# laravel-ai-moonshot

[Moonshot AI](https://platform.kimi.ai/) (Kimi K2) provider for the [Laravel AI SDK](https://github.com/laravel/ai).

Moonshot's API is OpenAI-compatible (`POST https://api.moonshot.ai/v1/chat/completions`), so this driver supports text generation, streaming, tool calling, and thinking-mode reasoning (`reasoning_content` deltas are surfaced as Laravel AI SDK `ReasoningStart` / `ReasoningDelta` / `ReasoningEnd` stream events).

## Requirements

- PHP 8.5+
- `laravel/ai` ^0.6.3

## Install

```bash
composer require jonaspauleta/laravel-ai-moonshot
```

The service provider auto-registers via Laravel package discovery.

## Configure

Set the API key:

```env
MOONSHOT_API_KEY=sk-...
```

In `config/ai.php`:

```php
'providers' => [
    // ...
    'moonshot' => [
        'driver' => 'moonshot',
        'name' => 'moonshot',
        'key' => env('MOONSHOT_API_KEY'),

        // Optional per-tier model overrides — defaults shown below.
        'models' => [
            'text' => [
                'default' => 'kimi-k2.6',
                'cheapest' => 'kimi-k2-0905-preview',
                'smartest' => 'kimi-k2-thinking',
            ],
        ],
    ],
],
```

## Usage

```php
use App\Ai\Agents\RaceEngineer;
use Illuminate\Broadcasting\PrivateChannel;

$agent = new RaceEngineer($user);

$agent
    ->forUser($user)
    ->broadcastOnQueue(
        'What is the best setup for the Nordschleife?',
        new PrivateChannel("agent.{$user->id}.{$requestId}"),
        provider: 'moonshot',
        model: 'kimi-k2.6',
    );
```

### Thinking mode

Kimi's `kimi-k2.6` supports persistent reasoning across turns with `thinking.keep = all`. Return the payload from your agent's `providerOptions()` and the gateway merges it into the chat-completions body unchanged:

```php
public function providerOptions(Lab|string $provider): array
{
    return $provider === 'moonshot'
        ? ['thinking' => ['type' => 'enabled', 'keep' => 'all']]
        : [];
}
```

`reasoning_content` deltas then surface as `ReasoningStart` / `ReasoningDelta` / `ReasoningEnd` on the broadcast channel, before text streaming begins.

## License

MIT © João Paulo Santos
