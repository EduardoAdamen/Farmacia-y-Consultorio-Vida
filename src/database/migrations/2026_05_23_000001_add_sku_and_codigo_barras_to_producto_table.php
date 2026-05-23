<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('producto', function (Blueprint $table) {
            if (! Schema::hasColumn('producto', 'sku')) {
                $table->string('sku', 50)->nullable()->after('nombre');
            }

            if (! Schema::hasColumn('producto', 'codigo_barras')) {
                $table->string('codigo_barras', 50)->nullable()->after('sku');
            }
        });

        DB::table('producto')
            ->whereNull('sku')
            ->orderBy('id')
            ->select(['id'])
            ->chunkById(100, function ($productos): void {
                foreach ($productos as $producto) {
                    DB::table('producto')
                        ->where('id', $producto->id)
                        ->update(['sku' => 'PROD-'.$producto->id]);
                }
            });

        DB::statement('ALTER TABLE producto MODIFY sku VARCHAR(50) NOT NULL');

        if (! $this->indexExists('producto', 'uq_producto_sku')) {
            DB::statement('ALTER TABLE producto ADD CONSTRAINT uq_producto_sku UNIQUE (sku)');
        }
    }

    public function down(): void
    {
        if ($this->indexExists('producto', 'uq_producto_sku')) {
            DB::statement('ALTER TABLE producto DROP INDEX uq_producto_sku');
        }

        Schema::table('producto', function (Blueprint $table) {
            if (Schema::hasColumn('producto', 'codigo_barras')) {
                $table->dropColumn('codigo_barras');
            }

            if (Schema::hasColumn('producto', 'sku')) {
                $table->dropColumn('sku');
            }
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::raw('DATABASE()'))
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }
};
