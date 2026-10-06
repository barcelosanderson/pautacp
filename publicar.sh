#!/usr/bin/env bash
#
# Instala ou atualiza o site no servidor (Hostinger: hospedagem ou VPS com CloudPanel).
#
#   Primeira vez:  bash publicar.sh instalar
#   Atualizar:     bash publicar.sh
#
# Para forçar uma versão do PHP:  PHP_BIN=php8.4 bash publicar.sh
#
# Tudo fica dentro de funções e só roda na última linha, para o script
# não se confundir quando o "git pull" trocar este próprio arquivo.

set -euo pipefail

passo() { printf '\n==> %s\n' "$1"; }
parar() { printf '\n%s\n\n' "$1" >&2; exit 1; }

# O "php" do terminal pode ser diferente do PHP do site.
# Procura um PHP 8.3 ou mais novo: PHP_BIN, depois "php", depois php8.x (VPS com
# CloudPanel) e as versões em /opt/alt (hospedagem compartilhada da Hostinger).
escolher_php() {
  local candidato
  for candidato in ${PHP_BIN:-} php php8.5 php8.4 php8.3 \
    /opt/alt/php85/usr/bin/php /opt/alt/php84/usr/bin/php /opt/alt/php83/usr/bin/php; do
    if command -v "$candidato" >/dev/null 2>&1 \
      && "$candidato" -r 'exit(version_compare(PHP_VERSION, "8.3.0", ">=") ? 0 : 1);' 2>/dev/null; then
      command -v "$candidato"
      return 0
    fi
  done
  return 1
}

escolher_composer() {
  if command -v composer2 >/dev/null 2>&1; then
    command -v composer2
  elif command -v composer >/dev/null 2>&1; then
    command -v composer
  else
    return 1
  fi
}

# Roda com o PHP escolhido, e não com o "php" padrão do terminal.
artisan() { "$PHP" artisan "$@"; }

composer_rodar() {
  if head -n 1 "$COMPOSER" | grep -q php; then
    "$PHP" "$COMPOSER" "$@"
  else
    "$COMPOSER" "$@"
  fi
}

main() {
  local modo="${1:-atualizar}"
  if [ "$modo" != "instalar" ] && [ "$modo" != "atualizar" ]; then
    parar "Uso: bash publicar.sh instalar   (primeira vez)
     bash publicar.sh            (atualizar)"
  fi

  cd "$(dirname "$0")"

  PHP="$(escolher_php)" \
    || parar "Não encontrei PHP 8.3 ou mais novo. Escolha PHP 8.3 ou mais novo no painel da hospedagem."
  COMPOSER="$(escolher_composer)" || parar "Não encontrei o Composer neste servidor."
  passo "Usando PHP $("$PHP" -r 'echo PHP_VERSION;') ($PHP) e $COMPOSER"

  if [ "$modo" = "atualizar" ]; then
    passo "Baixando a versão mais nova do GitHub"
    git pull --ff-only
  fi

  passo "Instalando as dependências (pode levar alguns minutos)"
  composer_rodar install --no-dev --optimize-autoloader --no-interaction

  if [ ! -f .env ]; then
    cp .env.example .env
    parar "Criei o arquivo .env. Agora preencha os dados do site, do banco e da escola:
    nano .env
Depois rode de novo: bash publicar.sh $modo"
  fi

  if ! grep -q '^APP_KEY=base64:' .env; then
    passo "Gerando a chave de segurança do site"
    artisan key:generate --force
  fi

  passo "Atualizando o banco de dados"
  if [ "$modo" = "instalar" ]; then
    artisan migrate --seed --force
  else
    artisan migrate --force
  fi

  passo "Preparando o site para ficar mais rápido"
  artisan optimize

  passo "Pronto."
}

main "$@"
