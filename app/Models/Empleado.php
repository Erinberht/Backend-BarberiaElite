<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    use HasFactory;

    protected $fillable = [
        'usuario_id',
        'fecha_contratacion'
    ];

    protected $casts = [
        'fecha_contratacion' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($empleado) {
            if (!$empleado->fecha_contratacion) {
                $empleado->fecha_contratacion = $empleado->created_at;
            }
        });
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function especialidades()
    {
        return $this->belongsToMany(Especialidad::class, 'empleado_especialidad');
    }


    public function servicios()
    {
        return $this->belongsToMany(Servicio::class);
    }
}
