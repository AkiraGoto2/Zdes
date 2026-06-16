<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    
    public function markAllRead()
    {
        Auth::user()->notifications()->where('is_read', false)->update(['is_read' => true]);
        return back();
    }

    
    public function markRead(int $id)
    {
        Auth::user()->notifications()->where('id', $id)->update(['is_read' => true]);
        return back();
    }
}
