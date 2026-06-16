<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name'     => 'Админ',
                'lastname' => 'Системный',
                'tel'      => '+70000000000',
                'password' => Hash::make('123456789'),
                'role'     => 'admin',
            ]
        );
    }
}
