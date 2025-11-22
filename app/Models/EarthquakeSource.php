<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EarthquakeSource extends Model
{
    protected $fillable = ['name', 'url', 'type_id', 'is_active'];

    public function type()
    {
        return $this->belongsTo(EarthquakeSourceType::class, 'type_id');
    }
}
