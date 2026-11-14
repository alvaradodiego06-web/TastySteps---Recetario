<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Usuario extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';
    public $timestamps = false;

    protected $fillable = [
        'Nombre',
        'Correo',
        'Contraseña',
        'Rol',
    ];

    protected $hidden = [
        'Contraseña',
    ];

    public function getAuthPassword()
    {
        return $this->Contraseña;
    }

    public function recetas(): HasMany
    {
        return $this->hasMany(
            Receta::class,
            'id_usuario',
            'id_usuario'
        );
    }
}
