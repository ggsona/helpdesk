<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class AuditController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:ver-auditorias');
    }

    public function index(Request $request)
    {
        $query = Activity::with('causer')->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject_type', 'like', "%{$search}%")
                  ->orWhere('event', 'like', "%{$search}%")
                  ->orWhere('log_name', 'like', "%{$search}%")
                  ->orWhereHas('causer', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('action')) {
            $query->where('event', $request->action);
        }

        if ($request->filled('type')) {
            if ($request->type === 'User') {
                $query->where(function ($q) use ($request) {
                    $q->where('subject_type', 'like', "%{$request->type}%")
                      ->orWhere('log_name', 'Autenticación');
                });
            } else {
                $query->where('subject_type', 'like', "%{$request->type}%");
            }
        }

        $logs = $query->paginate(15);

        return view('admin.auditorias.index', compact('logs'));
    }

    public function export(Request $request)
    {
        $query = Activity::with('causer')->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject_type', 'like', "%{$search}%")
                  ->orWhere('event', 'like', "%{$search}%")
                  ->orWhere('log_name', 'like', "%{$search}%")
                  ->orWhereHas('causer', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('action')) {
            $query->where('event', $request->action);
        }

        if ($request->filled('type')) {
            if ($request->type === 'User') {
                $query->where(function ($q) use ($request) {
                    $q->where('subject_type', 'like', "%{$request->type}%")
                      ->orWhere('log_name', 'Autenticación');
                });
            } else {
                $query->where('subject_type', 'like', "%{$request->type}%");
            }
        }

        $logs = $query->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="reporte_auditoria_' . date('Ymd_His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'Fecha y Hora',
                'Responsable',
                'Acción',
                'Módulo',
                'ID Afectado',
                'Valores Anteriores',
                'Valores Nuevos',
                'Propiedades (JSON)'
            ]);

            foreach ($logs as $log) {
                $old = isset($log->properties['old']) ? json_encode($log->properties['old'], JSON_UNESCAPED_UNICODE) : '';
                $new = isset($log->properties['attributes']) ? json_encode($log->properties['attributes'], JSON_UNESCAPED_UNICODE) : '';
                $all_props = json_encode($log->properties, JSON_UNESCAPED_UNICODE);

                fputcsv($file, [
                    $log->created_at->format('d/m/Y H:i:s'),
                    $log->causer ? $log->causer->name : 'Sistema',
                    $log->event,
                    $log->subject_type ? class_basename($log->subject_type) : $log->log_name,
                    $log->subject_id ?? 'N/A',
                    $old,
                    $new,
                    $all_props
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $query = Activity::with('causer')->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject_type', 'like', "%{$search}%")
                  ->orWhere('event', 'like', "%{$search}%")
                  ->orWhere('log_name', 'like', "%{$search}%")
                  ->orWhereHas('causer', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('action')) {
            $query->where('event', $request->action);
        }

        if ($request->filled('type')) {
            if ($request->type === 'User') {
                $query->where(function ($q) use ($request) {
                    $q->where('subject_type', 'like', "%{$request->type}%")
                      ->orWhere('log_name', 'Autenticación');
                });
            } else {
                $query->where('subject_type', 'like', "%{$request->type}%");
            }
        }

        $logs = $query->get();

        $pdf = Pdf::loadView('admin.auditorias.pdf', compact('logs'))->setPaper('a4', 'landscape');
        
        return $pdf->download('reporte_auditoria_' . date('Ymd_His') . '.pdf');
    }
}
