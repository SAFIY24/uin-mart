
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_payment_allocations', function (Blueprint $table) {
            $table->enum('payment_method', [
                'cod',
                'bank_transfer',
                'qris',
            ])->change();
        });
    }

    public function down(): void
    {
        // Jangan menghapus dukungan QRIS jika masih ada
        // data alokasi pembayaran yang menggunakan QRIS.
        $hasQris = DB::table('order_payment_allocations')
            ->where('payment_method', 'qris')
            ->exists();

        if ($hasQris) {
            throw new RuntimeException(
                'Migration tidak dapat dibatalkan karena masih ada data pembayaran QRIS.'
            );
        }

        Schema::table('order_payment_allocations', function (Blueprint $table) {
            $table->enum('payment_method', [
                'cod',
                'bank_transfer',
            ])->change();
        });
    }
};
