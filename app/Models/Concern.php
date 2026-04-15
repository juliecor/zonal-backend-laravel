<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Concern extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'category', 'subject', 'message', 'status', 'attachment_path', 'resolution_path', 'resolution_note',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
