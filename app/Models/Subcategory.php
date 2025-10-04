<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subcategory extends Model
{
    protected $fillable = ['category_name', 'title', 'image', 'description', 'sub_category_thumbnail_image'];
    use HasFactory;
}
