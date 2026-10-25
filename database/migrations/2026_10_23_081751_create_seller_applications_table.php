
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_applications', function (Blueprint $table) {
            $table->id();

            // Akun yang mendaftar sebagai penjual
            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            // Profil toko
            $table->string('shop_name', 150);
            $table->string('phone', 30);
            $table->text('description')->nullable();

            // Menunggu persetujuan admin
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'suspended',
            ])->default('pending');

            // Informasi verifikasi
            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_note')->nullable();

            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_applications');
    }
};
