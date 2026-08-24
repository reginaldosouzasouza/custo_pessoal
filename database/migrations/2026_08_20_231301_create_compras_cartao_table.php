<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compras_cartao', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('cartao_id')
                ->constrained('cartoes')
                ->cascadeOnDelete();

            $table->foreignId('categoria_id')
                ->constrained('categorias')
                ->restrictOnDelete();

            $table->string('descricao', 180);

            $table->decimal('valor_total', 14, 2);

            $table->date('data_compra');

            $table->unsignedInteger('quantidade_parcelas')
                ->default(1);

            $table->boolean('essencial')
                ->nullable();

            $table->enum('situacao', [
                'ativa',
                'cancelada',
            ])->default('ativa');

            $table->text('observacao')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'cartao_id']);
            $table->index(['user_id', 'categoria_id']);
            $table->index(['user_id', 'data_compra']);
            $table->index(['user_id', 'situacao']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compras_cartao');
    }
};