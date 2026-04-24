<?php

declare(strict_types=1);

namespace Jonaspauleta\LaravelAiMoonshot\Tests;

use Jonaspauleta\LaravelAiMoonshot\MoonshotServiceProvider;
use Laravel\Ai\AiServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * @return list<class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            AiServiceProvider::class,
            MoonshotServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('ai.providers.moonshot', [
            'driver' => 'moonshot',
            'name' => 'moonshot',
            'key' => env('MOONSHOT_API_KEY', 'test-key'),
        ]);

        $app['config']->set('ai.default', 'moonshot');
    }
}
