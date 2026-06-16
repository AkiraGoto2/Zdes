<?php

use App\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');
    $response->assertStatus(200);
});

test('registration redirects to verify page', function () {
    $response = $this->post('/register', [
        'name'                  => 'Иван',
        'lastname'              => 'Петров',
        'tel'                   => '+79001234567',
        'email'                 => 'newuser@example.com',
        'password'              => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    // Наша регистрация сохраняет данные в сессию и редиректит на страницу ввода кода
    $response->assertRedirect(route('register.verify'));
    // Пользователь ещё не авторизован — ждёт подтверждения кода
    $this->assertGuest();
});

test('verify code page can be rendered after registration', function () {
    // Имитируем сессию с данными регистрации
    session([
        'registration_data' => [
            'name'       => 'Иван',
            'lastname'   => 'Петров',
            'tel'        => '+79001234567',
            'email'      => 'test@example.com',
            'password'   => bcrypt('Password123!'),
            'code'       => '123456',
            'expires_at' => now()->addMinutes(15)->timestamp,
        ]
    ]);

    $response = $this->get(route('register.verify'));
    $response->assertStatus(200);
});
