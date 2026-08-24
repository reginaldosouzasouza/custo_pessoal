<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('nome', 120);

            $table->enum('tipo', [
                'receita',
                'despesa',
            ]);

            $table->enum('classificacao', [
                'essencial',
                'nao_essencial',
            ])->nullable();

            $table->string('icone', 100)->nullable();

            $table->boolean('ativa')->default(true);

            $table->boolean('padrao_sistema')->default(false);

            $table->timestamps();

            $table->index(['user_id', 'tipo']);
            $table->index(['user_id', 'ativa']);

            $table->unique(
                ['user_id', 'nome', 'tipo'],
                'categorias_user_nome_tipo_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};