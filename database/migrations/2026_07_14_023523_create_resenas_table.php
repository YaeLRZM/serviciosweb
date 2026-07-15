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
        Schema::create('resenas', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->ForeignId('articulo_id')->constrained()->onDelete('cascade');
            $table->ForeignId('user_id')->constrained()->onDelete('cascade');
            $table->Integer('calificacion');
            $table->Text('comentario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resenas');
    }
};
