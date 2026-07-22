<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Unifica estado final: completada → entregado.
 * No toca canceladas ni estados intermedios del flujo nuevo.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('ventas')
            ->whereRaw('LOWER(TRIM(estado)) = ?', ['completada'])
            ->update([
                'estado' => 'entregado',
                'next_state_at' => null,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // No se revierte de forma fiable (no distingue entregadas nuevas vs migradas).
    }
};
