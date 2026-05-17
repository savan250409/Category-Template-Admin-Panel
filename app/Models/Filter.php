<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Filter extends Model
{
    use HasFactory;

    protected $table = 'filters';

    protected $fillable = [
        'filter_category_id', 'name', 'is_premium',
        'saturation', 'brightness', 'contrast',
        'red', 'green', 'blue',
        'sort_order', 'status',
    ];

    protected $casts = [
        'is_premium' => 'boolean',
        'status'     => 'boolean',
        'saturation' => 'float',
        'brightness' => 'float',
        'contrast'   => 'float',
        'red'        => 'float',
        'green'      => 'float',
        'blue'       => 'float',
    ];

    public function category()
    {
        return $this->belongsTo(FilterCategory::class, 'filter_category_id');
    }
}
