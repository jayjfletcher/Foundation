<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Server\Registrar;
use RefactorCircus\Keystone\Auth\Authorizer;
use RefactorCircus\Keystone\Exceptions\UnknownPackageException;
use RefactorCircus\Keystone\Packages\Package;
use RefactorCircus\Keystone\Packages\PackageRegistry;
use RefactorCircus\Keystone\Tests\Fixtures\Acme\Domains\Widget\Models\WidgetModel;
use RefactorCircus\Keystone\Tests\Fixtures\Acme\Mcp\AcmeServer;

it('registers a package from its service provider', function (): void {
    $acme = app(PackageRegistry::class)->get('acme');

    expect($acme->label)->toBe('Acme')
        ->and($acme->namespace)->toBe('RefactorCircus\Keystone\Tests\Fixtures\Acme')
        ->and($acme->server)->toBe(AcmeServer::class);
});

it('finds the package a class belongs to by the longest namespace', function (): void {
    $registry = app(PackageRegistry::class);
    $registry->register(Package::make('acme_plus', 'RefactorCircus\Keystone\Tests\Fixtures\Acme\Plus'));

    expect($registry->for(WidgetModel::class)?->key)->toBe('acme')
        ->and($registry->for('RefactorCircus\Keystone\Tests\Fixtures\Acme\Plus\Thing')?->key)->toBe('acme_plus')
        ->and($registry->for('RefactorCircus\Keystone\Tests\Fixtures\AcmeOther\Thing'))->toBeNull()
        ->and($registry->for(new WidgetModel)?->key)->toBe('acme');
});

it('refuses a class no package owns', function (): void {
    app(PackageRegistry::class)->forOrFail('App\Models\User');
})->throws(UnknownPackageException::class);

it('loads domain routes inside the package api group', function (): void {
    $this->postJson('/acme/widgets', ['name' => 'Sprocket'])
        ->assertCreated()
        ->assertJson(['package' => 'acme']);

    expect(route('acme.widgets.store', absolute: false))->toBe('/acme/widgets');
});

it('serves the package mcp server over http and locally', function (): void {
    $mcp = app(Registrar::class);

    expect($mcp->getWebServer('mcp/acme'))->not->toBeNull()
        ->and($mcp->getLocalServer('acme'))->not->toBeNull();
});

it('authorizes through the gate only when the package asks for it', function (): void {
    Gate::define('create', fn (): bool => false);
    Gate::policy(WidgetModel::class, DenyAllWidgetPolicy::class);

    $this->postJson('/acme/widgets', ['name' => 'Sprocket'])->assertCreated();

    config()->set('acme.authorization', true);

    // A guest is refused outright; the route middleware let them in.
    $this->postJson('/acme/widgets', ['name' => 'Sprocket'])->assertForbidden();
});

it('defaults authorization from the package definition', function (): void {
    $registry = app(PackageRegistry::class);
    $strict = $registry->register(Package::make('strict', 'Strict')->authorization());

    expect(Authorizer::for($strict)->enabled())->toBeTrue();

    config()->set('strict.authorization', false);

    expect(Authorizer::for($strict)->enabled())->toBeFalse();
});

final class DenyAllWidgetPolicy
{
    public function create(): bool
    {
        return false;
    }
}
