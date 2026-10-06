<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use JayI\Foundation\Tests\Fixtures\Acme\Domains\Widget\Events\WidgetCreatedEvent;
use JayI\Foundation\Tests\Fixtures\Acme\Domains\Widget\Models\WidgetModel;

it('dispatches the lifecycle event the naming convention points at', function (): void {
    Event::fake([WidgetCreatedEvent::class]);

    WidgetModel::query()->create(['name' => 'Sprocket']);

    Event::assertDispatched(WidgetCreatedEvent::class, fn (WidgetCreatedEvent $event): bool => $event->model()->getAttribute('name') === 'Sprocket'
        && $event->hook() === 'created');
});

it('skips hooks without an event class', function (): void {
    expect((new WidgetModel)->dispatchesEvents())->toBe(['created' => WidgetCreatedEvent::class]);
});

it('fires the package model events for an application subclass', function (): void {
    Event::fake([WidgetCreatedEvent::class]);

    ApplicationWidget::query()->create(['name' => 'Cog']);

    Event::assertDispatched(WidgetCreatedEvent::class);
});

final class ApplicationWidget extends WidgetModel {}
