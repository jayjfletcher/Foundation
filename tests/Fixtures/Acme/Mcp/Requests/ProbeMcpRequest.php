<?php

declare(strict_types=1);

namespace JayI\Foundation\Tests\Fixtures\Acme\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Foundation\Support\Surface;
use JayI\Foundation\Tests\Fixtures\Acme\Exceptions\AcmeException;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

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
