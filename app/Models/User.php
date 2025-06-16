<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'nombre_usuario',
        'password',
        'rol',
        'nombre',
        'correo',
        'telefono',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'correo_verified_at' => 'datetime',
    ];

    public function cliente()
    {
        return $this->hasOne(Cliente::class, 'usuario_id');
    }
    public function empleado()
    {
        return $this->hasOne(Empleado::class, 'usuario_id');
    }
}
