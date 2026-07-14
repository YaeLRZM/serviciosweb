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
        Schema::create('detalle__ventas', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('venta_id')->constrained('ventas');
            $table->foreignId('articulo_id')->constrained('articulos');
            $table->integer('cantidad');
            $table->float('precio_unitario', 10, 2);
            $table->float('subtotal', 10, 2);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle__ventas');
    }
};
