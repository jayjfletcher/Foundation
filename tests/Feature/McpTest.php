<?php

declare(strict_types=1);

use RefactorCircus\Foundation\Support\Surface;
use RefactorCircus\Foundation\Tests\Fixtures\Acme\Mcp\AcmeServer;
use RefactorCircus\Foundation\Tests\Fixtures\Acme\Mcp\Tools\ProbeTool;

it('marks mcp calls with the mcp surface', function (): void {
    AcmeServer::tool(ProbeTool::class)
        ->assertOk()
        ->assertStructuredContent(['surface' => 'mcp', 'package' => 'acme']);
});

it('surfaces package exceptions to the caller', function (): void {
    AcmeServer::tool(ProbeTool::class, ['fail' => true])
        ->assertHasErrors(['Widgets are sold out.']);
});

it('keeps the declared description while cortex is absent', function (): void {
    expect(app(ProbeTool::class)->description())->toBe('Report the surface the call came through.');
});

it('keeps the cortex surface when an agent calls the tool', function (): void {
    $surface = app(Surface::class);
    $surface->enter('cortex');

    AcmeServer::tool(ProbeTool::class)->assertStructuredContent(['surface' => 'cortex', 'package' => 'acme']);

    $surface->leave();
});
