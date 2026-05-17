<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doodle extends Model
{
    use HasFactory;

    protected $table = 'doodles';

    protected $fillable = ['name', 'image', 'doodle_type', 'is_premium', 'sort_order', 'status'];

    protected $casts = [
        'is_premium' => 'boolean',
        'status'     => 'boolean',
    ];
}
