<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecetaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogoController;

// Rutas públicas
Route::get('/recetas', [RecetaController::class, 'index']);

Route::get('/recetas/{id}', [RecetaController::class, 'show'])
    ->whereNumber('id');

// Catálogos públicos para los formularios (categorías e ingredientes)
Route::get('/categorias', [CatalogoController::class, 'categorias']);
Route::get('/ingredientes', [CatalogoController::class, 'ingredientes']);

// Login público
Route::post('/login', [AuthController::class, 'login']);

// Registro público
Route::post('/register', [AuthController::class, 'register']);

// Rutas protegidas con Sanctum
Route::middleware('auth:sanctum')->group(function () {

    // Crear ingrediente nuevo (si no existe en el catálogo)
    Route::post('/ingredientes', [CatalogoController::class, 'crearIngrediente']);

    // Crear receta
    Route::post('/recetas', [RecetaController::class, 'store']);

    // Actualizar receta
    Route::put('/recetas/{id}', [RecetaController::class, 'update'])
        ->whereNumber('id');

    // Eliminar receta
    Route::delete('/recetas/{id}', [RecetaController::class, 'destroy'])
        ->whereNumber('id');

    // Cerrar sesión
    Route::post('/logout', [AuthController::class, 'logout']);
});
