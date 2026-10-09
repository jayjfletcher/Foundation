<?php

declare(strict_types=1);

namespace RefactorCircus\Foundation\Tests\Fixtures\Acme\Domains\Widget;

use Illuminate\Support\Facades\Route;
use RefactorCircus\Foundation\Support\ServiceProvider;
use RefactorCircus\Foundation\Tests\Fixtures\Acme\Domains\Widget\Http\Controllers\WidgetController;

final class WidgetServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadApiRoutesFrom(function (): void {
            Route::post('widgets', WidgetController::class)->name('widgets.store');
        });
    }
}
