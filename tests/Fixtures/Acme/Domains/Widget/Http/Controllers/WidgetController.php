<?php

declare(strict_types=1);

namespace JayI\Foundation\Tests\Fixtures\Acme\Domains\Widget\Http\Controllers;

use Illuminate\Http\JsonResponse;
use JayI\Foundation\Tests\Fixtures\Acme\Domains\Widget\Http\Requests\StoreWidgetRequest;

final class WidgetController
{
    public function __invoke(StoreWidgetRequest $request): JsonResponse
    {
        return $request->persist();
    }
}
