<?php

declare(strict_types=1);

namespace RefactorCircus\Foundation\Mcp;

use Laravel\Mcp\Server as McpServer;
use Laravel\Mcp\Server\ServerContext;
use RefactorCircus\Foundation\Cortex\CortexIntegration;
use RefactorCircus\Foundation\Packages\PackageRegistry;

/**
 * Base class for every package's MCP server.
 *
 * A server lists its tools in a `TOOLS` constant, offers them behind
 * ToolSearch, and is named on its package's definition, which also registers
 * the tools with Cortex when Cortex is installed:
 *
 *     public const array TOOLS = [ListProductsTool::class, ...];
 *
 *     protected array $tools = [ToolSearch::class => self::TOOLS];
 */
abstract class Server extends McpServer
{
    /**
     * Serve Cortex's published instructions override, when Cortex is
     * installed and one is published, in place of the declared ones.
     */
    public function createContext(): ServerContext
    {
        $context = parent::createContext();
        $package = app(PackageRegistry::class)->for(static::class);
        $override = $package === null ? null : CortexIntegration::for($package)->instructions();

        if ($override !== null) {
            $context->instructions = $override;
        }

        return $context;
    }
}
