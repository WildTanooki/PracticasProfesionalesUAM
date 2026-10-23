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

    // Dashboard del coordinador
    Route::get('/coordinador/home', function () {
        abort_unless(auth()->user()->rol === 'Coordinador', 403);
        return view('coordinador.home');
    })->name('coordinador.home');

    // Rutas de provisionales para el estudiante
    $rutasEstudiante = [
        'aceptacion' => 'Cartas de aceptación',
        'recomendacion' => 'Cartas de recomendación',
        'formatos' => 'Descargar formatos de prácticas',
        'documentos' => 'Subir documentos pendientes',
    ];
    foreach ($rutasEstudiante as $ruta => $descripcion) {
        Route::get("/estudiante/{$ruta}", function () {
            abort_unless(auth()->user()->rol === 'Estudiante', 403);

            return redirect()->route('estudiante.home');
        })->name("estudiante.{$ruta}");
    }

    // Rutas de provisionales para el coordinador
    $rutasCoordinador = [
        'aceptacion' => 'Cartas de aceptación',
        'recomendacion' => 'Cartas de recomendación',
        'formatos' => 'Subir formatos de prácticas',
        'alertas' => 'Alertas y riesgos',
        'reglas' => 'Reglas parametrizables',
    ];
    foreach ($rutasCoordinador as $ruta => $descripcion) {
        Route::get("/coordinador/{$ruta}", function () {
            abort_unless(auth()->user()->rol === 'Coordinador', 403);

            return redirect()->route('coordinador.home');
        })->name("coordinador.{$ruta}");
    }

    // Cerrar sesión
    Route::post('/logout', [AuthController::class, 'cerrarSesion'])
        ->name('logout');
});
