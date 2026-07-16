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
        Schema::table('artesanos', function (Blueprint $table) {
            $table->string('especialidad')->nullable()->after('nombre');
            $table->string('foto')->nullable()->after('especialidad');
            $table->string('ubicacion')->nullable()->after('foto');
            // revision | nueva | documentos | aprobado | rechazado
            $table->string('estado')->default('revision')->after('ubicacion');
            $table->decimal('ventas_total', 10, 2)->default(0)->after('estado');
            $table->unsignedInteger('ventas_items')->default(0)->after('ventas_total');
            $table->decimal('rating', 3, 2)->nullable()->after('ventas_items');
            $table->boolean('destacado')->default(false)->after('rating');
            $table->text('notas_moderacion')->nullable()->after('destacado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artesanos', function (Blueprint $table) {
            $table->dropColumn([
                'especialidad',
                'foto',
                'ubicacion',
                'estado',
                'ventas_total',
                'ventas_items',
                'rating',
                'destacado',
                'notas_moderacion',
            ]);
        });
    }
};
