<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consulta extends Model
{
    protected $table      = 'consulta';
    public    $timestamps = false;

    protected $fillable = [
        'expediente_id', 'medico_id', 'cita_id',
        'motivo', 'presion_arterial', 'temperatura',
        'frecuencia_cardiaca', 'peso', 'talla',
        'sintomas', 'diagnostico', 'estudios_solicitados',
        'tratamiento', 'notas_evolucion', 'tipo_consulta',
        'costo', 'estado_pago', 'proxima_cita', 'fecha_hora',
    ];

    protected $casts = [
        'fecha_hora'   => 'datetime',
        'proxima_cita' => 'date',
        'temperatura'  => 'decimal:1',
        'peso'         => 'decimal:2',
        'talla'        => 'decimal:2',
        'costo'        => 'decimal:2',
    ];

    public function expediente()
    {
        return $this->belongsTo(ExpedienteClinico::class, 'expediente_id');
    }

    public function medico()
    {
        return $this->belongsTo(Usuario::class, 'medico_id');
    }

    public function cita()
    {
        return $this->belongsTo(Cita::class, 'cita_id');
    }

    public function recetas()
    {
        return $this->hasMany(Receta::class, 'consulta_id');
    }
}
