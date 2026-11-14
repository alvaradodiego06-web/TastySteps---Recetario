<?php

namespace App\Http\Controllers;

use App\Models\Receta;
use App\Models\Ingrediente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class RecetaController extends Controller
{
    // GET: listar recetas
    // - Visitantes (app Ionic): solo las que están en estado "Publicado"
    // - Usuario con sesión (panel Vue): las publicadas + las suyas
    //   (el administrador ve todas)
    public function index()
    {
        $consulta = Receta::with([
            'categoria',
            'usuario:id_usuario,Nombre',
            'ingredientes',
            'pasos'
        ])->orderByDesc('id_receta');

        $usuario = auth('sanctum')->user();

        if (!$usuario) {
            $consulta->where('estado', 'Publicado');
        } elseif (!$this->esAdministrador($usuario)) {
            $consulta->where(function ($q) use ($usuario) {
                $q->where('estado', 'Publicado')
                  ->orWhere('id_usuario', $usuario->id_usuario);
            });
        }

        return response()->json([
            'mensaje' => 'Lista de recetas',
            'recetas' => $consulta->get()
        ], 200);
    }

    // GET: consultar una receta por ID
    public function show($id)
    {
        $receta = Receta::with([
            'categoria',
            'usuario:id_usuario,Nombre',
            'ingredientes',
            'pasos'
        ])->find($id);

        $usuario = auth('sanctum')->user();

        if (!$receta || !$this->puedeVer($usuario, $receta)) {
            return response()->json([
                'mensaje' => 'Receta no encontrada'
            ], 404);
        }

        return response()->json([
            'mensaje' => 'Receta encontrada',
            'receta' => $receta
        ], 200);
    }

    public function store(Request $request)
    {
        // 1. Validar información recibida
        $datos = $request->validate([
            'id_categoria' => [
                'required',
                'exists:categorias,id_categoria'
            ],
            'nombre_platillo' => [
                'required',
                'string',
                'max:150'
            ],
            'tiempo_preparacion' => [
                'required',
                'integer',
                'min:1'
            ],
            'dificultad' => [
                'required',
                Rule::in(['Fácil', 'Media', 'Difícil'])
            ],
            'porciones' => [
                'required',
                'integer',
                'min:1'
            ],
            'imagen' => [
                'nullable',
                'image',
                'max:5120'
            ],
            'estado' => [
                'required',
                Rule::in([
                    'Publicado',
                    'Borrador',
                    'Oculto'
                ])
            ],
            'cantidad_medida' => [
                'nullable',
                'string',
                'max:100'
            ],
            'ingredientes' => [
                'required',
                'array',
                'min:1'
            ],
            'ingredientes.*' => [
                'required',
                'integer',
                'distinct',
                'exists:ingredientes,id_ingrediente'
            ],
            'pasos' => [
                'required',
                'array',
                'min:1'
            ],
            'pasos.*' => [
                'required',
                'string'
            ],
        ]);

        // 2. Obtener usuario autenticado
        $usuario = $request->user();

        // 3. Guardar imagen
        $rutaImagen = null;

        if ($request->hasFile('imagen')) {
            $rutaImagen = $request->file('imagen')
                ->store('recetas', 'public');
        }

        // 4. Crear receta y relaciones
        try {
            $receta = DB::transaction(function () use (
                $datos,
                $usuario,
                $rutaImagen
            ) {
                $receta = Receta::create([
                    'id_usuario' => $usuario->id_usuario,
                    'id_categoria' => $datos['id_categoria'],
                    'nombre_platillo' => $datos['nombre_platillo'],
                    'tiempo_preparacion' => $datos['tiempo_preparacion'],
                    'dificultad' => $datos['dificultad'],
                    'porciones' => $datos['porciones'],
                    'imagen_url' => $rutaImagen,
                    'estado' => $datos['estado'],
                    'cantidad_medida' => $datos['cantidad_medida'] ?? null,
                ]);

                // Asociar ingredientes
                $receta->ingredientes()->sync(
                    $datos['ingredientes']
                );

                // Registrar pasos ordenados
                foreach ($datos['pasos'] as $indice => $descripcion) {
                    $receta->pasos()->create([
                        'numero_orden' => $indice + 1,
                        'descripcion_paso' => $descripcion
                    ]);
                }

                return $receta;
            });
        } catch (\Throwable $e) {
            if ($rutaImagen) {
                Storage::disk('public')->delete($rutaImagen);
            }

            throw $e;
        }

        // 5. Responder
        return response()->json([
            'mensaje' => 'Receta creada correctamente',
            'receta' => $receta->load([
                'categoria',
                'ingredientes',
                'pasos'
            ])
        ], 201);
    }

    // Comprobar si el usuario es administrador
    private function esAdministrador($usuario): bool
    {
        return in_array(
            strtolower($usuario->Rol),
            ['admin', 'administrador']
        );
    }

    // Comprobar permisos sobre una receta
    private function puedeModificar($usuario, $receta): bool
    {
        return $receta->id_usuario == $usuario->id_usuario
            || $this->esAdministrador($usuario);
    }

    // Comprobar si se puede ver una receta (las no publicadas son privadas)
    private function puedeVer($usuario, $receta): bool
    {
        if ($receta->estado === 'Publicado') {
            return true;
        }

        return $usuario && $this->puedeModificar($usuario, $receta);
    }

    // PUT: actualizar una receta
    public function update(Request $request, $id)
    {
        $receta = Receta::find($id);

        if (!$receta) {
            return response()->json([
                'mensaje' => 'Receta no encontrada'
            ], 404);
        }

        $usuario = $request->user();

        // Verificar permisos
        if (!$this->puedeModificar($usuario, $receta)) {
            return response()->json([
                'mensaje' => 'No tienes permiso para modificar esta receta'
            ], 403);
        }

        // Validar información
        $datos = $request->validate([
            'id_categoria' => [
                'sometimes',
                'exists:categorias,id_categoria'
            ],
            'nombre_platillo' => [
                'sometimes',
                'string',
                'max:150'
            ],
            'tiempo_preparacion' => [
                'sometimes',
                'integer',
                'min:1'
            ],
            'dificultad' => [
                'sometimes',
                Rule::in(['Fácil', 'Media', 'Difícil'])
            ],
            'porciones' => [
                'sometimes',
                'integer',
                'min:1'
            ],
            'imagen' => [
                'nullable',
                'image',
                'max:5120'
            ],
            'estado' => [
                'sometimes',
                Rule::in([
                    'Publicado',
                    'Borrador',
                    'Oculto'
                ])
            ],
            'cantidad_medida' => [
                'sometimes',
                'nullable',
                'string',
                'max:100'
            ],
            'ingredientes' => [
                'sometimes',
                'array',
                'min:1'
            ],
            'ingredientes.*' => [
                'required',
                'integer',
                'distinct',
                'exists:ingredientes,id_ingrediente'
            ],
            'pasos' => [
                'sometimes',
                'array',
                'min:1'
            ],
            'pasos.*' => [
                'required',
                'string'
            ],
        ]);

        $rutaAnterior = $receta->imagen_url;
        $rutaNueva = null;

        if ($request->hasFile('imagen')) {
            $rutaNueva = $request->file('imagen')
                ->store('recetas', 'public');
        }

        try {
            DB::transaction(function () use (
                $datos,
                $request,
                $receta,
                $rutaNueva
            ) {
                // Actualizar imagen si se recibió una nueva
                if ($rutaNueva) {
                    $datos['imagen_url'] = $rutaNueva;
                }

                // Separar los datos de las relaciones
                $campos = $datos;

                unset(
                    $campos['imagen'],
                    $campos['ingredientes'],
                    $campos['pasos']
                );

                $receta->update($campos);

                // Actualizar ingredientes si fueron enviados
                if (array_key_exists('ingredientes', $datos)) {
                    $receta->ingredientes()->sync(
                        $datos['ingredientes']
                    );
                }

                // Actualizar pasos si fueron enviados
                if (array_key_exists('pasos', $datos)) {
                    $receta->pasos()->delete();

                    foreach ($datos['pasos'] as $indice => $descripcion) {
                        $receta->pasos()->create([
                            'numero_orden' => $indice + 1,
                            'descripcion_paso' => $descripcion
                        ]);
                    }
                }
            });
        } catch (\Throwable $e) {
            if ($rutaNueva) {
                Storage::disk('public')->delete($rutaNueva);
            }

            throw $e;
        }

        // Eliminar imagen anterior después de actualizar
        if ($rutaNueva && $rutaAnterior) {
            Storage::disk('public')->delete($rutaAnterior);
        }

        return response()->json([
            'mensaje' => 'Receta actualizada correctamente',
            'receta' => $receta->fresh()->load([
                'categoria',
                'ingredientes',
                'pasos'
            ])
        ], 200);
    }

    // DELETE: eliminar una receta
    public function destroy(Request $request, $id)
    {
        $receta = Receta::find($id);

        if (!$receta) {
            return response()->json([
                'mensaje' => 'Receta no encontrada'
            ], 404);
        }

        $usuario = $request->user();

        // Verificar permisos
        if (!$this->puedeModificar($usuario, $receta)) {
            return response()->json([
                'mensaje' => 'No tienes permiso para eliminar esta receta'
            ], 403);
        }

        $rutaImagen = $receta->imagen_url;

        DB::transaction(function () use ($receta) {
            // Eliminar relaciones con ingredientes
            $receta->ingredientes()->detach();

            // Eliminar pasos
            $receta->pasos()->delete();

            // Eliminar receta
            $receta->delete();
        });

        // Eliminar archivo de imagen
        if ($rutaImagen) {
            Storage::disk('public')->delete($rutaImagen);
        }

        return response()->json([
            'mensaje' => 'Receta eliminada correctamente'
        ], 200);
    }
}
