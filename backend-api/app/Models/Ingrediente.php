<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ingrediente extends Model
{
    protected $table = 'ingredientes';
    protected $primaryKey = 'id_ingrediente';
    public $timestamps = false;

    protected $fillable = [
        'nombre_ingrediente',
    ];

    public function recetas(): BelongsToMany
    {
        return $this->belongsToMany(
            Receta::class,
            'receta_ingredientes',
            'id_ingrediente',
            'id_receta'
        )->withPivot('id_receta_ingrediente');
    }
}
