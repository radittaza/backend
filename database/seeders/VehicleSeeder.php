<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehicle; // Import model Vehicle
use App\Models\Brand;   // Import model Brand untuk pengecekan
use Illuminate\Support\Facades\DB;


class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Opsional: Kosongkan tabel vehicles terlebih dahulu
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // Vehicle::truncate();
        // DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Pastikan ada Brand sebelum membuat Vehicle
        if (Brand::count() == 0) {
            $this->command->info('No brands found, running BrandSeeder first...');
            $this->call(BrandSeeder::class); // Jalankan BrandSeeder jika belum ada brand
        }

        // Buat, misalnya, 20 data vehicle dummy menggunakan factory
        Vehicle::factory()->count(20)->create();

        $this->command->info('Vehicle seeder finished.');
    }
}
