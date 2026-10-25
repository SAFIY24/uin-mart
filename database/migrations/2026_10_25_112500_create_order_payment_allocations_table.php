
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_payment_allocations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->enum('recipient_type', [
                'seller',
                'admin',
            ]);

            $table->unsignedBigInteger('recipient_id');

            $table->unsignedBigInteger('amount_due');

            $table->enum('status', [
                'pending',
                'paid',
            ])->default('pending');

            $table->enum('payment_method', [
                'cod',
                'bank_transfer',
            ]);

            $table->foreignId('confirmed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('confirmed_at')->nullable();

            $table->timestamps();

            $table->unique([
                'order_id',
                'recipient_type',
                'recipient_id',
            ], 'unique_order_payment_recipient');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_payment_allocations');
    }
};
