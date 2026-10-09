<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Http\Controllers;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Keystone\Http\Requests\ListHistoryRequest;

final class HistoryController
{
    public function __invoke(ListHistoryRequest $request): JsonResponse
    {
        return $request->persist();
    }
}
