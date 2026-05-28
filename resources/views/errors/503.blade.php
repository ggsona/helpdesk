@extends('errors.layout')

@section('title', '503 - Servicio no disponible')
@section('icon', 'bi-tools')
@section('code', '503')
@section('message', 'Servicio no disponible')
@section('description', 'Estamos realizando mantenimiento. Vuelve a intentarlo en unos minutos.')

@section('actions')
    <button onclick="window.location.reload()" class="btn btn-outline-primary px-4">
        <i class="bi bi-arrow-clockwise me-1"></i> Verificar estado
    </button>
@endsection
