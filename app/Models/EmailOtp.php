<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailOtp extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','sent_to','code_hash','attempts','last_sent_at','expires_at'
    ];

    protected $casts = [
        'last_sent_at' => 'datetime',
        'expires_at' => 'datetime',
    ];
}
