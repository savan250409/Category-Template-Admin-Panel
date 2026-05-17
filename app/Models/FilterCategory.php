<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilterCategory extends Model
{
    use HasFactory;

    protected $table = 'filter_categories';

    protected $fillable = ['name', 'image', 'status', 'sort_order'];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function filters()
    {
        return $this->hasMany(Filter::class, 'filter_category_id');
    }
}
