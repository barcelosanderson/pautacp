<?php

use App\Http\Controllers\AcessoController;
use App\Http\Controllers\Coordenacao\CodigoController;
use App\Http\Controllers\Coordenacao\TarefaController;
use App\Http\Controllers\PautaController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/entrar', [AcessoController::class, 'mostrar'])->name('login');
    Route::post('/entrar', [AcessoController::class, 'entrar'])->middleware('throttle:acesso')->name('entrar');
    Route::post('/criar-conta', [AcessoController::class, 'criarConta'])->middleware('throttle:acesso')->name('criar-conta');
});

Route::middleware('auth')->group(function () {
    Route::get('/', [PautaController::class, 'index'])->name('pauta');
    Route::post('/tarefas/{tarefa}/feita', [PautaController::class, 'marcar'])->name('tarefas.marcar');
    Route::post('/sair', [AcessoController::class, 'sair'])->name('sair');
});

// Só para contas da coordenação (quem entrou com o código da coordenação).
Route::middleware(['auth', 'can:coordenar'])->prefix('coordenacao')->name('coordenacao.')->group(function () {
    Route::get('/pauta', [TarefaController::class, 'index'])->name('tarefas.index');
    Route::get('/pauta/novo', [TarefaController::class, 'create'])->name('tarefas.create');
    Route::post('/pauta', [TarefaController::class, 'store'])->name('tarefas.store');
    Route::get('/pauta/{tarefa}/editar', [TarefaController::class, 'edit'])->name('tarefas.edit');
    Route::put('/pauta/{tarefa}', [TarefaController::class, 'update'])->name('tarefas.update');
    Route::delete('/pauta/{tarefa}', [TarefaController::class, 'destroy'])->name('tarefas.destroy');

    Route::get('/codigos', [CodigoController::class, 'mostrar'])->name('codigos');
    Route::put('/codigos/{tipo}', [CodigoController::class, 'trocar'])
        ->whereIn('tipo', ['professores', 'coordenacao'])
        ->name('codigos.trocar');
});
