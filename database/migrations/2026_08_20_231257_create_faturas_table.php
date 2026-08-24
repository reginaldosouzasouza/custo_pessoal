<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faturas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('cartao_id')
                ->constrained('cartoes')
                ->cascadeOnDelete();

            $table->string('competencia', 7);

            $table->date('data_fechamento');

            $table->date('data_vencimento');

            $table->decimal('valor_total', 14, 2)->default(0);

            $table->decimal('valor_pago', 14, 2)->default(0);

            $table->enum('situacao', [
                'aberta',
                'fechada',
                'paga',
            ])->default('aberta');

            $table->date('data_pagamento')->nullable();

            $table->foreignId('conta_pagamento_id')
                ->nullable()
                ->constrained('contas')
                ->nullOnDelete();

            $table->timestamp('paga_em')->nullable();

            $table->timestamps();

            $table->unique(
                ['cartao_id', 'competencia'],
                'faturas_cartao_competencia_unique'
            );

            $table->index(['user_id', 'situacao']);
            $table->index(['user_id', 'data_vencimento']);
            $table->index(['user_id', 'competencia']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faturas');
    }
};