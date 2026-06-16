<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OAuthController extends Controller
{
    
    public function telegramCallback(Request $request): RedirectResponse
    {
        $data = $request->only(['id', 'first_name', 'last_name', 'username', 'photo_url', 'auth_date', 'hash']);

        if (! $this->verifyTelegramHash($data)) {
            return redirect()->route('login')->withErrors(['oauth' => 'Ошибка проверки Telegram. Попробуйте ещё раз.']);
        }

        
        if (time() - (int) ($data['auth_date'] ?? 0) > 86400) {
            return redirect()->route('login')->withErrors(['oauth' => 'Сессия Telegram истекла. Попробуйте войти снова.']);
        }

        $telegramId = $data['id'];

        $user = User::where('provider', 'telegram')->where('provider_id', $telegramId)->first();

        if (! $user) {
            $user = User::create([
                'name'        => $data['first_name'] ?? 'Пользователь',
                'lastname'    => $data['last_name'] ?? '',
                'email'       => 'tg_' . $telegramId . '@placeholder.local',
                'tel'         => '',
                'password'    => Hash::make(Str::random(32)),
                'avatar'      => $data['photo_url'] ?? null,
                'provider'    => 'telegram',
                'provider_id' => (string) $telegramId,
            ]);
        }

        Auth::login($user, remember: true);

        return redirect()->intended('/');
    }

    

    
    private function verifyTelegramHash(array $data): bool
    {
        $botToken = config('services.telegram.token');
        if (! $botToken) {
            return false;
        }

        $hash = $data['hash'] ?? '';
        unset($data['hash']);

        ksort($data);
        $checkString = implode("\n", array_map(
            fn ($k, $v) => "$k=$v",
            array_keys($data),
            array_values($data)
        ));

        $secretKey = hash('sha256', $botToken, true);
        $expectedHash = hash_hmac('sha256', $checkString, $secretKey);

        return hash_equals($expectedHash, $hash);
    }
}
