<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Trazabilidad de acciones administrativas sobre ventas
 * (cancelación admin / devolución de dinero).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->string('admin_nota', 500)->nullable()->after('next_state_at');
            $table->foreignId('admin_user_id')
                ->nullable()
                ->after('admin_nota')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('admin_accion_at')->nullable()->after('admin_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('admin_user_id');
            $table->dropColumn(['admin_nota', 'admin_accion_at']);
        });
    }
};
