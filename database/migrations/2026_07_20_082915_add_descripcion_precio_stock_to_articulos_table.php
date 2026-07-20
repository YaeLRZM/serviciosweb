<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración correctiva: la tabla viva de PostgreSQL se creó sin
 * descripcion/precio/stock aunque la create_articulos posterior ya los define.
 * Solo agrega columnas faltantes y rellena valores usables.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articulos', function (Blueprint $table) {
            if (! Schema::hasColumn('articulos', 'descripcion')) {
                $table->text('descripcion')->nullable();
            }
            if (! Schema::hasColumn('articulos', 'precio')) {
                $table->decimal('precio', 10, 2)->default(0);
            }
            if (! Schema::hasColumn('articulos', 'stock')) {
                $table->integer('stock')->default(0);
            }
        });

        // Datos reales usables en filas ya existentes (defaults 0 / null).
        $articulos = DB::table('articulos')->select('id', 'nombre', 'descripcion', 'precio', 'stock')->get();

        foreach ($articulos as $row) {
            $precio = $row->precio !== null ? (float) $row->precio : 0.0;
            $stock = $row->stock !== null ? (int) $row->stock : 0;
            $descripcion = is_string($row->descripcion) ? trim($row->descripcion) : '';

            $updates = [];

            if ($descripcion === '') {
                $updates['descripcion'] = 'Pieza artesanal: '.$row->nombre;
            }
            if ($precio <= 0) {
                // Precio determinista por id (rango ~199–1499)
                $updates['precio'] = round(199 + (($row->id * 37) % 1300) + (($row->id % 10) * 0.5), 2);
            }
            if ($stock <= 0) {
                $updates['stock'] = 5 + ($row->id % 20);
            }

            if ($updates !== []) {
                DB::table('articulos')->where('id', $row->id)->update($updates);
            }
        }
    }

    public function down(): void
    {
        Schema::table('articulos', function (Blueprint $table) {
            if (Schema::hasColumn('articulos', 'descripcion')) {
                $table->dropColumn('descripcion');
            }
            if (Schema::hasColumn('articulos', 'precio')) {
                $table->dropColumn('precio');
            }
            if (Schema::hasColumn('articulos', 'stock')) {
                $table->dropColumn('stock');
            }
        });
    }
};
