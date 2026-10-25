
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_discounts', function (Blueprint $table) {
            $table->id();

            // Mỗi sản phẩm có một cấu hình giảm giá
            $table->foreignId('product_id')
                ->unique()
                ->constrained('products')
                ->cascadeOnDelete();

            // Giảm theo phần trăm
            $table->unsignedTinyInteger('percentage');

            // Bật hoặc tắt giảm giá
            $table->boolean('is_active')->default(false);

            // Thời gian áp dụng (có thể để trống)
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_discounts');
    }
};
