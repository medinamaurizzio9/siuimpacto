@extends('layouts.app')

@section('content')
<div class="topbar">
    <h1 class="title">Importar equipo comercial</h1>
    <div class="actions">
        <a class="btn secondary" href="{{ route('asesores.template') }}">Descargar plantilla</a>
        <a class="btn secondary" href="{{ route('asesores.index') }}">Volver</a>
    </div>
</div>

@if (session('status'))
    <div class="status">{{ session('status') }}</div>
@endif

@if ($errors->any())
    <div class="errors">{{ $errors->first() }}</div>
@endif

@if (session('import_errors'))
    <div class="errors">
        <strong>Errores encontrados</strong>
        <ul>
            @foreach(session('import_errors') as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form class="card form" method="POST" action="{{ route('asesores.import.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="field full">
        <label>Archivo Excel o CSV</label>
        <input type="file" name="archivo" accept=".xlsx,.csv" required>
    </div>
    <div class="field full">
        <button class="btn" type="submit">Importar usuarios</button>
    </div>
</form>
@endsection
