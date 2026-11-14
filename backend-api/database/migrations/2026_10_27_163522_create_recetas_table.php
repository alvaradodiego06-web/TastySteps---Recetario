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
      Schema::create('recetas', function (Blueprint $table) {
        $table->id('id_receta');

        $table->unsignedBigInteger('id_usuario');
        $table->unsignedBigInteger('id_categoria');

        $table->string('nombre_platillo', 150);
        $table->integer('tiempo_preparacion');
        $table->string('dificultad', 20);
        $table->integer('porciones');

        $table->string('imagen_url', 255)->nullable();
        $table->string('estado', 20);
        $table->string('cantidad_medida', 100)->nullable();

        $table->foreign('id_usuario')
            ->references('id_usuario')
            ->on('usuarios');

        $table->foreign('id_categoria')
            ->references('id_categoria')
            ->on('categorias');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recetas');
    }
};
