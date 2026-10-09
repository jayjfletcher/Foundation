<?php

declare(strict_types=1);

namespace RefactorCircus\Foundation\Tests\Fixtures\Acme\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Foundation\Mcp\Requests\Request;
use RefactorCircus\Foundation\Support\Surface;
use RefactorCircus\Foundation\Tests\Fixtures\Acme\Exceptions\AcmeException;

final class ProbeMcpRequest extends Request
{
    protected function rules(): array
    {
        return ['fail' => ['sometimes', 'boolean']];
    }

    protected function handle(array $validated): ResponseFactory
    {
        if (($validated['fail'] ?? false) === true) {
            throw new AcmeException('Widgets are sold out.');
        }

        return Response::structured([
            'surface' => app(Surface::class)->current(),
            'package' => $this->package()->key,
        ]);
    }
}
