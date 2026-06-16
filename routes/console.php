<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('events:import --pages=5')->dailyAt('03:00');
