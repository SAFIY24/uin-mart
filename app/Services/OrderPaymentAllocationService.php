<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderPaymentAllocationService
{
    /**
     * Membuat pembagian pembayaran untuk setiap penerima.
     *
     * Jalankan setelah order_items dibuat.
     * Aman dipanggil ulang: tidak menimpa data yang sudah ada.
     */
    public function createForOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $lockedOrder = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Jangan mengubah alokasi yang sudah dibuat.
            $alreadyExists = DB::table('order_payment_allocations')
                ->where('order_id', $lockedOrder->id)
                ->exists();

            if ($alreadyExists) {
                return;
            }

            $items = DB::table('order_items')
                ->where('order_id', $lockedOrder->id)
                ->orderBy('id')
                ->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'payment' => 'Pesanan tidak memiliki produk.',
                ]);
            }

            $groups = [];

            foreach ($items as $item) {
                $sellerId = $item->seller_id;

                $type = $sellerId === null
                    ? 'admin'
                    : 'seller';

                $recipientId = $sellerId === null
                    ? 0
                    : (int) $sellerId;

                $key = $type . ':' . $recipientId;

                $subtotal = $this->rupiah($item->subtotal);

                if (!isset($groups[$key])) {
                    $groups[$key] = [
                        'recipient_type' => $type,
                        'recipient_id' => $recipientId,
                        'subtotal' => 0,
                        'amount_due' => 0,
                        'remainder' => 0,
                    ];
                }

                $groups[$key]['subtotal'] += $subtotal;
            }

            $subtotal = array_sum(
                array_column($groups, 'subtotal')
            );

            $orderSubtotal = $this->rupiah(
                $lockedOrder->subtotal
            );

            $total = $this->rupiah(
                $lockedOrder->total
            );

            if (
                $subtotal !== $orderSubtotal ||
                $total > $subtotal
            ) {
                throw ValidationException::withMessages([
                    'payment' => 'Nilai pesanan tidak cocok dengan detail produk.',
                ]);
            }

            $discount = $subtotal - $total;

            // Bagi diskon secara proporsional.
            $allocatedDiscount = 0;

            foreach ($groups as $key => $group) {
                $numerator = $discount * $group['subtotal'];

                $baseDiscount = $subtotal > 0
                    ? intdiv($numerator, $subtotal)
                    : 0;

                $remainder = $subtotal > 0
                    ? $numerator % $subtotal
                    : 0;

                $groups[$key]['amount_due'] =
                    $group['subtotal'] - $baseDiscount;

                $groups[$key]['remainder'] = $remainder;

                $allocatedDiscount += $baseDiscount;
            }

            // Bagikan sisa Rupiah akibat pembulatan.
            $remaining = $discount - $allocatedDiscount;

            $keys = array_keys($groups);

            usort($keys, function ($a, $b) use ($groups) {
                $compare = $groups[$b]['remainder']
                    <=> $groups[$a]['remainder'];

                return $compare !== 0
                    ? $compare
                    : strcmp($a, $b);
            });

            foreach ($keys as $key) {
                if ($remaining <= 0) {
                    break;
                }

                if ($groups[$key]['amount_due'] > 0) {
                    $groups[$key]['amount_due']--;
                    $remaining--;
                }
            }

            if (
                $remaining !== 0 ||
                array_sum(array_column($groups, 'amount_due')) !== $total
            ) {
                throw ValidationException::withMessages([
                    'payment' => 'Pembagian pembayaran tidak seimbang.',
                ]);
            }

            $paymentMethod = $lockedOrder->payment_method;

            if (!in_array(
                $paymentMethod,
                ['cod', 'bank_transfer'],
                true
            )) {
                throw ValidationException::withMessages([
                    'payment' => 'Metode pembayaran tidak didukung.',
                ]);
            }

            $now = now();
            $rows = [];

            foreach ($groups as $group) {
                $rows[] = [
                    'order_id' => $lockedOrder->id,
                    'recipient_type' => $group['recipient_type'],
                    'recipient_id' => $group['recipient_id'],
                    'amount_due' => $group['amount_due'],
                    'status' => 'pending',
                    'payment_method' => $paymentMethod,
                    'confirmed_by' => null,
                    'confirmed_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('order_payment_allocations')
                ->insert($rows);
        });
    }

    /**
     * Konversi nilai DECIMAL menjadi Rupiah integer.
     * Nilai pecahan Rupiah ditolak.
     */
    private function rupiah(mixed $value): int
    {
        $raw = trim((string) $value);

        if (!preg_match(
            '/^\d+(?:\.\d+)?$/',
            $raw
        )) {
            throw new \InvalidArgumentException(
                'Format nilai Rupiah tidak valid.'
            );
        }

        [$whole, $fraction] = array_pad(
            explode('.', $raw, 2),
            2,
            ''
        );

        if (
            trim($fraction, '0') !== '' ||
            strlen($whole) > 15
        ) {
            throw new \InvalidArgumentException(
                'Nilai Rupiah harus bilangan bulat yang valid.'
            );
        }

        return (int) $whole;
    }
}
