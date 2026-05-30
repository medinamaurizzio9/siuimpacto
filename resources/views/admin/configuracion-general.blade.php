@extends('layouts.app')
@section('content')
<div class="topbar"><h1 class="title">Configuracion General</h1></div>
@if (session('status')) <div class="status">{{ session('status') }}</div> @endif
@if ($errors->any()) <div class="errors">{{ $errors->first() }}</div> @endif

<form class="form card" method="POST" action="{{ route('admin.configuracion-general.update') }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="field"><label>Nombre del sistema</label><input name="system_name" value="{{ old('system_name', $settings['system_name']) }}" required></div>
    <div class="field"><label>Subtitulo del sistema</label><input name="system_subtitle" value="{{ old('system_subtitle', $settings['system_subtitle']) }}" required></div>
    <div class="field"><label>Nombre de la empresa</label><input name="company_name" value="{{ old('company_name', $settings['company_name']) }}"></div>
    <div class="field"><label>Razon social</label><input name="razon_social" value="{{ old('razon_social', $settings['razon_social']) }}"></div>
    <div class="field"><label>NIT</label><input name="nit" value="{{ old('nit', $settings['nit']) }}"></div>
    <div class="field"><label>Direccion</label><input name="direccion" value="{{ old('direccion', $settings['direccion']) }}"></div>
    <div class="field"><label>Ciudad</label><input name="ciudad" value="{{ old('ciudad', $settings['ciudad']) }}"></div>
    <div class="field"><label>Departamento</label><input name="departamento" value="{{ old('departamento', $settings['departamento']) }}"></div>
    <div class="field"><label>Telefono</label><input name="telefono" value="{{ old('telefono', $settings['telefono']) }}"></div>
    <div class="field"><label>Celular</label><input name="celular" value="{{ old('celular', $settings['celular']) }}"></div>
    <div class="field"><label>WhatsApp</label><input name="whatsapp" value="{{ old('whatsapp', $settings['whatsapp']) }}"></div>
    <div class="field"><label>Correo electronico</label><input type="email" name="email" value="{{ old('email', $settings['email']) }}"></div>
    <div class="field"><label>Sitio web</label><input name="website" value="{{ old('website', $settings['website']) }}"></div>
    <div class="field"><label>Pie de pagina</label><input name="footer_text" value="{{ old('footer_text', $settings['footer_text']) }}"></div>
    <div class="field"><label>Color principal</label><input type="color" name="primary_color" value="{{ old('primary_color', $settings['primary_color']) }}"></div>
    <div class="field"><label>Color secundario</label><input type="color" name="secondary_color" value="{{ old('secondary_color', $settings['secondary_color']) }}"></div>
    @foreach(['logo_main' => 'Logo principal', 'logo_login' => 'Logo para login', 'logo_pdf' => 'Logo para PDF'] as $key => $label)
        <div class="field">
            <label>{{ $label }}</label>
            @if($settings[$key])
                <img src="{{ asset('storage/'.$settings[$key]) }}" alt="{{ $label }}" style="max-height:70px;display:block;margin-bottom:8px;">
            @endif
            <input type="file" name="{{ $key }}" accept="image/jpeg,image/png,image/webp">
        </div>
    @endforeach
    <div class="field full"><button class="btn" type="submit">Guardar configuracion</button></div>
</form>
@endsection
