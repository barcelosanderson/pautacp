# Pauta Escolar

Site para os professores acompanharem a pauta do bimestre e marcarem o que já fizeram.
Feito em Laravel (PHP) com banco de dados MySQL.

- A pessoa cria a conta só com **nome, sobrenome e o código da escola** (entregue pessoalmente pela coordenação).
- Depois de entrar, o aparelho fica lembrado: não é preciso entrar de novo.
- Cada tarefa pode ser marcada como feita. A cor da linha mostra o status: feito, vence em até 2 dias, vencido ou a fazer.
- Visual segue o guia de estilo (contorno preto, sombra sólida, letras grandes, alto contraste).

## O que já está pronto (parte 1)

- Estrutura Laravel, banco de dados e telas.
- Criar conta e entrar com nome, sobrenome e código da escola.
- Pauta do 4º bimestre (outubro a dezembro) já cadastrada, com filtro por mês e barra de progresso.
- Marcar e desmarcar tarefas sem recarregar a página, com aviso de sucesso e de erro.
- Comando para definir quem é coordenador(a).

Próximas partes: área da coordenação (criar e editar tarefas, ver quem já marcou o quê, trocar o código da escola).

## Requisitos

- PHP 8.3 ou mais novo, com as extensões `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `fileinfo`
- [Composer](https://getcomposer.org)
- MySQL 8 (ou MariaDB 10.3+)

Não precisa de Node nem de `npm`: o CSS e o JS ficam prontos em `public/css` e `public/js`.

## Instalar no seu computador

```bash
# 1. Baixar o projeto e as dependências
git clone https://github.com/barcelosanderson/pautacp.git
cd pautacp
composer install

# 2. Criar o arquivo de configuração
cp .env.example .env
php artisan key:generate
```

Abra o arquivo `.env` e preencha:

```ini
DB_DATABASE=pauta        # nome do banco que você criou no MySQL
DB_USERNAME=root
DB_PASSWORD=sua_senha

ESCOLA_NOME="Nome da escola"
ESCOLA_CODIGO=ABC123     # o código que você vai passar para os professores
```

Depois crie as tabelas e a pauta:

```bash
# 3. Criar as tabelas, a escola e as tarefas da pauta
php artisan migrate --seed

# 4. Definir quem é da coordenação (cria a conta se ainda não existir)
php artisan pauta:coordenador "Maria" "Souza"

# 5. Abrir o site em http://localhost:8000
php artisan serve
```

Se você deixar `ESCOLA_CODIGO` vazio, um código é sorteado e aparece na tela no passo 3.

## Colocar na internet (hospedagem com PHP e MySQL)

1. Envie os arquivos do projeto para a hospedagem (por Git ou FTP) e rode `composer install --no-dev --optimize-autoloader`.
2. Aponte o domínio para a pasta **`public`** do projeto (nunca para a pasta raiz).
3. Crie o `.env` como acima, com `APP_ENV=production`, `APP_DEBUG=false` e o endereço do site em `APP_URL`.
4. Rode `php artisan migrate --seed --force` e `php artisan pauta:coordenador "Nome" "Sobrenome"`.
5. Rode `php artisan config:cache` e `php artisan route:cache` para o site ficar mais rápido.

## Testes

```bash
php artisan test
```

Os testes usam um banco SQLite na memória, então não mexem no seu MySQL.

## Onde fica cada coisa

| O quê | Onde |
| --- | --- |
| Endereços do site | `routes/web.php` |
| Criar conta, entrar e sair | `app/Http/Controllers/AcessoController.php` |
| Tela da pauta e marcar tarefa | `app/Http/Controllers/PautaController.php` |
| Regras da pauta (status, resumo, meses) | `app/Services/Pauta.php` |
| Tabelas do banco | `database/migrations` |
| Datas e textos da pauta | `database/seeders/PautaSeeder.php` |
| Telas | `resources/views` |
| Visual (cores, botões, campos…) | `public/css/pauta.css` |
| Avisos, menu e marcar sem recarregar | `public/js/pauta.js` |

## Banco de dados

| Tabela | Para que serve |
| --- | --- |
| `escolas` | Nome da escola e o código de acesso |
| `users` | Pessoas: nome, sobrenome, escola e papel (`professor` ou `coordenador`) |
| `tarefas` | Itens da pauta: data, prazo, descrição, se repete todo mês |
| `tarefa_user` | Quem marcou cada tarefa como feita, e quando |

## Sobre a segurança do acesso

Entrar só com nome e código é simples para quem tem pouca familiaridade com tecnologia,
mas quem souber o nome de um colega e o código da escola consegue entrar como ele.
Por isso:

- o site limita o número de tentativas seguidas;
- é bom trocar o código da escola se ele vazar.

Se quiser mais proteção depois, dá para pedir um PIN de 4 números só quando a pessoa entrar em um aparelho novo.
