<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'     => 'Александр',
                'lastname' => 'Петров',
                'tel'      => '+79001234567',
                'email'    => 'user1@gmail.com',
                'password' => Hash::make('123456789'),
                'role'     => 'user',
            ],
            [
                'name'     => 'Мария',
                'lastname' => 'Иванова',
                'tel'      => '+79007654321',
                'email'    => 'user2@gmail.com',
                'password' => Hash::make('123456789'),
                'role'     => 'user',
            ],
            [
                'name'     => 'Дмитрий',
                'lastname' => 'Сидоров',
                'tel'      => '+79009876543',
                'email'    => 'user3@gmail.com',
                'password' => Hash::make('123456789'),
                'role'     => 'user',
            ],
        ];

        foreach ($users as $data) {
            User::firstOrCreate(['email' => $data['email']], $data);
        }
    }
}
