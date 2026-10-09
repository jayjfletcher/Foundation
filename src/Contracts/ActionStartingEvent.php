<?php

declare(strict_types=1);

namespace RefactorCircus\Foundation\Contracts;

/**
 * Fired when an action starts, before it does any work.
 *
 * Dispatched immediately, so a listener runs before the action touches the
 * database. Listen to this interface to see every action start.
 */
interface ActionStartingEvent
{
    //
}
