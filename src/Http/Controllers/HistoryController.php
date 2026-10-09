<?php

declare(strict_types=1);

namespace RefactorCircus\Foundation\Http\Controllers;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Foundation\Http\Requests\ListHistoryRequest;

final class HistoryController
{
    public function __invoke(ListHistoryRequest $request): JsonResponse
    {
        return $request->persist();
    }
}
