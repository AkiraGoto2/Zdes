<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\Photo;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class PhotoSeeder extends Seeder
{
    // Unsplash source — разные тематические фото
    private array $photos = [
        'Techno Rooftop: Summer Session' => [
            'https://images.unsplash.com/photo-1598387993441-a364f854c3e1?w=800&q=80',
            'https://images.unsplash.com/photo-1429962714451-bb934ecdc4ec?w=800&q=80',
        ],
        'Sunrise Yoga Flow в Городском бору' => [
            'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=800&q=80',
            'https://images.unsplash.com/photo-1506126613408-eca07ce68773?w=800&q=80',
        ],
        'Выставка «Уральский авангард»' => [
            'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?w=800&q=80',
            'https://images.unsplash.com/photo-1536924940846-227afb31e2a5?w=800&q=80',
        ],
        'Creative Startup Meet' => [
            'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&q=80',
            'https://images.unsplash.com/photo-1528605248644-14dd04022da1?w=800&q=80',
        ],
        'Мастер-класс по акварели для начинающих' => [
            'https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?w=800&q=80',
            'https://images.unsplash.com/photo-1513364776144-60967b0f800f?w=800&q=80',
        ],
        'Уличный фуд-маркет «Вкус Урала»' => [
            'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=800&q=80',
            'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800&q=80',
        ],
    ];

    public function run(): void
    {
        foreach ($this->photos as $eventName => $urls) {
            $event = Event::where('name', $eventName)->first();
            if (!$event) continue;

            // Удаляем старые фото
            $event->photos()->each(function ($p) {
                Storage::disk('public')->delete($p->path);
                $p->delete();
            });

            $dir = 'events/' . $event->id;
            Storage::disk('public')->makeDirectory($dir);

            foreach ($urls as $i => $url) {
                try {
                    $response = Http::timeout(10)->get($url);
                    if (!$response->ok()) continue;

                    $filename = $dir . '/seed_' . ($i + 1) . '.jpg';
                    Storage::disk('public')->put($filename, $response->body());
                    Photo::create(['event_id' => $event->id, 'path' => $filename]);
                } catch (\Exception $e) {
                    // Пропускаем если сеть недоступна
                    continue;
                }
            }
        }
    }
}
