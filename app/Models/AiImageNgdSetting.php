<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiImageNgdSetting extends Model
{
    use HasFactory;

    protected $table = 'ai_image_ngd_setting';
    protected $fillable = ['model'];
}
