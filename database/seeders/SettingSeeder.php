<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            'store_name' => 'POS KASIR MU',
            'store_address' => 'Jl. Merdeka No. 123, Jakarta',
            'store_phone' => '081234567890',
            'receipt_footer' => 'Terima kasih atas kunjungan Anda!',
        ];

        foreach ($settings as $key => $value) {
            \App\Models\Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
