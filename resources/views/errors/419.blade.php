@extends('errors.layout')

@section('title', '419 - Sesión expirada')
@section('icon', 'bi-hourglass-split')
@section('code', '419')
@section('message', 'Sesión expirada')
@section('description', 'Tu sesión expiró por seguridad. Vuelve a intentarlo.')

@section('actions')
    <button onclick="window.location.reload()" class="btn btn-outline-secondary px-4">
        <i class="bi bi-arrow-clockwise me-1"></i> Recargar
    </button>
@endsection
