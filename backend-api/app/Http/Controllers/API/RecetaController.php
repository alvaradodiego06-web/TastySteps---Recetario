<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Receta;
use Illuminate\Http\JsonResponse;

class RecetaController extends Controller
{
    // GET /api/recetas
    public function index(): JsonResponse
    {
        $recetas = Receta::with('categoria')
            ->get();

        return response()->json([
            'mensaje' => 'Listado de recetas',
            'datos' => $recetas,
        ], 200);
    }

    // GET /api/recetas/{id}
    public function show(string $id): JsonResponse
    {
        $receta = Receta::with([
            'categoria',
            'ingredientes',
            'pasos',
        ])->find($id);

        if (!$receta) {
            return response()->json([
                'mensaje' => 'Receta no encontrada',
            ], 404);
        }

        return response()->json([
            'mensaje' => 'Detalle de la receta',
            'datos' => $receta,
        ], 200);
    }
}