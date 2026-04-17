<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class ClienteLayout extends Component
{
    public function render(): View
    {
        // Aquí apunta al archivo layouts/cliente.blade.php que creamos antes
        return view('layouts.cliente');
    }
}