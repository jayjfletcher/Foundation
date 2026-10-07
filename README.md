# Foundation

The shared, headless runtime for the jayi package suite (Atrium, Cortex, Impex, Keystone, Keen, PennantPlus, Polycart, Roster).

Foundation has no routes, views or config of its own. It holds the base classes and contracts every package used to copy, so each package works the same way and works without the Atrium dashboard.

## Installation

Packages of the suite require it; an application never needs to install it directly.

```bash
composer require jayi/foundation
```

## Describing a package

A package's main service provider extends `PackageServiceProvider`, describes the package once, and registers it before any domain provider:

```php
use JayI\Foundation\Packages\Package;
use JayI\Foundation\Support\PackageServiceProvider;

final class KeystoneServiceProvider extends PackageServiceProvider
{
    protected function definition(): Package
    {
        return Package::make('keystone', 'JayI\Keystone')
            ->label('Keystone')
            ->server(KeystoneServer::class);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/keystone.php', 'keystone');
        $this->registerPackage();
        $this->app->register(DomainServiceProvider::class);
    }

    public function boot(): void
    {
        $this->registerPolicies();      // keystone.policies
        $this->registerMcpServer();     // keystone.mcp.web / keystone.mcp.local
        $this->registerCortex();        // keystone.cortex.*, when Cortex is installed
        $this->registerAtriumPlugin(KeystonePlugin::class); // keystone.ui.enabled, when Atrium is installed
        $this->loadHistoryRoutes();     // GET {prefix}/history
    }
}
```

Every base class finds its package by namespace, through the `PackageRegistry`, so nothing else is configured per class. The helpers read a config file every package shapes the same way:

```php
return [
    'authorization' => false,
    'policies' => [/* Model::class => Policy::class */],
    'routes' => ['enabled' => true, 'prefix' => 'api/keystone', 'middleware' => ['api']],
    'mcp' => [
        'web' => ['enabled' => false, 'route' => 'mcp/keystone', 'middleware' => []],
        'local' => ['enabled' => false, 'handle' => 'keystone'],
    ],
    'cortex' => ['enabled' => true, 'server' => 'keystone', 'tools' => null, 'tags' => null],
    'ui' => ['enabled' => true],
];
```

## What it provides

| Class | Purpose |
|---|---|
| `Actions\Action` | `execute()` → `protected handle()` base for mutating logic |
| `Contracts\ActionStartingEvent`, `ActionFinishedEvent`, `ModelLifecycleEvent` | The event contracts every package's events implement. Listen to one to hear every package. |
| `Models\Concerns\DispatchesModelEvents` | Maps each Eloquent hook to `Domains\{Domain}\Events\{Entity}{Hook}Event` |
| `Support\ServiceProvider` | Domain provider base: `loadApiRoutesFrom()`, `keepMorphAliases()` |
| `Support\PackageServiceProvider` | Main provider base, as above |
| `Auth\Authorizer` | The `authorization` switch and Gate checks |
| `Http\Requests\Request` | FormRequest with `persist()`, `actor()`, `allows()`, `allowsEach()` |
| `Mcp\Requests\Request`, `Mcp\Tool`, `Mcp\Server` | The same pattern for MCP, with Cortex overrides |
| `Cortex\CortexIntegration` | Registers a package's server and tools with Cortex when it is installed |
| `Exceptions\PackageException` | Base for a package's rule-broken exceptions (409 JSON, MCP error text) |
| `Policies\Policy` | `allowsOn()` defers a child model to its parent's policy |
| `Support\Surface` | Which surface a call came through: `atrium`, `http`, `mcp`, `cortex`, `cli`, `code` |

## Audit seams

Foundation defines how packages talk to an audit log without depending on one. [jayi/keen](https://github.com/jayjfletcher/Keen) is the audit log; until it is installed, `AuditTrail` is bound to `NullAuditTrail`.

- `Audit\Contracts\AuditTrail`: read entries with an `AuditFilter`, get an `AuditPage` of `AuditEntry` values.
- `Audit\Contracts\Auditable`: an optional interface for action events that name their subject and context.
- `Audit\AuditHooks`: what a package teaches the log about its models (labels, snapshot extras, subject and scope pickers, context, redacted fields).
- `Package::authorizeHistory()` lets a package that does not authorize through policies decide who reads its history.
- `Audit\History` plus `loadHistoryRoutes()` and `Mcp\Tools\ListHistoryTool`: every package serves its own history over HTTP and MCP. Both answer "not installed" while Keen is absent.

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
