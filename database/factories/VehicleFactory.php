<?php

namespace Database\Factories;

use App\Models\Brand; // Import model Brand
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr; // Untuk mengambil elemen random dari array
use Carbon\Carbon; // Untuk manipulasi tanggal

class VehicleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Vehicle::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Ambil ID brand yang sudah ada secara random
        // Pastikan BrandSeeder sudah dijalankan sebelumnya
        $brandIds = Brand::pluck('id')->toArray();
        if (empty($brandIds)) {
            // Jika tidak ada brand, buat satu brand dummy atau throw error
            // Untuk seeder, lebih baik pastikan BrandSeeder dijalankan dulu.
            // Sebagai fallback, kita bisa create brand di sini, tapi kurang ideal.
            // $brand = Brand::factory()->create();
            // $brandId = $brand->id;
            // Untuk contoh ini, kita asumsikan brand sudah ada.
            // Jika tidak, seeder ini akan gagal.
            // Anda bisa menambahkan pengecekan di DatabaseSeeder.php untuk urutan.
             throw new \Exception('No brands found. Please run BrandSeeder first.');
        }
        $brandId = Arr::random($brandIds);

        $vehicleTypes = ['motorcycle', 'car'];
        $availabilityStatuses = ['available', 'rented', 'inactive'];

        // Contoh data untuk specification_list (bisa berupa string dipisah koma, atau JSON string)
        $sampleSpecifications = [
            'Air Conditioner, Bluetooth, GPS Navigation, Automatic Transmission',
            'Leather Seats, Sunroof, Parking Sensors, Cruise Control',
            'ABS, EBD, Dual Airbags, Power Steering',
            'USB Port, Alloy Wheels, Fog Lamps',
        ];

        // Generate tahun antara 5 tahun lalu sampai tahun ini
        $year = Carbon::now()->subYears(rand(0, 5))->year;


        return [
            'brand_id' => $brandId,
            'vehicle_type' => Arr::random($vehicleTypes),
            'vehicle_name' => $this->faker->company() . ' ' . $this->faker->word(), // Contoh: "Abbott Inc Prius"
            'rental_price' => $this->faker->numberBetween(200000, 2000000), // Harga sewa per hari
            'availability_status' => Arr::random($availabilityStatuses),
            // 'year' di migrasi adalah dateTime, kita akan set ke awal tahun dari tahun random
            'year' => Carbon::createFromDate($year, rand(1,12), rand(1,28))->startOfDay(),
            'seats' => $this->faker->numberBetween(2, 7),
            'horse_power' => $this->faker->numberBetween(80, 500),
            'description' => $this->faker->sentence(10), // Deskripsi singkat
            'specification_list' => Arr::random($sampleSpecifications),
            // Gunakan URL placeholder untuk gambar, atau null
            'public_url_image' => $this->faker->imageUrl(640, 480, 'transport', true), // Contoh URL gambar dummy
            'secure_url_image' => $this->faker->imageUrl(640, 480, 'transport', true), // Bisa sama
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
