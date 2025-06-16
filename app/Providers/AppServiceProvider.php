<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Observers\UserObserver;
use App\Observers\EmpleadoObserver;
use App\Models\Empleado;

class AppServiceProvider extends ServiceProvider
{
  

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);
        Empleado::observe(EmpleadoObserver::class);
    }
}
