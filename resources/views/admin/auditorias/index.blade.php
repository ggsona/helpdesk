@extends('layouts.admin')

@section('content')
@push('styles')
<style>
    .audit-action-badge {
        font-size: 0.75rem;
        padding: 0.55rem 0.9rem;
        border-radius: 999px;
        font-weight: 700;
        text-transform: uppercase;
        border: 1px solid transparent;
        letter-spacing: 0.02em;
        display: inline-flex;
        align-items: center;
    }
    .audit-create { background: rgba(25, 135, 84, 0.2); color: #0f5132; border-color: rgba(25, 135, 84, 0.35); }
    .audit-update { background: rgba(255, 193, 7, 0.22); color: #664d03; border-color: rgba(255, 193, 7, 0.38); }
    .audit-delete { background: rgba(220, 53, 69, 0.2); color: #842029; border-color: rgba(220, 53, 69, 0.35); }
    .audit-login { background: rgba(13, 202, 240, 0.2); color: #055160; border-color: rgba(13, 202, 240, 0.35); }
    .audit-logout { background: rgba(108, 117, 125, 0.22); color: #41464b; border-color: rgba(108, 117, 125, 0.35); }
    .audit-failed { background: rgba(220, 53, 69, 0.2); color: #842029; border-color: rgba(220, 53, 69, 0.35); }
    .audit-sync { background: rgba(13, 110, 253, 0.2); color: #084298; border-color: rgba(13, 110, 253, 0.35); }
    .audit-generic { background: rgba(108, 117, 125, 0.18); color: #495057; border-color: rgba(108, 117, 125, 0.3); }

    [data-bs-theme="dark"] .audit-create { color: #75d7ae; background: rgba(25, 135, 84, 0.22); border-color: rgba(25, 135, 84, 0.45); }
    [data-bs-theme="dark"] .audit-update { color: #ffda6a; background: rgba(255, 193, 7, 0.22); border-color: rgba(255, 193, 7, 0.45); }
    [data-bs-theme="dark"] .audit-delete { color: #f5a3ad; background: rgba(220, 53, 69, 0.24); border-color: rgba(220, 53, 69, 0.45); }
    [data-bs-theme="dark"] .audit-login { color: #6edff6; background: rgba(13, 202, 240, 0.2); border-color: rgba(13, 202, 240, 0.45); }
    [data-bs-theme="dark"] .audit-logout { color: #ced4da; background: rgba(108, 117, 125, 0.25); border-color: rgba(108, 117, 125, 0.45); }
    [data-bs-theme="dark"] .audit-failed { color: #f1aeb5; background: rgba(220, 53, 69, 0.24); border-color: rgba(220, 53, 69, 0.45); }
    [data-bs-theme="dark"] .audit-sync { color: #9ec5fe; background: rgba(13, 110, 253, 0.22); border-color: rgba(13, 110, 253, 0.45); }
    [data-bs-theme="dark"] .audit-generic { color: #dee2e6; background: rgba(108, 117, 125, 0.25); border-color: rgba(108, 117, 125, 0.45); }

    .audit-json {
        font-size: 0.7rem;
        max-width: 250px;
        overflow-x: auto;
        white-space: pre-wrap;
    }
    [data-bs-theme="dark"] .audit-json {
        background: #1f2327 !important;
        color: #e9ecef !important;
        border-color: #343a40 !important;
    }
</style>
@endpush
<div class="py-3 px-1">
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h1 class="h2 fw-bold mb-1 theme-text"><i class="bi bi-journal-text text-primary me-2"></i>Bitácora de Auditorías</h1>
            <p class="text-secondary mb-2">Monitorea y audita detalladamente qué usuario creó, modificó o eliminó elementos en la plataforma.</p>
            <div class="d-flex gap-2 mt-2">
                <a href="{{ route('admin.auditorias.export', request()->query()) }}" class="btn btn-success rounded-3 px-3 py-1.5 fw-bold shadow-sm d-inline-flex align-items-center gap-2 btn-sm">
                    <i class="bi bi-file-earmark-spreadsheet-fill"></i> Excel (CSV)
                </a>
                <a href="{{ route('admin.auditorias.pdf', request()->query()) }}" class="btn btn-danger rounded-3 px-3 py-1.5 fw-bold shadow-sm d-inline-flex align-items-center gap-2 btn-sm">
                    <i class="bi bi-file-earmark-pdf-fill"></i> Descargar PDF
                </a>
            </div>
        </div>
    </div>

    <!-- Componente Livewire: Tabla de Auditorías Interactiva -->
    <livewire:admin.auditorias-table />
</div>
@endsection
