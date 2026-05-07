<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Modelo que representa una venta realizada en el sistema
class Venta extends Model
{
    // Nombre de la tabla en la base de datos que usa este modelo
    protected $table      = 'venta';
    // Desactiva el registro automático de fechas de creación y actualización
    public    $timestamps = false;

    // Campos que se pueden guardar o actualizar de forma masiva (por formulario, por ejemplo)
    protected $fillable = [
        'vendedor_id', 'folio', 'fecha_hora', 'total', 'estado',
    ];

    // Define el tipo de dato esperado para ciertos campos al leerlos desde la base de datos
    protected $casts = [
        'fecha_hora' => 'datetime',  // Se convierte a un objeto de fecha y hora
        'total'      => 'decimal:2', // Se muestra con 2 decimales
    ];

    // Relaciones: conexiones de este modelo con otras tablas del sistema

    // Usuario que realizó esta venta en su rol de vendedor
    public function vendedor()
    {
        return $this->belongsTo(Usuario::class, 'vendedor_id');
    }

    // Líneas de detalle que componen la venta (cada producto vendido con su cantidad y precio)
    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }

    // Recetas médicas que están asociadas a esta venta (para productos que requieren receta)
    public function recetas()
    {
        return $this->hasMany(Receta::class, 'venta_id');
    }

    // Genera un folio único para identificar la venta, con formato: VTA-YYYYMMDD-XXXX
    public static function generarFolio(): string
    {
        // Obtiene la fecha actual en formato YYYYMMDD para incluirla en el folio
        $fecha  = now()->format('Ymd');
        // Cuenta cuántas ventas ya existen hoy para calcular el número consecutivo
        $ultimo = self::whereDate('fecha_hora', now()->toDateString())->count();
        // Arma el folio combinando el prefijo, la fecha y el consecutivo con ceros a la izquierda
        return 'VTA-' . $fecha . '-' . str_pad($ultimo + 1, 4, '0', STR_PAD_LEFT);
    }
}