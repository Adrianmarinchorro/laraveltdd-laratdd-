@extends('layout')

@section('title', 'Crear usuario');

@section('content')
    <h1>Crear usuario:</h1>

    <form action="{{ route('users.store') }}" method="POST">
        {{ csrf_field() }}
        {{-- {!! csrf_field() !!}--}}
        <button type="submit">Crear usuario</button>
    </form>

    <p>
        <a href="{{ route('users.index') }}">Regresar al listado de usuarios</a>
    </p>

@endsection