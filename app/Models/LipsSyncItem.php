<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LipsSyncItem extends Model
{
    use HasFactory;

    protected $table = 'lips_sync_items';

    protected $fillable = [
        'lips_sync_category_id',
        'title',
        'song',
        'video',
        'video_thumbnail',
        'sort_order',
    ];

    public function category()
    {
        return $this->belongsTo(LipsSyncCategory::class, 'lips_sync_category_id');
    }
}
