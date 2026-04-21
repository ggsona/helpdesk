<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            return view('admin.dashboard');
        }

        if ($user->hasRole('gestor')) {
            return view('gestor.dashboard');
        }

        if ($user->hasRole('tecnico')) {
            return view('tecnico.dashboard');
        }

        // Si no es ninguno de los anteriores, asumimos que es cliente
        return view('usuario.home');
    }
}