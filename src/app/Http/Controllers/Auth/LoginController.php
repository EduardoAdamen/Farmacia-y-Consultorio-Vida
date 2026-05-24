<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

// Controlador que gestiona la autenticación de usuarios (inicio y cierre de sesión)
class LoginController extends Controller
{
    // Muestra el formulario de inicio de sesión si el usuario no ha ingresado
    public function showLoginForm()
    {
        // Si el usuario ya está autenticado, lo redirige directamente al panel de inicio
        if (Auth::check()) {
            return redirect()->route('panel-inicio');
        }
        return view('auth.login');
    }

    // Procesa la solicitud de inicio de sesión del usuario
    public function login(Request $request)
    {
        // Valida que el nombre de usuario y la contraseña sean obligatorios
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'El usuario es obligatorio.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        // Busca un usuario en la base de datos que coincida con el nombre y esté activo
        $usuario = Usuario::where('username', $request->username)
                          ->where('estado', 'activo')
                          ->first();

        // Si el usuario no existe o la contraseña no coincide con el hash, regresa con error
        if (!$usuario || !Hash::check($request->password, $usuario->password_hash)) {
            return back()->withErrors([
                'username' => 'Nombre de usuario o contraseña incorrectos. Intente de nuevo.'
            ])->withInput(['username' => $request->username]);
        }

        // Autentica al usuario en la sesión y regenera la sesión para evitar ataques de fijación
        Auth::login($usuario);
        $request->session()->regenerate();
        // Registra el tiempo de la última actividad para el control de inactividad
        session(['ultima_actividad' => time()]);

        return redirect()->route('panel-inicio');
    }

    // Cierra la sesión activa del usuario y limpia los datos de sesión
    public function logout(Request $request)
    {
        // Cierra sesión en el guard de Laravel
        Auth::logout();
        // Invalida la sesión actual del usuario
        $request->session()->invalidate();
        // Regenera el token CSRF por seguridad
        $request->session()->regenerateToken();
        
        return redirect()->route('login')->with('success', 'Sesión cerrada correctamente.');
    }
}
