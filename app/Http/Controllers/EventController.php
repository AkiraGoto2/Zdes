<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event;
use App\Models\Notification;
use App\Services\ModerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class EventController extends Controller
{
    private function geocode(string $address): ?array
    {
        try {
            $resp = Http::timeout(5)
                ->withHeaders(['User-Agent' => 'GdeDvizh/1.0'])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $address, 'format' => 'json', 'limit' => 1,
                ]);
            if ($resp->ok()) {
                $data = $resp->json();
                if (!empty($data[0])) {
                    return ['lat' => (float)$data[0]['lat'], 'lng' => (float)$data[0]['lon']];
                }
            }
        } catch (\Exception $e) {}
        return null;
    }

    /** JSON для карты */
    public function mapEvents(Request $request)
    {
        $query = Event::with('category','photos')
            ->where('status', 'approved')
            ->whereNotNull('lat')->whereNotNull('lng')
            ->where('event_date', '>=', now()->startOfDay());

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn($s) => $s->where('name','like',"%$q%")->orWhere('address','like',"%$q%")->orWhere('description','like',"%$q%"));
        }
        if ($request->filled('category')) $query->where('category_id', $request->category);
        if ($request->date === 'today')   $query->whereDate('event_date', today());
        elseif ($request->date === 'week') $query->whereBetween('event_date', [now(), now()->addWeek()]);
        elseif ($request->filled('date_from')) $query->whereDate('event_date', '>=', $request->date_from);
        if ($request->filled('date_to'))  $query->whereDate('event_date', '<=', $request->date_to);
        if ($request->filled('time_from')) $query->whereTime('event_date', '>=', $request->time_from);
        if ($request->filled('time_to'))   $query->whereTime('event_date', '<=', $request->time_to);
        if ($request->filter === 'free')  $query->where(fn($s) => $s->whereNull('price')->orWhere('price', 0));
        elseif ($request->filter === 'paid') $query->where('price', '>', 0);

        $events = $query->get()->map(function ($e) {
            $price = (!$e->price || $e->price == 0) ? 'Бесплатно'
                : (number_format($e->price,0,'','  ') . ($e->price_to ? ' – '.number_format($e->price_to,0,'',' ') : '') . ' ₽');
            return [
                'id'         => $e->id,
                'name'       => $e->name,
                'category'   => $e->category->name ?? '',
                'category_id'=> $e->category_id,
                'event_date' => \Carbon\Carbon::parse($e->event_date)->translatedFormat('d M, H:i'),
                'address'    => $e->address,
                'price'      => $price,
                'age'        => $e->age,
                'lat'        => (float)$e->lat,
                'lng'        => (float)$e->lng,
                'url'        => route('events.show', $e->id),
                'cover'      => $e->photos->first() ? \Storage::url($e->photos->first()->path) : null,
            ];
        });

        return response()->json($events);
    }

    /** Лента событий */
    public function index(Request $request)
    {
        $query = Event::with(['category','user','photos'])
            ->where('status','approved')
            ->where('event_date', '>=', now()->startOfDay());

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn($s) => $s->where('name','like',"%$q%")->orWhere('address','like',"%$q%"));
        }
        if ($request->filled('category'))  $query->where('category_id', $request->category);
        if ($request->filter === 'free')   $query->where(fn($s) => $s->whereNull('price')->orWhere('price',0));
        elseif ($request->filter === 'paid') $query->where('price','>',0);
        if ($request->filled('age'))       $query->where('age', $request->age);

        // Диапазон дат
        if ($request->filled('date_from')) $query->whereDate('event_date', '>=', $request->date_from);
        if ($request->filled('date_to'))   $query->whereDate('event_date', '<=', $request->date_to);
        // Диапазон времени
        if ($request->filled('time_from')) $query->whereTime('event_date', '>=', $request->time_from);
        if ($request->filled('time_to'))   $query->whereTime('event_date', '<=', $request->time_to);

        // Сортировка
        $sort = $request->get('sort', 'soonest');
        if ($sort === 'newest') $query->orderBy('created_at', 'desc');
        else $query->orderBy('event_date', 'asc');

        $events     = $query->paginate(12)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('events.index', compact('events','categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('events.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required','string','max:255'],
            'category_id' => ['required','exists:categories,id'],
            'event_date'  => ['required','date','after:now'],
            'age'         => ['required','in:0+,6+,12+,16+,18+'],
            'description' => ['required','string','min:20'],
            'price'       => ['nullable','integer','min:0'],
            'price_to'    => ['nullable','integer','min:0'],
            'address'     => ['required','string','max:255'],
            'lat'         => ['nullable','numeric'],
            'lng'         => ['nullable','numeric'],
            'photos'      => ['nullable','array','max:8'],
            'photos.*'    => ['image','mimes:jpg,jpeg,png,webp','max:5120'],
            'socials'     => ['nullable','array'],
            'socials.*.platform' => ['nullable','string','max:50'],
            'socials.*.url'      => ['nullable','url','max:255'],
        ]);

        if (empty($validated['lat']) || empty($validated['lng'])) {
            $coords = $this->geocode($validated['address']);
            if ($coords) { $validated['lat'] = $coords['lat']; $validated['lng'] = $coords['lng']; }
        }

        // Автомодерация
        $mod = app(ModerationService::class)->check(
            $validated['name'], $validated['description'], $validated['address']
        );

        $photos  = $request->file('photos', []);
        $socials = $request->input('socials', []);
        unset($validated['photos'], $validated['socials']);

        $validated['user_id'] = Auth::id();
        $validated['status']  = $mod['status'];

        $event = Event::create($validated);

        // Уведомление если автоматически отклонено
        if ($mod['status'] === 'rejected') {
            Notification::create([
                'user_id'    => Auth::id(),
                'type'       => 'event_rejected',
                'message'    => $mod['reason'],
                'related_id' => $event->id,
            ]);
        }

        foreach ($photos as $file) {
            $path = $file->store('events/'.$event->id, 'public');
            \App\Models\Photo::create(['event_id' => $event->id, 'path' => $path]);
        }

        foreach ($socials as $s) {
            if (!empty($s['platform']) && !empty($s['url'])) {
                \App\Models\Socials::create(['event_id' => $event->id, 'platform' => $s['platform'], 'url' => $s['url']]);
            }
        }

        $msg = $mod['status'] === 'approved'
            ? 'Событие прошло автоматическую проверку и опубликовано!'
            : ($mod['status'] === 'rejected'
                ? 'Событие автоматически отклонено. Проверьте уведомления.'
                : 'Событие отправлено на проверку модератору.');

        return redirect()->route('my-events')->with('success', $msg);
    }

    public function show(Event $event)
    {
        if ($event->status !== 'approved') {
            $isOwner = Auth::check() && Auth::id() === $event->user_id;
            $isAdmin = Auth::check() && Auth::user()->isAdmin();
            if (!$isOwner && !$isAdmin) abort(404);
        }
        $event->load(['category','user','photos','socials']);
        return view('events.show', compact('event'));
    }

    public function dashboard()
    {
        $user = Auth::user();
        $myEvents = Event::where('user_id', $user->id)->with('category')->orderBy('event_date','desc')->get();
        $applications = \App\Models\Application::where('user_id', $user->id)->where('status','published')->with(['event.category'])->orderBy('created_at','desc')->get();
        $notifications = $user->notifications()->get();
        $unreadCount   = $notifications->where('is_read', false)->count();
        return view('dashboard', compact('myEvents','applications','notifications','unreadCount'));
    }

    public function myEvents()
    {
        $events = Event::where('user_id', Auth::id())->with('category')->orderBy('created_at','desc')->get();
        return view('events.my', compact('events'));
    }

    public function edit(Event $event)
    {
        $this->authorize('update', $event);
        $categories = Category::orderBy('name')->get();
        $event->load('photos','socials');
        return view('events.edit', compact('event','categories'));
    }

    public function update(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $validated = $request->validate([
            'name'        => ['required','string','max:255'],
            'category_id' => ['required','exists:categories,id'],
            'event_date'  => ['required','date'],
            'age'         => ['required','in:0+,6+,12+,16+,18+'],
            'description' => ['required','string','min:20'],
            'price'       => ['nullable','integer','min:0'],
            'price_to'    => ['nullable','integer','min:0'],
            'address'     => ['required','string','max:255'],
            'lat'         => ['nullable','numeric'],
            'lng'         => ['nullable','numeric'],
            'photos'      => ['nullable','array'],
            'photos.*'    => ['image','mimes:jpg,jpeg,png,webp','max:5120'],
            'socials'     => ['nullable','array'],
            'socials.*.platform' => ['nullable','string','max:50'],
            'socials.*.url'      => ['nullable','url','max:255'],
        ]);

        if ((empty($validated['lat']) || empty($validated['lng'])) && $validated['address'] !== $event->address) {
            $coords = $this->geocode($validated['address']);
            if ($coords) { $validated['lat'] = $coords['lat']; $validated['lng'] = $coords['lng']; }
        }

        $mod = app(ModerationService::class)->check($validated['name'], $validated['description'], $validated['address']);

        $photos  = $request->file('photos', []);
        $socials = $request->input('socials', []);
        unset($validated['photos'], $validated['socials']);

        $validated['status'] = $mod['status'];
        $event->update($validated);

        $existingCount = $event->photos()->count();
        foreach ($photos as $file) {
            if ($existingCount >= 8) break;
            $path = $file->store('events/'.$event->id, 'public');
            \App\Models\Photo::create(['event_id' => $event->id, 'path' => $path]);
            $existingCount++;
        }

        // Обновляем соцсети
        $event->socials()->delete();
        foreach ($socials as $s) {
            if (!empty($s['platform']) && !empty($s['url'])) {
                \App\Models\Socials::create(['event_id' => $event->id, 'platform' => $s['platform'], 'url' => $s['url']]);
            }
        }

        return redirect()->route('my-events')->with('success', 'Событие обновлено.');
    }

    public function destroy(Event $event)
    {
        $this->authorize('delete', $event);
        $event->delete();
        return redirect()->route('my-events')->with('success', 'Событие удалено.');
    }
}
