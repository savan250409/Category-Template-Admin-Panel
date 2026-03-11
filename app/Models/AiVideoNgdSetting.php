<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiVideoNgdSetting extends Model
{
    use HasFactory;

    protected $table = 'ai_video_ngd_settings';
    protected $fillable = ['model', 'couple_active'];
}
