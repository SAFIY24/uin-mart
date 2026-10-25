
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('subtotal', 14, 2)
                ->nullable();

            $table->decimal('discount_amount', 14, 2)
                ->default(0);

            $table->foreignId('promotion_id')
                ->nullable()
                ->constrained('promotions')
                ->nullOnDelete();

            $table->string('promotion_code', 50)
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('promotion_id');

            $table->dropColumn([
                'subtotal',
                'discount_amount',
                'promotion_code',
            ]);
        });
    }
};
