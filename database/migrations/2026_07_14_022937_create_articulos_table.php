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
        Schema::create('articulos', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('categoria_id')->constrained()->onDelete('cascade');
            $table->foreignId('artesano_id')->constrained()->onDelete('cascade');
            $table->foreignId('tienda_id')->constrained()->onDelete('cascade');
            $table->string('nombre');
            $table->text('talla');
            $table->text('color');
            $table->text('bordado');
            $table->text('tela');
            $table->text('region');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articulos');
    }
};
