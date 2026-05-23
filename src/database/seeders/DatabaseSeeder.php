<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('usuario')->insertOrIgnore([
            ['nombre_completo' => 'Carlos Mendoza', 'username' => 'dueno', 'password_hash' => Hash::make('dueno1234'), 'rol' => 'dueno', 'estado' => 'activo', 'created_at' => now()],
            ['nombre_completo' => 'Ana Ruiz', 'username' => 'vendedor', 'password_hash' => Hash::make('vendedor1234'), 'rol' => 'vendedor', 'estado' => 'activo', 'created_at' => now()],
            ['nombre_completo' => 'Dr. Roberto Silva', 'username' => 'medico', 'password_hash' => Hash::make('medico1234'), 'rol' => 'medico', 'estado' => 'activo', 'created_at' => now()],
        ]);

        $categorias = ['Analgesicos', 'Antibioticos', 'Antiinflamatorios', 'Antihistaminicos', 'Vitaminas y Suplementos', 'Respiratorio', 'Gastrointestinal', 'Dermatologia', 'Oftalmologia'];
        foreach ($categorias as $cat) {
            DB::table('categoria')->insertOrIgnore(['nombre' => $cat]);
        }

        DB::table('proveedor')->insertOrIgnore([
            'nombre_empresa' => 'Distribuidora Farmaceutica Nacional',
            'nombre_contacto' => 'Luis Garcia',
            'telefono' => '7471234567',
            'estado' => 'activo',
        ]);

        DB::table('dia_visita_proveedor')->insertOrIgnore([
            ['proveedor_id' => 1, 'dia_semana' => 'lun'],
            ['proveedor_id' => 1, 'dia_semana' => 'jue'],
        ]);

        $productos = [
            ['proveedor_id' => 1, 'categoria_id' => 1, 'nombre' => 'Paracetamol 500mg', 'sku' => 'PAR-500', 'precio_compra' => 2.50, 'precio_venta' => 5.00, 'stock_total' => 45, 'stock_minimo' => 10, 'fecha_vencimiento' => now()->addYears(2)->format('Y-m-d'), 'requiere_receta' => 0, 'estado' => 'activo'],
            ['proveedor_id' => 1, 'categoria_id' => 2, 'nombre' => 'Amoxicilina 875mg', 'sku' => 'AMX-875', 'precio_compra' => 8.00, 'precio_venta' => 12.00, 'stock_total' => 30, 'stock_minimo' => 5, 'fecha_vencimiento' => now()->addYears(2)->format('Y-m-d'), 'requiere_receta' => 1, 'estado' => 'activo'],
            ['proveedor_id' => 1, 'categoria_id' => 1, 'nombre' => 'Ibuprofeno 400mg', 'sku' => 'IBU-400', 'precio_compra' => 3.00, 'precio_venta' => 6.50, 'stock_total' => 60, 'stock_minimo' => 15, 'fecha_vencimiento' => now()->addYears(2)->format('Y-m-d'), 'requiere_receta' => 0, 'estado' => 'activo'],
            ['proveedor_id' => 1, 'categoria_id' => 5, 'nombre' => 'Vitamina C 1000mg', 'sku' => 'VITC-1000', 'precio_compra' => 4.00, 'precio_venta' => 9.00, 'stock_total' => 2, 'stock_minimo' => 10, 'fecha_vencimiento' => now()->addDays(20)->format('Y-m-d'), 'requiere_receta' => 0, 'estado' => 'activo'],
            ['proveedor_id' => 1, 'categoria_id' => 3, 'nombre' => 'Diclofenaco 100mg', 'sku' => 'DCF-100', 'precio_compra' => 3.50, 'precio_venta' => 7.00, 'stock_total' => 3, 'stock_minimo' => 8, 'fecha_vencimiento' => now()->addDays(10)->format('Y-m-d'), 'requiere_receta' => 0, 'estado' => 'activo'],
        ];

        foreach ($productos as $producto) {
            $cantidadInicial = $producto['stock_total'];
            $fechaVencimiento = $producto['fecha_vencimiento'];
            unset($producto['fecha_vencimiento']);

            DB::table('producto')->insertOrIgnore($producto);

            $productoGuardado = DB::table('producto')->where('sku', $producto['sku'])->first();
            if ($productoGuardado && ! DB::table('lote')->where('producto_id', $productoGuardado->id)->exists()) {
                DB::table('lote')->insert([
                    'producto_id' => $productoGuardado->id,
                    'numero_lote' => 'INI-'.$producto['sku'],
                    'cantidad' => $cantidadInicial,
                    'fecha_vencimiento' => $fechaVencimiento,
                    'fecha_ingreso' => now(),
                ]);
            }
        }
    }
}
