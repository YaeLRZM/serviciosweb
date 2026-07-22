<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extiende el flujo de pago simulado (tarjeta / efectivo) sin romper ventas previas.
 * - metodo_pago: tarjeta|efectivo|null (legacy)
 * - codigo_barras: solo efectivo activado por vendedor
 * - next_state_at: avance automático de estados (2 min por paso)
 * - estado ampliado a 40 chars para nuevos valores
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->string('estado', 40)->change();

            $table->string('metodo_pago', 20)->nullable()->after('estado');
            $table->string('codigo_barras', 64)->nullable()->after('metodo_pago');
            $table->timestamp('next_state_at')->nullable()->after('auto_complete_at');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn(['metodo_pago', 'codigo_barras', 'next_state_at']);
            $table->string('estado', 20)->change();
        });
    }
};
