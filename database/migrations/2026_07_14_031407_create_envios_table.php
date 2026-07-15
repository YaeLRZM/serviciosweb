<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('envios', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('venta_id')->constrained()->onDelete('cascade');
            $table->foreignId('direccion_id')->constrained()->onDelete('cascade');
            $table->string('numero_guia');
            $table->string('paqueteria');
            $table->string('estado_envio');
            $table->date('fecha_envio');
            $table->date('fecha_entrega')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('envios');
    }
};
