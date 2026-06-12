@php($whatsappUrl = \App\Support\WhatsAppLink::url($cliente->telefono, $cliente->nombre))
<span class="phone-with-whatsapp">
    <span>{{ $cliente->telefono ?: 'Sin telefono' }}</span>
    @if($whatsappUrl)
        <a class="whatsapp-link" href="{{ $whatsappUrl }}" target="_blank" rel="noopener" title="Abrir conversacion WhatsApp" aria-label="Abrir conversacion WhatsApp">
            <i class="fab fa-whatsapp" aria-hidden="true"></i>
        </a>
    @endif
</span>
