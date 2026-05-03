<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Главная
Route::get('/', function () {
    $categories = \App\Models\Category::orderBy('name')->get();
    return view('welcome', compact('categories'));
});

// JSON для карты (AJAX)
Route::get('/api/map-events', [EventController::class, 'mapEvents'])->name('api.map-events');

// Лента событий (публичная)
Route::get('/events', [EventController::class, 'index'])->name('events');

// Авторизованные маршруты — /events/create ДОЛЖЕН идти РАНЬШЕ /events/{event}
Route::middleware(['auth'])->group(function () {
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::patch('/events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');

    Route::get('/my-events', [EventController::class, 'myEvents'])->name('my-events');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Страница одного события (публичная) — ПОСЛЕ /events/create
Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

require __DIR__.'/auth.php';
