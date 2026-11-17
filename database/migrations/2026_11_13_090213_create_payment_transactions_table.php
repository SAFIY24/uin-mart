
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();

            // Relasi ke pesanan UIN Mart
            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            // Identitas Payment Gateway
            $table->string('provider', 30)->default('midtrans');

            // Nomor transaksi dari UIN Mart
            $table->string('provider_order_id')->unique();

            // ID transaksi dari Midtrans
            $table->string('provider_transaction_id')
                ->nullable()
                ->unique();

            // Jenis pembayaran
            $table->string('payment_type', 30)->default('qris');

            // Status transaksi dari Payment Gateway
            $table->string('status', 30)->default('pending');

            // Nominal pembayaran dalam Rupiah
            $table->unsignedBigInteger('gross_amount');

            $table->string('currency', 3)->default('IDR');

            // URL gambar QRIS
            $table->text('qr_url')->nullable();

            // Masa berlaku QRIS
            $table->timestamp('expires_at')->nullable();

            // Waktu pembayaran berhasil
            $table->timestamp('paid_at')->nullable();

            // Respons transaksi dari Midtrans
            $table->json('response_data')->nullable();

            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
