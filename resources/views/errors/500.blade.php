@extends('errors.layout')

@section('title', '500 - Error interno del servidor')
@section('icon', 'bi-exclamation-triangle-fill')
@section('code', '500')
@section('message', 'Error interno del servidor')
@section('description', 'Algo salió mal de nuestro lado. Estamos trabajando para resolverlo.')

@section('actions')
    <button onclick="window.location.reload()" class="btn btn-outline-secondary px-4">
        <i class="bi bi-arrow-clockwise me-1"></i> Reintentar
    </button>
@endsection

@if(app()->environment('local', 'testing') && isset($exception))
    @section('extra')
        <code>{{ $exception->getMessage() }}</code>
    @endsection
@endif
