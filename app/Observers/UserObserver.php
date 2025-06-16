<?php

namespace App\Observers;
// app/Observers/UserObserver.php

namespace App\Observers;

use App\Models\User;
use App\Models\Cliente;
use App\Models\Empleado;

class UserObserver
{
    public function created(User $user): void  {
        if ($user->rol === 'empleado') {
            Empleado::create([
                'usuario_id' => $user->id,
                'fecha_contratacion' => now(), // puedes sobrescribirla luego
            ]);
        } elseif ($user->rol === 'cliente') {
            Cliente::create([
                'usuario_id' => $user->id,
            ]);
        }
    }

}


