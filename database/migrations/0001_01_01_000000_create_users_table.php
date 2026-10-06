<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A conta é criada só com nome, sobrenome e o código da escola.
        // "chave" guarda o nome completo sem acentos e em minúsculas, para
        // achar a pessoa mesmo que ela digite "jose" em vez de "José".
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('escola_id')->constrained('escolas')->cascadeOnDelete();
            $table->string('nome', 60);
            $table->string('sobrenome', 80);
            $table->string('chave', 150);
            $table->string('papel', 20)->default('professor');
            $table->rememberToken();
            $table->timestamps();

            $table->unique(['escola_id', 'chave']);
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('users');
    }
};
