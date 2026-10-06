/*
 * Pauta Escolar — comportamento das telas
 * - avisos rápidos que somem em 4 segundos
 * - menu de 3 riscos em telas menores que 900px
 * - marcar e desmarcar tarefas sem recarregar a página
 */
(function () {
  'use strict';

  var TEMPO_AVISO = 4000;

  var ICONES = {
    sucesso: '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>',
    erro: '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7.5v5.5"/><path d="M12 16.5v.5"/></svg>'
  };

  var MENSAGEM_ERRO = 'Não foi possível salvar. Verifique a internet e tente de novo.';
  var MENSAGEM_EXPIROU = 'A página ficou aberta por muito tempo. Recarregue a página e tente de novo.';

  /* ---------- Avisos ---------- */

  function agendarSaida(aviso) {
    setTimeout(function () {
      aviso.classList.add('saindo');
      setTimeout(function () { aviso.remove(); }, 300);
    }, TEMPO_AVISO);
  }

  function mostrarAviso(tipo, texto) {
    var area = document.getElementById('avisos');
    if (!area) { return; }

    var aviso = document.createElement('div');
    aviso.className = 'aviso aviso-' + tipo;
    aviso.setAttribute('role', tipo === 'erro' ? 'alert' : 'status');
    aviso.innerHTML = ICONES[tipo] || '';

    var span = document.createElement('span');
    span.textContent = texto;
    aviso.appendChild(span);

    area.innerHTML = '';
    area.appendChild(aviso);
    agendarSaida(aviso);
  }

  window.mostrarAviso = mostrarAviso;

  document.querySelectorAll('#avisos .aviso').forEach(agendarSaida);

  /* ---------- Menu ---------- */

  var menu = document.querySelector('[data-menu]');
  if (menu) {
    var abrir = menu.querySelector('[data-menu-abrir]');
    var rotulo = menu.querySelector('[data-menu-rotulo]');
    abrir.addEventListener('click', function () {
      var aberto = menu.classList.toggle('aberto');
      abrir.setAttribute('aria-expanded', aberto ? 'true' : 'false');
      rotulo.textContent = aberto ? 'Fechar' : 'Menu';
    });
  }

  /* ---------- Tarefas ---------- */

  var tokenMeta = document.querySelector('meta[name="csrf-token"]');
  var token = tokenMeta ? tokenMeta.getAttribute('content') : '';

  function atualizarResumo(resumo) {
    var texto = document.querySelector('[data-progresso-texto]');
    var percentual = document.querySelector('[data-progresso-percentual]');
    var barra = document.querySelector('[data-progresso-barra]');
    var trilho = document.querySelector('[data-progresso]');
    var recado = document.querySelector('[data-recado]');
    var recadoTexto = document.querySelector('[data-recado-texto]');

    if (texto) { texto.textContent = resumo.texto; }
    if (percentual) { percentual.textContent = resumo.percentual + '%'; }
    if (barra) {
      barra.style.width = resumo.percentual + '%';
      barra.toggleAttribute('data-vazia', resumo.percentual === 0);
    }
    if (trilho) { trilho.setAttribute('aria-valuenow', resumo.feitas); }
    if (recado && recadoTexto) {
      recado.hidden = !resumo.alerta;
      recadoTexto.textContent = resumo.alerta || '';
    }
  }

  function atualizarContagemDoMes(linha) {
    var mes = linha.closest('[data-mes]');
    if (!mes) { return; }
    var total = mes.querySelectorAll('[data-tarefa]').length;
    var feitas = mes.querySelectorAll('[data-tarefa] input:checked').length;
    var alvo = mes.querySelector('[data-mes-contagem]');
    if (alvo) {
      alvo.textContent = total + (total === 1 ? ' tarefa' : ' tarefas') + ' · ' +
        feitas + (feitas === 1 ? ' feita' : ' feitas');
    }
  }

  function aplicarStatus(linha, status) {
    linha.classList.remove('status-feito', 'status-logo', 'status-vencido', 'status-afazer');
    linha.classList.add('status-' + status.chave);
    var selo = linha.querySelector('[data-selo]');
    if (selo) { selo.textContent = status.rotulo; }
  }

  document.querySelectorAll('[data-tarefa]').forEach(function (linha) {
    var caixa = linha.querySelector('input[type="checkbox"]');
    if (!caixa) { return; }

    caixa.addEventListener('change', function () {
      if (linha.getAttribute('aria-busy') === 'true') {
        caixa.checked = !caixa.checked;
        return;
      }

      var feita = caixa.checked;
      linha.setAttribute('aria-busy', 'true');

      fetch(linha.getAttribute('data-url'), {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': token
        },
        body: JSON.stringify({ feita: feita })
      })
        .then(function (resposta) {
          if (!resposta.ok) { throw resposta; }
          return resposta.json();
        })
        .then(function (dados) {
          aplicarStatus(linha, dados.status);
          atualizarContagemDoMes(linha);
          atualizarResumo(dados.resumo);
          mostrarAviso('sucesso', dados.mensagem);
        })
        .catch(function (erro) {
          caixa.checked = !feita;
          mostrarAviso('erro', erro && erro.status === 419 ? MENSAGEM_EXPIROU : MENSAGEM_ERRO);
        })
        .then(function () {
          linha.removeAttribute('aria-busy');
        });
    });
  });
})();
