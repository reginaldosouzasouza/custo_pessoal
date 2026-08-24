<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receitas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('categoria_id')
                ->constrained('categorias')
                ->restrictOnDelete();

            $table->foreignId('conta_id')
                ->nullable()
                ->constrained('contas')
                ->nullOnDelete();

            $table->foreignId('forma_pagamento_id')
                ->nullable()
                ->constrained('formas_pagamento')
                ->nullOnDelete();

            $table->foreignId('recorrencia_id')
                ->nullable()
                ->constrained('recorrencias')
                ->nullOnDelete();

            $table->string('descricao', 180);

            $table->decimal('valor', 14, 2);

            $table->date('data_prevista');

            $table->date('data_recebimento')->nullable();

            $table->enum('situacao', [
                'pendente',
                'recebida',
                'cancelada',
            ])->default('pendente');

            $table->text('observacao')->nullable();

            $table->timestamp('recebida_em')->nullable();

            $table->timestamp('cancelada_em')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'situacao']);
            $table->index(['user_id', 'data_prevista']);
            $table->index(['user_id', 'categoria_id']);
            $table->index(['user_id', 'conta_id']);
            $table->index('recorrencia_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receitas');
    }
};