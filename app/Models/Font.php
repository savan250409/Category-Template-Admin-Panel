<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Font extends Model
{
    use HasFactory;

    protected $table = 'fonts';

    protected $fillable = ['font_name', 'font_file', 'preview_image', 'is_premium', 'sort_order', 'status'];

    protected $casts = [
        'is_premium' => 'boolean',
        'status'     => 'boolean',
    ];
}
