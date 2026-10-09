<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Mcp\Requests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Laravel\Mcp\Request as McpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Keystone\Auth\Authorizer;
use RefactorCircus\Keystone\Exceptions\PackageException;
use RefactorCircus\Keystone\Packages\Package;
use RefactorCircus\Keystone\Packages\PackageRegistry;
use RefactorCircus\Keystone\Support\Surface;

/**
 * Base MCP request for every package in the suite.
 *
 * Mirrors the HTTP request's `persist()` pattern so tools stay one line and
 * both surfaces resolve the same Actions. Parity is then structural rather than
 * something to maintain by hand. Everything a call does is marked as coming
 * from the `mcp` surface, or stays `cortex` when a Cortex agent made it.
 *
 * The request finds its package by namespace. With the package's
 * `authorization` config key on, every call acts as the authenticated user
 * and is checked against the policies in its `policies` key.
 */
abstract class Request extends McpRequest
{
    private ?Package $package = null;

    final public function persist(): Response|ResponseFactory
    {
        $surface = app(Surface::class);

        // A Cortex agent calling the tool stays `cortex`; any other client is `mcp`.
        return $surface->using($surface->current() === 'cortex' ? 'cortex' : 'mcp', function (): Response|ResponseFactory {
            try {
                if (! $this->authorize()) {
                    return Response::error('Unauthorized.');
                }

                return $this->handle($this->validated());
            } catch (ModelNotFoundException) {
                return Response::error('Not found.');
            } catch (PackageException $e) {
                // Package exceptions carry guidance an agent can act on, so
                // surface the message rather than a generic failure.
                return Response::error($e->getMessage());
            }
        });
    }

    /**
     * Handle the validated tool call.
     *
     * @param  array<string, mixed>  $validated
     */
    abstract protected function handle(array $validated): Response|ResponseFactory;

    /**
     * Answer for another package than the one the request's namespace names,
     * as shared requests such as the history request do.
     */
    public function forPackage(Package $package): static
    {
        $this->package = $package;

        return $this;
    }

    /**
     * The package this request belongs to.
     */
    protected function package(): Package
    {
        return $this->package ?? app(PackageRegistry::class)->forOrFail(static::class);
    }

    /**
     * Wrap a resolved collection in a `data` envelope.
     *
     * `Response::structured([])` throws, so an empty list must still ship
     * inside a non-empty `{ "data": [...] }` payload.
     *
     * @param  array<int, mixed>  $items
     * @param  array<string, mixed>  $meta
     */
    protected function structuredCollection(array $items, array $meta = []): ResponseFactory
    {
        return Response::structured(['data' => $items] + $meta);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [];
    }

    protected function authorize(): bool
    {
        return $this->authorizer()->authenticated($this->user());
    }

    protected function authorizer(): Authorizer
    {
        return Authorizer::for($this->package());
    }

    /**
     * The user calls act as, or null when authorization is off.
     */
    protected function actor(): ?Model
    {
        return $this->authorizer()->actor($this->user());
    }

    /**
     * Check an ability against the model's policy from the package's
     * `policies` config key.
     *
     * @param  Model|class-string<Model>  $subject
     * @param  array<int, mixed>  $arguments
     */
    protected function allows(string $ability, Model|string $subject, array $arguments = []): bool
    {
        return $this->authorizer()->can($this->user(), $ability, $subject, $arguments);
    }

    /**
     * Check an ability against every model given; an empty list passes.
     *
     * @param  iterable<int, Model>  $models
     */
    protected function allowsEach(string $ability, iterable $models): bool
    {
        foreach ($models as $model) {
            if (! $this->allows($ability, $model)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(): array
    {
        return Validator::validate($this->all(), $this->rules());
    }
}
