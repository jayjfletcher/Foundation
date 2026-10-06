<?php

declare(strict_types=1);

namespace JayI\Foundation\Audit;

use JayI\Foundation\Audit\Contracts\AuditTrail;
use JayI\Foundation\Audit\Data\AuditFilter;
use JayI\Foundation\Audit\Data\AuditPage;

/**
 * The audit trail while no audit log is installed: never available, always
 * empty. jayi/audit replaces the binding.
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
