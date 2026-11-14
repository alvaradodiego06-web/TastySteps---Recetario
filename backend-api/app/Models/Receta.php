<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Receta extends Model
{
    protected $table = 'recetas';
    protected $primaryKey = 'id_receta';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_categoria',
        'nombre_platillo',
        'tiempo_preparacion',
        'dificultad',
        'porciones',
        'imagen_url',
        'estado',
        'cantidad_medida',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            Usuario::class,
            'id_usuario',
            'id_usuario'
        );
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(
            Categoria::class,
            'id_categoria',
            'id_categoria'
        );
    }

    public function ingredientes(): BelongsToMany
    {
        return $this->belongsToMany(
            Ingrediente::class,
            'receta_ingredientes',
            'id_receta',
            'id_ingrediente'
        )->withPivot('id_receta_ingrediente');
    }

    public function pasos(): HasMany
    {
        return $this->hasMany(
            Paso::class,
            'id_receta',
            'id_receta'
        )->orderBy('numero_orden');
    }
}
