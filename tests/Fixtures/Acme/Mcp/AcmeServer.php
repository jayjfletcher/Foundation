<?php

declare(strict_types=1);

namespace JayI\Foundation\Tests\Fixtures\Acme\Mcp;

use JayI\Foundation\Mcp\Server;
use JayI\Foundation\Tests\Fixtures\Acme\Mcp\Tools\ListAcmeHistoryTool;
use JayI\Foundation\Tests\Fixtures\Acme\Mcp\Tools\ProbeTool;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Laravel\Mcp\Server\Tool;

#[Name('Acme')]
#[Version('1.0.0')]
#[Instructions('Manage Acme widgets.')]
final class AcmeServer extends Server
{
    /**
     * @var array<int, class-string<Tool>>
     */
    public const array TOOLS = [
        ProbeTool::class,
        ListAcmeHistoryTool::class,
    ];

    /**
     * @var array<int, class-string<Tool>|Tool>
     */
    protected array $tools = self::TOOLS;
}
