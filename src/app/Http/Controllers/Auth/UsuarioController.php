<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = Usuario::orderBy('nombre_completo')->get();
        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        return view('usuarios.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_completo' => 'required|string|max:150',
            'username'        => 'required|string|max:60|unique:usuario,username',
            'password'        => 'required|string|min:8|regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/',
            'rol'             => 'required|in:dueno,vendedor,medico',
        ], [
            'username.unique' => 'El nombre de usuario ya existe en el sistema.',
            'password.min'    => 'La contraseña debe tener al menos 8 caracteres.',
            'password.regex'  => 'La contraseña debe combinar letras y números.',
        ]);

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

    public function edit(int $id)
    {
        $usuario = Usuario::findOrFail($id);
        return view('usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, int $id)
    {
        $usuario = Usuario::findOrFail($id);

        $request->validate([
            'nombre_completo' => 'required|string|max:150',
            'username'        => "required|string|max:60|unique:usuario,username,{$id}",
            'rol'             => 'required|in:dueno,vendedor,medico',
        ]);

        $usuario->update([
            'nombre_completo' => $request->nombre_completo,
            'username'        => $request->username,
            'rol'             => $request->rol,
        ]);

        return redirect()->route('usuarios.index')
                         ->with('success', 'Usuario actualizado correctamente.');
    }

    public function toggleEstado(int $id)
    {
        $usuario = Usuario::findOrFail($id);

        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes desactivar tu propia cuenta.');
        }

        $nuevoEstado = $usuario->estado === 'activo' ? 'inactivo' : 'activo';
        $usuario->update(['estado' => $nuevoEstado]);

        $msg = $nuevoEstado === 'activo' ? 'activado' : 'desactivado';
        return back()->with('success', "Usuario {$msg} correctamente.");
    }

    public function resetPassword(int $id)
    {
        $usuario   = Usuario::findOrFail($id);
        $nuevaPass = Str::random(10);

        $usuario->update(['password_hash' => Hash::make($nuevaPass)]);

        return back()->with('success', "Contraseña temporal: {$nuevaPass} — Comunícala al usuario.");
    }

    // Cambiar propia contraseña (FA_004 de CU-03)
    public function showCambiarPassword()
    {
        return view('usuarios.cambiar-password');
    }

    public function cambiarPassword(Request $request)
    {
        $request->validate([
            'password_actual' => 'required',
            'password_nuevo'  => 'required|min:8|regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/|confirmed',
        ], [
            'password_nuevo.min'    => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password_nuevo.regex'  => 'La nueva contraseña debe combinar letras y números.',
            'password_nuevo.confirmed' => 'La confirmación de contraseña no coincide.',
        ]);

        if (!Hash::check($request->password_actual, auth()->user()->password_hash)) {
            return back()->withErrors(['password_actual' => 'La contraseña actual es incorrecta.']);
        }

        auth()->user()->update(['password_hash' => Hash::make($request->password_nuevo)]);
        return back()->with('success', 'Contraseña actualizada exitosamente.');
    }
}
