
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambahkan seller_id ke setiap item pesanan.
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('seller_id')
                ->nullable()
                ->after('product_id');

            $table->index('seller_id');
        });

        // Isi seller_id pesanan lama berdasarkan pemilik produk saat ini.
        // Produk Admin tetap memiliki seller_id NULL.
        DB::table('order_items')
            ->join(
                'products',
                'order_items.product_id',
                '=',
                'products.id'
            )
            ->select(
                'order_items.id',
                'products.seller_id'
            )
            ->chunkById(
                100,
                function ($items) {
                    foreach ($items as $item) {
                        DB::table('order_items')
                            ->where('id', $item->id)
                            ->update([
                                'seller_id' => $item->seller_id,
                            ]);
                    }
                },
                'order_items.id',
                'id'
            );
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['seller_id']);
            $table->dropColumn('seller_id');
        });
    }
};
