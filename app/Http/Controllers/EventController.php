<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event;
use App\Models\Notification;
use App\Services\ModerationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class EventController extends Controller
{
    use AuthorizesRequests;
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
        if ($request->filled('age'))      $query->where('age', $request->age);
        if ($request->filter === 'free')  $query->whereNull('price');
        elseif ($request->filter === 'paid') $query->whereNotNull('price')->where('price', '>', 0);

        $events = $query->get()->map(function ($e) {
            $price = is_null($e->price) ? 'Бесплатно'
                : (number_format($e->price,0,'','  ') . ($e->price_to ? ' – '.number_format($e->price_to,0,'',' ') : '') . ' ₽');
            return [
                'id'             => $e->id,
                'name'           => $e->name,
                'category'       => $e->category->name ?? '',
                'category_id'    => $e->category_id,
                'event_date'     => \Carbon\Carbon::parse($e->event_date)->translatedFormat('d M, H:i'),
                'event_date_raw' => $e->event_date,
                'address'        => $e->address,
                'price'          => $price,
                'age'            => $e->age,
                'lat'            => (float) $e->lat,
                'lng'            => (float) $e->lng,
                'url'            => route('events.show', $e->id),
                'cover'          => $e->photos->first() ? \Storage::url($e->photos->first()->path) : null,
            ];
        });

        return response()->json($events);
    }

    
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
        if ($request->filled('city'))      $query->where('address','like','%'.$request->city.'%');
        if ($request->filter === 'free')   $query->where(fn($s) => $s->whereNull('price')->orWhere('price',0));
        elseif ($request->filter === 'paid') $query->where('price','>',0);
        if ($request->filled('age'))       $query->where('age', $request->age);
        if ($request->filled('price_from')) $query->where('price', '>=', $request->price_from);
        if ($request->filled('price_to'))   $query->where('price', '<=', $request->price_to);

        if ($request->filled('date_from')) $query->whereDate('event_date', '>=', $request->date_from);
        if ($request->filled('date_to'))   $query->whereDate('event_date', '<=', $request->date_to);
        if ($request->filled('time_from')) $query->whereTime('event_date', '>=', $request->time_from);
        if ($request->filled('time_to'))   $query->whereTime('event_date', '<=', $request->time_to);

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
            'event_date'     => ['required','date','after:now'],
            'event_date_end' => ['nullable','date','after:event_date'],
            'age'         => ['required','in:0+,6+,12+,16+,18+'],
            'description' => ['required','string','min:20'],
            'price'       => ['nullable','integer','min:0'],
            'price_to'    => ['nullable','integer','min:0'],
            'address'     => ['required','string','max:255', function($attr, $val, $fail) {
                if (mb_strlen(trim($val)) < 5) {
                    $fail('Укажите полный адрес с городом.');
                }
            }],
            'lat'         => ['nullable','numeric'],
            'lng'         => ['nullable','numeric'],
            'photos'      => ['nullable','array','max:8'],
            'photos.*'    => ['image','mimes:jpg,jpeg,png,webp','max:5120'],
            'socials'     => ['nullable','array'],
            'socials.*.platform' => ['nullable','string','max:50'],
            'socials.*.url'      => ['nullable','url','max:255'],
            'contact_phone'    => ['nullable','string','max:255'],
            'contact_site'     => ['nullable','url','max:255'],
            'contact_telegram' => ['nullable','string','max:255'],
            'contact_vk'       => ['nullable','string','max:255'],
            'max_participants'  => ['nullable','integer','min:1'],
            'price_type'       => ['nullable','string','in:free,fixed,range'],
        ]);

        // Обрабатываем цену по типу
        $priceType = $request->input('price_type', 'free');
        if ($priceType === 'free') {
            $validated['price']    = null;
            $validated['price_to'] = null;
        } elseif ($priceType === 'fixed') {
            $validated['price_to'] = null;
        } elseif ($priceType === 'range') {
            $validated['price'] = $request->input('price_range_from');
        }
        unset($validated['price_type']);

        if (empty($validated['lat']) || empty($validated['lng'])) {
            $coords = $this->geocode($validated['address']);
            if ($coords) { $validated['lat'] = $coords['lat']; $validated['lng'] = $coords['lng']; }
        }

        
        $mod = app(ModerationService::class)->check(
            $validated['name'], $validated['description'], $validated['address']
        );

        $photos  = $request->file('photos', []);
        $socials = $request->input('socials', []);
        unset($validated['photos'], $validated['socials']);

        $validated['user_id'] = Auth::id();
        $validated['status']  = $mod['status'];

        $event = Event::create($validated);

        
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
        $tickets = \App\Models\Ticket::where('user_id', $user->id)->where('status','active')->with('event.category')->orderBy('created_at','desc')->get();
        $notifications = $user->notifications()->get();
        $unreadCount   = $notifications->where('is_read', false)->count();
        return view('dashboard', compact('myEvents','applications','tickets','notifications','unreadCount'));
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
            'event_date_day'      => ['required','date_format:Y-m-d'],
            'event_date_time'     => ['required','date_format:H:i'],
            'event_date_end_day'  => ['nullable','date_format:Y-m-d'],
            'event_date_end_time' => ['nullable','date_format:H:i'],
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
            'contact_phone'    => ['nullable','string','max:255'],
            'contact_site'     => ['nullable','url','max:255'],
            'contact_telegram' => ['nullable','string','max:255'],
            'contact_vk'       => ['nullable','string','max:255'],
            'max_participants'  => ['nullable','integer','min:1'],
            'price_type'       => ['nullable','string','in:free,fixed,range'],
        ]);

        // Собираем datetime из двух раздельных полей
        $validated['event_date'] = $validated['event_date_day'] . ' ' . $validated['event_date_time'] . ':00';
        if (!empty($validated['event_date_end_day']) && !empty($validated['event_date_end_time'])) {
            $validated['event_date_end'] = $validated['event_date_end_day'] . ' ' . $validated['event_date_end_time'] . ':00';
        } else {
            $validated['event_date_end'] = null;
        }

        // Проверяем что дата не в прошлом
        if (\Carbon\Carbon::parse($validated['event_date'])->isPast()) {
            return back()->withErrors(['event_date_day' => 'Дата начала должна быть в будущем.'])->withInput();
        }
        if ($validated['event_date_end'] && \Carbon\Carbon::parse($validated['event_date_end'])->lte(\Carbon\Carbon::parse($validated['event_date']))) {
            return back()->withErrors(['event_date_end_day' => 'Дата окончания должна быть позже даты начала.'])->withInput();
        }

        unset($validated['event_date_day'], $validated['event_date_time'], $validated['event_date_end_day'], $validated['event_date_end_time']);

        // Обрабатываем цену по типу
        $priceType = $request->input('price_type', 'free');
        if ($priceType === 'free') {
            $validated['price']    = null;
            $validated['price_to'] = null;
        } elseif ($priceType === 'fixed') {
            $validated['price_to'] = null;
        } elseif ($priceType === 'range') {
            $validated['price'] = $request->input('price_range_from');
        }
        unset($validated['price_type']);

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

        // Удаляем фото из storage
        $event->photos()->each(function ($photo) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($photo->path);
            $photo->delete();
        });

        // Удаляем связанные данные
        $event->socials()->delete();
        \App\Models\Application::where('event_id', $event->id)->delete();
        \App\Models\Ticket::where('event_id', $event->id)->delete();
        \App\Models\Notification::where('related_id', $event->id)->delete();

        $event->delete();
        return redirect()->route('my-events')->with('success', 'Событие удалено.');
    }
}
