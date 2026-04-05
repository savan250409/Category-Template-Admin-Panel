<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilterAiImage extends Model
{
    use HasFactory;

    protected $table = 'filter_ai_images';

    protected $fillable = [
        'category_id',
        'name',
        'ai_prompt',
        'ai_model',
        'image_path',
        'no_of_image',
        'name_change',
        'sort_order'
    ];

    public function category()
    {
        return $this->belongsTo(FilterAiImageCategory::class, 'category_id');
    }
}
