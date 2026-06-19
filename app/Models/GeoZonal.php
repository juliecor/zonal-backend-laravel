<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeoZonal extends Model
{
    protected $table = 'geo_zonal';

    protected $fillable = [
        'address_key',
        'lat',
        'lon',
        'label',
        'value_per_sqm',
        'classification_code',
        'province',
        'city_municipality',
        'barangay',
        'street_location',
        'source',
        'geocoded_at',
    ];

    protected $casts = [
        'lat' => 'float',
        'lon' => 'float',
        'value_per_sqm' => 'float',
        'geocoded_at' => 'datetime',
    ];
}
