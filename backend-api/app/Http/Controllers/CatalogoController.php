<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Ingrediente;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    // GET /api/categorias
    public function categorias()
{
    // Si la tabla está vacía, crea las categorías base
    if (Categoria::count() === 0) {
        foreach (['Desayunos', 'Almuerzos', 'Cenas', 'Postres'] as $nombre) {
            Categoria::create(['nombre_categoria' => $nombre]);
        }
    }

    return response()->json(
        Categoria::orderBy('nombre_categoria')->get(),
        200
    );
}

    // GET /api/ingredientes
    public function ingredientes()
    {
        return response()->json(
            Ingrediente::orderBy('nombre_ingrediente')->get(),
            200
        );
    }

    // POST /api/ingredientes (requiere sesión)
    public function crearIngrediente(Request $request)
    {
        $datos = $request->validate([
            'nombre_ingrediente' => ['required', 'string', 'max:100'],
        ]);

        $nombre = trim($datos['nombre_ingrediente']);

        // Si ya existe (sin distinguir mayúsculas), se devuelve el existente
        $existente = Ingrediente::whereRaw(
            'LOWER(nombre_ingrediente) = ?',
            [mb_strtolower($nombre)]
        )->first();

        if ($existente) {
            return response()->json($existente, 200);
        }

        $ingrediente = Ingrediente::create([
            'nombre_ingrediente' => $nombre,
        ]);

        return response()->json($ingrediente, 201);
    }
}
