<?php

declare(strict_types=1);

namespace RefactorCircus\Foundation\Audit\Data;

use Illuminate\Contracts\Support\Arrayable;

/**
 * One page of audit entries, newest first.
 *
 * @implements Arrayable<string, mixed>
 */
final class AuditPage implements Arrayable
{
    /**
     * @param  list<AuditEntry>  $entries
     */
    public function __construct(
        public private(set) array $entries = [],
        public private(set) ?string $nextCursor = null,
    ) {}

    public function isEmpty(): bool
    {
        return $this->entries === [];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'data' => array_map(fn (AuditEntry $entry): array => $entry->toArray(), $this->entries),
            'next_cursor' => $this->nextCursor,
        ];
    }
}
