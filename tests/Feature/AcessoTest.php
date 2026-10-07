<?php

namespace Tests\Feature;

use App\Models\Escola;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcessoTest extends TestCase
{
    use RefreshDatabase;

    private Escola $escola;

    protected function setUp(): void
    {
        parent::setUp();

        $this->escola = Escola::factory()->create(['codigo' => 'CP2026', 'codigo_coordenacao' => 'COORD2026']);
    }

    public function test_a_tela_de_entrada_abre(): void
    {
        $this->get('/entrar')
            ->assertOk()
            ->assertSee('Criar minha conta')
            ->assertSee('Código da escola');

        $this->get('/entrar?modo=entrar')
            ->assertOk()
            ->assertSee('Entrar na minha conta');
    }

    public function test_cria_conta_com_codigo_valido_e_ja_entra(): void
    {
        $this->post('/criar-conta', [
            'nome' => 'Maria',
            'sobrenome' => 'da Silva',
            'codigo' => 'CP2026',
        ])->assertRedirect(route('pauta'));

        $usuario = User::sole();
        $this->assertAuthenticatedAs($usuario);
        $this->assertSame($this->escola->id, $usuario->escola_id);
        $this->assertSame('maria da silva', $usuario->chave);
        $this->assertSame('MS', $usuario->iniciais);
        $this->assertSame(User::PAPEL_PROFESSOR, $usuario->papel);
    }

    public function test_codigo_aceita_minusculas_e_espacos(): void
    {
        $this->post('/criar-conta', [
            'nome' => 'João',
            'sobrenome' => 'Pereira',
            'codigo' => ' cp 2026 ',
        ])->assertRedirect(route('pauta'));

        $this->assertAuthenticated();
    }

    public function test_nao_cria_conta_com_codigo_errado(): void
    {
        $this->from('/entrar')->post('/criar-conta', [
            'nome' => 'Maria',
            'sobrenome' => 'Silva',
            'codigo' => 'ERRADO',
        ])->assertRedirect('/entrar')->assertSessionHasErrors('codigo');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_campos_sao_obrigatorios(): void
    {
        $this->from('/entrar')->post('/criar-conta', [])
            ->assertSessionHasErrors(['nome', 'sobrenome', 'codigo']);

        $this->assertGuest();
    }

    public function test_nao_cria_duas_contas_com_o_mesmo_nome_na_mesma_escola(): void
    {
        User::factory()->for($this->escola)->create(['nome' => 'José', 'sobrenome' => 'da Silva']);

        $this->from('/entrar')->post('/criar-conta', [
            'nome' => 'jose',
            'sobrenome' => 'DA  SILVA',
            'codigo' => 'CP2026',
        ])->assertSessionHasErrors('nome');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
    }

    public function test_entra_com_conta_existente_mesmo_sem_acento(): void
    {
        $usuario = User::factory()->for($this->escola)->create(['nome' => 'José', 'sobrenome' => 'Araújo']);

        $this->post('/entrar', [
            'nome' => 'jose',
            'sobrenome' => 'araujo',
            'codigo' => 'cp2026',
        ])->assertRedirect(route('pauta'));

        $this->assertAuthenticatedAs($usuario);
    }

    public function test_nao_entra_com_nome_que_nao_existe(): void
    {
        $this->from('/entrar?modo=entrar')->post('/entrar', [
            'nome' => 'Ninguém',
            'sobrenome' => 'Cadastrado',
            'codigo' => 'CP2026',
        ])->assertSessionHasErrors('nome');

        $this->assertGuest();
    }

    public function test_nao_entra_com_codigo_de_outra_escola(): void
    {
        User::factory()->for($this->escola)->create(['nome' => 'Ana', 'sobrenome' => 'Costa']);
        Escola::factory()->create(['codigo' => 'OUTRA1']);

        $this->from('/entrar?modo=entrar')->post('/entrar', [
            'nome' => 'Ana',
            'sobrenome' => 'Costa',
            'codigo' => 'OUTRA1',
        ])->assertSessionHasErrors('nome');

        $this->assertGuest();
    }

    public function test_codigo_da_coordenacao_cria_conta_da_coordenacao(): void
    {
        $this->post('/criar-conta', [
            'nome' => 'Ana',
            'sobrenome' => 'Coordenadora',
            'codigo' => 'coord2026',
        ])->assertRedirect(route('coordenacao.tarefas.index'));

        $this->assertTrue(User::sole()->ehCoordenador());
    }

    public function test_conta_da_coordenacao_nao_entra_com_o_codigo_dos_professores(): void
    {
        User::factory()->for($this->escola)->coordenador()->create(['nome' => 'Ana', 'sobrenome' => 'Coordenadora']);

        $this->from('/entrar?modo=entrar')->post('/entrar', [
            'nome' => 'Ana',
            'sobrenome' => 'Coordenadora',
            'codigo' => 'CP2026',
        ])->assertSessionHasErrors('codigo');

        $this->assertGuest();
    }

    public function test_conta_da_coordenacao_entra_com_o_codigo_da_coordenacao(): void
    {
        $coordenadora = User::factory()->for($this->escola)->coordenador()->create(['nome' => 'Ana', 'sobrenome' => 'Coordenadora']);

        $this->post('/entrar', [
            'nome' => 'Ana',
            'sobrenome' => 'Coordenadora',
            'codigo' => 'COORD2026',
        ])->assertRedirect(route('coordenacao.tarefas.index'));

        $this->assertAuthenticatedAs($coordenadora);
    }

    public function test_professor_que_entra_com_o_codigo_da_coordenacao_vira_coordenador(): void
    {
        $usuario = User::factory()->for($this->escola)->create(['nome' => 'Bruno', 'sobrenome' => 'Lima']);

        $this->post('/entrar', [
            'nome' => 'Bruno',
            'sobrenome' => 'Lima',
            'codigo' => 'COORD2026',
        ])->assertRedirect(route('coordenacao.tarefas.index'));

        $this->assertTrue($usuario->fresh()->ehCoordenador());
    }

    public function test_sair_da_conta(): void
    {
        $usuario = User::factory()->for($this->escola)->create();

        $this->actingAs($usuario)
            ->post('/sair')
            ->assertRedirect(route('login', ['modo' => 'entrar']));

        $this->assertGuest();
    }

    public function test_quem_ja_entrou_vai_direto_para_a_pauta(): void
    {
        $usuario = User::factory()->for($this->escola)->create();

        $this->actingAs($usuario)->get('/entrar')->assertRedirect('/');
    }
}
