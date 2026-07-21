<?php

namespace App\Console\Commands;

use App\Services\CarritoReservaService;
use Illuminate\Console\Command;

class LiberarReservasCarritoCommand extends Command
{
    protected $signature = 'carrito:liberar-reservas';

    protected $description = 'Libera reservas de carrito vencidas y devuelve el stock';

    public function handle(CarritoReservaService $carrito): int
    {
        $n = $carrito->liberarVencidas();
        $this->info("Reservas liberadas: {$n}");

        return self::SUCCESS;
    }
}
