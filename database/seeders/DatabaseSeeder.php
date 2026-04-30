<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Event;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Пользователи
        $users = User::factory()->count(10)->create();

        // 2. Категории
        $categories = [
            'Концерты',
            'Вечеринки',
            'Выставки',
            'Спорт',
            'Театр',
            'Кино'
        ];

        foreach ($categories as $name) {
            Category::create(['name' => $name]);
        }

        $categories = Category::all();

        // 3. События
        $events = [
            [
                'name' => 'Концерт в центре',
                'address' => 'Челябинск, ул. Ленина 20',
                'lat' => 55.1600,
                'lng' => 61.4020,
            ],
            [
                'name' => 'Выставка искусства',
                'address' => 'Челябинск, ул. Кирова 15',
                'lat' => 55.1625,
                'lng' => 61.3950,
            ],
            [
                'name' => 'Ночной клуб',
                'address' => 'Челябинск, ул. Труда 50',
                'lat' => 55.1550,
                'lng' => 61.4100,
            ],
            [
                'name' => 'Футбольный матч',
                'address' => 'Челябинск, стадион Центральный',
                'lat' => 55.1480,
                'lng' => 61.3900,
            ],
        ];

        foreach ($events as $event) {
            Event::create([
                'user_id' => $users->random()->id,
                'category_id' => $categories->random()->id,

                'name' => $event['name'],
                'event_date' => now()->addDays(rand(1, 30)),
                'age' => ['0+', '6+', '12+', '16+', '18+'][rand(0, 4)],
                'description' => 'Описание мероприятия: ' . $event['name'],
                'price' => rand(0, 2000),
                'address' => $event['address'],

                'lat' => $event['lat'],
                'lng' => $event['lng'],
            ]);
        }

        // дополнительно случайные события
        Event::factory()->count(10)->create([
            'user_id' => $users->random()->id,
            'category_id' => $categories->random()->id,
        ]);
    }
}