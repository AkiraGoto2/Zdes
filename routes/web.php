<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $categories = \App\Models\Category::orderBy('name')->get();
    return view('welcome', compact('categories'));
});

Route::get('/api/map-events', [EventController::class, 'mapEvents'])->name('api.map-events');

Route::get('/events', [EventController::class, 'index'])->name('events');

Route::middleware(['auth'])->group(function () {

    
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::patch('/events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');

    
    Route::post('/events/{event}/apply', [ApplicationController::class, 'store'])->name('events.apply');
    Route::delete('/events/{event}/apply', [ApplicationController::class, 'destroy'])->name('events.unapply');

    
    Route::post('/events/{event}/photos', [PhotoController::class, 'store'])->name('events.photos.store');
    Route::delete('/photos/{photo}', [PhotoController::class, 'destroy'])->name('photos.destroy');

    
    Route::get('/my-events', [EventController::class, 'myEvents'])->name('my-events');

    
    Route::get('/dashboard', [EventController::class, 'dashboard'])->name('dashboard');

    
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
});

Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::post('/events/{event}/approve', [AdminController::class, 'approve'])->name('events.approve');
    Route::post('/events/{event}/reject', [AdminController::class, 'reject'])->name('events.reject');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/events/{event}/ticket', [\App\Http\Controllers\TicketController::class, 'create'])->name('tickets.create');
    Route::post('/events/{event}/ticket', [\App\Http\Controllers\TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{ticket}', [\App\Http\Controllers\TicketController::class, 'show'])->name('tickets.show');
    Route::patch('/tickets/{ticket}/cancel', [\App\Http\Controllers\TicketController::class, 'cancel'])->name('tickets.cancel');
});

require __DIR__.'/auth.php';
