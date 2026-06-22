<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();

        // Verificar si está aprobado
        if (!$user->is_approved) {
            return response()->json([
                'message' => 'Tu cuenta aún no ha sido aprobada por un administrador.'
            ], 403);
        }

        // Crear token de Sanctum
        $token = $user->createToken('auth_token_movil')->plainTextToken;

        return response()->json([
            'message' => 'Login exitoso',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                // Si el usuario tiene roles asociados (Spatie), retornamos el primer rol para la lógica de la App
                'role' => $user->roles->first()->name ?? 'usuario'
            ]
        ]);
    }

    public function logout(Request $request)
    {
        // Revocar el token actual con el que se hizo la petición
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Cierre de sesión exitoso'
        ]);
    }
}
