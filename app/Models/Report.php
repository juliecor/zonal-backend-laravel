<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'street',
        'barangay',
        'city',
        'province',
        'zonal_value',
        'sqm',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'sqm' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
