<?php

namespace App\Console\Commands;

use App\Services\VentaAutoCompleteService;
use Illuminate\Console\Command;

class CompletarVentasPendientesCommand extends Command
{
    protected $signature = 'ventas:completar-pendientes';

    protected $description = 'Marca como completadas las compras pendientes cuyo tiempo de confirmación venció';

    public function handle(VentaAutoCompleteService $service): int
    {
        $n = $service->completarVencidas();
        $this->info("Compras completadas: {$n}");

        return self::SUCCESS;
    }
}
