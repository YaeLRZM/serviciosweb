<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Evita que borrar una forma de pago elimine ventas históricas (CASCADE → RESTRICT).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropForeign(['forma_pago_id']);
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->foreign('forma_pago_id')
                ->references('id')
                ->on('forma_pagos')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropForeign(['forma_pago_id']);
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->foreign('forma_pago_id')
                ->references('id')
                ->on('forma_pagos')
                ->cascadeOnDelete();
        });
    }
};
