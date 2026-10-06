<?php

declare(strict_types=1);

namespace JayI\Foundation\Tests;

use JayI\Foundation\FoundationServiceProvider;
use JayI\Foundation\Tests\Fixtures\Acme\AcmeServiceProvider;
use Laravel\Mcp\Server\McpServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            McpServiceProvider::class,
            FoundationServiceProvider::class,
            AcmeServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('acme', [
            'routes' => ['enabled' => true, 'prefix' => 'acme', 'middleware' => ['api']],
            'mcp' => [
                'web' => ['enabled' => true, 'route' => 'mcp/acme', 'middleware' => []],
                'local' => ['enabled' => true, 'handle' => 'acme'],
            ],
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__.'/Fixtures/migrations');
    }
}
