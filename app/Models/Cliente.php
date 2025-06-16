<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = ['usuario_id'];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class);
    }
}

