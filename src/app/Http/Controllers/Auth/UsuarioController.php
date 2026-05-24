<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

// Controlador que gestiona la administración de usuarios del sistema y sus credenciales
class UsuarioController extends Controller
{
    // Muestra la lista de todos los usuarios registrados en el sistema, ordenados por nombre completo
    public function index()
    {
        $usuarios = Usuario::orderBy('nombre_completo')->get();
        return view('usuarios.index', compact('usuarios'));
    }

    // Muestra el formulario para registrar un nuevo usuario
    public function create()
    {
        return view('usuarios.create');
    }

    // Registra un nuevo usuario en la base de datos después de validar sus datos
    public function store(Request $request)
    {
        // Valida que los datos del nuevo usuario cumplan con las reglas de seguridad
        $request->validate([
            'nombre_completo' => 'required|string|max:150',
            'username'        => 'required|string|max:60|unique:usuario,username',
            'password'        => 'required|string|min:8|regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/',
            'rol'             => 'required|in:dueno,vendedor,medico',
        ], [
            // Mensajes de error personalizados para retroalimentación clara del usuario
            'username.unique' => 'El nombre de usuario ya existe en el sistema.',
            'password.min'    => 'La contraseña debe tener al menos 8 caracteres.',
            'password.regex'  => 'La contraseña debe combinar letras y números.',
        ]);

        // Crea el usuario con la contraseña encriptada de forma segura
        Usuario::create([
            'nombre_completo' => $request->nombre_completo,
            'username'        => $request->username,
            'password_hash'   => Hash::make($request->password),
            'rol'             => $request->rol,
            'estado'          => 'activo',
        ]);

        return redirect()->route('usuarios.index')
                         ->with('success', 'Usuario creado exitosamente.');
    }

    // Muestra el formulario de edición para un usuario específico
    public function edit(int $id)
    {
        $usuario = Usuario::findOrFail($id);
        return view('usuarios.edit', compact('usuario'));
    }

    // Actualiza los datos de un usuario existente
    public function update(Request $request, int $id)
    {
        $usuario = Usuario::findOrFail($id);

        // Valida que el nombre de usuario siga siendo único, excluyendo al usuario actual
        $request->validate([
            'nombre_completo' => 'required|string|max:150',
            'username'        => "required|string|max:60|unique:usuario,username,{$id}",
            'rol'             => 'required|in:dueno,vendedor,medico',
        ]);

        // Actualiza el nombre, el nombre de usuario y el rol del usuario
        $usuario->update([
            'nombre_completo' => $request->nombre_completo,
            'username'        => $request->username,
            'rol'             => $request->rol,
        ]);

        return redirect()->route('usuarios.index')
                         ->with('success', 'Usuario actualizado correctamente.');
    }

    // Activa o desactiva la cuenta de un usuario en el sistema
    public function toggleEstado(int $id)
    {
        $usuario = Usuario::findOrFail($id);

        // Impide que un usuario se desactive a sí mismo para evitar el bloqueo del sistema
        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes desactivar tu propia cuenta.');
        }

        // Alterna entre activo e inactivo y actualiza la base de datos
        $nuevoEstado = $usuario->estado === 'activo' ? 'inactivo' : 'activo';
        $usuario->update(['estado' => $nuevoEstado]);

        $msg = $nuevoEstado === 'activo' ? 'activado' : 'desactivado';
        return back()->with('success', "Usuario {$msg} correctamente.");
    }

    // Genera una contraseña temporal aleatoria para un usuario y la actualiza en la base de datos
    public function resetPassword(int $id)
    {
        $usuario   = Usuario::findOrFail($id);
        // Genera una contraseña aleatoria de 10 caracteres
        $nuevaPass = Str::random(10);

        // Encripta y guarda la nueva contraseña temporal
        $usuario->update(['password_hash' => Hash::make($nuevaPass)]);

        return back()->with('success', "Contraseña temporal: {$nuevaPass} — Comunícala al usuario.");
    }

    // Muestra la vista para que el usuario actual cambie su propia contraseña
    // Cambiar propia contraseña (FA_004 de CU-03)
    public function showCambiarPassword()
    {
        return view('usuarios.cambiar-password');
    }

    // Guarda la nueva contraseña definida por el propio usuario
    public function cambiarPassword(Request $request)
    {
        // Valida que la contraseña nueva cumpla con las políticas de seguridad y coincida con la confirmación
        $request->validate([
            'password_actual' => 'required',
            'password_nuevo'  => 'required|min:8|regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/|confirmed',
        ], [
            'password_nuevo.min'    => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password_nuevo.regex'  => 'La nueva contraseña debe combinar letras y números.',
            'password_nuevo.confirmed' => 'La confirmación de contraseña no coincide.',
        ]);

        // Verifica que la contraseña actual ingresada sea correcta
        if (!Hash::check($request->password_actual, auth()->user()->password_hash)) {
            return back()->withErrors(['password_actual' => 'La contraseña actual es incorrecta.']);
        }

        // Actualiza el hash de la contraseña en el usuario autenticado
        auth()->user()->update(['password_hash' => Hash::make($request->password_nuevo)]);
        return back()->with('success', 'Contraseña actualizada exitosamente.');
    }
}
