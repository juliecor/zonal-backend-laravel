<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacetCache extends Model
{
    protected $table = 'facet_cache';

    protected $fillable = [
        'cache_key',
        'payload',
        'refreshed_at',
    ];

    protected $casts = [
        'refreshed_at' => 'datetime',
    ];
}
