<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiVideoSubcategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_name',
        'category_thumbnail_image',
        'title',
        'videos',
        'description',
        'name_change',
        'trending'
    ];
}
