<?php

declare(strict_types=1);

namespace RefactorCircus\Foundation\Support;

use Closure;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\CachesRoutes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use RefactorCircus\Foundation\Packages\Package;
use RefactorCircus\Foundation\Packages\PackageRegistry;

/**
 * Base class for a package's domain service providers.
 *
 * The provider finds its package by namespace, so `loadApiRoutesFrom()` can
 * put every JSON API route in one group - the package's configured
 * `routes.prefix` and `routes.middleware`, and its `{key}.` name prefix -
 * without each domain building the group itself.
 */
abstract class ServiceProvider extends BaseServiceProvider
{
    /**
     * The package this provider belongs to.
     */
    protected function package(): Package
    {
        return $this->app->make(PackageRegistry::class)->forOrFail(static::class);
    }

    /**
     * Load a routes file, or a closure declaring routes, inside the package's
     * JSON API route group, when its `routes.enabled` config key is true.
     */
    protected function loadApiRoutesFrom(string|Closure $routes): void
    {
        $package = $this->package();
        $config = $this->app->make(Repository::class);

        if ($config->get($package->configKey('routes.enabled')) !== true || $this->routesAreCached()) {
            return;
        }

        /** @var string $prefix */
        $prefix = $config->get($package->configKey('routes.prefix'), $package->key);

        /** @var array<int, string> $middleware */
        $middleware = $config->get($package->configKey('routes.middleware'), ['api']);

        Route::prefix($prefix)->middleware($middleware)->name($package->key.'.')->group($routes);
    }

    /**
     * Keep the names models were stored under before they moved into their
     * domain, so any polymorphic `*_type` column or other record written with
     * the old names still resolves - and new records keep writing the same
     * value. The first alias listed for a model is the one it writes.
     *
     * @param  array<string, class-string<Model>>  $map
     */
    protected function keepMorphAliases(array $map): void
    {
        Relation::morphMap($map);
    }

    protected function routesAreCached(): bool
    {
        return $this->app instanceof CachesRoutes && $this->app->routesAreCached();
    }
}
