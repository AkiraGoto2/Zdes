<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Event;
use App\Models\Category;
use Illuminate\Support\Facades\Hash;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        // Создаём пользователей
        $user1 = User::firstOrCreate(
            ['email' => 'ivan@example.com'],
            [
                'name'     => 'Иван',
                'lastname' => 'Петров',
                'tel'      => '+79001234567',
                'password' => Hash::make('password'),
            ]
        );

        $user2 = User::firstOrCreate(
            ['email' => 'anna@example.com'],
            [
                'name'     => 'Анна',
                'lastname' => 'Смирнова',
                'tel'      => '+79007654321',
                'password' => Hash::make('password'),
            ]
        );

        // Категории
        $music   = Category::where('name', 'Музыка и концерты')->first();
        $sport   = Category::where('name', 'Спорт и активный отдых')->first();
        $art     = Category::where('name', 'Выставки и галереи')->first();
        $party   = Category::where('name', 'Вечеринки и клубы')->first();
        $lecture = Category::where('name', 'Лекции и мастер-классы')->first();
        $food    = Category::where('name', 'Еда и напитки')->first();

        $events = [
            [
                'user_id'     => $user1->id,
                'category_id' => $music?->id ?? 1,
                'name'        => 'Techno Rooftop: Summer Session',
                'description' => 'Легендарная серия вечеринок на крыше возвращается! Resident DJ и специальный гость из Берлина. Панорамный вид на город, атмосфера и незабываемый звук.',
                'event_date'  => now()->addDays(2)->setTime(22, 0),
                'age'         => '18+',
                'price'       => 1200,
                'price_to'    => null,
                'address'     => 'Челябинск, ул. Кирова, 142 (крыша)',
                'lat'         => 55.1596,
                'lng'         => 61.4023,
                'status'      => 'approved',
            ],
            [
                'user_id'     => $user1->id,
                'category_id' => $sport?->id ?? 2,
                'name'        => 'Sunrise Yoga Flow в Горпарке',
                'description' => 'Утренняя практика йоги на свежем воздухе для всех уровней. Инструктор Мария проведёт вас через приветствие солнца и медитацию. Коврик и вода в наличии.',
                'event_date'  => now()->addDays(1)->setTime(7, 0),
                'age'         => '0+',
                'price'       => 0,
                'price_to'    => null,
                'address'     => 'Челябинск, Городской бор, главный вход',
                'lat'         => 55.1672,
                'lng'         => 61.3891,
                'status'      => 'approved',
            ],
            [
                'user_id'     => $user2->id,
                'category_id' => $art?->id ?? 3,
                'name'        => 'Выставка «Уральский авангард»',
                'description' => 'Масштабная выставка современных уральских художников. Живопись, скульптура, инсталляции. Более 80 работ от 30 авторов. Экскурсии по расписанию.',
                'event_date'  => now()->addDays(5)->setTime(12, 0),
                'age'         => '6+',
                'price'       => 300,
                'price_to'    => 500,
                'address'     => 'Челябинск, ул. Труда, 92, Музей изобразительных искусств',
                'lat'         => 55.1553,
                'lng'         => 61.4002,
                'status'      => 'approved',
            ],
            [
                'user_id'     => $user2->id,
                'category_id' => $party?->id ?? 4,
                'name'        => 'Creative Startup Meet',
                'description' => 'Неформальная встреча стартаперов, дизайнеров и разработчиков Челябинска. Питчинг, нетворкинг, хорошая еда. Приходи с идеей или просто познакомиться.',
                'event_date'  => now()->addDays(3)->setTime(19, 0),
                'age'         => '16+',
                'price'       => 0,
                'price_to'    => null,
                'address'     => 'Челябинск, Loft 42, пр. Ленина, 42',
                'lat'         => 55.1621,
                'lng'         => 61.4105,
                'status'      => 'approved',
            ],
            [
                'user_id'     => $user1->id,
                'category_id' => $lecture?->id ?? 5,
                'name'        => 'Мастер-класс по акварели для начинающих',
                'description' => 'Художник Ольга Кравцова учит работать с акварелью с нуля. За три часа создадите свой первый городской пейзаж. Все материалы включены в стоимость.',
                'event_date'  => now()->addDays(7)->setTime(14, 0),
                'age'         => '12+',
                'price'       => 800,
                'price_to'    => 1000,
                'address'     => 'Челябинск, ул. Цвиллинга, 15, арт-студия',
                'lat'         => 55.1535,
                'lng'         => 61.4188,
                'status'      => 'approved',
            ],
            [
                'user_id'     => $user2->id,
                'category_id' => $food?->id ?? 6,
                'name'        => 'Уличный фуд-маркет «Вкус Урала»',
                'description' => 'Фестиваль уличной еды с акцентом на уральскую кухню и авторские рецепты. Более 20 фудтраков, живая музыка, мастер-классы шеф-поваров.',
                'event_date'  => now()->addDays(4)->setTime(11, 0),
                'age'         => '0+',
                'price'       => 0,
                'price_to'    => null,
                'address'     => 'Челябинск, площадь Революции',
                'lat'         => 55.1576,
                'lng'         => 61.3967,
                'status'      => 'approved',
            ],
        ];

        foreach ($events as $data) {
            Event::firstOrCreate(
                ['name' => $data['name'], 'user_id' => $data['user_id']],
                $data
            );
        }
    }
}
