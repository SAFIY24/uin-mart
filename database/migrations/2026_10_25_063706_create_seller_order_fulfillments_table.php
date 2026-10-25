
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_order_fulfillments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->foreignId('seller_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->enum('delivery_method', [
                'pickup',
                'seller_delivery',
            ])->nullable();

            $table->enum('status', [
                'pending',
                'confirmed',
                'preparing',
                'ready',
                'delivering',
                'handed_over',
                'completed',
                'cancelled',
            ])->default('pending');

            $table->text('delivery_address')->nullable();
            $table->string('pickup_location')->nullable();

            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('handed_over_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->unique(['order_id', 'seller_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_order_fulfillments');
    }
};
