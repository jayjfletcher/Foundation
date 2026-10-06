<?php

declare(strict_types=1);

namespace JayI\Foundation\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Foundation\Mcp\Requests\ListHistoryMcpRequest;
use JayI\Foundation\Mcp\Tool;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

/**
 * Base for a package's history tool: its audit entries, newest first.
 *
 * Each package adds a one-line subclass to its server's `TOOLS`, named for
 * the package so tool names stay unique - `ListKeystoneHistoryTool` is
 * `list-keystone-history-tool`.
 */
abstract class ListHistoryTool extends Tool
{
    public function description(): string
    {
        $package = $this->package();
        $declared = "List {$package->label}'s audit history, newest first: who did what, through which surface, and which fields changed. "
            .'Pass subject_type and subject_id for one record\'s history. Cursor paginated. Needs jayi/audit installed.';

        return parent::description() === '' ? $declared : parent::description();
    }

    public function handle(ListHistoryMcpRequest $request): Response|ResponseFactory
    {
        return $request->forPackage($this->package())->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'subject_type' => $schema->string()->description('Morph type of the record whose history to list, with subject_id.'),
            'subject_id' => $schema->string()->description('Key of the record whose history to list, with subject_type.'),
            'action' => $schema->string()->description('Only entries of this action, such as product.updated.'),
            'cursor' => $schema->string()->description('Cursor from a previous page (next_cursor).'),
            'per_page' => $schema->integer()->description('Results per page, up to 100.')->min(1)->max(100),
        ];
    }
}
