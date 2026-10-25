<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductDiscount;

class ProductPricing
{
    public function originalPrice(Product $product): float
    {
        return max(0, (float) $product->price);
    }

    public function discountPercentage(Product $product): int
    {
        // Uu tien quan he discount da duoc eager load.
        if ($product->relationLoaded('discount')) {
            $discount = $product->getRelation('discount');
        } else {
            $discount = ProductDiscount::where(
                'product_id',
                $product->id
            )->first();
        }

        if (!$discount || !$discount->isCurrentlyActive()) {
            return 0;
        }

        // Khong cho phep phan tram vuot qua 100.
        return max(0, min(100, (int) $discount->percentage));
    }

    public function finalPrice(Product $product): float
    {
        $original = $this->originalPrice($product);
        $percentage = $this->discountPercentage($product);

        $final = $original * (100 - $percentage) / 100;

        return round(max(0, $final), 2);
    }

    public function discountAmount(Product $product): float
    {
        return round(
            max(
                0,
                $this->originalPrice($product)
                - $this->finalPrice($product)
            ),
            2
        );
    }
}
