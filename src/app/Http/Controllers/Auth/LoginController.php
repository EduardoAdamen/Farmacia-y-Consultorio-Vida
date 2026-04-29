<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'El usuario es obligatorio.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        // Buscar usuario activo (sin bloqueo por intentos en esta versión)
        $usuario = Usuario::where('username', $request->username)
                          ->where('estado', 'activo')
                          ->first();

        if (!$usuario || !Hash::check($request->password, $usuario->password_hash)) {
            return back()->withErrors([
                'username' => 'Nombre de usuario o contraseña incorrectos. Intente de nuevo.'
            ])->withInput(['username' => $request->username]);
        }

        Auth::login($usuario);
        $request->session()->regenerate();
        session(['ultima_actividad' => time()]);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Sesión cerrada correctamente.');
    }
}
