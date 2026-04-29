<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KardexProducto extends Model
{
    protected $table      = 'kardex_producto';
    public    $timestamps = false;

    protected $fillable = [
        'producto_id', 'usuario_id', 'tipo',
        'cantidad', 'referencia_id', 'fecha_hora',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
