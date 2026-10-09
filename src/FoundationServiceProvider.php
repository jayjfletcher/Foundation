<?php

declare(strict_types=1);

namespace RefactorCircus\Foundation;

use Illuminate\Support\ServiceProvider;
use RefactorCircus\Foundation\Audit\AuditHooks;
use RefactorCircus\Foundation\Audit\Contracts\AuditTrail;
use RefactorCircus\Foundation\Audit\NullAuditTrail;
use RefactorCircus\Foundation\Packages\PackageRegistry;
use RefactorCircus\Foundation\Support\Surface;

/**
 * The shared runtime every package of the suite stands on.
 *
 * It has no routes, views or config of its own: it holds the package
 * registry, the hooks packages give the audit log, the current surface, and
 * a null audit trail until refactor-circus/keen binds a real one.
 */
class FoundationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singletonIf(PackageRegistry::class);
        $this->app->singletonIf(AuditHooks::class);
        $this->app->scopedIf(Surface::class);
        $this->app->singletonIf(AuditTrail::class, NullAuditTrail::class);
    }
}
