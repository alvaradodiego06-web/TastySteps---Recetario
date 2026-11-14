<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecetaIngrediente extends Model
{
    protected $table = 'receta_ingredientes';
    protected $primaryKey = 'id_receta_ingrediente';
    public $timestamps = false;

    protected $fillable = [
        'id_receta',
        'id_ingrediente',
    ];

    public function receta(): BelongsTo
    {
        return $this->belongsTo(
            Receta::class,
            'id_receta',
            'id_receta'
        );
    }

    public function ingrediente(): BelongsTo
    {
        return $this->belongsTo(
            Ingrediente::class,
            'id_ingrediente',
            'id_ingrediente'
        );
    }
}
