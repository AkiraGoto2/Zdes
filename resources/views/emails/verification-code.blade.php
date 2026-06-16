<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Код подтверждения</title>
<style>
  body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background:#f5f5f5; margin:0; padding:20px; color:#1a1a1a; }
  .wrap { max-width:480px; margin:0 auto; background:white; border-radius:20px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,.08); }
  .header { background:linear-gradient(135deg,#4338ca,#5b21b6); padding:32px; color:white; text-align:center; }
  .header h1 { margin:0 0 4px; font-size:22px; font-weight:800; }
  .header p  { margin:0; font-size:13px; opacity:.8; }
  .body { padding:32px; text-align:center; }
  .code-box { display:inline-block; background:#f0f0ff; border:2px solid #c7d2fe; border-radius:16px; padding:16px 40px; margin:20px 0; }
  .code { font-size:40px; font-weight:900; letter-spacing:10px; color:#4338ca; font-family:monospace; }
  .footer { padding:20px 32px; background:#f9f9f9; border-top:1px solid #f0f0f0; font-size:12px; color:#9ca3af; text-align:center; }
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <h1>{{ config('app.name') }}</h1>
    <p>Подтверждение регистрации</p>
  </div>
  <div class="body">
    <p style="font-size:15px;">Привет, <strong>{{ $name }}</strong>!</p>
    <p style="font-size:13px; color:#6b7280;">Введи этот код на сайте, чтобы завершить регистрацию.</p>
    <div class="code-box">
      <div class="code">{{ $code }}</div>
    </div>
    <p style="font-size:13px; color:#6b7280;">Код действителен <strong>15 минут</strong>.</p>
  </div>
  <div class="footer">© {{ date('Y') }} {{ config('app.name') }}</div>
</div>
</body>
</html>
