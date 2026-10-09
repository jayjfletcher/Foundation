<?php

declare(strict_types=1);

namespace RefactorCircus\Foundation\Tests\Fixtures;

use RefactorCircus\Foundation\Audit\Contracts\AuditTrail;
use RefactorCircus\Foundation\Audit\Data\AuditEntry;
use RefactorCircus\Foundation\Audit\Data\AuditFilter;
use RefactorCircus\Foundation\Audit\Data\AuditPage;

/**
 * An installed audit log that remembers the last filter it was asked for.
 */
final class FakeAuditTrail implements AuditTrail
{
    public ?AuditFilter $filter = null;

    /**
     * @param  list<AuditEntry>  $entries
     */
    public function __construct(private readonly array $entries = []) {}

    public function available(): bool
    {
        return true;
    }

    public function entries(AuditFilter $filter): AuditPage
    {
        $this->filter = $filter;

        return new AuditPage($this->entries, 'next');
    }
}
