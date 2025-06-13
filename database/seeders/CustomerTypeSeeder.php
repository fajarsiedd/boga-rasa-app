<?php

namespace Database\Seeders;

use App\Models\CustomerType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CustomerType::create([
            'name' => 'Pedagang',
            'discount' => 300,
        ]);

        CustomerType::create([
            'name' => 'Pelanggan',
            'discount' => 200,
        ]);

        CustomerType::create([
            'name' => 'Umum',
            'discount' => 0,
        ]);
    }
}
