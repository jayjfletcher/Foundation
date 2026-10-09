<?php

declare(strict_types=1);

namespace RefactorCircus\Foundation\Actions;

/**
 * Base class for mutating business logic across the suite.
 *
 * Callers invoke `execute()`; concrete actions implement a `protected handle()`
 * with their own typed signature. The split gives every action one uniform
 * entry point and keeps a place to hook cross-cutting behavior without
 * touching each action.
 *
 * Every action announces itself with a pair of events: `handle()` dispatches
 * a starting event (`DashboardCreatingActionEvent`, carrying the input)
 * before any work, and a finished event (`DashboardCreatedActionEvent`,
 * carrying the result) once the work succeeds. Finished events implement
 * `ActionFinishedEvent`, so they wait for the surrounding transaction to
 * commit and never fire for a write that was rolled back.
 *
 * Resolve and invoke through the container rather than a static constructor:
 *
 *     app(CreateDashboardAction::class)->execute($data, $user);
 */
abstract class Action
{
    /**
     * Execute the action.
     *
     * Concrete actions implement `handle()` with a concrete return type; it is
     * invoked here so subclasses can wrap execution in one place.
     */
    public function execute(mixed ...$arguments): mixed
    {
        return $this->handle(...$arguments); // @phpstan-ignore method.notFound
    }
}
