<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BankTransfer; // Import model BankTransfer
use App\Models\User; // Import model User untuk mengambil admin
use Illuminate\Support\Facades\DB;

class BankTransferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Kosongkan tabel bank_transfers terlebih dahulu (opsional)
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // BankTransfer::truncate();
        // DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Cari user admin pertama (asumsi email admin unik atau username unik)
        $adminUser = User::where('role', 'user')->first();

        if (!$adminUser) {
            $this->command->error('Admin user not found. Please run UserSeeder first or create an admin user.');
            return;
        }

        $banks = [
            [
                'user_id' => $adminUser->id,
                'name_bank' => 'BCA (Bank Central Asia)',
                'number' => '1234567890', // Ganti dengan nomor rekening valid
                // createdAt dan updatedAt akan diisi otomatis oleh model karena kita definisikan const di sana
            ],
            [
                'user_id' => $adminUser->id,
                'name_bank' => 'Mandiri',
                'number' => '0987654321', // Ganti dengan nomor rekening valid
            ],
            [
                'user_id' => $adminUser->id,
                'name_bank' => 'BNI (Bank Negara Indonesia)',
                'number' => '1122334455', // Ganti dengan nomor rekening valid
            ],
            [
                'user_id' => $adminUser->id,
                'name_bank' => 'BRI (Bank Rakyat Indonesia)',
                'number' => '5544332211', // Ganti dengan nomor rekening valid
            ],
        ];

        foreach ($banks as $bank) {
            // Karena model BankTransfer menggunakan createdAt dan updatedAt,
            // Eloquent akan mengisinya secara otomatis jika $timestamps = true (default)
            // atau jika kita tidak set $timestamps = false dan nama kolom sesuai default (created_at, updated_at)
            // Dalam kasus kita, karena nama kolomnya createdAt dan updatedAt,
            // dan kita sudah set const di model, Eloquent akan handle.
            // Jika tidak, kita bisa set manual:
            // $bank['createdAt'] = now();
            // $bank['updatedAt'] = now();
            BankTransfer::create($bank);
        }
    }
}
