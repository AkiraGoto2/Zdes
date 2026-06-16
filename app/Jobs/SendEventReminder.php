<?php

namespace App\Jobs;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendEventReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $ticketId) {}

    public function handle(): void
    {
        $ticket = Ticket::with(['event', 'user'])->find($this->ticketId);

        if (! $ticket || $ticket->status !== 'active' || $ticket->reminder_sent) {
            return;
        }

        $user  = $ticket->user;
        $event = $ticket->event;

        
        if ($user->provider !== 'telegram' || ! $user->provider_id) {
            $ticket->update(['reminder_sent' => true]);
            return;
        }

        $token   = config('services.telegram.token');
        $chat_id = $user->provider_id; 

        $date = \Carbon\Carbon::parse($event->event_date)->translatedFormat('d F Y в H:i');

        $text = "🔔 *Напоминание о мероприятии*\n\n"
              . "*{$event->name}*\n"
              . "📅 {$date}\n"
              . "📍 {$event->address}\n\n"
              . "Ваш билет: `{$ticket->ticket_code}`\n\n"
              . "До начала остался 1 час — не опаздывайте! 🎉";

        try {
            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id'    => $chat_id,
                'text'       => $text,
                'parse_mode' => 'Markdown',
            ]);

            if ($response->successful()) {
                $ticket->update(['reminder_sent' => true]);
            } else {
                Log::warning('Telegram reminder failed', ['ticket' => $this->ticketId, 'response' => $response->body()]);
            }
        } catch (\Throwable $e) {
            Log::error('Telegram reminder exception', ['ticket' => $this->ticketId, 'error' => $e->getMessage()]);
        }
    }
}
