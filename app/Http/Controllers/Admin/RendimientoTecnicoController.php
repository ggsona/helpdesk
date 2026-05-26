<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Ticket;
use App\Models\TicketAsignacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RendimientoTecnicoController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:ver-rendimiento-tecnico');
    }

    public function index()
    {
        // Obtener todos los usuarios con rol técnico
        $tecnicos = User::role('tecnico')->get();

        $metrics = [];
        foreach ($tecnicos as $tecnico) {
            // Tickets asignados
            $assignedIds = TicketAsignacion::where('id_usuario_tecnico', $tecnico->id)->pluck('id_ticket');

            $total = Ticket::whereIn('id_ticket', $assignedIds)->count();
            $resueltos = Ticket::whereIn('id_ticket', $assignedIds)->whereIn('estatus', [3, 4])->count();
            $activos = Ticket::whereIn('id_ticket', $assignedIds)->whereIn('estatus', [1, 2])->count();

            // Calcular el promedio de tiempo de resolución en horas
            $resolvedTickets = Ticket::whereIn('id_ticket', $assignedIds)
                ->whereIn('estatus', [3, 4])
                ->with(['asignacion', 'solucion'])
                ->get();

            $totalHours = 0;
            $countForSpeed = 0;

            foreach ($resolvedTickets as $ticket) {
                $start = $ticket->asignacion ? $ticket->asignacion->created_at : $ticket->created_at;
                $end = $ticket->solucion ? $ticket->solucion->created_at : $ticket->fecha_cierre;

                if ($start && $end) {
                    $startDt = \Carbon\Carbon::parse($start);
                    $endDt = \Carbon\Carbon::parse($end);
                    $diffInHours = $startDt->diffInMinutes($endDt) / 60;
                    $totalHours += $diffInHours;
                    $countForSpeed++;
                }
            }

            $avgSpeed = $countForSpeed > 0 ? round($totalHours / $countForSpeed, 1) : null;
            $resolutionRate = $total > 0 ? round(($resueltos / $total) * 100, 1) : 0;

            $metrics[] = [
                'tecnico' => $tecnico,
                'total' => $total,
                'resueltos' => $resueltos,
                'activos' => $activos,
                'avg_speed' => $avgSpeed,
                'rate' => $resolutionRate
            ];
        }

        // Métricas globales
        $globalTotal = Ticket::count();
        $globalResueltos = Ticket::whereIn('estatus', [3, 4])->count();
        $globalActivos = Ticket::whereIn('estatus', [1, 2])->count();
        $globalRate = $globalTotal > 0 ? round(($globalResueltos / $globalTotal) * 100, 1) : 0;

        return view('admin.rendimiento.index', compact('metrics', 'globalTotal', 'globalResueltos', 'globalActivos', 'globalRate'));
    }
}
