<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ArticuloConocimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KnowledgeBaseController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        // Si es usuario estándar, denegamos el acceso (según las reglas de negocio)
        if ($user->role === 'usuario') {
            return response()->json(['message' => 'Acceso denegado a la Base de Conocimientos para usuarios estándar.'], 403);
        }

        // Técnicos, Gestores y Admins pueden ver los artículos.
        $articulos = ArticuloConocimiento::with(['categoria', 'autor', 'adjuntos'])
            ->where('estado', 'publicado') // Solo artículos publicados
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $articulos
        ]);
    }
}
