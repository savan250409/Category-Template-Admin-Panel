<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DynamicPhotoFrameCategory extends Model
{
    use HasFactory;

    protected $table = 'dynamic_photo_frame_categories';

    protected $fillable = ['category_name', 'image', 'sort_order', 'status'];

    public function frames()
    {
        return $this->hasMany(DynamicPhotoFrame::class, 'dynamic_photo_frame_category_id');
    }
}
