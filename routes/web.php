<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
//Controladores
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FormatoController;

use App\Http\Controllers\Estudiante\DashboardController;
use App\Http\Controllers\Estudiante\CartaAprobacionController;
use App\Http\Controllers\Estudiante\CartaRecomendacionController;
use App\Http\Controllers\Estudiante\PasantiaController;

use App\Http\Controllers\Coordinador\PasantiaCoordinadorController;
use App\Http\Controllers\Coordinador\CartaAprobacionCoordinadorController;
use App\Http\Controllers\Coordinador\CartaRecomendacionCoordinadorController;

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
    
    // Rutas para el manejo de formatos del estudiante
    Route::get(
            '/estudiante/formatos',
            [FormatoController::class, 'formatosEstudiante']
    )->name('estudiante.formatos');
    
    Route::get(
        '/estudiante/formatos/{idFormato}/descargar',
        [FormatoController::class, 'descargarEstudiante']
    )->name('estudiante.formatos.descargar');

    Route::get(
    '/estudiante/cartas-recomendacion/{idCartaRecomendacion}/ver',
    [CartaRecomendacionController::class, 'verCarta']
    )->middleware('auth')->name('estudiante.recomendacion.verCarta');

    //ver las pasantias del estudiante
    Route::get('/estudiante/pasantias', 
    [PasantiaController::class, 'index'])
    ->name('estudiante.pasantias');

    //ver detalles de una pasantia
    Route::get(
    '/estudiante/pasantias/{idPasantia}',
    [PasantiaController::class, 'show']
    )->name('estudiante.pasantias.show');


    // -------------------------------------------
    // |Aquí comienzan las rutas del coordinador.|
    // -------------------------------------------


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

    //rutas para el manejo de cartas de recomendación del coordinador
    Route::get('/coordinador/recomendacion', [
        CartaRecomendacionCoordinadorController::class,
        'index'
    ])->name('coordinador.recomendacion');

    //gestionar cartas de recomendacion
    Route::get(
    '/coordinador/cartas-recomendacion/{idCartaRecomendacion}',
    [CartaRecomendacionCoordinadorController::class, 'show']
    )->middleware('auth')->name('coordinador.recomendacion.show');

    Route::post(
    '/coordinador/cartas-recomendacion/{idCartaRecomendacion}/resolver',
    [CartaRecomendacionCoordinadorController::class, 'resolver']
    )->middleware('auth')->name('coordinador.recomendacion.resolver');

    // Listado de cartas de aprobación del coordinador
    Route::get('/coordinador/aceptacion', [
        CartaAprobacionCoordinadorController::class,
        'index'
        ])->name('coordinador.aceptacion');
        
        // Gestionar o consultar una carta de aprobación
        Route::get(
            '/coordinador/aceptacion/{idCartaAprobacion}/gestionar',[
                CartaAprobacionCoordinadorController::class,
                'gestionar'
        ])->name('coordinador.aceptacion.gestionar');

        // Resolver estado una carta de aprobación
        Route::post(
            '/coordinador/aceptacion/{idCartaAprobacion}/resolver',
            [
                CartaAprobacionCoordinadorController::class,
                'resolver'
            ])->name('coordinador.aceptacion.resolver');

        Route::post(
            '/coordinador/aceptacion/{idCartaAprobacion}/crear-pasantia',
            [PasantiaCoordinadorController::class, 'store']
            )->name('coordinador.aceptacion.pasantia.store');

    // Rutas de provisionales para el coordinador
    $rutasCoordinador = [
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
