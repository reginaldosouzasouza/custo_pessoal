<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parcelamentos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('categoria_id')
                ->constrained('categorias')
                ->restrictOnDelete();

            $table->foreignId('forma_pagamento_id')
                ->nullable()
                ->constrained('formas_pagamento')
                ->nullOnDelete();

            $table->foreignId('conta_padrao_id')
                ->nullable()
                ->constrained('contas')
                ->nullOnDelete();

            $table->string('descricao', 180);

            $table->decimal('valor_total', 14, 2);

            $table->unsignedInteger('quantidade_parcelas');

            $table->decimal('valor_parcela', 14, 2);

            $table->date('primeiro_vencimento');

            $table->enum('situacao', [
                'ativo',
                'quitado',
                'cancelado',
            ])->default('ativo');

            $table->text('observacao')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'situacao']);
            $table->index(['user_id', 'categoria_id']);
            $table->index(['user_id', 'primeiro_vencimento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parcelamentos');
    }
};