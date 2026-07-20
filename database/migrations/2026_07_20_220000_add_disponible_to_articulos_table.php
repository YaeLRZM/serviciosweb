<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articulos', function (Blueprint $table) {
            // Disponibilidad comercial del vendedor (independiente del stock numérico).
            $table->boolean('disponible')->default(true)->after('stock');
        });
    }

    public function down(): void
    {
        Schema::table('articulos', function (Blueprint $table) {
            $table->dropColumn('disponible');
        });
    }
};
