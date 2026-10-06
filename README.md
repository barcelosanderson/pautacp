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

## Colocar na internet (Hostinger)

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
bash publicar.sh instalar      # cria as tabelas e a pauta
php artisan pauta:coordenador "Seu Nome" "Sobrenome"

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
