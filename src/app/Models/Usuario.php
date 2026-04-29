<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuario';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'nombre_completo',
        'username',
        'password_hash',
        'rol',
        'estado',
    ];

    protected $hidden = ['password_hash'];

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopeMedicos($query)
    {
        return $query->where('rol', 'medico');
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'vendedor_id');
    }

    public function consultas()
    {
        return $this->hasMany(Consulta::class, 'medico_id');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'medico_id');
    }

    public function kardexMovimientos()
    {
        return $this->hasMany(KardexProducto::class, 'usuario_id');
    }
}
