<?php

namespace App\Providers;

use App\Services\NotificacionService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Cantidad de notificaciones sin leer para el menú del estudiante.
        View::composer('components.sidebarestudiante', function ($view) {
            $usuario = auth()->user();

            $view->with(
                'notificacionesNoLeidas',
                $usuario && $usuario->rol === 'Estudiante'
                    ? app(NotificacionService::class)->noLeidas($usuario->credencial)
                    : 0
            );
        });
    }
}
