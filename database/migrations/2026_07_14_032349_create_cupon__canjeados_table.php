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
        Schema::create('cupon__canjeados', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('cupon_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('venta_id')->constrained()->onDelete('cascade');
            $table->float('monto_descuento', 10, 2);
            $table->dateTime('fecha_canje');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cupon__canjeados');
    }
};
