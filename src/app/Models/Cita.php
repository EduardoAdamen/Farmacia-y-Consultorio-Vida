<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    protected $table      = 'cita';
    public    $timestamps = false;

    protected $fillable = [
        'medico_id', 'expediente_id', 'fecha', 'hora',
        'motivo', 'nombre_temporal', 'estado',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function medico()
    {
        return $this->belongsTo(Usuario::class, 'medico_id');
    }

    public function expediente()
    {
        return $this->belongsTo(ExpedienteClinico::class, 'expediente_id');
    }

    public function consulta()
    {
        return $this->hasOne(Consulta::class, 'cita_id');
    }
}
