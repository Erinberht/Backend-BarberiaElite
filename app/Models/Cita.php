<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cliente;

class Cita extends Model
{
    use HasFactory;

    protected $fillable = ['cliente_id', 'empleado_id', 'fecha', 'hora', 'estado'];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }

    public function servicios()
    {
        return $this->belongsToMany(Servicio::class, 'cita_servicio')->withPivot('hora')->withTimestamps();
    }
}
