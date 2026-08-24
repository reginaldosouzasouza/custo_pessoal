<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('nome', 120);

            $table->enum('tipo', [
                'conta_corrente',
                'conta_digital',
                'poupanca',
                'dinheiro',
                'carteira',
                'outro',
            ]);

            $table->string('instituicao', 150)->nullable();

            $table->decimal('saldo_inicial', 14, 2)->default(0);

            $table->date('data_saldo_inicial')->nullable();

            $table->boolean('ativa')->default(true);

            $table->text('observacao')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'ativa']);
            $table->index(['user_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contas');
    }
};