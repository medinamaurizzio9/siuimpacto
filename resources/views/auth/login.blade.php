@extends('layouts.app')

@section('content')
<div class="login-page">
    <form method="POST" action="{{ route('login.store') }}" class="login-card">
        @csrf
        @if(!empty($systemSettings['logo_login'] ?? $systemSettings['logo_main']))
            <img src="{{ asset('storage/'.($systemSettings['logo_login'] ?: $systemSettings['logo_main'])) }}" alt="Logo" style="max-height:92px;display:block;margin:0 auto 14px;">
        @endif
        <h1>{{ $systemSettings['system_name'] ?? 'IMPACTO URBANIZACIONES' }}</h1>
        <p class="muted">{{ $systemSettings['system_subtitle'] ?? 'Sistema Integral de Terrenos' }}</p>
        <p class="muted">Ingreso administrativo</p>
        @if ($errors->any())
            <div class="errors">{{ $errors->first() }}</div>
        @endif
        <div class="field">
            <label>Email</label>
            <input name="email" type="email" value="{{ old('email', 'admin@impacto.test') }}" required autofocus>
        </div>
        <div class="field" style="margin-top: 14px;">
            <label>Contrasena</label>
            <input name="password" type="password" value="password" required>
        </div>
        <label style="display:flex;gap:8px;align-items:center;margin:14px 0;">
            <input type="checkbox" name="remember" value="1" style="width:auto;"> Recordarme
        </label>
        <button class="btn" type="submit" style="width:100%;">Ingresar</button>
    </form>
</div>
@endsection
