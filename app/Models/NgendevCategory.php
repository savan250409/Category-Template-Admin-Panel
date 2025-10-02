<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NgendevCategory extends Model
{
    use HasFactory;

    protected $table = 'ngendev_categories';

    protected $fillable = ['category_name', 'category_image'];

    public function images()
    {
        return $this->hasMany(NgendevImage::class, 'category_id');
    }
}
