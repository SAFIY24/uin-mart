
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_order_fulfillments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                ->unique()
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->enum('status', [
                'pending',
                'handed_over',
                'completed',
            ])->default('pending');

            $table->timestamp('handed_over_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // Buat catatan untuk pesanan lama yang
        // memiliki produk yang dijual Admin.
        $orderIds = DB::table('order_items')
            ->whereNull('seller_id')
            ->distinct()
            ->pluck('order_id');

        foreach ($orderIds as $orderId) {
            DB::table('admin_order_fulfillments')->insert([
                'order_id' => $orderId,
                'status' => 'pending',
                'handed_over_at' => null,
                'completed_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_order_fulfillments');
    }
};
