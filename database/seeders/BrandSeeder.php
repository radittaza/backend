<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand; // Import model Brand
use Illuminate\Support\Facades\DB; // Untuk transaction atau truncate jika perlu

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Kosongkan tabel brands terlebih dahulu jika seeder dijalankan ulang (opsional)
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // Nonaktifkan cek foreign key sementara jika ada relasi
        // Brand::truncate();
        // DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // Aktifkan kembali

        $brands = [
            [
                'brand_name' => 'Toyota',
                // Untuk 'public_url_image' dan 'secure_url_image',
                // kamu bisa set null atau path ke gambar default jika ada.
                // Untuk seeder, biasanya kita tidak meng-handle upload file fisik.
                'public_url_image' => null,
                'secure_url_image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'brand_name' => 'Honda',
                'public_url_image' => null,
                'secure_url_image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'brand_name' => 'Mitsubishi',
                'public_url_image' => null,
                'secure_url_image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'brand_name' => 'Suzuki',
                'public_url_image' => null,
                'secure_url_image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'brand_name' => 'Daihatsu',
                'public_url_image' => null,
                'secure_url_image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Tambahkan brand lain sesuai kebutuhan
        ];

        foreach ($brands as $brand) {
            Brand::create($brand);
        }

        // Atau jika ingin menggunakan factory:
        // Brand::factory()->count(5)->create();
    }
}
