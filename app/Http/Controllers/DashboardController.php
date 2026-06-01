<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Ticket;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->can('ver-panel-operativo')) {
            $stats = [];
            
            if ($user->can('asignar-tickets')) {
                // Gestores / Admins
                $stats['nuevos'] = Ticket::where('estatus', 1)->count();
                $stats['en_gestion'] = Ticket::where('estatus', 2)->count();
                $stats['cerrados_hoy'] = Ticket::where('estatus', 3)->whereDate('updated_at', Carbon::today())->count();
                $stats['tiempo_promedio'] = "45m";

                // Gráficas
                // 1. Porcentaje por Categoría
                $categoriasData = \Illuminate\Support\Facades\DB::table('tickets')
                    ->join('categorias', 'tickets.id_categoria', '=', 'categorias.id_categoria')
                    ->select('categorias.nombre_categoria', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
                    ->groupBy('categorias.nombre_categoria')
                    ->get();
                $stats['chart_cat_labels'] = $categoriasData->pluck('nombre_categoria')->toJson();
                $stats['chart_cat_data'] = $categoriasData->pluck('total')->toJson();

                // 2. Rendimiento por técnico (Tickets resueltos)
                $tecnicosData = \App\Models\User::role('tecnico')->withCount(['asignaciones as resueltos' => function($q) {
                    $q->whereHas('ticket', function($t) { $t->where('estatus', 3); });
                }])->get();
                $stats['chart_tech_labels'] = $tecnicosData->pluck('name')->toJson();
                $stats['chart_tech_data'] = $tecnicosData->pluck('resueltos')->toJson();
            } else {
                // Técnicos
                $stats['pendientes_tecnico'] = Ticket::whereHas('asignacion', function($q) use ($user) {
                    $q->where('id_usuario_tecnico', $user->id);
                })->where('estado_tecnico', 'pendiente')->count();

                $stats['en_proceso_tecnico'] = Ticket::whereHas('asignacion', function($q) use ($user) {
                    $q->where('id_usuario_tecnico', $user->id);
                })->where('estado_tecnico', 'en_progreso')->count();

                $stats['resueltos_tecnico'] = Ticket::whereHas('asignacion', function($q) use ($user) {
                    $q->where('id_usuario_tecnico', $user->id);
                })->where('estatus', 3)->count();
            }

            return view('soporte.dashboard', compact('stats'));
        }

        // Si no tiene acceso al panel operativo, se le redirige al inicio de cliente
        return redirect()->route('usuario.home');
    }
}