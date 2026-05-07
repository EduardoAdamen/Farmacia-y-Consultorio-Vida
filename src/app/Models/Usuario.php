<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// Modelo que representa a un usuario del sistema (puede ser vendedor, médico, administrador, etc.)
// Extiende de Authenticatable para que Laravel pueda manejar el inicio de sesión con este modelo
class Usuario extends Authenticatable
{
    // Permite al usuario recibir notificaciones del sistema
    use Notifiable;

    // Nombre de la tabla en la base de datos que usa este modelo
    protected $table = 'usuario';
    // Campo que identifica de forma única a cada usuario
    protected $primaryKey = 'id';
    // Desactiva el registro automático de fechas de creación y actualización
    public $timestamps = false;

    // Campos que se pueden guardar o actualizar de forma masiva (por formulario, por ejemplo)
    protected $fillable = [
        'nombre_completo',
        'username',
        'password_hash',  // Contraseña ya encriptada
        'rol',            // Define qué tipo de usuario es (médico, vendedor, admin, etc.)
        'estado',
    ];

  
    protected $hidden = ['password_hash'];

    // Le indica a Laravel cuál es el campo que contiene la contraseña del usuario
    // Es necesario porque el campo no se llama 'password' como Laravel espera por defecto
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    // Devuelve solo los usuarios que están marcados como activos
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    // Devuelve solo los usuarios que tienen el rol de médico
    public function scopeMedicos($query)
    {
        return $query->where('rol', 'medico');
    }

    // Relaciones: conexiones de este modelo con otras tablas del sistema

    // Ventas que ha realizado este usuario como vendedor
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'vendedor_id');
    }

    // Consultas médicas que ha atendido este usuario (aplica cuando el rol es médico)
    public function consultas()
    {
        return $this->hasMany(Consulta::class, 'medico_id');
    }

    // Citas agendadas a cargo de este usuario (aplica cuando el rol es médico)
    public function citas()
    {
        return $this->hasMany(Cita::class, 'medico_id');
    }

    // Movimientos de inventario que ha registrado este usuario (entradas y salidas de productos)
    public function kardexMovimientos()
    {
        return $this->hasMany(KardexProducto::class, 'usuario_id');
    }
}