<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Audit\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * An action event that says what its audit entry is about.
 *
 * Optional. Without it the audit log takes the first model among the event's
 * public properties as the subject and records the rest as context. Implement
 * it when that guess is wrong - a role assignment that is really about the
 * user - or when the entry needs context the properties do not carry.
 */
interface Auditable
{
    /**
     * The model the entry is about, or null for an entry about no model.
     */
    public function auditSubject(): ?Model;

    /**
     * Extra context recorded with the entry.
     *
     * @return array<string, mixed>
     */
    public function auditContext(): array;
}
