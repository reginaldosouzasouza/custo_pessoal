<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parcelas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('parcelamento_id')
                ->constrained('parcelamentos')
                ->cascadeOnDelete();

            $table->unsignedInteger('numero_parcela');

            $table->unsignedInteger('total_parcelas');

            $table->decimal('valor', 14, 2);

            $table->date('data_vencimento');

            $table->date('data_pagamento')->nullable();

            $table->enum('situacao', [
                'pendente',
                'paga',
                'cancelada',
            ])->default('pendente');

            $table->foreignId('conta_id')
                ->nullable()
                ->constrained('contas')
                ->nullOnDelete();

            $table->foreignId('forma_pagamento_id')
                ->nullable()
                ->constrained('formas_pagamento')
                ->nullOnDelete();

            $table->timestamp('paga_em')->nullable();

            $table->text('observacao')->nullable();

            $table->timestamps();

            $table->unique(
                ['parcelamento_id', 'numero_parcela'],
                'parcelas_parcelamento_numero_unique'
            );

            $table->index(['user_id', 'situacao']);
            $table->index(['user_id', 'data_vencimento']);
            $table->index(['user_id', 'conta_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parcelas');
    }
};