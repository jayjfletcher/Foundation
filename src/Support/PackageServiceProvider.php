<?php

declare(strict_types=1);

namespace JayI\Foundation\Support;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use JayI\Foundation\Cortex\CortexIntegration;
use JayI\Foundation\FoundationServiceProvider;
use JayI\Foundation\Http\Controllers\HistoryController;
use JayI\Foundation\Packages\Package;
use JayI\Foundation\Packages\PackageRegistry;
use Laravel\Mcp\Facades\Mcp;

/**
 * Base class for a package's main service provider.
 *
 * The package describes itself once in `definition()` and registers that
 * definition first thing in `register()`, through `registerPackage()`. The
 * helpers below then read everything else - policies, MCP routes, the Atrium
 * switch - from the package's config file, which every package in the suite
 * shapes the same way:
 *
 *     authorization, policies, routes{enabled,prefix,middleware},
 *     mcp{web{enabled,route,middleware},local{enabled,handle}},
 *     cortex{enabled,server,tools}, ui{enabled}
 */
abstract class PackageServiceProvider extends ServiceProvider
{
    /**
     * Describe the package: its key, namespace, label and MCP server.
     */
    abstract protected function definition(): Package;

    /**
     * Register the package with the shared runtime. Call it from `register()`
     * before any domain provider, since those find their package through it.
     */
    protected function registerPackage(): Package
    {
        // Package discovery orders providers alphabetically, not by
        // dependency, so make sure the shared runtime is there first.
        $this->app->register(FoundationServiceProvider::class);

        return $this->app->make(PackageRegistry::class)->register($this->definition());
    }

    /**
     * Register each model's policy from the package's `policies` config key,
     * so an application swaps one by pointing its model at another class there.
     */
    protected function registerPolicies(): void
    {
        /** @var array<class-string, class-string> $policies */
        $policies = $this->config()->get($this->package()->configKey('policies'), []);

        foreach ($policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }

    /**
     * Serve the package's MCP server over HTTP and locally, as its `mcp`
     * config key allows. Skipped when laravel/mcp is not installed.
     */
    protected function registerMcpServer(): void
    {
        $package = $this->package();

        if ($package->server === null || ! class_exists(Mcp::class)) {
            return;
        }

        $config = $this->config();

        if ($config->get($package->configKey('mcp.web.enabled')) === true) {
            /** @var array<int, string> $middleware */
            $middleware = $config->get($package->configKey('mcp.web.middleware'), []);

            Mcp::web((string) $config->get($package->configKey('mcp.web.route')), $package->server)
                ->middleware($middleware);
        }

        if ($config->get($package->configKey('mcp.local.enabled')) === true) {
            Mcp::local((string) $config->get($package->configKey('mcp.local.handle')), $package->server);
        }
    }

    /**
     * Connect the package's MCP server and tools to Cortex, when Cortex is
     * installed and the package's `cortex.enabled` config key allows it.
     */
    protected function registerCortex(): void
    {
        CortexIntegration::for($this->package())->register();
    }

    /**
     * Register the package's plugin with the Atrium dashboard, when Atrium is
     * installed and the package's `ui.enabled` config key is true.
     *
     * @param  class-string  $plugin
     */
    protected function registerAtriumPlugin(string $plugin): void
    {
        $atrium = 'JayI\\Atrium\\Atrium';

        if (! class_exists($atrium) || $this->config()->get($this->package()->configKey('ui.enabled')) !== true) {
            return;
        }

        $this->app->make($atrium)->plugin($plugin);
    }

    /**
     * Serve `GET {prefix}/history`: the package's audit entries, newest first,
     * inside its JSON API route group. Answers 404 while jayi/audit is not
     * installed.
     */
    protected function loadHistoryRoutes(): void
    {
        $key = $this->package()->key;

        $this->loadApiRoutesFrom(function () use ($key): void {
            Route::get('history', HistoryController::class)->name('history.index')->defaults('package', $key);
        });
    }

    protected function config(): Repository
    {
        return $this->app->make(Repository::class);
    }
}
