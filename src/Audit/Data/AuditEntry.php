<?php

declare(strict_types=1);

namespace JayI\Foundation\Audit\Data;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Contracts\Support\Arrayable;

/**
 * One audit log entry, as any package reads it.
 *
 * @implements Arrayable<string, mixed>
 */
final class AuditEntry implements Arrayable
{
    /**
     * @param  array<string, array{0: mixed, 1: mixed}>  $changes  field => [old, new]
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        public private(set) int|string $id,
        public private(set) string $source,
        public private(set) string $action,
        public private(set) string $surface,
        public private(set) CarbonImmutable $createdAt,
        public private(set) ?string $actorId = null,
        public private(set) ?string $actorLabel = null,
        public private(set) ?string $subjectType = null,
        public private(set) ?string $subjectId = null,
        public private(set) ?string $subjectLabel = null,
        public private(set) array $changes = [],
        public private(set) array $context = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'source' => $this->source,
            'action' => $this->action,
            'surface' => $this->surface,
            'actor' => $this->actorId === null ? null : ['id' => $this->actorId, 'label' => $this->actorLabel],
            'subject' => $this->subjectType === null ? null : [
                'type' => $this->subjectType,
                'id' => $this->subjectId,
                'label' => $this->subjectLabel,
            ],
            'changes' => $this->changes,
            'context' => $this->context,
            'created_at' => $this->createdAt->format(DateTimeInterface::ATOM),
        ];
    }
}
