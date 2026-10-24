<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
//Controladores
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FormatoController;
use App\Http\Controllers\Estudiante\DashboardController;
use App\Http\Controllers\Estudiante\CartaAprobacionController;
use App\Http\Controllers\Estudiante\CartaRecomendacionController;

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
        return app(\App\Http\Controllers\Estudiante\DashboardController::class)
        ->index(request());
    })->name('estudiante.home');

    // Listado de cartas de aprobación del estudiante.
    Route::get('/estudiante/aceptacion', function () {
        abort_unless(auth()->user()->rol === 'Estudiante', 403);
        return app(\App\Http\Controllers\Estudiante\CartaAprobacionController::class)
        ->index();
    })->name('estudiante.aceptacion');

    //creación de carta de aprobación
    Route::get('/estudiante/aceptacion/crear', [
        CartaAprobacionController::class, 'create'
    ])->name('estudiante.aceptacion.crear');
    
    Route::post('/estudiante/aceptacion', [
        CartaAprobacionController::class, 'store'
    ])->name('estudiante.aceptacion.store');

    //editar carta de aprobación
    Route::get('/estudiante/aceptacion/{idCartaAprobacion}/editar', [
        CartaAprobacionController::class,
        'edit'
    ])->name('estudiante.aceptacion.editar');
    
    Route::put('/estudiante/aceptacion/{idCartaAprobacion}', [
         CartaAprobacionController::class,
         'update'
         ])->name('estudiante.aceptacion.update');

    //eliminar carta de aprobación
    Route::delete('/estudiante/aceptacion/{idCartaAprobacion}', [
        CartaAprobacionController::class,
        'destroy'
    ])->name('estudiante.aceptacion.eliminar');

        // Listado de cartas de aprobación del estudiante.
    Route::get('/estudiante/recomendacion', function () {
        abort_unless(auth()->user()->rol === 'Estudiante', 403);
        return app(\App\Http\Controllers\Estudiante\CartaRecomendacionController::class)
        ->index();
    })->name('estudiante.recomendacion');

    //creación de carta de recomendación
    Route::get('/estudiante/recomendacion/crear', [
        CartaRecomendacionController::class, 'create'
    ])->name('estudiante.recomendacion.crear');
    
    Route::post('/estudiante/recomendacion', [
        CartaRecomendacionController::class, 'store'
    ])->name('estudiante.recomendacion.store');

    //editar carta de recomendación
    Route::get('/estudiante/recomendacion/{idCartaRecomendacion}/editar', [
        CartaRecomendacionController::class,
        'edit'
    ])->name('estudiante.recomendacion.editar');
    
    Route::put('/estudiante/recomendacion/{idCartaRecomendacion}', [
         CartaRecomendacionController::class,
         'update'
         ])->name('estudiante.recomendacion.update');

    //eliminar carta de recomendación
    Route::delete('/estudiante/recomendacion/{idCartaRecomendacion}', [
        CartaRecomendacionController::class,
        'destroy'
    ])->name('estudiante.recomendacion.eliminar');

    // Dashboard del coordinador
    Route::get('/coordinador/home', function () {
        abort_unless(auth()->user()->rol === 'Coordinador', 403);
        return view('coordinador.home');
    })->name('coordinador.home');

    //rutas para el manejo de formatos del coordinador
    Route::get('/coordinador/formatos', [
        FormatoController::class,
        'index'
        ])->name('coordinador.formatos');
        
    Route::post('/coordinador/formatos', [
        FormatoController::class,
        'store'
    ])->name('coordinador.formatos.store');

    Route::put('/coordinador/formatos/{idFormato}', [
        FormatoController::class,
        'update'
    ])->name('coordinador.formatos.update');
        
    Route::get('/coordinador/formatos/{idFormato}/descargar', [
        FormatoController::class,
        'descargar'
    ])->name('coordinador.formatos.descargar');

    // Rutas de provisionales para el estudiante
    $rutasEstudiante = [
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
