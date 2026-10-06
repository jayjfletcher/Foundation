<?php

declare(strict_types=1);

namespace JayI\Foundation\Tests\Fixtures\Acme\Domains\Widget\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ModelLifecycleEvent;
use JayI\Foundation\Tests\Fixtures\Acme\Domains\Widget\Models\WidgetModel;

final class WidgetCreatedEvent implements ModelLifecycleEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public WidgetModel $widget) {}

    public function model(): Model
    {
        return $this->widget;
    }

    public function hook(): string
    {
        return 'created';
    }
}
