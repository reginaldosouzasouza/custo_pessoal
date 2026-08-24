<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recorrencias', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('categoria_id')
                ->nullable()
                ->constrained('categorias')
                ->nullOnDelete();

            $table->foreignId('forma_pagamento_id')
                ->nullable()
                ->constrained('formas_pagamento')
                ->nullOnDelete();

            $table->foreignId('conta_padrao_id')
                ->nullable()
                ->constrained('contas')
                ->nullOnDelete();

            $table->enum('tipo', [
                'receita',
                'despesa',
            ]);

            $table->string('descricao', 180);

            $table->enum('tipo_valor', [
                'fixo',
                'variavel',
            ])->default('fixo');

            $table->decimal('valor_padrao', 14, 2)->nullable();

            $table->enum('frequencia', [
                'semanal',
                'mensal',
                'trimestral',
                'semestral',
                'anual',
            ])->default('mensal');

            $table->unsignedTinyInteger('dia_vencimento')->nullable();

            $table->date('data_inicio');

            $table->date('data_fim')->nullable();

            $table->boolean('essencial')->nullable();

            $table->boolean('ativa')->default(true);

            $table->text('observacao')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'tipo']);
            $table->index(['user_id', 'ativa']);
            $table->index(['user_id', 'frequencia']);
            $table->index('data_inicio');
            $table->index('data_fim');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recorrencias');
    }
};