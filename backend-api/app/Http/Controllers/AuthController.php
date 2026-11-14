<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'correo' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $usuario = Usuario::where(
            'Correo',
            $request->correo
        )->first();

        if (
            !$usuario ||
            !Hash::check(
                $request->password,
                $usuario->Contraseña
            )
        ) {
            throw ValidationException::withMessages([
                'correo' => ['Credenciales incorrectas.'],
            ]);
        }

        if (!in_array(
            strtolower($usuario->Rol),
            ['admin', 'administrador', 'chef']
        )) {
            return response()->json([
                'mensaje' => 'No tienes permisos para acceder.',
            ], 403);
        }

        $token = $usuario->createToken(
            'tastysteps-token'
        )->plainTextToken;

        return response()->json([
            'mensaje' => 'Inicio de sesión exitoso.',
            'usuario' => $usuario,
            'token' => $token,
            'tipo_token' => 'Bearer',
        ], 200);
    }

    public function register(Request $request)
{
    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255', 'unique:usuarios,Correo'],
        'password' => ['required', 'string', 'min:8'],
    ]);

    $usuario = new Usuario();

    $usuario->Nombre = $request->name;
    $usuario->Correo = $request->email;
    $usuario->Contraseña = Hash::make($request->password);
    $usuario->Rol = 'chef';

    $usuario->save();

    return response()->json([
        'mensaje' => 'Usuario registrado correctamente.',
        'usuario' => [
            'id' => $usuario->id_usuario,
            'nombre' => $usuario->Nombre,
            'correo' => $usuario->Correo,
            'rol' => $usuario->Rol,
        ],
    ], 201);
}

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'mensaje' => 'Sesión cerrada correctamente.',
        ], 200);
    }
}
