<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $table      = 'venta';
    public    $timestamps = false;

    protected $fillable = [
        'vendedor_id', 'folio', 'fecha_hora', 'total', 'estado',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'total'      => 'decimal:2',
    ];

    public function vendedor()
    {
        return $this->belongsTo(Usuario::class, 'vendedor_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }

    public function recetas()
    {
        return $this->hasMany(Receta::class, 'venta_id');
    }

    public static function generarFolio(): string
    {
        $fecha  = now()->format('Ymd');
        $ultimo = self::whereDate('fecha_hora', now()->toDateString())->count();
        return 'VTA-' . $fecha . '-' . str_pad($ultimo + 1, 4, '0', STR_PAD_LEFT);
    }
}
