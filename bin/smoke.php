<?php

declare(strict_types=1);

/*
 * Live-API smoke test for the Moonshot provider.
 *
 * Hits the real Moonshot endpoint with three scenarios:
 *   1. one-shot prompt
 *   2. streaming prompt
 *   3. tool call
 *
 * Gated by the MOONSHOT_API_KEY environment variable. Skips with a clear
 * message when the key is missing so it stays safe to wire into CI on
 * `workflow_dispatch` or release tags only.
 */

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Jonaspauleta\LaravelAiMoonshot\MoonshotServiceProvider;

use function Laravel\Ai\agent;

use Laravel\Ai\AiServiceProvider;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Streaming\Events\StreamEvent;
use Laravel\Ai\Streaming\Events\TextDelta;
use Laravel\Ai\Tools\Request as ToolRequest;
use Orchestra\Testbench\Foundation\Application;

$autoload = __DIR__.'/../vendor/autoload.php';

if (! file_exists($autoload)) {
    fwrite(STDERR, "vendor/autoload.php not found — run `composer install` first.\n");
    exit(1);
}

require $autoload;

$apiKey = getenv('MOONSHOT_API_KEY') ?: '';

if ($apiKey === '') {
    fwrite(STDERR, "MOONSHOT_API_KEY is not set — skipping live smoke test.\n");
    exit(0);
}

$app = Application::create(
    basePath: dirname(__DIR__).'/build/smoke',
    resolvingCallback: function ($app) use ($apiKey): void {
        $app->register(AiServiceProvider::class);
        $app->register(MoonshotServiceProvider::class);

        $app['config']->set('ai.providers.moonshot', [
            'driver' => 'moonshot',
            'name' => 'moonshot',
            'key' => $apiKey,
        ]);
        $app['config']->set('ai.default', 'moonshot');
    },
);

echo "[1/3] one-shot prompt against kimi-k2.6\n";
$response = agent('You are concise. Reply in five words or fewer.')
    ->prompt('Say hello.', provider: 'moonshot', model: 'kimi-k2.6');
echo '    -> '.trim($response->text)."\n";

echo "[2/3] streaming prompt against kimi-k2.6\n";
$buffer = '';
$stream = agent('You are concise. Reply in five words or fewer.')
    ->stream('Say hello again.', provider: 'moonshot', model: 'kimi-k2.6');
foreach ($stream as $event) {
    if ($event instanceof StreamEvent && $event instanceof TextDelta) {
        $buffer .= $event->delta;
    }
}
echo '    -> '.trim($buffer)."\n";

echo "[3/3] tool call against kimi-k2.6\n";

$weatherTool = new class implements Tool
{
    public function description(): string
    {
        return 'Return the current weather for a city as a short string.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'city' => $schema->string()->description('City name')->required(),
        ];
    }

    public function handle(ToolRequest $request): string
    {
        return 'Sunny, 24C in '.$request->string('city').'.';
    }
};

$toolResponse = agent(
    instructions: 'Always use the weather tool when asked about weather.',
    tools: [$weatherTool],
)->prompt('What is the weather in Lisbon?', provider: 'moonshot', model: 'kimi-k2.6');

echo '    -> '.trim($toolResponse->text)."\n";

echo "smoke OK\n";
