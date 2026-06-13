@if ($errors->any())
    <div class="alert error">
        <strong>Revisa los datos ingresados.</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="field">
    <label>Nombre</label>
    <input name="name" value="{{ old('name', $usuario->name) }}" required>
</div>

<div class="field">
    <label>Email</label>
    <input type="email" name="email" value="{{ old('email', $usuario->email) }}" required>
</div>

<div class="field">
    <label>Rol</label>
    <select name="rol" required>
        <option value="">Seleccionar rol</option>
        @foreach($roles as $rol)
            <option value="{{ $rol->name }}" @selected(old('rol', $usuario->roles->first()?->name) === $rol->name)>
                {{ ucfirst($rol->name) }}
            </option>
        @endforeach
    </select>
</div>

<div class="field">
    <label>Contraseña {{ $usuario->exists ? '(opcional)' : '' }}</label>
    <input type="password" name="password" @if(! $usuario->exists) required @endif>
</div>

<div class="field">
    <label>Confirmar contraseña {{ $usuario->exists ? '(opcional)' : '' }}</label>
    <input type="password" name="password_confirmation" @if(! $usuario->exists) required @endif>
</div>
