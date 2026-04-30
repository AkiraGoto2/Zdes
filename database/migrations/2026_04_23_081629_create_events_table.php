<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 10 пользователей
        $users = User::factory()->count(10)->create()->each(function ($user) {
            $user->update([
                'lastname' => fake()->lastName(),
                'tel' => fake()->phoneNumber(),
                'password' => Hash::make('password'),
            ]);
        });

        // тестовый пользователь
        $testUser = User::create([
            'name' => 'Test',
            'lastname' => 'User',
            'tel' => '+79999999999',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // события
        $events = [
            [
                'name' => 'Концерт в парке',
                'address' => 'Челябинск, Парк Гагарина',
                'lat' => 55.1602,
                'lng' => 61.4021,
            ],
            [
                'name' => 'Выставка искусства',
                'address' => 'Челябинск, ул. Ленина 45',
                'lat' => 55.1599,
                'lng' => 61.3950,
            ],
            [
                'name' => 'Фестиваль еды',
                'address' => 'Челябинск, ТРК Горки',
                'lat' => 55.1735,
                'lng' => 61.3982,
            ],
            [
                'name' => 'Открытый кинотеатр',
                'address' => 'Челябинск, набережная',
                'lat' => 55.1650,
                'lng' => 61.4100,
            ],
            [
                'name' => 'Йога на свежем воздухе',
                'address' => 'Челябинск, парк Победы',
                'lat' => 55.1801,
                'lng' => 61.3899,
            ],
        ];

        foreach ($events as $event) {
            Event::create([
                'user_id' => $testUser->id,
                'category_id' => 1, // если нет — создай категорию!
                'name' => $event['name'],
                'event_date' => now()->addDays(rand(1, 10)),
                'age' => ['0+', '6+', '12+', '16+', '18+'][rand(0, 4)],
                'description' => fake()->text(120),
                'price' => rand(0, 2000),
                'address' => $event['address'],
                'lat' => $event['lat'],
                'lng' => $event['lng'],
            ]);
        }
    }
}