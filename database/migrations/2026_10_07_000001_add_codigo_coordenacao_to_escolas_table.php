<?php

use App\Models\Escola;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Código separado para a coordenação: com o código dos professores
     * ninguém entra numa conta da coordenação.
     */
    public function up(): void
    {
        Schema::table('escolas', function (Blueprint $table) {
            $table->string('codigo_coordenacao', 40)->nullable()->unique()->after('codigo');
        });

        // Escolas que já existiam recebem um código sorteado.
        // Para ver: php artisan pauta:codigos
        foreach (DB::table('escolas')->whereNull('codigo_coordenacao')->pluck('id') as $id) {
            DB::table('escolas')->where('id', $id)->update([
                'codigo_coordenacao' => Escola::sortearCodigo(8),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('escolas', function (Blueprint $table) {
            $table->dropUnique(['codigo_coordenacao']);
            $table->dropColumn('codigo_coordenacao');
        });
    }
};
