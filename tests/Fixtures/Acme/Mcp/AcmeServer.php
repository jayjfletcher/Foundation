<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Tests\Fixtures\Acme\Mcp;

use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Laravel\Mcp\Server\Tool;
use RefactorCircus\Keystone\Mcp\Server;
use RefactorCircus\Keystone\Tests\Fixtures\Acme\Mcp\Tools\ListAcmeHistoryTool;
use RefactorCircus\Keystone\Tests\Fixtures\Acme\Mcp\Tools\ProbeTool;

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
