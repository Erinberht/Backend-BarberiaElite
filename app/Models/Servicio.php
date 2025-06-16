<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'precio',
        'duracion_minutos',
    
    ];
    public function citas()
    {
        return $this->belongsToMany(Cita::class, 'cita_servicio')->withPivot('hora')->withTimestamps();
    }
    // Servicio.php
    public function empleados()
    {
        return $this->belongsToMany(Empleado::class);
    }

    public function especialidades()
    {
        return $this->belongsToMany(Especialidad::class, 'servicio_especialidad');
    }
}
