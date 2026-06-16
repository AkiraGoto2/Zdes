<?php

namespace App\Providers;

use App\Models\Event;
use App\Policies\EventPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::policy(Event::class, EventPolicy::class);

        
        \Carbon\Carbon::setLocale('ru');
        \Illuminate\Pagination\Paginator::defaultView('pagination.custom');
    }
}
