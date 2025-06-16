<?php

namespace App\Observers;

use App\Models\Empleado;

class EmpleadoObserver
{
    /**
     * Handle the Empleado "created" event.
     */
    public function created(Empleado $empleado): void
    {
        //
    }

    /**
     * Handle the Empleado "updated" event.
     */
    public function updated(Empleado $empleado): void
    {
        //
    }

    /**
     * Handle the Empleado "deleted" event.
     */
    public function deleted(Empleado $empleado): void
    {
        //
    }

    /**
     * Handle the Empleado "restored" event.
     */
    public function restored(Empleado $empleado): void
    {
        //
    }

    /**
     * Handle the Empleado "force deleted" event.
     */
    public function forceDeleted(Empleado $empleado): void
    {
        //
    }
}
