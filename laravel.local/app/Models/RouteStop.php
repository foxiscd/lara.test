<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $route_id
 * @property int $stop_id
 * @property int $stop_order
 * @mixin Builder
 */
class RouteStop extends Model
{

    use HasFactory;

    protected $fillable = ['route_id', 'stop_id', 'stop_order'];

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function stop()
    {
        return $this->belongsTo(Stop::class);
    }

}
