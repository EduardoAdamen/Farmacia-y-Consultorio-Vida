<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Modelo que representa un pedido de compra realizado a un proveedor
class Pedido extends Model
{
    // Nombre de la tabla en la base de datos que usa este modelo
    protected $table      = 'pedido';
    // Desactiva el registro automático de fechas de creación y actualización
    public    $timestamps = false;

    // Campos que se pueden guardar o actualizar de forma masiva (por formulario, por ejemplo)
    protected $fillable = [
        'proveedor_id', 'usuario_id', 'folio',
        'fecha_estimada', // Fecha en que se espera recibir el pedido
        'estado', 'monto_total',
        'fecha_pago',     // Fecha en que se realizó o planea realizar el pago al proveedor
    ];

    // Define el tipo de dato esperado para ciertos campos al leerlos desde la base de datos
    protected $casts = [
        'fecha_estimada' => 'date',    // Se convierte a un objeto de fecha (sin hora)
        'fecha_pago'     => 'date',    // Se convierte a un objeto de fecha (sin hora)
        'created_at'     => 'datetime',
        'monto_total'    => 'decimal:2', // Se muestra con 2 decimales
    ];

    // Relaciones: conexiones de este modelo con otras tablas del sistema

    // Proveedor al que se le realizó este pedido
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    // Usuario que generó o registró el pedido en el sistema
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    // Productos específicos que se están pidiendo, con sus cantidades y precios
    public function detalles()
    {
        return $this->hasMany(DetallePedido::class, 'pedido_id');
    }

    // Genera un folio único para identificar el pedido, con formato: PED-YYYYMMDD-XXXX
    // Ejemplo: PED-20250507-0001 (primer pedido del día), PED-20250507-0002 (segundo), etc.
    public static function generarFolio(): string
    {
        // Obtiene la fecha actual en formato YYYYMMDD para incluirla en el folio
        $fecha  = now()->format('Ymd');
        // Busca cuántos pedidos del día ya existen buscando por el patrón del folio
        // Esto es más confiable que contar por fecha, ya que usa el folio como referencia directa
        $ultimo = self::where('folio', 'like', 'PED-' . $fecha . '-%')->count();
        // Arma el folio combinando el prefijo, la fecha y el consecutivo con ceros a la izquierda
        return 'PED-' . $fecha . '-' . str_pad($ultimo + 1, 4, '0', STR_PAD_LEFT);
    }
}
