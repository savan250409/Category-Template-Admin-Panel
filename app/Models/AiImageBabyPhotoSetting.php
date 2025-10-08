<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiImageBabyPhotoSetting extends Model
{
    use HasFactory;

    protected $table = 'ai_image_baby_photo_setting';
    protected $fillable = ['model'];
}
