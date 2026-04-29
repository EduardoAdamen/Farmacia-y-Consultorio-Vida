<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $table      = 'proveedor';
    public    $timestamps = false;

    protected $fillable = [
        'nombre_empresa', 'nombre_contacto', 'telefono',
        'rfc', 'correo_electronico', 'estado',
    ];

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function diasVisita()
    {
        return $this->hasMany(DiaVisitaProveedor::class, 'proveedor_id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'proveedor_id');
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'proveedor_id');
    }
}
