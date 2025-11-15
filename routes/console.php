<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('avisos:membresias')->dailyAt('08:00');

// Comando de motivación
Artisan::command('avisos:motivacion', function () {
    $this->call(\App\Console\Commands\EnviarMotivacionMembresias::class);
})->describe('Enviar correos de motivación a clientes con membresía mensual');

// Scheduler diario a las 9:00 AM
Schedule::command('avisos:motivacion')->dailyAt('09:00');