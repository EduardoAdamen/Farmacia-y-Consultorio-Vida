<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleReceta extends Model
{
    protected $table      = 'detalle_receta';
    public    $timestamps = false;

    protected $fillable = [
        'receta_id', 'producto_id', 'nombre_medicamento',
        'dosis', 'frecuencia', 'duracion', 'indicaciones_especificas',
    ];

    public function receta()
    {
        return $this->belongsTo(Receta::class, 'receta_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
