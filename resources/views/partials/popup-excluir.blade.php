{{-- Pop-up de confirmação. Aberto pelos botões com data-excluir (public/js/pauta.js).
     O foco começa em "Cancelar" para ninguém excluir sem querer apertando Enter. --}}
<dialog class="popup" id="popup-excluir" aria-labelledby="popup-excluir-titulo">
    <form method="post" class="popup-conteudo" data-popup-formulario>
        @csrf
        @method('DELETE')
        <h2 class="titulo-card" id="popup-excluir-titulo">Excluir este item?</h2>
        <p class="texto">
            O item <strong data-popup-nome></strong> vai sair da pauta de todos os professores,
            junto com as marcações de quem já fez. Não é possível desfazer.
        </p>
        <div class="popup-botoes">
            <button type="submit" class="botao botao-perigo-cheio">Sim, excluir</button>
            <button type="button" class="botao botao-secundario" data-popup-fechar autofocus>Cancelar</button>
        </div>
    </form>
</dialog>
