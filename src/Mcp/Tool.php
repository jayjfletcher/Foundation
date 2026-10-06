<?php

declare(strict_types=1);

namespace JayI\Foundation\Mcp;

use JayI\Foundation\Cortex\CortexIntegration;
use JayI\Foundation\Packages\Package;
use JayI\Foundation\Packages\PackageRegistry;
use Laravel\Mcp\Server\Tool as McpTool;

/**
 * Base class for every package's MCP tools.
 *
 * A tool does nothing but hand its request to `persist()`. All behaviour lives
 * in the Action the request wraps, which the HTTP surface calls too.
 */
abstract class Tool extends McpTool
{
    /**
     * Cortex's published description override, when Cortex is installed and
     * one is published, in place of the code-declared description.
     */
    public function description(): string
    {
        $package = app(PackageRegistry::class)->for(static::class);

        $override = $package === null ? null : CortexIntegration::for($package)->description($this->name());

        return $override ?? parent::description();
    }

    /**
     * The package this tool belongs to.
     */
    protected function package(): Package
    {
        return app(PackageRegistry::class)->forOrFail(static::class);
    }
}
