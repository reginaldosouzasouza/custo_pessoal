<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimentacoes_conta', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('conta_id')
                ->constrained('contas')
                ->restrictOnDelete();

            $table->enum('tipo', [
                'entrada',
                'saida',
            ]);

            $table->enum('origem_tipo', [
                'receita',
                'despesa',
                'parcela',
                'transferencia',
                'pagamento_fatura',
                'ajuste',
                'estorno',
            ]);

            $table->unsignedBigInteger('origem_id')->nullable();

            $table->decimal('valor', 14, 2);

            $table->date('data_movimentacao');

            $table->string('descricao', 180);

            $table->boolean('estornada')->default(false);

            $table->foreignId('movimentacao_estorno_id')
                ->nullable()
                ->constrained('movimentacoes_conta')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['user_id', 'conta_id']);
            $table->index(['user_id', 'data_movimentacao']);
            $table->index(['user_id', 'tipo']);
            $table->index(['origem_tipo', 'origem_id']);
            $table->index(['user_id', 'estornada']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimentacoes_conta');
    }
};