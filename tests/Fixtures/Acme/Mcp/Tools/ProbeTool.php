<?php

declare(strict_types=1);

namespace JayI\Foundation\Tests\Fixtures\Acme\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Foundation\Mcp\Tool;
use JayI\Foundation\Tests\Fixtures\Acme\Mcp\Requests\ProbeMcpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Report the surface the call came through.')]
final class ProbeTool extends Tool
{
    public function handle(ProbeMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'fail' => $schema->boolean()->description('Throw a package exception.'),
        ];
    }
}
