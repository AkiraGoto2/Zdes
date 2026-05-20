<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Event;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApplicationController extends Controller
{
    /** Записаться на событие */
    public function store(Event $event)
    {
        // Нельзя записаться на своё же событие
        if ($event->user_id === Auth::id()) {
            return back()->with('error', 'Вы не можете записаться на своё событие.');
        }

        // Уже записан?
        $exists = Application::where('user_id', Auth::id())
            ->where('event_id', $event->id)
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($exists) {
            return back()->with('error', 'Вы уже записаны на это событие.');
        }

        Application::create([
            'user_id'  => Auth::id(),
            'event_id' => $event->id,
            'status'   => 'published',
        ]);

        // Уведомление организатору
        Notification::create([
            'user_id'    => $event->user_id,
            'type'       => 'new_application',
            'message'    => Auth::user()->name . ' ' . Auth::user()->lastname . ' записался(ась) на ваше событие «' . $event->name . '».',
            'related_id' => $event->id,
        ]);

        return back()->with('success', 'Вы успешно записались на событие!');
    }

    /** Отменить запись */
    public function destroy(Event $event)
    {
        Application::where('user_id', Auth::id())
            ->where('event_id', $event->id)
            ->update(['status' => 'cancelled']);

        return back()->with('success', 'Запись отменена.');
    }
}
