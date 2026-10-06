<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Gate;
use JayI\Foundation\Audit\Contracts\AuditTrail;
use JayI\Foundation\Audit\Data\AuditEntry;
use JayI\Foundation\Tests\Fixtures\Acme\Domains\Widget\Models\WidgetModel;
use JayI\Foundation\Tests\Fixtures\Acme\Mcp\AcmeServer;
use JayI\Foundation\Tests\Fixtures\Acme\Mcp\Tools\ListAcmeHistoryTool;
use JayI\Foundation\Tests\Fixtures\FakeAuditTrail;

function installAuditTrail(): FakeAuditTrail
{
    $trail = new FakeAuditTrail([
        new AuditEntry(
            id: 1,
            source: 'acme',
            action: 'widget.created',
            surface: 'http',
            createdAt: CarbonImmutable::parse('2026-10-06 12:00:00'),
            subjectType: WidgetModel::class,
            subjectId: '1',
            subjectLabel: 'Sprocket',
            changes: ['name' => [null, 'Sprocket']],
        ),
    ]);

    app()->instance(AuditTrail::class, $trail);

    return $trail;
}

it('answers 404 while no audit log is installed', function (): void {
    $this->getJson('/acme/history')
        ->assertNotFound()
        ->assertJson(['message' => 'No audit log is installed. Install jayi/audit to record history.']);
});

it('serves the package history from the audit trail', function (): void {
    $trail = installAuditTrail();

    $this->getJson('/acme/history?subject_type='.urlencode(WidgetModel::class).'&subject_id=1&per_page=10')
        ->assertOk()
        ->assertJsonPath('data.0.action', 'widget.created')
        ->assertJsonPath('data.0.subject.label', 'Sprocket')
        ->assertJsonPath('data.0.changes.name', [null, 'Sprocket'])
        ->assertJsonPath('next_cursor', 'next');

    expect($trail->filter?->source)->toBe('acme')
        ->and($trail->filter?->subjectType)->toBe(WidgetModel::class)
        ->and($trail->filter?->subjectId)->toBe('1')
        ->and($trail->filter?->limit)->toBe(10);
});

it('asks view on the subject when authorization is on', function (): void {
    installAuditTrail();
    config()->set('acme.authorization', true);
    $widget = WidgetModel::query()->create(['name' => 'Sprocket']);
    $user = User::forceCreate(['name' => 'Ada', 'email' => 'ada@example.com', 'password' => 'secret']);

    Gate::policy(WidgetModel::class, ViewOwnWidgetPolicy::class);

    $this->actingAs($user)
        ->getJson('/acme/history?subject_type='.urlencode(WidgetModel::class).'&subject_id='.$widget->id)
        ->assertForbidden();

    ViewOwnWidgetPolicy::$allow = true;

    $this->actingAs($user)
        ->getJson('/acme/history?subject_type='.urlencode(WidgetModel::class).'&subject_id='.$widget->id)
        ->assertOk();
});

it('guards the whole history with viewAuditLog when the application defines it', function (): void {
    installAuditTrail();
    config()->set('acme.authorization', true);
    $user = User::forceCreate(['name' => 'Ada', 'email' => 'ada@example.com', 'password' => 'secret']);

    $this->actingAs($user)->getJson('/acme/history')->assertOk();

    Gate::define('viewAuditLog', fn (User $user, string $package): bool => $package === 'other');

    $this->actingAs($user)->getJson('/acme/history')->assertForbidden();
});

it('offers the same history as an mcp tool', function (): void {
    AcmeServer::tool(ListAcmeHistoryTool::class)
        ->assertHasErrors(['No audit log is installed, so there is no history to show. Install jayi/audit to record it.']);

    installAuditTrail();

    AcmeServer::tool(ListAcmeHistoryTool::class)
        ->assertOk()
        ->assertSee('widget.created');

    expect(app(ListAcmeHistoryTool::class)->name())->toBe('list-acme-history-tool');
});

final class ViewOwnWidgetPolicy
{
    public static bool $allow = false;

    public function view(): bool
    {
        return self::$allow;
    }
}
