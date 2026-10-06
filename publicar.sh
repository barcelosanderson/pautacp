#!/usr/bin/env bash
#
# Instala ou atualiza o site na hospedagem (feito para a Hostinger, via SSH).
#
#   Primeira vez:  bash publicar.sh instalar
#   Atualizar:     bash publicar.sh
#
# Tudo fica dentro de funções e só roda na última linha, para o script
# não se confundir quando o "git pull" trocar este próprio arquivo.

set -euo pipefail

passo() { printf '\n==> %s\n' "$1"; }
parar() { printf '\n%s\n\n' "$1" >&2; exit 1; }

# Na Hostinger, o "php" do terminal pode ser mais antigo que o escolhido no hPanel.
# Procura um PHP 8.3 ou mais novo, inclusive nas versões instaladas em /opt/alt.
escolher_php() {
  local candidato
  for candidato in php /opt/alt/php85/usr/bin/php /opt/alt/php84/usr/bin/php /opt/alt/php83/usr/bin/php; do
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
    echo composer2
  elif command -v composer >/dev/null 2>&1; then
    echo composer
  else
    return 1
  fi
}

main() {
  local modo="${1:-atualizar}"
  if [ "$modo" != "instalar" ] && [ "$modo" != "atualizar" ]; then
    parar "Uso: bash publicar.sh instalar   (primeira vez)
     bash publicar.sh            (atualizar)"
  fi

  cd "$(dirname "$0")"

  local php_bin composer
  php_bin="$(escolher_php)" \
    || parar "Não encontrei PHP 8.3 ou mais novo. No hPanel, abra Configuração do PHP e escolha 8.3 ou mais novo."
  export PATH="$(dirname "$php_bin"):$PATH"
  composer="$(escolher_composer)" || parar "Não encontrei o Composer nesta hospedagem."
  passo "Usando PHP $(php -r 'echo PHP_VERSION;') e $composer"

  if [ "$modo" = "atualizar" ]; then
    passo "Baixando a versão mais nova do GitHub"
    git pull --ff-only
  fi

  passo "Instalando as dependências (pode levar alguns minutos)"
  "$composer" install --no-dev --optimize-autoloader --no-interaction

  if [ ! -f .env ]; then
    cp .env.example .env
    parar "Criei o arquivo .env. Agora preencha os dados do site, do banco e da escola:
    nano .env
Depois rode de novo: bash publicar.sh $modo"
  fi

  if ! grep -q '^APP_KEY=base64:' .env; then
    passo "Gerando a chave de segurança do site"
    php artisan key:generate --force
  fi

  passo "Atualizando o banco de dados"
  if [ "$modo" = "instalar" ]; then
    php artisan migrate --seed --force
  else
    php artisan migrate --force
  fi

  passo "Preparando o site para ficar mais rápido"
  php artisan optimize

  passo "Pronto."
}

main "$@"
