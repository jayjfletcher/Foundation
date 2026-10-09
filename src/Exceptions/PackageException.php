<?php

declare(strict_types=1);

namespace RefactorCircus\Foundation\Exceptions;

use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * A rule of a package that the caller broke.
 *
 * The message is written for whoever made the call — a person or an agent —
 * so the HTTP API and MCP tools surface it as it is. Each package extends this
 * with its own exception base.
 */
abstract class PackageException extends RuntimeException
{
    /**
     * Conflict, not a validation error, by default: the request was
     * well-formed, the package's current state simply makes it impossible.
     */
    protected int $status = 409;

    public function render(): Response
    {
        return new Response(
            json_encode(['message' => $this->getMessage()], JSON_THROW_ON_ERROR),
            $this->status,
            ['Content-Type' => 'application/json'],
        );
    }
}
