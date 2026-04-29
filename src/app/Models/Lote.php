<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lote extends Model
{
    protected $table      = 'lote';
    public    $timestamps = false;

    protected $fillable = [
        'producto_id', 'numero_lote', 'cantidad',
        'fecha_vencimiento', 'fecha_ingreso',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'fecha_ingreso'     => 'datetime',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function scopeConStock($query)
    {
        return $query->where('cantidad', '>', 0);
    }

    public function scopeProximosAVencer($query, int $dias = 30)
    {
        return $query->conStock()
                     ->whereDate('fecha_vencimiento', '<=', now()->addDays($dias))
                     ->whereDate('fecha_vencimiento', '>=', now());
    }
}
