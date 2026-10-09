<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Tests\Fixtures\Acme\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Keystone\Mcp\Requests\Request;
use RefactorCircus\Keystone\Support\Surface;
use RefactorCircus\Keystone\Tests\Fixtures\Acme\Exceptions\AcmeException;

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
