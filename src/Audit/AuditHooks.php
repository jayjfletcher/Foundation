<?php

declare(strict_types=1);

namespace JayI\Foundation\Audit;

use Closure;
use Illuminate\Database\Eloquent\Model;

/**
 * What packages teach the audit log about their own models and events.
 *
 * Packages register hooks from their service providers whether or not
 * jayi/audit is installed; registering costs nothing, and the audit log reads
 * them when it records. Every hook is optional.
 *
 *     $hooks->label(RoleModel::class, fn (RoleModel $role) => $role->name);
 *     $hooks->snapshot(RoleModel::class, fn (RoleModel $role) => ['permissions' => ...]);
 *     $hooks->context(fn (object $event, ?Model $subject) => ['impersonator' => ...]);
 *     $hooks->scope(fn (object $event, array $models, ?Model $subject) => $organization);
 *     $hooks->redact('client_secret');
 */
final class AuditHooks
{
    /**
     * @var array<class-string, Closure(Model): ?string>
     */
    private array $labels = [];

    /**
     * @var array<class-string, list<Closure(Model): array<string, mixed>>>
     */
    private array $snapshots = [];

    /**
     * @var list<Closure(object, array<string, Model>): ?Model>
     */
    private array $subjects = [];

    /**
     * @var list<Closure(object, ?Model): array<string, mixed>>
     */
    private array $contexts = [];

    /**
     * @var list<Closure(object, array<string, Model>, ?Model): ?Model>
     */
    private array $scopes = [];

    /**
     * @var list<string>
     */
    private array $redacted = [];

    /**
     * How to name a model of this class, or a subclass, in an entry.
     *
     * @param  class-string  $class
     * @param  Closure(Model): ?string  $label
     */
    public function label(string $class, Closure $label): self
    {
        $this->labels[$class] = $label;

        return $this;
    }

    /**
     * Extra fields to snapshot for a model of this class, such as related
     * records whose changes belong in its diff.
     *
     * @param  class-string  $class
     * @param  Closure(Model): array<string, mixed>  $snapshot
     */
    public function snapshot(string $class, Closure $snapshot): self
    {
        $this->snapshots[$class][] = $snapshot;

        return $this;
    }

    /**
     * Pick an entry's subject from an event's models. The first hook to
     * return a model wins; return null to leave the choice to the next.
     *
     * @param  Closure(object, array<string, Model>): ?Model  $subject
     */
    public function subject(Closure $subject): self
    {
        $this->subjects[] = $subject;

        return $this;
    }

    /**
     * Add context to every entry, such as who is impersonating.
     *
     * @param  Closure(object, ?Model): array<string, mixed>  $context
     */
    public function context(Closure $context): self
    {
        $this->contexts[] = $context;

        return $this;
    }

    /**
     * Name the model an entry is scoped to - an organization, a tenant - so
     * the log can be read per scope. The first hook to return a model wins.
     *
     * @param  Closure(object, array<string, Model>, ?Model): ?Model  $scope
     */
    public function scope(Closure $scope): self
    {
        $this->scopes[] = $scope;

        return $this;
    }

    /**
     * Field names whose values never reach the log.
     */
    public function redact(string ...$fields): self
    {
        $this->redacted = array_values(array_unique([...$this->redacted, ...$fields]));

        return $this;
    }

    public function labelFor(Model $model): ?string
    {
        foreach ($this->labels as $class => $label) {
            if ($model instanceof $class) {
                return $label($model);
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function snapshotFor(Model $model): array
    {
        $extra = [];

        foreach ($this->snapshots as $class => $snapshots) {
            if ($model instanceof $class) {
                foreach ($snapshots as $snapshot) {
                    $extra = [...$extra, ...$snapshot($model)];
                }
            }
        }

        return $extra;
    }

    /**
     * @param  array<string, Model>  $models
     */
    public function subjectFor(object $event, array $models): ?Model
    {
        foreach ($this->subjects as $subject) {
            $model = $subject($event, $models);

            if ($model !== null) {
                return $model;
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function contextFor(object $event, ?Model $subject): array
    {
        $context = [];

        foreach ($this->contexts as $provider) {
            $context = [...$context, ...$provider($event, $subject)];
        }

        return $context;
    }

    /**
     * @param  array<string, Model>  $models
     */
    public function scopeFor(object $event, array $models, ?Model $subject): ?Model
    {
        foreach ($this->scopes as $scope) {
            $model = $scope($event, $models, $subject);

            if ($model !== null) {
                return $model;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    public function redacted(): array
    {
        return $this->redacted;
    }
}
