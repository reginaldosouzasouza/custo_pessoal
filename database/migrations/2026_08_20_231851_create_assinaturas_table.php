<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assinaturas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('plano_id')
                ->constrained('planos')
                ->restrictOnDelete();

            $table->date('data_inicio');

            $table->date('data_vencimento');

            $table->enum('situacao', [
                'ativa',
                'vencida',
                'suspensa',
                'cancelada',
            ])->default('ativa');

            $table->boolean('renovacao_automatica')
                ->default(false);

            $table->string('gateway', 100)->nullable();

            $table->string('referencia_externa', 191)->nullable();

            $table->timestamps();

            $table->index(['user_id', 'situacao']);
            $table->index(['user_id', 'data_vencimento']);
            $table->index('plano_id');
            $table->index('referencia_externa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assinaturas');
    }
};