<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->can('ver-panel-operativo')) {
            return view('soporte.dashboard');
        }

        // Si no tiene acceso al panel operativo, se le redirige al inicio de cliente
        return redirect()->route('usuario.home');
    }
}