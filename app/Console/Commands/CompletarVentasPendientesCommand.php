<?php

namespace App\Console\Commands;

use App\Services\VentaAutoCompleteService;
use Illuminate\Console\Command;

class CompletarVentasPendientesCommand extends Command
{
    protected $signature = 'ventas:completar-pendientes';

    protected $description = 'Avanza estados de compra vencidos (flujo simulado y legacy hacia entregado)';

    public function handle(VentaAutoCompleteService $service): int
    {
        $n = $service->completarVencidas();
        $this->info("Compras actualizadas: {$n}");

        return self::SUCCESS;
    }
}
