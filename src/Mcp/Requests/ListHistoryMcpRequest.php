<?php

declare(strict_types=1);

namespace JayI\Foundation\Mcp\Requests;

use JayI\Foundation\Audit\Data\AuditEntry;
use JayI\Foundation\Audit\History;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

/**
 * A package's audit entries for its history tool. The tool names the package.
 */
final class ListHistoryMcpRequest extends Request
{
    protected function authorize(): bool
    {
        return app(History::class)->allows($this->package(), $this->user(), $this->all());
    }

    protected function rules(): array
    {
        return History::rules();
    }

    protected function handle(array $validated): Response|ResponseFactory
    {
        $history = app(History::class);

        if (! $history->available()) {
            return Response::error('No audit log is installed, so there is no history to show. Install jayi/keen to record it.');
        }

        $page = $history->page($this->package(), $validated);

        return $this->structuredCollection(
            array_map(fn (AuditEntry $entry): array => $entry->toArray(), $page->entries),
            ['next_cursor' => $page->nextCursor],
        );
    }
}
