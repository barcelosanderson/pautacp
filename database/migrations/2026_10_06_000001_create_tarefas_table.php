<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarefas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('escola_id')->constrained('escolas')->cascadeOnDelete();
            $table->string('quando', 40);           // como aparece na tela: "06 a 09/10"
            $table->date('inicio');                 // usado para ordenar e separar por mês
            $table->date('prazo');                  // último dia: define a cor do status
            $table->text('descricao');
            $table->boolean('repete_todo_mes')->default(false);
            $table->boolean('checavel')->default(true); // false = só um aviso, como o recesso
            $table->unsignedInteger('ordem')->default(0);
            $table->timestamps();

            $table->index(['escola_id', 'inicio']);
        });

        // Quem já marcou cada tarefa como feita (e quando).
        Schema::create('tarefa_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tarefa_id')->constrained('tarefas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['tarefa_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarefa_user');
        Schema::dropIfExists('tarefas');
    }
};
