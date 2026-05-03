<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Музыка и концерты',
            'Вечеринки и клубы',
            'Выставки и галереи',
            'Спорт и активный отдых',
            'Театр и кино',
            'Маркеты и ярмарки',
            'Фестивали',
            'Лекции и мастер-классы',
            'Еда и напитки',
            'Для детей',
            'Природа и прогулки',
            'Бизнес и networking',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(['name' => $name]);
        }
    }
}
