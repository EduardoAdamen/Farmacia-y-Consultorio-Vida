<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index()
    {
        $categorias = Categoria::orderBy('nombre')->paginate(20);
        return view('categorias.index', compact('categorias'));
    }

    public function create()
    {
        return view('categorias.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:100|unique:categoria,nombre',
            'descripcion' => 'nullable|string|max:255',
        ], [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.unique'   => 'Este nombre de categoría ya está registrado.',
            'nombre.max'      => 'El nombre no puede exceder los 100 caracteres.',
            'descripcion.max' => 'La descripción no puede exceder los 255 caracteres.',
        ]);

        Categoria::create($request->only(['nombre', 'descripcion']));

        return redirect()->route('categorias.index')
                         ->with('success', 'Categoría creada exitosamente.');
    }

    public function edit(int $id)
    {
        $categoria = Categoria::findOrFail($id);
        return view('categorias.edit', compact('categoria'));
    }

    public function update(Request $request, int $id)
    {
        $categoria = Categoria::findOrFail($id);

        $request->validate([
            'nombre'      => "required|string|max:100|unique:categoria,nombre,{$id}",
            'descripcion' => 'nullable|string|max:255',
        ], [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.unique'   => 'Este nombre de categoría ya está registrado.',
            'nombre.max'      => 'El nombre no puede exceder los 100 caracteres.',
            'descripcion.max' => 'La descripción no puede exceder los 255 caracteres.',
        ]);

        $categoria->update($request->only(['nombre', 'descripcion']));

        return redirect()->route('categorias.index')
                         ->with('success', 'Categoría actualizada exitosamente.');
    }
}
