
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Mostrar el formulario de inicio de sesión
Route::get('/login', [AuthController::class, 'mostrarLogin'])
    ->name('login');

// Procesar las credenciales
Route::post('/login', [AuthController::class, 'iniciarSesion'])
    ->middleware('throttle:5,1');

// Rutas que requieren una sesión autenticada
Route::middleware('auth')->group(function () {

    // Dashboard del estudiante
    Route::get('/estudiante/home', function () {
        abort_unless(auth()->user()->rol === 'Estudiante', 403);

        return view('estudiante.home');
    })->name('estudiante.home');

    Route::get('/coordinador/home', function () {
        abort_unless(auth()->user()->rol === 'Coordinador', 403);

        return view('coordinador.home');
    })->name('coordinador.home');

    // Cerrar sesión
    Route::post('/logout', [AuthController::class, 'cerrarSesion'])
        ->name('logout');
});
