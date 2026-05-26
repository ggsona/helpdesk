<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Auditoría - Helpdesk GDC</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9px;
            color: #333333;
            margin: 0;
            padding: 0;
        }
        @page {
            margin: 30px;
        }
        .header {
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header table {
            width: 100%;
            border-collapse: collapse;
        }
        .logo-title {
            font-size: 18px;
            font-weight: bold;
            color: #0d6efd;
            margin: 0;
        }
        .subtitle {
            font-size: 10px;
            color: #666666;
            margin-top: 5px;
        }
        .meta-info {
            text-align: right;
            font-size: 8px;
            color: #555555;
            line-height: 1.4;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .table th {
            background-color: #f4f6f9;
            color: #495057;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8px;
            border-bottom: 2px solid #dee2e6;
            padding: 8px 6px;
            text-align: left;
        }
        .table td {
            padding: 8px 6px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: top;
        }
        .badge {
            display: inline-block;
            padding: 3px 6px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7px;
            border-radius: 3px;
            text-align: center;
        }
        .bg-success {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        .bg-warning {
            background-color: #fff3cd;
            color: #664d03;
        }
        .bg-danger {
            background-color: #f8d7da;
            color: #842029;
        }
        .bg-secondary {
            background-color: #e2e3e5;
            color: #41464b;
        }
        .text-bold {
            font-weight: bold;
        }
        .text-muted {
            color: #6c757d;
        }
        .json-box {
            font-family: monospace;
            font-size: 7.5px;
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            padding: 4px;
            border-radius: 3px;
            white-space: pre-wrap;
            max-width: 200px;
            word-wrap: break-word;
        }
        .footer {
            position: fixed;
            bottom: -10px;
            left: 0px;
            right: 0px;
            height: 20px;
            text-align: center;
            font-size: 8px;
            color: #999999;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }
    </style>
</head>
<body>

    <div class="header">
        <table>
            <tr>
                <td>
                    <div class="logo-title">HELPDESK GDC</div>
                    <div class="subtitle">Bitácora Oficial de Auditoría y Control Interno</div>
                </td>
                <td class="meta-info">
                    <strong>Generado por:</strong> {{ auth()->user()->name }}<br>
                    <strong>Fecha de Emisión:</strong> {{ date('d/m/Y H:i:s') }}<br>
                    <strong>Registros Totales:</strong> {{ count($logs) }}
                </td>
            </tr>
        </table>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 12%;">Fecha / Hora</th>
                <th style="width: 15%;">Responsable</th>
                <th style="width: 10%;">Acción</th>
                <th style="width: 12%;">Componente</th>
                <th style="width: 8%;">ID Ref</th>
                <th style="width: 18%;">Valores Anteriores</th>
                <th style="width: 18%;">Valores Nuevos</th>
                <th style="width: 7%;">Origen IP</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>
                        <span class="text-bold">{{ $log->created_at->format('d/m/Y') }}</span><br>
                        <span class="text-muted">{{ $log->created_at->format('H:i:s') }}</span>
                    </td>
                    <td>
                        @if($log->user)
                            <span class="text-bold">{{ $log->user->name }}</span><br>
                            <span class="text-muted" style="font-size: 7.5px;">{{ $log->user->roles->pluck('name')->first() ?? 'Soporte' }}</span>
                        @else
                            <span class="text-muted">Sistema</span>
                        @endif
                    </td>
                    <td>
                        @if($log->action === 'create')
                            <span class="badge bg-success">Creación</span>
                        @elseif($log->action === 'update')
                            <span class="badge bg-warning">Edición</span>
                        @elseif($log->action === 'delete')
                            <span class="badge bg-danger">Eliminación</span>
                        @else
                            <span class="badge bg-secondary">{{ $log->action }}</span>
                        @endif
                    </td>
                    <td>
                        <span class="text-bold">{{ class_basename($log->auditable_type) }}</span>
                    </td>
                    <td>
                        <code>#{{ $log->auditable_id }}</code>
                    </td>
                    <td>
                        @if($log->old_values)
                            <div class="json-box">{{ json_encode($log->old_values, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</div>
                        @else
                            <span class="text-muted" style="font-style: italic;">Nuevo Registro</span>
                        @endif
                    </td>
                    <td>
                        @if($log->new_values)
                            <div class="json-box">{{ json_encode($log->new_values, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</div>
                        @else
                            <span class="text-muted" style="font-style: italic;">Registro Eliminado</span>
                        @endif
                    </td>
                    <td>
                        <code>{{ $log->ip_address ?? 'Local' }}</code>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 30px;">
                        No se registraron movimientos en la bitácora para el rango de filtros seleccionado.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Helpdesk GDC &copy; {{ date('Y') }} - Reporte Técnico Confidencial de Auditoría
    </div>

</body>
</html>
