<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Paso extends Model
{
    protected $table = 'pasos';
    protected $primaryKey = 'id_paso';
    public $timestamps = false;

    protected $fillable = [
        'id_receta',
        'numero_orden',
        'descripcion_paso',
    ];

    public function receta(): BelongsTo
    {
        return $this->belongsTo(
            Receta::class,
            'id_receta',
            'id_receta'
        );
    }
}
