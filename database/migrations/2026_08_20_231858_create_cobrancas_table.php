<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cobrancas', function (Blueprint $table) {
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

            $table->decimal('valor', 12, 2);

            $table->date('data_vencimento');

            $table->date('data_pagamento')->nullable();

            $table->enum('situacao', [
                'pendente',
                'paga',
                'vencida',
                'cancelada',
            ])->default('pendente');

            $table->string('forma_pagamento', 100)->nullable();

            $table->string('gateway', 100)->nullable();

            $table->string('gateway_id', 191)->nullable();

            $table->text('link_pagamento')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'situacao']);
            $table->index(['user_id', 'data_vencimento']);
            $table->index('assinatura_id');
            $table->index('plano_id');
            $table->index('gateway_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cobrancas');
    }
};