<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DynamicPhotoFrame extends Model
{
    use HasFactory;

    protected $table = 'dynamic_photo_frames';

    protected $fillable = [
        'dynamic_photo_frame_category_id',
        'zip_file',
        'input_count',
        'thumbnail',
        'sort_order',
    ];

    public function category()
    {
        return $this->belongsTo(DynamicPhotoFrameCategory::class, 'dynamic_photo_frame_category_id');
    }
}
