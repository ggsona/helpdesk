@extends('errors.layout')

@section('title', '403 - Acceso denegado')
@section('icon', 'bi-shield-lock')
@section('code', '403')
@section('message', 'Acceso denegado')
@section('description', isset($exception) && $exception->getMessage() ? $exception->getMessage() : 'No tienes permisos para acceder a este recurso.')

@section('actions')
    <button onclick="history.back()" class="btn btn-outline-secondary px-4">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </button>
@endsection
