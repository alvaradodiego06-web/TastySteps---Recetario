<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('receta_ingredientes', function (Blueprint $table) {
        $table->id('id_receta_ingrediente');

        $table->unsignedBigInteger('id_receta');
        $table->unsignedBigInteger('id_ingrediente');

        $table->foreign('id_receta')
            ->references('id_receta')
            ->on('recetas');

        $table->foreign('id_ingrediente')
            ->references('id_ingrediente')
            ->on('ingredientes');

        $table->unique(['id_receta', 'id_ingrediente']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receta_ingredientes');
    }
};
