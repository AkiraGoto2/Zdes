<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\EmailVerificationCode;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'tel'      => ['required', 'string', 'min:9', 'max:30'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $code = (string) random_int(100000, 999999);

        $request->session()->put('registration_data', [
            'name'       => $request->name,
            'lastname'   => $request->lastname,
            'tel'        => $request->tel,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'code'       => $code,
            'expires_at' => now()->addMinutes(15)->timestamp,
        ]);

        Log::info('[REGISTER] Session saved', [
            'email'      => $request->email,
            'session_id' => session()->getId(),
            'has_data'   => session()->has('registration_data'),
        ]);

        try {
            Mail::to($request->email)->send(new EmailVerificationCode($code, $request->name));
            Log::info('[REGISTER] Code sent to ' . $request->email);
        } catch (\Throwable $e) {
            Log::error('[REGISTER] Mail failed: ' . $e->getMessage());
        }

        Log::info('[REGISTER] Redirecting to verify page');
        return redirect()->route('register.verify');
    }

    public function showVerify(Request $request): View
    {
        Log::info('[VERIFY] showVerify called', [
            'session_id'   => session()->getId(),
            'has_reg_data' => session()->has('registration_data'),
            'session_all'  => array_keys(session()->all()),
        ]);

        abort_unless(session()->has('registration_data'), 404);
        return view('auth.verify-code');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $data = $request->session()->get('registration_data');

        Log::info('[VERIFY] verify called', [
            'session_id' => session()->getId(),
            'has_data'   => !is_null($data),
            'input_code' => $request->code,
            'real_code'  => $data['code'] ?? 'NO DATA',
        ]);

        if (! $data) {
            Log::warning('[VERIFY] No registration_data in session');
            return redirect()->route('register')
                ->withErrors(['code' => 'Сессия истекла. Зарегистрируйтесь заново.']);
        }

        if (now()->timestamp > $data['expires_at']) {
            $request->session()->forget('registration_data');
            Log::warning('[VERIFY] Code expired');
            return redirect()->route('register')
                ->withErrors(['code' => 'Код истёк. Пожалуйста, зарегистрируйтесь заново.']);
        }

        if ($request->code !== $data['code']) {
            Log::warning('[VERIFY] Wrong code', ['input' => $request->code, 'expected' => $data['code']]);
            return back()->withErrors(['code' => 'Неверный код. Попробуйте ещё раз.']);
        }

        $user = User::create([
            'name'     => $data['name'],
            'lastname' => $data['lastname'],
            'tel'      => $data['tel'],
            'email'    => $data['email'],
            'password' => $data['password'],
        ]);

        $request->session()->forget('registration_data');

        event(new Registered($user));
        Auth::login($user);

        Log::info('[VERIFY] User created and logged in', ['user_id' => $user->id]);
        return redirect('/');
    }

    public function resend(Request $request): RedirectResponse
    {
        $data = $request->session()->get('registration_data');

        if (! $data) {
            return redirect()->route('register')
                ->withErrors(['code' => 'Сессия истекла. Зарегистрируйтесь заново.']);
        }

        $code = (string) random_int(100000, 999999);
        $data['code']       = $code;
        $data['expires_at'] = now()->addMinutes(15)->timestamp;
        $request->session()->put('registration_data', $data);

        try {
            Mail::to($data['email'])->send(new EmailVerificationCode($code, $data['name']));
            Log::info('[REGISTER] Code resent to ' . $data['email']);
        } catch (\Throwable $e) {
            Log::error('[REGISTER] Resend mail failed: ' . $e->getMessage());
        }

        return back()->with('resent', 'Новый код отправлен на ' . $data['email']);
    }
}
