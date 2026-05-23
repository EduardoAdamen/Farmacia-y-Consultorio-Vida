<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
            if (! Schema::hasColumn('pedido', 'created_at')) {
                $table->dateTime('created_at')->useCurrent()->after('fecha_pago');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
            if (Schema::hasColumn('pedido', 'created_at')) {
                $table->dropColumn('created_at');
            }
        });
    }
};
