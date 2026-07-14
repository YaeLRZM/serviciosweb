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
        Schema::create('direccions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->ForeignId('user_id')->constrained()->onDelete('cascade');
            $table->ForeignId('estado_id')->constrained()->onDelete('cascade');
            $table->string('calle');
            $table->string('colonia');
            $table->string('codigo_postal');
            $table->string('pais');
            $table->integer('numero_exterior');
            $table->integer('numero_interior');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('direccions');
    }
};
