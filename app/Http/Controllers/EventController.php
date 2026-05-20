<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class EventController extends Controller
{
    /** Геокодирование адреса через Nominatim */
    private function geocode(string $address): ?array
    {
        try {
            $resp = Http::timeout(5)
                ->withHeaders(['User-Agent' => 'GdeDvizh/1.0'])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q'              => $address,
                    'format'         => 'json',
                    'limit'          => 1,
                    'addressdetails' => 0,
                ]);

            if ($resp->ok()) {
                $data = $resp->json();
                if (!empty($data[0])) {
                    return [
                        'lat' => (float) $data[0]['lat'],
                        'lng' => (float) $data[0]['lon'],
                    ];
                }
            }
        } catch (\Exception $e) {
            // Если геокодер недоступен — координаты останутся null
        }
        return null;
    }

    /** JSON endpoint для карты на главной */
    public function mapEvents(Request $request)
    {
        $query = Event::with('category')
            ->where('status', 'approved')
            ->whereNotNull('lat')
            ->whereNotNull('lng');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('address', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->date === 'today') {
            $query->whereDate('event_date', today());
        } elseif ($request->date === 'week') {
            $query->whereBetween('event_date', [now(), now()->addWeek()]);
        }

        if ($request->filter === 'free') {
            $query->where(function ($sub) {
                $sub->whereNull('price')->orWhere('price', 0);
            });
        } elseif ($request->filter === 'paid') {
            $query->where('price', '>', 0);
        }

        $events = $query->get()->map(function ($e) {
            $price = null;
            if (!$e->price || $e->price == 0) {
                $price = 'Бесплатно';
            } elseif ($e->price_to) {
                $price = number_format($e->price, 0, '', ' ') . ' – ' . number_format($e->price_to, 0, '', ' ') . ' ₽';
            } else {
                $price = number_format($e->price, 0, '', ' ') . ' ₽';
            }

            return [
                'id'           => $e->id,
                'name'         => $e->name,
                'category'     => $e->category->name ?? '',
                'category_id'  => $e->category_id,
                'event_date'   => \Carbon\Carbon::parse($e->event_date)->translatedFormat('d M, H:i'),
                'address'      => $e->address,
                'price'        => $price,
                'age'          => $e->age,
                'lat'          => (float) $e->lat,
                'lng'          => (float) $e->lng,
                'url'          => route('events.show', $e->id),
            ];
        });

        return response()->json($events);
    }

    /** Лента событий (публичная) */
    public function index(Request $request)
    {
        $query = Event::with(['category', 'user', 'photos'])
            ->where('status', 'approved');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('address', 'like', "%{$q}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filter === 'free') {
            $query->where(function ($sub) {
                $sub->whereNull('price')->orWhere('price', 0);
            });
        } elseif ($request->filter === 'paid') {
            $query->where('price', '>', 0);
        }

        if ($request->filled('age')) {
            $query->where('age', $request->age);
        }

        if ($request->date === 'today') {
            $query->whereDate('event_date', today());
        } elseif ($request->date === 'week') {
            $query->whereBetween('event_date', [now(), now()->addWeek()]);
        }

        $query->orderBy('event_date', 'asc');

        $events     = $query->paginate(12)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('events.index', compact('events', 'categories'));
    }

    /** Страница создания события */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('events.create', compact('categories'));
    }

    /** Сохранение нового события */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'event_date'  => ['required', 'date', 'after:now'],
            'age'         => ['required', 'in:0+,6+,12+,16+,18+'],
            'description' => ['required', 'string', 'min:20'],
            'price'       => ['nullable', 'integer', 'min:0'],
            'price_to'    => ['nullable', 'integer', 'min:0'],
            'address'     => ['required', 'string', 'max:255'],
            'lat'         => ['nullable', 'numeric'],
            'lng'         => ['nullable', 'numeric'],
            'photos'      => ['nullable', 'array', 'max:8'],
            'photos.*'    => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if (empty($validated['lat']) || empty($validated['lng'])) {
            $coords = $this->geocode($validated['address']);
            if ($coords) {
                $validated['lat'] = $coords['lat'];
                $validated['lng'] = $coords['lng'];
            }
        }

        $validated['user_id'] = Auth::id();
        $validated['status']  = 'pending';

        $photos = $request->file('photos', []);
        unset($validated['photos']);

        $event = \App\Models\Event::create($validated);

        foreach ($photos as $file) {
            $path = $file->store('events/' . $event->id, 'public');
            \App\Models\Photo::create(['event_id' => $event->id, 'path' => $path]);
        }

        return redirect()->route('my-events')
            ->with('success', 'Событие отправлено на проверку. После одобрения модератором оно появится на карте.');
    }

    /** Страница одного события */
    public function show(Event $event)
    {
        if ($event->status !== 'approved' && (!Auth::check() || Auth::id() !== $event->user_id)) {
            abort(404);
        }

        $event->load(['category', 'user', 'photos']);
        return view('events.show', compact('event'));
    }

    /** Dashboard — профиль пользователя */
    public function dashboard()
    {
        $user = Auth::user();

        // Созданные мероприятия
        $myEvents = Event::where('user_id', $user->id)
            ->with('category')
            ->orderBy('event_date', 'desc')
            ->get();

        // Мероприятия на которые записан
        $applications = \App\Models\Application::where('user_id', $user->id)
            ->where('status', 'published')
            ->with(['event.category'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Непрочитанные уведомления
        $notifications = $user->notifications()->get();
        $unreadCount   = $notifications->where('is_read', false)->count();

        return view('dashboard', compact('myEvents', 'applications', 'notifications', 'unreadCount'));
    }

    /** Мои события */
    public function myEvents()
    {
        $events = Event::where('user_id', Auth::id())
            ->with('category')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('events.my', compact('events'));
    }

    /** Редактирование */
    public function edit(Event $event)
    {
        $this->authorize('update', $event);
        $categories = Category::orderBy('name')->get();
        return view('events.edit', compact('event', 'categories'));
    }

    /** Обновление */
    public function update(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'event_date'  => ['required', 'date'],
            'age'         => ['required', 'in:0+,6+,12+,16+,18+'],
            'description' => ['required', 'string', 'min:20'],
            'price'       => ['nullable', 'integer', 'min:0'],
            'price_to'    => ['nullable', 'integer', 'min:0'],
            'address'     => ['required', 'string', 'max:255'],
            'lat'         => ['nullable', 'numeric'],
            'lng'         => ['nullable', 'numeric'],
            'photos'      => ['nullable', 'array'],
            'photos.*'    => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if ((empty($validated['lat']) || empty($validated['lng'])) && $validated['address'] !== $event->address) {
            $coords = $this->geocode($validated['address']);
            if ($coords) {
                $validated['lat'] = $coords['lat'];
                $validated['lng'] = $coords['lng'];
            }
        }

        $photos = $request->file('photos', []);
        unset($validated['photos']);

        $validated['status'] = 'pending';
        $event->update($validated);

        $existingCount = $event->photos()->count();
        foreach ($photos as $file) {
            if ($existingCount >= 8) break;
            $path = $file->store('events/' . $event->id, 'public');
            \App\Models\Photo::create(['event_id' => $event->id, 'path' => $path]);
            $existingCount++;
        }

        return redirect()->route('my-events')
            ->with('success', 'Событие обновлено и отправлено на повторную проверку.');
    }

    /** Удаление */
    public function destroy(Event $event)
    {
        $this->authorize('delete', $event);
        $event->delete();

        return redirect()->route('my-events')->with('success', 'Событие удалено.');
    }
}
