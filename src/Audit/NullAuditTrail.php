<?php

declare(strict_types=1);

namespace RefactorCircus\Foundation\Audit;

use RefactorCircus\Foundation\Audit\Contracts\AuditTrail;
use RefactorCircus\Foundation\Audit\Data\AuditFilter;
use RefactorCircus\Foundation\Audit\Data\AuditPage;

/**
 * The audit trail while no audit log is installed: never available, always
 * empty. refactor-circus/keen replaces the binding.
 */
final class NullAuditTrail implements AuditTrail
{
    public function available(): bool
    {
        return false;
    }

    public function entries(AuditFilter $filter): AuditPage
    {
        return new AuditPage;
    }
}
