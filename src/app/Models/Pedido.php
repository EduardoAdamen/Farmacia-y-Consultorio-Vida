<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table      = 'pedido';
    public    $timestamps = false;

    protected $fillable = [
        'proveedor_id', 'usuario_id', 'folio',
        'fecha_estimada', 'estado', 'monto_total', 'fecha_pago',
    ];

    protected $casts = [
        'fecha_estimada' => 'date',
        'fecha_pago'     => 'date',
        'monto_total'    => 'decimal:2',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetallePedido::class, 'pedido_id');
    }

    public static function generarFolio(): string
    {
        $fecha  = now()->format('Ymd');
        $ultimo = self::where('folio', 'like', 'PED-' . $fecha . '-%')->count();
        return 'PED-' . $fecha . '-' . str_pad($ultimo + 1, 4, '0', STR_PAD_LEFT);
    }
}
