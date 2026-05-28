@extends('errors.layout')

@section('title', '401 - No autorizado')
@section('icon', 'bi-person-lock')
@section('code', '401')
@section('message', 'No autorizado')
@section('description', 'Debes iniciar sesión para continuar.')

@section('actions')
    <a href="{{ route('login') }}" class="btn btn-outline-primary px-4">
        <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar sesión
    </a>
@endsection
