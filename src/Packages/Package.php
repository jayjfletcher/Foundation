<?php

declare(strict_types=1);

namespace JayI\Foundation\Packages;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;
use Laravel\Mcp\Server;

/**
 * One package of the suite, as the shared runtime knows it.
 *
 * The key names the package's config file, route names and audit source
 * (`keystone`); the namespace is how a class is traced back to the package
 * that owns it, so base classes find their package without being told.
 *
 *     Package::make('keystone', 'JayI\Keystone')
 *         ->label('Keystone')
 *         ->server(KeystoneServer::class);
 */
final class Package
{
    public private(set) string $label;

    /**
     * The package's MCP server, when it has one.
     *
     * @var class-string<Server>|null
     */
    public private(set) ?string $server = null;

    /**
     * Whether calls are authorized through the Gate when the package's
     * `authorization` config key is not set.
     */
    public private(set) bool $authorization = false;

    /**
     * An extra check before anyone reads the package's history, on top of
     * the subject's `view` policy and the `viewAuditLog` ability.
     *
     * @var (Closure(?Authenticatable): bool)|null
     */
    private ?Closure $historyCheck = null;

    private function __construct(
        public private(set) string $key,
        public private(set) string $namespace,
    ) {
        $this->namespace = trim($namespace, '\\');
        $this->label = Str::headline($key);
    }

    public static function make(string $key, string $namespace): self
    {
        return new self($key, $namespace);
    }

    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @param  class-string<Server>  $server
     */
    public function server(string $server): self
    {
        $this->server = $server;

        return $this;
    }

    public function authorization(bool $authorization = true): self
    {
        $this->authorization = $authorization;

        return $this;
    }

    /**
     * Guard the package's history with its own check as well, such as the
     * ability that guards the rest of its API.
     *
     * @param  Closure(?Authenticatable): bool  $check
     */
    public function authorizeHistory(Closure $check): self
    {
        $this->historyCheck = $check;

        return $this;
    }

    /**
     * Whether the package's own history check lets the user through.
     */
    public function allowsHistory(?Authenticatable $user): bool
    {
        return $this->historyCheck === null || ($this->historyCheck)($user) === true;
    }

    /**
     * The full config key for a path under the package's config file.
     */
    public function configKey(string $path): string
    {
        return $this->key.'.'.$path;
    }

    /**
     * Whether a class lives in the package's namespace.
     *
     * @param  object|class-string  $class
     */
    public function owns(object|string $class): bool
    {
        $name = ltrim(is_object($class) ? $class::class : $class, '\\');

        return str_starts_with($name, $this->namespace.'\\');
    }
}
