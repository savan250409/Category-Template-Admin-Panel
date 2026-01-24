<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiBabyVideoModuleSetting extends Model
{
    use HasFactory;

    protected $table = 'ai_baby_video_module_setting';
    protected $fillable = ['model'];
}
