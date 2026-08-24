<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parcelas_cartao', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('compra_cartao_id')
                ->constrained('compras_cartao')
                ->cascadeOnDelete();

            $table->foreignId('fatura_id')
                ->constrained('faturas')
                ->cascadeOnDelete();

            $table->unsignedInteger('numero_parcela');

            $table->unsignedInteger('total_parcelas');

            $table->decimal('valor', 14, 2);

            $table->string('competencia', 7);

            $table->date('data_vencimento');

            $table->enum('situacao', [
                'pendente',
                'paga',
                'cancelada',
            ])->default('pendente');

            $table->timestamps();

            $table->unique(
                ['compra_cartao_id', 'numero_parcela'],
                'parcelas_cartao_compra_numero_unique'
            );

            $table->index(['user_id', 'fatura_id']);
            $table->index(['user_id', 'situacao']);
            $table->index(['user_id', 'competencia']);
            $table->index(['user_id', 'data_vencimento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parcelas_cartao');
    }
};