<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Pemilik Usaha',
            'username' => 'browner',
            'password' => Hash::make('12345678'),
            'role' => 'owner',
        ]);

        User::create([
            'name' => 'Bag. Keuangan',
            'username' => 'brfinance',
            'password' => Hash::make('12345678'),
            'role' => 'finance',
        ]);

        User::create([
            'name' => 'Bag. Administrasi',
            'username' => 'bradmin',
            'password' => Hash::make('12345678'),
            'role' => 'admin',
        ]);
    }
}
