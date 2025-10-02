<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NgendevImage extends Model
{
    use HasFactory;

    protected $table = 'ngendev_images';

    protected $fillable = ['category_id', 'image_path', 'ai_prompt', 'ai_model'];

    public function category()
    {
        return $this->belongsTo(NgendevCategory::class, 'category_id');
    }
}
