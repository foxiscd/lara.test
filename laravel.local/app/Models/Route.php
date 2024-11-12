<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property RouteStop[] $routeStops
 * @property string $bus_number
 * @property string $direction
 * @mixin Builder
 */
class Route extends Model
{
    use HasFactory;

    protected $fillable = ['bus_number', 'direction'];

    public function routeStops()
    {
        return $this->hasMany(RouteStop::class);
    }


    public function schedules()
    {
        return $this->hasOne(Schedule::class);
    }
}
