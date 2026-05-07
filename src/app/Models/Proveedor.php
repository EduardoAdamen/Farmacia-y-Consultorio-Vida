<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Modelo que representa a un proveedor que suministra productos a la farmacia
class Proveedor extends Model
{
    // Nombre de la tabla en la base de datos que usa este modelo
    protected $table      = 'proveedor';
    // Desactiva el registro automático de fechas de creación y actualización
    public    $timestamps = false;

    // Campos que se pueden guardar o actualizar de forma masiva (por formulario, por ejemplo)
    protected $fillable = [
        'nombre_empresa',    
        'nombre_contacto',   
        'telefono',
        'rfc',               
        'correo_electronico',
        'estado',
    ];


    // Devuelve solo los proveedores que están marcados como activos
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    // Relaciones: conexiones de este modelo con otras tablas del sistema

    // Días de la semana en que este proveedor visita o hace entregas a la farmacia
    public function diasVisita()
    {
        return $this->hasMany(DiaVisitaProveedor::class, 'proveedor_id');
    }

    // Pedidos de compra que se han generado hacia este proveedor
    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'proveedor_id');
    }

    // Productos que este proveedor suministra a la farmacia
    public function productos()
    {
        return $this->hasMany(Producto::class, 'proveedor_id');
    }
}