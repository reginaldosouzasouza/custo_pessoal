<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historico_assinaturas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('assinatura_id')
                ->constrained('assinaturas')
                ->cascadeOnDelete();

            $table->foreignId('plano_id')
                ->constrained('planos')
                ->restrictOnDelete();

            $table->date('data_inicio');

            $table->date('data_fim');

            $table->decimal('valor', 12, 2);

            $table->enum('situacao', [
                'ativa',
                'finalizada',
                'cancelada',
            ]);

            $table->string('forma_pagamento', 100)->nullable();

            $table->date('data_pagamento')->nullable();

            $table->string('referencia_externa', 191)->nullable();

            $table->text('observacao')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'data_inicio']);
            $table->index(['user_id', 'situacao']);
            $table->index('assinatura_id');
            $table->index('plano_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historico_assinaturas');
    }
};