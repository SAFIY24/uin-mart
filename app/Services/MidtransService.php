<?php

namespace App\Services;

use App\Models\Order;
use Midtrans\Config;
use Midtrans\CoreApi;
use RuntimeException;

class MidtransService
{
    /**
     * Mengatur konfigurasi Midtrans.
     */
    public function __construct()
    {
        $serverKey = config('services.midtrans.server_key');

        if (empty($serverKey)) {
            throw new RuntimeException(
                'Midtrans Server Key belum dikonfigurasi.'
            );
        }

        Config::$serverKey = $serverKey;

        Config::$isProduction = (bool) config(
            'services.midtrans.is_production',
            false
        );

        Config::$isSanitized = true;
    }

    /**
     * Membuat transaksi QRIS melalui Midtrans Core API.
     */
    public function createQris(Order $order): array
    {
        // Pastikan nominal pembayaran berupa Rupiah bulat.
        $total = (string) $order->getRawOriginal('total');

        if (!preg_match('/^\d+(?:\.0{1,2})?$/', $total)) {
            throw new RuntimeException(
                'Nominal pembayaran harus berupa Rupiah bulat.'
            );
        }

        $amount = (int) explode('.', $total)[0];

        if ($amount < 1) {
            throw new RuntimeException(
                'Nominal pembayaran tidak valid.'
            );
        }

        // Data transaksi yang dikirim ke Midtrans.
        $params = [
            'payment_type' => 'qris',

            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => $amount,
            ],

            'qris' => [
                'acquirer' => 'gopay',
            ],
        ];

        // Meminta Midtrans membuat transaksi QRIS.
        $response = CoreApi::charge($params);
        if (!is_object($response)) {
            throw new RuntimeException(
                'Respons Midtrans tidak valid.'
            );
        }

        // Memeriksa respons transaksi.
        if (
            empty($response->transaction_id) ||
            empty($response->order_id)
        ) {
            throw new RuntimeException(
                'Respons transaksi QRIS dari Midtrans tidak lengkap.'
            );
        }

        // Mengambil URL gambar QR Code.
        $qrUrl = null;

        foreach ($response->actions ?? [] as $action) {
            if (($action->name ?? '') === 'generate-qr-code-v2') {
                $qrUrl = $action->url ?? null;
                break;
            }

            if (
                ($action->name ?? '') === 'generate-qr-code' &&
                empty($qrUrl)
            ) {
                $qrUrl = $action->url ?? null;
            }
        }

        if (empty($qrUrl)) {
            throw new RuntimeException(
                'URL QR Code tidak ditemukan pada respons Midtrans.'
            );
        }

        // Mengubah respons SDK menjadi array agar mudah disimpan.
        $responseData = json_decode(
            json_encode($response),
            true
        );

        return [
            'order_id' => $response->order_id,
            'transaction_id' => $response->transaction_id,
            'payment_type' => $response->payment_type ?? 'qris',
            'transaction_status' => $response->transaction_status ?? 'pending',
            'gross_amount' => $response->gross_amount ?? $amount,
            'currency' => $response->currency ?? 'IDR',
            'qr_url' => $qrUrl,
            'transaction_time' => $response->transaction_time ?? null,
            'response_data' => $responseData,
        ];
    }
}
