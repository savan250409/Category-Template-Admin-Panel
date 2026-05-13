<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StickerCategory extends Model
{
    use HasFactory;

    protected $table = 'sticker_categories';

    protected $fillable = ['category_name', 'image', 'stickers', 'is_premium', 'sort_order', 'status'];

    protected $casts = [
        'stickers'   => 'array',
        'is_premium' => 'boolean',
        'status'     => 'boolean',
    ];

    public function getStickersListAttribute(): array
    {
        return is_array($this->stickers) ? array_values($this->stickers) : [];
    }
}
