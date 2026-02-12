<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Informe automático semanal para docentes (lunes a las 8am)
Schedule::command('reports:generate-scheduled')->weeklyOn(1, '08:00');
