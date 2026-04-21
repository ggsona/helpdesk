<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

// CAMBIA ESTO: de ClienteLayout a UsuarioLayout
class UsuarioLayout extends Component
{
    public function render(): View
    {
        // Asegúrate de que este archivo exista en resources/views/layouts/usuario.blade.php
        return view('layouts.usuario');
    }
}