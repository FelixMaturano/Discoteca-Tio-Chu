<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryMixerOption extends Model
{
    protected $fillable = [
        'category',
        'mixer_name',
        'product_id',
    ];

    public function mixerProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
