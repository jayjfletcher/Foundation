<?php

declare(strict_types=1);

namespace JayI\Foundation\Audit\Contracts;

use JayI\Foundation\Audit\Data\AuditFilter;
use JayI\Foundation\Audit\Data\AuditPage;

/**
 * Read access to the audit log, whichever package keeps it.
 *
 * Every package and the Atrium dashboard read history through this contract,
 * so none of them depends on jayi/audit. Until it is installed the shared
 * runtime binds `NullAuditTrail`, which is never available and always empty.
 */
interface AuditTrail
{
    /**
     * Whether an audit log is installed and recording.
     */
    public function available(): bool;

    /**
     * The entries matching the filter, newest first.
     */
    public function entries(AuditFilter $filter): AuditPage;
}
