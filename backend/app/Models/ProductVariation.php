<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariation extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'attributes',
        'stock',
        'price',
        'sku',
        'is_active',
    ];

    protected $casts = [
        'attributes' => 'array',
        'is_active' => 'boolean',
        'stock' => 'integer',
        'price' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

