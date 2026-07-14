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
        Schema::create('detalle__campanas', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('campana_id')->constrained()->onDelete('cascade');
            $table->foreignId('articulo_id')->constrained()->onDelete('cascade');
            $table->foreignId('categoria_id')->constrained()->onDelete('cascade');
            $table->integer('porcentaje_descuento');
            $table->float('precio_fijo_oferta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle__campanas');
    }
};
