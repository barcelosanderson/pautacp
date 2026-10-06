{{-- Aviso rápido no topo da tela. Some sozinho em 4 segundos (public/js/pauta.js). --}}
<div id="avisos" class="avisos">
    @if ($errors->any())
        <div class="aviso aviso-erro" role="alert">
            <x-icone nome="alerta" />
            <span>Confira os campos marcados em vermelho.</span>
        </div>
    @elseif (session('erro'))
        <div class="aviso aviso-erro" role="alert">
            <x-icone nome="alerta" />
            <span>{{ session('erro') }}</span>
        </div>
    @elseif (session('sucesso'))
        <div class="aviso aviso-sucesso" role="status">
            <x-icone nome="certo" />
            <span>{{ session('sucesso') }}</span>
        </div>
    @endif
</div>
