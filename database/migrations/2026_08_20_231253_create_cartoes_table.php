<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cartoes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('nome', 120);

            $table->string('instituicao', 150)->nullable();

            $table->string('ultimos_digitos', 4)->nullable();

            $table->decimal('limite_total', 14, 2)->default(0);

            $table->unsignedTinyInteger('dia_fechamento');

            $table->unsignedTinyInteger('dia_vencimento');

            $table->foreignId('conta_pagamento_id')
                ->nullable()
                ->constrained('contas')
                ->nullOnDelete();

            $table->boolean('ativo')->default(true);

            $table->text('observacao')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'ativo']);
            $table->index(['user_id', 'instituicao']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cartoes');
    }
};