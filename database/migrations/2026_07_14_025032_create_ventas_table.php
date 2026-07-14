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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->ForeignId('user_id')->constrained()->onDelete('cascade');
            $table->ForeignId('forma_pago_id')->constrained('forma__pagos')->onDelete('cascade');
            $table->ForeignId('tienda_id')->constrained()->onDelete('cascade');

            $table->float('total', 8, 2);
            $table->string('estado', 20);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
