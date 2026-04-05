<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopSliderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'top_slider_category_id',
        'prompt',
        'file_type',
        'file',
        'video_thumbnail',
        'status',
        'sort_order',
    ];

    public function category()
    {
        return $this->belongsTo(TopSliderCategory::class, 'top_slider_category_id');
    }
}
