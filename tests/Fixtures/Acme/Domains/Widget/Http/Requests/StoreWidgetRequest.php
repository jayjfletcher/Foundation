<?php

declare(strict_types=1);

namespace RefactorCircus\Foundation\Tests\Fixtures\Acme\Domains\Widget\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Foundation\Http\Requests\Request;
use RefactorCircus\Foundation\Tests\Fixtures\Acme\Domains\Widget\Models\WidgetModel;

final class StoreWidgetRequest extends Request
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->allows('create', WidgetModel::class);
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string']];
    }

    public function persist(): JsonResponse
    {
        $widget = WidgetModel::query()->create(['name' => $this->string('name')->toString()]);

        return new JsonResponse(['id' => $widget->id, 'package' => $this->package()->key], 201);
    }
}
