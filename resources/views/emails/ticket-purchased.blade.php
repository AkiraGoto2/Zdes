<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ваш билет</title>
<style>
  body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background:#f5f5f5; margin:0; padding:20px; color:#1a1a1a; }
  .wrap { max-width:520px; margin:0 auto; background:white; border-radius:20px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,.08); }
  .header { background:linear-gradient(135deg,#4338ca,#5b21b6); padding:32px 32px 24px; color:white; }
  .header h1 { margin:0 0 4px; font-size:22px; font-weight:800; }
  .header p { margin:0; font-size:13px; opacity:.8; }
  .body { padding:28px 32px; }
  .ticket-box { border:2px dashed #e0d7ff; border-radius:16px; padding:20px 24px; margin:20px 0; background:#fafaff; }
  .ticket-code { font-size:28px; font-weight:900; letter-spacing:4px; color:#4338ca; text-align:center; margin:12px 0 4px; font-family:monospace; }
  .ticket-label { font-size:11px; color:#9ca3af; text-align:center; text-transform:uppercase; letter-spacing:1px; }
  .row { display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f3f4f6; font-size:13px; }
  .row:last-child { border-bottom:none; }
  .row .label { color:#6b7280; }
  .row .value { font-weight:600; text-align:right; }
  .price-row .value { color:#4338ca; font-size:16px; font-weight:800; }
  .footer { padding:20px 32px; background:#f9f9f9; border-top:1px solid #f0f0f0; font-size:12px; color:#9ca3af; text-align:center; }
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <h1>{{ config('app.name') }}</h1>
    <p>Ваш билет успешно оформлен</p>
  </div>
  <div class="body">
    <p style="font-size:15px;">Привет, <strong>{{ $ticket->buyer_name }}</strong>! 🎉</p>
    <p style="font-size:13px;color:#6b7280;">Вы успешно купили билет на мероприятие. Сохраните этот код — он понадобится на входе.</p>

    <div class="ticket-box">
      <div class="ticket-label">Код билета</div>
      <div class="ticket-code">{{ $ticket->ticket_code }}</div>
      <div class="ticket-label">предъявите на входе</div>
    </div>

    <div>
      <div class="row">
        <span class="label">Мероприятие</span>
        <span class="value">{{ $ticket->event->name }}</span>
      </div>
      <div class="row">
        <span class="label">Дата и время</span>
        <span class="value">{{ \Carbon\Carbon::parse($ticket->event->event_date)->translatedFormat('d F Y, H:i') }}</span>
      </div>
      <div class="row">
        <span class="label">Место</span>
        <span class="value">{{ $ticket->event->address }}</span>
      </div>
      <div class="row">
        <span class="label">Кол-во билетов</span>
        <span class="value">{{ $ticket->quantity }} шт.</span>
      </div>
      <div class="row price-row">
        <span class="label">Итого</span>
        <span class="value">{{ $ticket->price_paid == 0 ? 'Бесплатно' : number_format($ticket->price_paid * $ticket->quantity, 0, '', ' ') . ' ₽' }}</span>
      </div>
    </div>
  </div>
  <div class="footer">
    Письмо отправлено автоматически — не нужно отвечать на него.<br>
    © {{ date('Y') }} {{ config('app.name') }}
  </div>
</div>
</body>
</html>
