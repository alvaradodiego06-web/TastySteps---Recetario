<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Ingrediente;
use Illuminate\Database\Seeder;

class CatalogoSeeder extends Seeder
{
    public function run(): void
    {
        // Los nombres deben coincidir con los que muestra la app Ionic
        foreach (['Desayunos', 'Almuerzos', 'Cenas', 'Postres'] as $nombre) {
            Categoria::firstOrCreate(['nombre_categoria' => $nombre]);
        }

        $ingredientes = [
            'Harina', 'Harina de maíz', 'Sal', 'Azúcar', 'Huevo', 'Leche',
            'Mantequilla', 'Aceite de oliva', 'Queso', 'Pollo', 'Carne molida',
            'Arroz', 'Frijoles', 'Papas', 'Zanahoria', 'Tomate', 'Cebolla',
            'Ajo', 'Cilantro', 'Albahaca', 'Romero', 'Limón', 'Pasta',
            'Tortillas', 'Chocolate', 'Caldo',
        ];

        foreach ($ingredientes as $nombre) {
            Ingrediente::firstOrCreate(['nombre_ingrediente' => $nombre]);
        }
    }
}
