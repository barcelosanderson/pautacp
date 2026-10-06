<?php

namespace Tests\Feature;

use App\Models\Escola;
use App\Models\Tarefa;
use App\Models\User;
use Database\Seeders\PautaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PautaTest extends TestCase
{
    use RefreshDatabase;

    private Escola $escola;

    private User $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::create(2026, 10, 6, 10));

        $this->escola = Escola::factory()->create();
        $this->usuario = User::factory()->for($this->escola)->create(['nome' => 'Maria']);
    }

    public function test_quem_nao_entrou_vai_para_a_tela_de_entrada(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_mostra_so_as_tarefas_da_escola_da_pessoa(): void
    {
        Tarefa::factory()->for($this->escola)->create(['descricao' => 'Tarefa da minha escola']);
        Tarefa::factory()->create(['descricao' => 'Tarefa de outra escola']);

        $this->actingAs($this->usuario)
            ->get('/')
            ->assertOk()
            ->assertSee('Olá, Maria.')
            ->assertSee('Tarefa da minha escola')
            ->assertDontSee('Tarefa de outra escola');
    }

    public function test_marcar_e_desmarcar_tarefa(): void
    {
        $tarefa = Tarefa::factory()->for($this->escola)->noDia('2026-10-20')->create();

        $this->actingAs($this->usuario)
            ->postJson(route('tarefas.marcar', $tarefa), ['feita' => true])
            ->assertOk()
            ->assertJsonPath('feita', true)
            ->assertJsonPath('status.rotulo', 'Feito')
            ->assertJsonPath('resumo.feitas', 1)
            ->assertJsonPath('resumo.percentual', 100);

        $this->assertDatabaseHas('tarefa_user', ['tarefa_id' => $tarefa->id, 'user_id' => $this->usuario->id]);

        // Marcar de novo não duplica.
        $this->actingAs($this->usuario)->postJson(route('tarefas.marcar', $tarefa), ['feita' => true])->assertOk();
        $this->assertDatabaseCount('tarefa_user', 1);

        $this->actingAs($this->usuario)
            ->postJson(route('tarefas.marcar', $tarefa), ['feita' => false])
            ->assertOk()
            ->assertJsonPath('feita', false)
            ->assertJsonPath('status.rotulo', 'A fazer')
            ->assertJsonPath('resumo.feitas', 0);

        $this->assertDatabaseCount('tarefa_user', 0);
    }

    public function test_marcacao_de_uma_pessoa_nao_aparece_para_outra(): void
    {
        $tarefa = Tarefa::factory()->for($this->escola)->create();
        $colega = User::factory()->for($this->escola)->create();

        $this->actingAs($this->usuario)->postJson(route('tarefas.marcar', $tarefa), ['feita' => true]);

        $this->actingAs($colega)
            ->postJson(route('tarefas.marcar', $tarefa), ['feita' => false])
            ->assertJsonPath('resumo.feitas', 0);

        $this->assertDatabaseHas('tarefa_user', ['tarefa_id' => $tarefa->id, 'user_id' => $this->usuario->id]);
    }

    public function test_nao_marca_tarefa_de_outra_escola(): void
    {
        $tarefa = Tarefa::factory()->create();

        $this->actingAs($this->usuario)
            ->postJson(route('tarefas.marcar', $tarefa), ['feita' => true])
            ->assertNotFound();
    }

    public function test_nao_marca_aviso_como_o_recesso(): void
    {
        $aviso = Tarefa::factory()->for($this->escola)->create(['checavel' => false]);

        $this->actingAs($this->usuario)
            ->postJson(route('tarefas.marcar', $aviso), ['feita' => true])
            ->assertNotFound();
    }

    public function test_status_conforme_o_prazo(): void
    {
        $hoje = Carbon::create(2026, 10, 6, 15);
        $status = fn (string $prazo, bool $feita = false) => Tarefa::factory()->noDia($prazo)->make()->status($feita, $hoje)['rotulo'];

        $this->assertSame('Vencido', $status('2026-10-05'));
        $this->assertSame('Vence hoje', $status('2026-10-06'));
        $this->assertSame('Vence amanhã', $status('2026-10-07'));
        $this->assertSame('Vence em 2 dias', $status('2026-10-08'));
        $this->assertSame('A fazer', $status('2026-10-09'));
        $this->assertSame('Feito', $status('2026-10-01', true));
    }

    public function test_abre_no_mes_atual_e_filtra_por_mes(): void
    {
        Tarefa::factory()->for($this->escola)->noDia('2026-10-20')->create(['descricao' => 'Tarefa de outubro']);
        Tarefa::factory()->for($this->escola)->noDia('2026-11-20')->create(['descricao' => 'Tarefa de novembro']);

        $this->actingAs($this->usuario)->get('/')
            ->assertSee('Tarefa de outubro')
            ->assertDontSee('Tarefa de novembro');

        $this->actingAs($this->usuario)->get('/?mes=2026-11')
            ->assertSee('Tarefa de novembro')
            ->assertDontSee('Tarefa de outubro');

        $this->actingAs($this->usuario)->get('/?mes=todos')
            ->assertSee('Tarefa de outubro')
            ->assertSee('Tarefa de novembro');
    }

    public function test_avisa_sobre_tarefas_vencidas_e_proximas(): void
    {
        Tarefa::factory()->for($this->escola)->noDia('2026-10-05')->create();
        Tarefa::factory()->for($this->escola)->noDia('2026-10-07')->create();

        $this->actingAs($this->usuario)->get('/')
            ->assertSee('Atenção: 1 tarefa vencida e 1 tarefa que vence em até 2 dias.');
    }

    public function test_pauta_vazia_explica_o_que_acontece(): void
    {
        $this->actingAs($this->usuario)->get('/')
            ->assertSee('Nenhuma tarefa criada ainda.');
    }

    public function test_seeder_cria_a_escola_e_a_pauta_completa_sem_duplicar(): void
    {
        config(['pauta.escola.codigo' => 'abc123', 'pauta.escola.nome' => 'Colégio Teste']);

        $this->seed(PautaSeeder::class);
        $this->seed(PautaSeeder::class);

        $escola = Escola::porCodigo('ABC123');
        $this->assertNotNull($escola);
        $this->assertSame(34, $escola->tarefas()->count());
        $this->assertSame(33, $escola->tarefas()->where('checavel', true)->count());

        $recesso = $escola->tarefas()->where('checavel', false)->sole();
        $this->assertSame('2027-01-17', $recesso->prazo->toDateString());
    }
}
