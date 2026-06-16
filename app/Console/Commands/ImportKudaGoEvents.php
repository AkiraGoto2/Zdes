<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportKudaGoEvents extends Command
{
    protected $signature   = 'events:import {--pages=3 : Сколько страниц тянуть (25 событий на странице)}';
    protected $description = 'Импорт событий из KudaGo API';

    private const LOCATION = 'chel';

    private const CATEGORY_MAP = [
        'concert'         => 'Музыка и концерты',
        'party'           => 'Вечеринки и клубы',
        'exhibition'      => 'Выставки и галереи',
        'sport'           => 'Спорт и активный отдых',
        'theater'         => 'Театр и кино',
        'festival'        => 'Фестивали',
        'education'       => 'Лекции и мастер-классы',
        'food'            => 'Еда и напитки',
        'kids'            => 'Для детей',
        'nature'          => 'Природа и прогулки',
        'business'        => 'Бизнес и networking',
        'shopping'        => 'Маркеты и ярмарки',
        'other'           => 'Фестивали',
    ];

    public function handle(): int
    {
        $bot = User::where('role', 'admin')->first();
        if (! $bot) {
            $this->error('Нет ни одного admin-пользователя. Запусти сидер сначала.');
            return self::FAILURE;
        }

        $pages   = (int) $this->option('pages');
        $created = 0;
        $skipped = 0;

        for ($page = 1; $page <= $pages; $page++) {
            $this->line("Страница {$page}/{$pages}...");

            $response = Http::timeout(15)
                ->withHeaders(['User-Agent' => 'GdeDvizh/1.0 (https://github.com)'])
                ->get('https://kudago.com/public-api/v1.4/events/', [
                    'location'    => self::LOCATION,
                    'lang'        => 'ru',
                    'page_size'   => 25,
                    'page'        => $page,
                    'fields'      => 'id,title,description,short_title,place,dates,price,is_free,categories,images,age_restriction',
                    'expand'      => 'place,dates',
                    'actual_only' => 1,
                    'order_by'    => 'date',
                ]);

            if (! $response->ok()) {
                $this->warn("KudaGo вернул {$response->status()} на странице {$page}. Прерываю.");
                break;
            }

            $items = $response->json('results', []);

            if (empty($items)) {
                $this->line('Событий больше нет.');
                break;
            }

            foreach ($items as $item) {
                $result = $this->processItem($item, $bot->id);
                $result ? $created++ : $skipped++;
            }

            sleep(1);
        }

        $this->info("Готово. Создано: {$created}, пропущено (уже есть): {$skipped}.");
        return self::SUCCESS;
    }

    private function processItem(array $item, int $userId): bool
    {
        $title = trim($item['title'] ?? $item['short_title'] ?? '');
        if (! $title) return false;

        if (Event::where('kudago_id', $item['id'])->exists()) return false;

        $dates = $item['dates'] ?? [];
        if (empty($dates)) return false;

        $firstDate = $dates[0];
        $start = isset($firstDate['start']) ? Carbon::createFromTimestamp($firstDate['start']) : null;
        if (! $start || $start->isPast()) return false;

        $description = strip_tags($item['description'] ?? $item['short_title'] ?? $title);
        if (mb_strlen($description) < 20) {
            $description = $title . '. ' . $description;
        }
        if (mb_strlen($description) < 20) return false;

        $place    = $item['place'] ?? null;
        $address  = $place['address'] ?? ($place['title'] ?? 'Адрес уточняется');
        $lat      = $place['coords']['lat'] ?? null;
        $lng      = $place['coords']['lon'] ?? null;

        $categoryName = $this->mapCategory($item['categories'] ?? []);
        $category     = Category::where('name', $categoryName)->first();

        $price   = null;
        $priceTo = null;
        if (! ($item['is_free'] ?? false)) {
            $priceStr = $item['price'] ?? '';
            if (preg_match('/(\d+)\s*[–-]\s*(\d+)/', $priceStr, $m)) {
                $price   = (int) $m[1];
                $priceTo = (int) $m[2];
            } elseif (preg_match('/(\d+)/', $priceStr, $m)) {
                $price = (int) $m[1];
            }
        }

        $age = '0+';
        $restriction = $item['age_restriction'] ?? null;
        if ($restriction) {
            $map = [6 => '6+', 12 => '12+', 16 => '16+', 18 => '18+'];
            $age = $map[(int)$restriction] ?? '0+';
        }

        Event::create([
            'user_id'     => $userId,
            'category_id' => $category?->id ?? Category::first()->id,
            'name'        => mb_substr($title, 0, 255),
            'description' => mb_substr($description, 0, 5000),
            'event_date'  => $start,
            'age'         => $age,
            'price'       => $price,
            'price_to'    => $priceTo,
            'address'     => mb_substr($address, 0, 255),
            'lat'         => $lat ? (float)$lat : null,
            'lng'         => $lng ? (float)$lng : null,
            'status'      => 'approved',
            'kudago_id'   => $item['id'],
        ]);

        return true;
    }

    private function mapCategory(array $slugs): string
    {
        foreach ($slugs as $slug) {
            if (isset(self::CATEGORY_MAP[$slug])) {
                return self::CATEGORY_MAP[$slug];
            }
        }
        return 'Фестивали';
    }
}
