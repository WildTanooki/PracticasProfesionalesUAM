<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function mostrarLogin()
    {
        return view('auth.login');
    }

    public function iniciarSesion(Request $request)
    {
        $datos = $request->validate([
            'credencial' => ['required', 'string'],
            'contrasena' => ['required', 'string'],
        ]);

        $credenciales = [
            'credencial' => $datos['credencial'],
            'password' => $datos['contrasena'],
        ];

        if (! Auth::attempt($credenciales)) {
            return back()
                ->withErrors([
                    'credencial' => 'La credencial o la contraseña son incorrectas.',
                ])
                ->onlyInput('credencial');
        }

        // Renovar el identificador de sesión después del login
        $request->session()->regenerate();


        
        $usuario = Auth::user();

        // Por ahora solo está habilitado el dashboard del estudiante
        if ($usuario->rol === 'Estudiante') {
            return redirect()->route('estudiante.home');
        }

        if ($usuario->rol === 'Coordinador') {
            return redirect()->route('coordinador.home');
        }

        // No mantener abierta una sesión si el rol aún no tiene ruta
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->withErrors([
                'credencial' => 'El acceso para este rol todavía no está configurado.',
            ]);
    }

    public function cerrarSesion(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
