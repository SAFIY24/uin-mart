
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();

            // Tên chương trình
            $table->string('name', 150);

            // Mã voucher, ví dụ UINMART10
            $table->string('code', 50)->unique();

            // percentage hoặc fixed
            $table->string('type', 20)->default('percentage');

            // Mức giảm: 10 (%) hoặc 20000 (Rp)
            $table->decimal('value', 12, 2);

            // Giá trị đơn hàng tối thiểu
            $table->decimal('min_order_amount', 12, 2)
                ->default(0);

            // Số tiền giảm tối đa, có thể để trống
            $table->decimal('max_discount_amount', 12, 2)
                ->nullable();

            // Giới hạn tổng số lượt sử dụng
            $table->unsignedInteger('usage_limit')
                ->nullable();

            // Số lượt đã sử dụng
            $table->unsignedInteger('used_count')
                ->default(0);

            // Cho phép cộng dồn với Diskon Produk
            $table->boolean('is_stackable')
                ->default(true);

            // Trạng thái voucher
            $table->boolean('is_active')
                ->default(false);

            // Thời gian áp dụng
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();

            $table->timestamps();

            // Xóa mềm, có thể khôi phục
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
