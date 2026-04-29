<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receta extends Model
{
    protected $table      = 'receta';
    public    $timestamps = false;

    protected $fillable = [
        'consulta_id', 'venta_id', 'folio',
        'indicaciones', 'fecha', 'estado_valida',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function consulta()
    {
        return $this->belongsTo(Consulta::class, 'consulta_id');
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleReceta::class, 'receta_id');
    }

    public static function generarFolio(): string
    {
        $fecha  = now()->format('Ymd');
        $ultimo = self::whereDate('fecha', now()->toDateString())->count();
        return 'REC-' . $fecha . '-' . str_pad($ultimo + 1, 4, '0', STR_PAD_LEFT);
    }
}
