<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiBabyVideo extends Model
{
    use HasFactory;

    protected $table = 'ai_baby_videos';

    protected $fillable = [
        'category_id',
        'video_title',
        'video_path',
        'video_thumbnail',
        'ai_prompt',
        'name_change'
    ];

    public function category()
    {
        return $this->belongsTo(AiVideoCategory::class, 'category_id');
    }
}
