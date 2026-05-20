<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PhotoController;
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

// Авторизованные маршруты
Route::middleware(['auth'])->group(function () {

    // События — создание / редактирование (ПЕРЕД /{event})
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::patch('/events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');

    // Запись на событие
    Route::post('/events/{event}/apply', [ApplicationController::class, 'store'])->name('events.apply');
    Route::delete('/events/{event}/apply', [ApplicationController::class, 'destroy'])->name('events.unapply');

    // Фотографии
    Route::post('/events/{event}/photos', [PhotoController::class, 'store'])->name('events.photos.store');
    Route::delete('/photos/{photo}', [PhotoController::class, 'destroy'])->name('photos.destroy');

    // Мои события
    Route::get('/my-events', [EventController::class, 'myEvents'])->name('my-events');

    // Dashboard (профиль)
    Route::get('/dashboard', [EventController::class, 'dashboard'])->name('dashboard');

    // Уведомления
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    // Профиль
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Страница одного события (публичная) — ПОСЛЕ /events/create
Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

// Панель администратора
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::post('/events/{event}/approve', [AdminController::class, 'approve'])->name('events.approve');
    Route::post('/events/{event}/reject', [AdminController::class, 'reject'])->name('events.reject');
});

require __DIR__.'/auth.php';
