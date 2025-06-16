<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AgendaEmpleado extends Model
{
    use HasFactory;

    protected $table = 'agenda_empleados';

    protected $fillable = [
        'empleado_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'disponible'
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora_inicio' => 'string',
        'hora_fin' => 'string',
        'disponible' => 'boolean'
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }
} 