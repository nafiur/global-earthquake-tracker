<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EarthquakeSourceType extends Model
{
    public const KEY_USGS = 'usgs';
    public const KEY_EMSC = 'emsc';

    protected $fillable = ['name', 'key'];

    public function sources()
    {
        return $this->hasMany(EarthquakeSource::class, 'type_id');
    }
}