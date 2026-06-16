<?php

namespace App\Http\Controllers;

use App\Mail\TicketPurchased;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    public function create(Event $event)
    {
        abort_unless($event->status === 'approved', 404);
        return view('tickets.create', ['event' => $event->load('category', 'photos')]);
    }

    public function store(Request $request, Event $event)
    {
        abort_unless($event->status === 'approved', 404);

        $validated = $request->validate([
            'buyer_name'  => ['required', 'string', 'max:100'],
            'buyer_email' => ['required', 'email', 'max:255'],
            'buyer_phone' => ['nullable', 'string', 'max:30'],
            'quantity'    => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $price = $event->price ?? 0;

        $ticket = Ticket::create([
            'user_id'     => Auth::id(),
            'event_id'    => $event->id,
            'ticket_code' => strtoupper(Str::random(3) . '-' . Str::random(4) . '-' . Str::random(3)),
            'quantity'    => $validated['quantity'],
            'price_paid'  => $price,
            'buyer_name'  => $validated['buyer_name'],
            'buyer_email' => $validated['buyer_email'],
            'buyer_phone' => $validated['buyer_phone'] ?? null,
            'status'      => 'active',
        ]);

        try {
            Mail::to($ticket->buyer_email)->send(new TicketPurchased($ticket));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Mail error: ' . $e->getMessage());
        }

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Билет оформлен! Проверьте почту.');
    }

    public function show(Ticket $ticket)
    {
        abort_unless(Auth::id() === $ticket->user_id, 403);
        return view('tickets.show', ['ticket' => $ticket->load('event.category')]);
    }

    public function cancel(Ticket $ticket)
    {
        abort_unless(Auth::id() === $ticket->user_id, 403);
        $ticket->update(['status' => 'cancelled']);
        return back()->with('success', 'Билет отменён.');
    }
}
