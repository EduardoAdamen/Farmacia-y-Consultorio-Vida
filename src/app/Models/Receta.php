<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Modelo que representa una receta médica dentro del sistema
class Receta extends Model
{
    // Nombre de la tabla en la base de datos que usa este modelo
    protected $table      = 'receta';
    // Desactiva el registro automático de fechas de creación y actualización
    public    $timestamps = false;

    // Campos que se pueden guardar o actualizar de forma masiva (por formulario, por ejemplo)
    protected $fillable = [
        'consulta_id', 'venta_id', 'folio',
        'indicaciones', // Instrucciones del médico para el paciente (dosis, frecuencia, etc.)
        'fecha', 'estado_valida', // Indica si la receta sigue siendo válida para surtirse
    ];

    // Define el tipo de dato esperado para ciertos campos al leerlos desde la base de datos
    protected $casts = [
        'fecha' => 'date', // Se convierte a un objeto de fecha (sin hora)
    ];

    // Relaciones: conexiones de este modelo con otras tablas del sistema

    // Consulta médica en la que se generó esta receta (puede ser nula si la receta es independiente)
    public function consulta()
    {
        return $this->belongsTo(Consulta::class, 'consulta_id');
    }

    // Venta en la que se surtió esta receta (puede ser nula si aún no se ha surtido)
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    // Medicamentos específicos indicados en la receta, cada uno con su dosis y cantidad
    public function detalles()
    {
        return $this->hasMany(DetalleReceta::class, 'receta_id');
    }

    // Genera un folio único para identificar la receta, con formato: REC-YYYYMMDD-XXXX
    public static function generarFolio(): string
    {
        // Obtiene la fecha actual en formato YYYYMMDD para incluirla en el folio
        $fecha  = now()->format('Ymd');
        // Cuenta cuántas recetas ya existen hoy para calcular el número consecutivo
        $ultimo = self::whereDate('fecha', now()->toDateString())->count();
        // Arma el folio combinando el prefijo, la fecha y el consecutivo con ceros a la izquierda
        return 'REC-' . $fecha . '-' . str_pad($ultimo + 1, 4, '0', STR_PAD_LEFT);
    }
}