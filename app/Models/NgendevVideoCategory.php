<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NgendevVideoCategory extends Model
{
    use HasFactory;

    protected $table = 'ngendev_video_categories';

    protected $fillable = ['category_name', 'category_image', 'sort_order', 'status', 'type'];

    public function videos()
    {
        return $this->hasMany(NgendevVideo::class, 'category_id');
    }
}
