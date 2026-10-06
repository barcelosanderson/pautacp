<?php

use App\Http\Controllers\AcessoController;
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
