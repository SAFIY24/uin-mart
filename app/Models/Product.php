<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'stock',
        'image',
        'status',
    ];

    // Quan hệ với danh mục sản phẩm
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // Mỗi sản phẩm có tối đa một cấu hình giảm giá
    public function discount(): HasOne
    {
        return $this->hasOne(ProductDiscount::class);
    }

    // Quan hệ với người bán sở hữu sản phẩm
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
    
    // Maksimal tiga foto tambahan produk
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)
            ->orderBy('sort_order');
    }

}
