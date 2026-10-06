<?php

declare(strict_types=1);

namespace JayI\Foundation\Tests\Fixtures\Acme\Domains\Widget\Models;

use Illuminate\Database\Eloquent\Model;
use JayI\Foundation\Models\Concerns\DispatchesModelEvents;

/**
 * @property int $id
 * @property string $name
 */
class WidgetModel extends Model
{
    use DispatchesModelEvents;

    protected $table = 'widgets';

    protected $guarded = [];
}
