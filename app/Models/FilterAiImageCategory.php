<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilterAiImageCategory extends Model
{
    use HasFactory;

    protected $table = 'filter_ai_image_categories';

    protected $fillable = ['category_name', 'category_image', 'sort_order', 'status', 'type'];

    public function images()
    {
        return $this->hasMany(FilterAiImage::class, 'category_id');
    }
}
