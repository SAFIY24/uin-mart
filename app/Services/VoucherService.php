<?php

namespace App\Services;

use App\Models\Promotion;
use Illuminate\Validation\ValidationException;

class VoucherService
{
    public function calculate(
        string $code,
        float $subtotal,
        bool $hasProductDiscount = false
    ): array {
        $code = strtoupper(trim($code));

        $voucher = Promotion::query()
            ->where('code', $code)
            ->first();

        if (!$voucher || !$voucher->isCurrentlyActive()) {
            throw ValidationException::withMessages([
                'promotion_code' => 'Voucher tidak valid, tidak aktif, atau sudah kedaluwarsa.',
            ]);
        }

        if (!$voucher->is_stackable && $hasProductDiscount) {
            throw ValidationException::withMessages([
                'promotion_code' => 'Voucher tidak dapat digabung dengan diskon produk.',
            ]);
        }

        if ($subtotal < (float) $voucher->min_order_amount) {
            throw ValidationException::withMessages([
                'promotion_code' => 'Minimum belanja untuk voucher ini belum terpenuhi.',
            ]);
        }

        if ($voucher->type === 'percentage') {
            $discount = $subtotal * ((float) $voucher->value / 100);
        } elseif ($voucher->type === 'fixed') {
            $discount = (float) $voucher->value;
        } else {
            throw ValidationException::withMessages([
                'promotion_code' => 'Jenis voucher tidak dikenal.',
            ]);
        }

        if ($voucher->max_discount_amount !== null) {
            $discount = min(
                $discount,
                (float) $voucher->max_discount_amount
            );
        }

        $discount = round(min($discount, $subtotal), 2);

        return [
            'voucher' => $voucher,
            'discount' => $discount,
            'total' => round(max(0, $subtotal - $discount), 2),
        ];
    }
}
