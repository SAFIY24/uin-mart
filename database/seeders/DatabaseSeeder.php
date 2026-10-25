<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Danh mục khởi tạo (không xóa dữ liệu đã có).
        $categories = [
            'Elektronik & Aksesoris',
            'Fashion Mahasiswa',
            'Buku & Alat Tulis',
            'Perlengkapan Kuliah',
            'Kebutuhan Kos',
            'Makanan & Minuman',
            'Olahraga',
            'Lainnya',
        ];

        foreach ($categories as $name) {
            DB::table('categories')->updateOrInsert(
                ['name' => $name],
                ['updated_at' => now(), 'created_at' => now()]
            );
        }

        // Tidak membuat akun admin dengan kata sandi mặc định vì alasan keamanan.
        // Đăng ký tài khoản trên website, sau đó đặt role='admin' trong phpMyAdmin.
    }
}
