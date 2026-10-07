# Pauta Escolar

Site para os professores acompanharem a pauta do bimestre e marcarem o que já fizeram.
Feito em Laravel (PHP) com banco de dados MySQL.

- A pessoa cria a conta só com **nome, sobrenome e o código da escola** (entregue pessoalmente).
- Há dois códigos: o **dos professores** e o **da coordenação**. Só quem tem o código da coordenação edita a pauta.
- Depois de entrar, o aparelho fica lembrado: não é preciso entrar de novo.
- Cada tarefa pode ser marcada como feita. A cor da linha mostra o status: feito, vence em até 2 dias, vencido ou a fazer.
- Visual segue o guia de estilo (contorno preto, sombra sólida, letras grandes, alto contraste).

## O que já está pronto

**Parte 1**

- Estrutura Laravel, banco de dados e telas.
- Criar conta e entrar com nome, sobrenome e código da escola.
- Pauta do 4º bimestre (outubro a dezembro) já cadastrada, com filtro por mês e barra de progresso.
- Marcar e desmarcar tarefas sem recarregar a página, com aviso de sucesso e de erro.

**Parte 2: coordenação**

- Tela **Editar pauta**: acrescentar, editar e excluir itens (com pop-up de confirmação), e ver quantos professores já fizeram cada item.
- Tela **Códigos de acesso**: ver e trocar o código dos professores e o da coordenação.
- Conta da coordenação só entra com o código da coordenação. Quem entra com esse código passa a ser da coordenação.
- Comandos `pauta:codigos` (mostra os códigos) e `pauta:coordenador` (cria ou promove uma conta).

## Requisitos

- PHP 8.3 ou mais novo (recomendado: 8.4), com as extensões `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `fileinfo`
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
ESCOLA_CODIGO=ABC123                 # código para os professores
ESCOLA_CODIGO_COORDENACAO=XYZ98765   # código só da coordenação (não passe para os professores)
```

Depois crie as tabelas e a pauta:

```bash
# 3. Criar as tabelas, a escola e as tarefas da pauta
php artisan migrate --seed

# 4. Ver os dois códigos de acesso
php artisan pauta:codigos

# 5. Abrir o site em http://localhost:8000
php artisan serve
```

Códigos deixados em branco são sorteados e aparecem na tela no passo 3. Para virar coordenação,
crie a conta (ou entre) no site usando o código da coordenação.

## Colocar na internet: VPS da Hostinger com CloudPanel

Versões: **Laravel 13** e **PHP 8.4** (o mínimo é PHP 8.3).

### 1. Domínio

Na zona de DNS do domínio, crie (ou confira) um registro **A** para `@` e outro para `www`,
os dois apontando para o IP da VPS.

### 2. No CloudPanel (`https://IP-DA-VPS:8443`)

1. **Site:** *Sites → + Add Site → Create a PHP Site*.
   - *Application:* **Laravel 13** (se não aparecer, escolha o Laravel mais novo da lista; isso só configura o servidor para abrir a pasta `public`).
   - *PHP Version:* **8.4**.
   - *Domain Name:* o seu domínio, sem `www` (ex.: `appmordomia.com.br`).
   - *Site User* e *Site User Password:* um usuário (ex.: `pauta`) e uma senha forte. É com eles que você entra no terminal.
2. **Banco de dados:** dentro do site, aba *Databases → Add Database*. Anote o nome do banco, o usuário e a senha.
3. **HTTPS:** dentro do site, aba *SSL/TLS → Actions → New Let's Encrypt Certificate*. Só funciona depois que o domínio já aponta para a VPS.

### 3. No terminal

```bash
ssh pauta@IP-DA-VPS                     # o Site User criado no CloudPanel

cd ~/htdocs
rm -rf appmordomia.com.br               # pasta vazia criada pelo CloudPanel
git clone https://github.com/barcelosanderson/pautacp.git appmordomia.com.br
cd appmordomia.com.br

PHP_BIN=php8.4 bash publicar.sh instalar   # instala as dependências, cria o .env e para
nano .env                                  # preencha (veja abaixo)
PHP_BIN=php8.4 bash publicar.sh instalar   # cria as tabelas e a pauta (e mostra os códigos)
php8.4 artisan pauta:codigos               # mostra os códigos de novo, quando precisar
```

No `.env`, altere as mesmas linhas da seção da hospedagem compartilhada (abaixo), com `DB_HOST=127.0.0.1`
e o banco, o usuário e a senha criados no CloudPanel.

Para atualizar depois: `cd ~/htdocs/appmordomia.com.br && PHP_BIN=php8.4 bash publicar.sh`

## Colocar na internet: hospedagem compartilhada da Hostinger

Precisa de um plano com acesso SSH (Premium, Business ou Cloud). O plano Single não tem SSH.

### 1. No hPanel

1. **PHP:** no painel do site, em *Configuração do PHP*, escolha **8.3 ou mais novo**.
2. **Banco de dados:** em *Bancos de dados MySQL*, crie um banco. Anote o nome do banco, o usuário e a senha (os dois primeiros começam com `u123456789_`).
3. **SSH:** em *Avançado → Acesso SSH*, ative o acesso e anote o IP, a porta e o usuário.
4. **HTTPS:** em *Segurança → SSL*, instale o certificado gratuito e ative *Forçar HTTPS*.

### 2. No terminal (SSH)

```bash
ssh -p PORTA USUARIO@IP

# Se "php -v" mostrar uma versão menor que 8.3, rode uma vez:
echo 'export PATH=/opt/alt/php83/usr/bin:$PATH' >> ~/.bashrc && source ~/.bashrc

cd ~/domains/SEU-DOMINIO.com.br
git clone https://github.com/barcelosanderson/pautacp.git
cd pautacp
bash publicar.sh instalar      # instala as dependências, cria o .env e para
nano .env                      # preencha (veja abaixo) e salve com Ctrl+O, Enter, Ctrl+X
bash publicar.sh instalar      # cria as tabelas e a pauta (e mostra os códigos)

# Faz o domínio abrir a pasta public do projeto
cd ..
mv public_html public_html_antigo
ln -s pautacp/public public_html
```

No `.env`, altere estas linhas:

```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://SEU-DOMINIO.com.br
LOG_LEVEL=error

DB_HOST=localhost
DB_DATABASE=u123456789_pauta
DB_USERNAME=u123456789_pauta
DB_PASSWORD="a senha do banco"

ESCOLA_NOME="Nome da escola"
ESCOLA_CODIGO=ABC123
ESCOLA_CODIGO_COORDENACAO=XYZ98765
```

E acrescente no fim do arquivo: `SESSION_SECURE_COOKIE=true`

### 3. Atualizar o site depois

Sempre que houver mudanças no GitHub:

```bash
cd ~/domains/SEU-DOMINIO.com.br/pautacp
bash publicar.sh
```

### Se der erro

- **Erro 500:** veja o motivo com `tail -n 40 storage/logs/laravel.log`.
- **Aparece a página padrão da Hostinger:** o atalho `public_html` não foi criado. Confira com `ls -la ~/domains/SEU-DOMINIO.com.br`.
- **"Access denied" no banco:** confira nome do banco, usuário e senha no `.env` e rode `php artisan config:clear`.
- Mudou alguma coisa no `.env`? Rode `php artisan optimize` para o site ler de novo.

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
| Editar pauta (coordenação) | `app/Http/Controllers/Coordenacao/TarefaController.php` |
| Códigos de acesso (coordenação) | `app/Http/Controllers/Coordenacao/CodigoController.php` |
| Regras da pauta (status, resumo, meses) | `app/Services/Pauta.php` |
| Tabelas do banco | `database/migrations` |
| Datas e textos da pauta | `database/seeders/PautaSeeder.php` |
| Telas | `resources/views` |
| Visual (cores, botões, campos…) | `public/css/pauta.css` |
| Avisos, menu e marcar sem recarregar | `public/js/pauta.js` |
| Instalar e atualizar na hospedagem | `publicar.sh` |

## Banco de dados

| Tabela | Para que serve |
| --- | --- |
| `escolas` | Nome da escola, código dos professores e código da coordenação |
| `users` | Pessoas: nome, sobrenome, escola e papel (`professor` ou `coordenador`) |
| `tarefas` | Itens da pauta: data, prazo, descrição, se repete todo mês, se é só aviso |
| `tarefa_user` | Quem marcou cada tarefa como feita, e quando |

## Sobre a segurança do acesso

Entrar só com nome e código é simples para quem tem pouca familiaridade com tecnologia,
mas quem souber o nome de um colega e o código da escola consegue entrar como ele.
Por isso:

- a coordenação tem um código próprio: com o código dos professores ninguém entra numa conta da coordenação;
- o site limita o número de tentativas seguidas;
- se um código vazar, troque na tela **Códigos de acesso**.

Se quiser mais proteção depois, dá para pedir um PIN de 4 números só quando a pessoa entrar em um aparelho novo.
