<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Completa compras pendientes cada minuto (también se evalúa al listar/ver ventas).
Schedule::command('ventas:completar-pendientes')->everyMinute();

// Libera reservas de carrito vencidas y devuelve stock.
Schedule::command('carrito:liberar-reservas')->everyMinute();
