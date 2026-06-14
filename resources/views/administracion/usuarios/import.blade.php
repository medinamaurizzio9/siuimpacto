@extends('layouts.app')

@section('content')
<div class="topbar">
    <h1 class="title">Importar usuarios</h1>
    <div class="actions">
        <a href="{{ route('admin.usuarios.template') }}" class="btn secondary">Descargar plantilla</a>
        <a href="{{ route('admin.usuarios') }}" class="btn secondary">Volver</a>
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

<form class="card form" method="POST" action="{{ route('admin.usuarios.import.store') }}" enctype="multipart/form-data">
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
