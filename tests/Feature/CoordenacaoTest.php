<?php

namespace Tests\Feature;

use App\Models\Escola;
use App\Models\Tarefa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CoordenacaoTest extends TestCase
{
    use RefreshDatabase;

    private Escola $escola;

    private User $coordenadora;

    private User $professor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::create(2026, 10, 7, 10));

        $this->escola = Escola::factory()->create(['codigo' => 'PROF26', 'codigo_coordenacao' => 'COORD26']);
        $this->coordenadora = User::factory()->for($this->escola)->coordenador()->create();
        $this->professor = User::factory()->for($this->escola)->create();
    }

    public function test_professor_nao_entra_na_area_da_coordenacao(): void
    {
        $tarefa = Tarefa::factory()->for($this->escola)->create();

        $this->actingAs($this->professor)->get(route('coordenacao.tarefas.index'))->assertForbidden()->assertSee('fale com o cara');
        $this->actingAs($this->professor)->get(route('coordenacao.tarefas.create'))->assertForbidden();
        $this->actingAs($this->professor)->post(route('coordenacao.tarefas.store'), ['inicio' => '2026-10-20', 'descricao' => 'X'])->assertForbidden();
        $this->actingAs($this->professor)->delete(route('coordenacao.tarefas.destroy', $tarefa))->assertForbidden();
        $this->actingAs($this->professor)->get(route('coordenacao.codigos'))->assertForbidden();

        $this->assertDatabaseCount('tarefas', 1);
    }

    public function test_menu_mostra_as_abas_da_coordenacao_so_para_a_coordenacao(): void
    {
        $this->actingAs($this->professor)->get('/')->assertDontSee('Editar pauta');
        $this->actingAs($this->coordenadora)->get('/')->assertSee('Editar pauta')->assertSee('Códigos de acesso');
    }

    public function test_lista_os_itens_com_quantos_professores_ja_fizeram(): void
    {
        $tarefa = Tarefa::factory()->for($this->escola)->noDia('2026-10-20')->create(['descricao' => 'Entregar o plano']);
        $this->professor->tarefasFeitas()->attach($tarefa);

        $this->actingAs($this->coordenadora)
            ->get(route('coordenacao.tarefas.index'))
            ->assertOk()
            ->assertSee('Entregar o plano')
            ->assertSee('Feito por 1 de 1 professor');
    }

    public function test_acrescenta_item_e_monta_o_texto_da_data_sozinho(): void
    {
        $this->actingAs($this->coordenadora)
            ->post(route('coordenacao.tarefas.store'), [
                'inicio' => '2026-10-06',
                'prazo' => '2026-10-09',
                'descricao' => 'Recado de boas-vindas',
                'repete_todo_mes' => '1',
            ])
            ->assertRedirect(route('coordenacao.tarefas.index', ['mes' => '2026-10']))
            ->assertSessionHas('sucesso', 'Item acrescentado à pauta.');

        $tarefa = Tarefa::sole();
        $this->assertSame($this->escola->id, $tarefa->escola_id);
        $this->assertSame('06 a 09/10', $tarefa->quando);
        $this->assertTrue($tarefa->repete_todo_mes);
        $this->assertTrue($tarefa->checavel);

        // O professor vê o item novo na pauta dele.
        $this->actingAs($this->professor)->get('/')->assertSee('Recado de boas-vindas');
    }

    public function test_acrescenta_aviso_de_um_dia_com_texto_proprio(): void
    {
        $this->actingAs($this->coordenadora)->post(route('coordenacao.tarefas.store'), [
            'inicio' => '2026-12-21',
            'quando' => 'Até 21/12',
            'descricao' => 'Recesso',
            'aviso' => '1',
        ]);

        $tarefa = Tarefa::sole();
        $this->assertSame('Até 21/12', $tarefa->quando);
        $this->assertSame('2026-12-21', $tarefa->prazo->toDateString());
        $this->assertFalse($tarefa->checavel);
    }

    public function test_texto_da_data_para_cada_caso(): void
    {
        $rotulo = fn (string $inicio, string $prazo) => Tarefa::rotuloDasDatas(Carbon::parse($inicio), Carbon::parse($prazo));

        $this->assertSame('06/10', $rotulo('2026-10-06', '2026-10-06'));
        $this->assertSame('06 e 07/10', $rotulo('2026-10-06', '2026-10-07'));
        $this->assertSame('06 a 09/10', $rotulo('2026-10-06', '2026-10-09'));
        $this->assertSame('21/12 a 17/01', $rotulo('2026-12-21', '2027-01-17'));
    }

    public function test_nao_salva_item_sem_dia_ou_com_prazo_antes_do_inicio(): void
    {
        $this->actingAs($this->coordenadora)
            ->from(route('coordenacao.tarefas.create'))
            ->post(route('coordenacao.tarefas.store'), ['inicio' => '', 'descricao' => ''])
            ->assertSessionHasErrors(['inicio', 'descricao']);

        $this->actingAs($this->coordenadora)
            ->from(route('coordenacao.tarefas.create'))
            ->post(route('coordenacao.tarefas.store'), ['inicio' => '2026-10-10', 'prazo' => '2026-10-09', 'descricao' => 'X'])
            ->assertSessionHasErrors('prazo');

        $this->assertDatabaseCount('tarefas', 0);
    }

    public function test_edita_item(): void
    {
        $tarefa = Tarefa::factory()->for($this->escola)->noDia('2026-10-09')->create(['descricao' => 'Antigo']);

        $this->actingAs($this->coordenadora)
            ->get(route('coordenacao.tarefas.edit', $tarefa))
            ->assertOk()
            ->assertSee('Antigo');

        $this->actingAs($this->coordenadora)
            ->put(route('coordenacao.tarefas.update', $tarefa), [
                'inicio' => '2026-11-03',
                'prazo' => '2026-11-04',
                'descricao' => 'Novo texto',
            ])
            ->assertRedirect(route('coordenacao.tarefas.index', ['mes' => '2026-11']));

        $tarefa->refresh();
        $this->assertSame('Novo texto', $tarefa->descricao);
        $this->assertSame('03 e 04/11', $tarefa->quando);
    }

    public function test_texto_proprio_da_data_aparece_no_formulario_e_o_automatico_nao(): void
    {
        $automatico = Tarefa::factory()->for($this->escola)->noDia('2026-10-09')->create();
        $proprio = Tarefa::factory()->for($this->escola)->noDia('2026-10-06')->create(['quando' => 'Até 06/10']);

        $this->assertSame('', $automatico->quandoPersonalizado());
        $this->assertSame('Até 06/10', $proprio->quandoPersonalizado());
    }

    public function test_exclui_item_junto_com_as_marcacoes(): void
    {
        $tarefa = Tarefa::factory()->for($this->escola)->create();
        $this->professor->tarefasFeitas()->attach($tarefa);

        $this->actingAs($this->coordenadora)
            ->delete(route('coordenacao.tarefas.destroy', $tarefa))
            ->assertSessionHas('sucesso', 'Item excluído da pauta.');

        $this->assertDatabaseCount('tarefas', 0);
        $this->assertDatabaseCount('tarefa_user', 0);
    }

    public function test_nao_mexe_em_item_de_outra_escola(): void
    {
        $deOutra = Tarefa::factory()->create(['descricao' => 'De outra escola']);

        $this->actingAs($this->coordenadora)->get(route('coordenacao.tarefas.edit', $deOutra))->assertNotFound();
        $this->actingAs($this->coordenadora)->put(route('coordenacao.tarefas.update', $deOutra), [
            'inicio' => '2026-10-10', 'descricao' => 'Hackeado',
        ])->assertNotFound();
        $this->actingAs($this->coordenadora)->delete(route('coordenacao.tarefas.destroy', $deOutra))->assertNotFound();

        $this->assertSame('De outra escola', $deOutra->fresh()->descricao);
    }

    public function test_mostra_e_troca_os_codigos(): void
    {
        $this->actingAs($this->coordenadora)
            ->get(route('coordenacao.codigos'))
            ->assertOk()
            ->assertSee('PROF26')
            ->assertSee('COORD26');

        $this->actingAs($this->coordenadora)
            ->put(route('coordenacao.codigos.trocar', 'professores'), ['novo_codigo_professores' => ' novo 2027 '])
            ->assertRedirect(route('coordenacao.codigos'))
            ->assertSessionHas('sucesso', 'Código dos professores trocado.');

        $this->assertSame('NOVO2027', $this->escola->fresh()->codigo);

        $this->actingAs($this->coordenadora)
            ->put(route('coordenacao.codigos.trocar', 'coordenacao'), ['novo_codigo_coordenacao' => 'CHEFIA99'])
            ->assertSessionHas('sucesso', 'Código da coordenação trocado.');

        $this->assertSame('CHEFIA99', $this->escola->fresh()->codigo_coordenacao);
    }

    public function test_codigo_antigo_dos_professores_para_de_funcionar(): void
    {
        $this->actingAs($this->coordenadora)
            ->put(route('coordenacao.codigos.trocar', 'professores'), ['novo_codigo_professores' => 'NOVO2027']);

        auth()->logout();

        $this->from('/entrar')->post('/criar-conta', [
            'nome' => 'Carla', 'sobrenome' => 'Dias', 'codigo' => 'PROF26',
        ])->assertSessionHasErrors('codigo');

        $this->post('/criar-conta', [
            'nome' => 'Carla', 'sobrenome' => 'Dias', 'codigo' => 'NOVO2027',
        ])->assertRedirect(route('pauta'));
    }

    public function test_codigos_nao_podem_ser_iguais_nem_repetir_outra_escola(): void
    {
        Escola::factory()->create(['codigo' => 'OUTRA1', 'codigo_coordenacao' => 'OUTRA2']);

        $trocar = fn (string $tipo, string $valor) => $this->actingAs($this->coordenadora)
            ->from(route('coordenacao.codigos'))
            ->put(route('coordenacao.codigos.trocar', $tipo), ['novo_codigo_'.$tipo => $valor]);

        $trocar('professores', 'coord26')->assertSessionHasErrors('novo_codigo_professores');
        $trocar('coordenacao', 'PROF26')->assertSessionHasErrors('novo_codigo_coordenacao');
        $trocar('professores', 'OUTRA2')->assertSessionHasErrors('novo_codigo_professores');
        $trocar('professores', 'ABC')->assertSessionHasErrors('novo_codigo_professores');
        $trocar('professores', 'AÇÚCAR1')->assertSessionHasErrors('novo_codigo_professores');
        $trocar('professores', '')->assertSessionHasErrors('novo_codigo_professores');

        $this->assertSame('PROF26', $this->escola->fresh()->codigo);
    }
}
