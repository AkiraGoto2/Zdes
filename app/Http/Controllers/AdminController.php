<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /** Панель модерации */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'pending');

        $pending  = Event::with(['user','category','photos','socials'])->where('status','pending')->orderBy('created_at','desc')->get();
        $approved = Event::with(['user','category','photos','socials'])->where('status','approved')->orderBy('created_at','desc')->paginate(20);
        $rejected = Event::with(['user','category','photos','socials'])->where('status','rejected')->orderBy('created_at','desc')->paginate(20);

        $stats = [
            'pending'  => $pending->count(),
            'approved' => Event::where('status','approved')->count(),
            'rejected' => Event::where('status','rejected')->count(),
            'users'    => User::where('role','user')->count(),
        ];

        return view('admin.index', compact('pending','approved','rejected','stats','tab'));
    }

    /** Одобрить событие */
    public function approve(Event $event)
    {
        $event->update(['status' => 'approved']);

        // Уведомление организатору
        Notification::create([
            'user_id'    => $event->user_id,
            'type'       => 'event_approved',
            'message'    => "Ваше событие «{$event->name}» одобрено и опубликовано на карте.",
            'related_id' => $event->id,
        ]);

        return back()->with('success', "Событие «{$event->name}» одобрено.");
    }

    /** Отклонить событие */
    public function reject(Request $request, Event $event)
    {
        $event->update(['status' => 'rejected']);

        $reason = $request->input('reason', 'Не соответствует правилам площадки.');

        Notification::create([
            'user_id'    => $event->user_id,
            'type'       => 'event_rejected',
            'message'    => "Ваше событие «{$event->name}» отклонено. Причина: {$reason}",
            'related_id' => $event->id,
        ]);

        return back()->with('success', "Событие «{$event->name}» отклонено.");
    }
}
