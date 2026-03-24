<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZonalValue extends Model
{
    protected $table = 'zonal_values';

    // Uncomment if your table has no timestamps columns
    // public $timestamps = false;

    protected $fillable = [
        'province',
        'city_municipality',
        'barangay',
        'street_location',
        'vicinity',
        'classification_code',
        'value_per_sqm',
    ];
}
