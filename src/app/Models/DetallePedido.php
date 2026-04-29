<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetallePedido extends Model
{
    protected $table      = 'detalle_pedido';
    public    $timestamps = false;

    protected $fillable = [
        'pedido_id', 'producto_id',
        'cantidad_solicitada', 'cantidad_recibida',
        'precio_compra_real',
    ];

    protected $casts = [
        'precio_compra_real' => 'decimal:2',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
