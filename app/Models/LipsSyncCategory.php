<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LipsSyncCategory extends Model
{
    use HasFactory;

    protected $table = 'lips_sync_categories';

    protected $fillable = ['category_name', 'image', 'sort_order', 'status'];

    public function items()
    {
        return $this->hasMany(LipsSyncItem::class, 'lips_sync_category_id');
    }
}
