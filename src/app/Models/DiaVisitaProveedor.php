<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiaVisitaProveedor extends Model
{
    protected $table      = 'dia_visita_proveedor';
    public    $timestamps = false;

    protected $fillable = [
        'proveedor_id', 'dia_semana',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }
}
