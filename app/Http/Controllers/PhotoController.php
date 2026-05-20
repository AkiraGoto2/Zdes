<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    /** Загрузка фото к событию */
    public function store(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $request->validate([
            'photos'   => ['required', 'array', 'max:8'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        foreach ($request->file('photos') as $file) {
            $path = $file->store('events/' . $event->id, 'public');
            Photo::create(['event_id' => $event->id, 'path' => $path]);
        }

        return back()->with('success', 'Фотографии добавлены.');
    }

    /** Удаление одного фото */
    public function destroy(Photo $photo)
    {
        $this->authorize('update', $photo->event);

        Storage::disk('public')->delete($photo->path);
        $photo->delete();

        return back()->with('success', 'Фото удалено.');
    }
}
