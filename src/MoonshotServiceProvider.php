<?php

declare(strict_types=1);

namespace Jonaspauleta\LaravelAiMoonshot;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Laravel\Ai\AiManager;

final class MoonshotServiceProvider extends ServiceProvider
{
    public const string KEY = 'moonshot';

    public function boot(): void
    {
        $this->app->afterResolving(AiManager::class, function (AiManager $manager): void {
            $manager->extend(self::KEY, fn (Application $app, array $config): MoonshotProvider => new MoonshotProvider(
                $config,
                $app->make(Dispatcher::class),
            ));
        });
    }
}
