<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

// Controlador que maneja las operaciones para administrar las categorías de productos
class CategoriaController extends Controller
{
    // Muestra el listado de todas las categorías ordenadas alfabéticamente
    public function index()
    {
        // Pagina los resultados de 20 en 20 para no cargar todas las categorías de golpe
        $categorias = Categoria::orderBy('nombre')->paginate(20);
        return view('categorias.index', compact('categorias'));
    }

    // Muestra el formulario para registrar una nueva categoría
    public function create()
    {
        return view('categorias.create');
    }

    // Guarda una nueva categoría en la base de datos
    public function store(Request $request)
    {
        // Valida que el nombre sea único y no exceda los límites de longitud permitidos
        $request->validate([
            'nombre'      => 'required|string|max:100|unique:categoria,nombre',
            'descripcion' => 'nullable|string|max:255',
        ], [
            // Mensajes de error personalizados para mostrar al usuario en caso de fallo
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.unique'   => 'Este nombre de categoría ya está registrado.',
            'nombre.max'      => 'El nombre no puede exceder los 100 caracteres.',
            'descripcion.max' => 'La descripción no puede exceder los 255 caracteres.',
        ]);

        // Guarda solo los campos permitidos 
        Categoria::create($request->only(['nombre', 'descripcion']));

        // Redirige al listado mostrando un mensaje de confirmación
        return redirect()->route('categorias.index')
                         ->with('success', 'Categoría creada exitosamente.');
    }

    // Muestra el formulario para editar una categoría existente
    public function edit(int $id)
    {
        // Si la categoría no existe, lanza un error 404 automáticamente
        $categoria = Categoria::findOrFail($id);
        return view('categorias.edit', compact('categoria'));
    }

    // Guarda los cambios realizados a una categoría existente
    public function update(Request $request, int $id)
    {
        $categoria = Categoria::findOrFail($id);

        // La regla unique ignora el registro actual para no marcarlo como duplicado de sí mismo
        $request->validate([
            'nombre'      => "required|string|max:100|unique:categoria,nombre,{$id}",
            'descripcion' => 'nullable|string|max:255',
        ], [
            // Mensajes de error personalizados para mostrar al usuario en caso de fallo
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.unique'   => 'Este nombre de categoría ya está registrado.',
            'nombre.max'      => 'El nombre no puede exceder los 100 caracteres.',
            'descripcion.max' => 'La descripción no puede exceder los 255 caracteres.',
        ]);

        // Actualiza solo los campos permitidos en la base de datos
        $categoria->update($request->only(['nombre', 'descripcion']));

        // Redirige al listado mostrando un mensaje de confirmación
        return redirect()->route('categorias.index')
                         ->with('success', 'Categoría actualizada exitosamente.');
    }
}