<?php

declare(strict_types=1);

namespace JayI\Foundation;

use Illuminate\Support\ServiceProvider;
use JayI\Foundation\Audit\AuditHooks;
use JayI\Foundation\Audit\Contracts\AuditTrail;
use JayI\Foundation\Audit\NullAuditTrail;
use JayI\Foundation\Packages\PackageRegistry;
use JayI\Foundation\Support\Surface;

/**
 * The shared runtime every package of the suite stands on.
 *
 * It has no routes, views or config of its own: it holds the package
 * registry, the hooks packages give the audit log, the current surface, and
 * a null audit trail until jayi/keen binds a real one.
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
