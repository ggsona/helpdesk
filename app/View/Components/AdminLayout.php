<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AdminLayout extends Component
{
    public function render(): View
    {
        // Aquí le decimos que apunte al nuevo nombre admin.blade.php
        return view('layouts.admin'); 
    }
}