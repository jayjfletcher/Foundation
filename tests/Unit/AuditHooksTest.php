<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use RefactorCircus\Foundation\Audit\AuditHooks;
use RefactorCircus\Foundation\Audit\Contracts\AuditTrail;
use RefactorCircus\Foundation\Audit\Data\AuditFilter;
use RefactorCircus\Foundation\Audit\NullAuditTrail;
use RefactorCircus\Foundation\Tests\Fixtures\Acme\Domains\Widget\Models\WidgetModel;

it('labels models of a class and its subclasses', function (): void {
    $hooks = (new AuditHooks)->label(WidgetModel::class, fn (WidgetModel $widget): string => 'Widget '.$widget->name);

    expect($hooks->labelFor(new WidgetModel(['name' => 'Cog'])))->toBe('Widget Cog')
        ->and($hooks->labelFor(new class extends WidgetModel {}))->toBe('Widget ')
        ->and($hooks->labelFor(new class extends Model {}))->toBeNull();
});

it('merges every snapshot hook for a model', function (): void {
    $hooks = (new AuditHooks)
        ->snapshot(WidgetModel::class, fn (): array => ['parts' => ['a']])
        ->snapshot(Model::class, fn (): array => ['tenant' => 't1']);

    expect($hooks->snapshotFor(new WidgetModel))->toBe(['parts' => ['a'], 'tenant' => 't1']);
});

it('takes the first subject and scope a hook names', function (): void {
    $widget = new WidgetModel(['name' => 'Cog']);
    $hooks = (new AuditHooks)
        ->subject(fn (): ?Model => null)
        ->subject(fn (object $event, array $models): ?Model => $models['widget'] ?? null)
        ->scope(fn (): Model => $widget);

    expect($hooks->subjectFor(new stdClass, ['widget' => $widget]))->toBe($widget)
        ->and($hooks->scopeFor(new stdClass, [], null))->toBe($widget);
});

it('merges context and redacted fields', function (): void {
    $hooks = (new AuditHooks)
        ->context(fn (): array => ['impersonator' => 'ada'])
        ->context(fn (): array => ['transfer' => 7])
        ->redact('secret', 'token')
        ->redact('token');

    expect($hooks->contextFor(new stdClass, null))->toBe(['impersonator' => 'ada', 'transfer' => 7])
        ->and($hooks->redacted())->toBe(['secret', 'token']);
});

it('binds a null audit trail until an audit log is installed', function (): void {
    $trail = app(AuditTrail::class);

    expect($trail)->toBeInstanceOf(NullAuditTrail::class)
        ->and($trail->available())->toBeFalse()
        ->and($trail->entries(AuditFilter::make())->isEmpty())->toBeTrue();
});

it('builds filters fluently', function (): void {
    $widget = new WidgetModel;
    $widget->id = 5;

    $filter = AuditFilter::make()->source('acme')->subject($widget)->action('widget.updated')->limit(0)->cursor('c');

    expect($filter->subjectType)->toBe(WidgetModel::class)
        ->and($filter->subjectId)->toBe('5')
        ->and($filter->limit)->toBe(1)
        ->and($filter->cursor)->toBe('c');
});

it('filters by scope', function (): void {
    $scope = new WidgetModel;
    $scope->id = 3;

    $filter = AuditFilter::make()->scope($scope);

    expect($filter->scopeType)->toBe(WidgetModel::class)
        ->and($filter->scopeId)->toBe('3');
});
