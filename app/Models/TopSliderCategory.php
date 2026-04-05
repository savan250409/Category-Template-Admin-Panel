<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopSliderCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'thumbnail_image',
        'title',
        'description',
        'file_type',
        'image',
        'video',
        'video_thumbnail',
        'top_slider_is_on',
        'status',
        'sort_order',
    ];
    public function items()
    {
        return $this->hasMany(TopSliderItem::class, 'top_slider_category_id');
    }
    
    public function originalImageCategory()
    {
        return $this->belongsTo(NgendevCategory::class, 'category_id', 'id');
    }

    public function originalVideoCategory()
    {
        return $this->belongsTo(NgendevVideoCategory::class, 'category_id', 'id');
    }
    
    public function getCategoryNameAttribute()
    {
        if ($this->file_type === 'image' && $this->originalImageCategory) {
            return $this->originalImageCategory->category_name;
        } elseif ($this->file_type === 'video' && $this->originalVideoCategory) {
            return $this->originalVideoCategory->category_name;
        }
        return 'Unknown';
    }
}
