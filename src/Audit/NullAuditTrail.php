<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Audit;

use RefactorCircus\Keystone\Audit\Contracts\AuditTrail;
use RefactorCircus\Keystone\Audit\Data\AuditFilter;
use RefactorCircus\Keystone\Audit\Data\AuditPage;

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
