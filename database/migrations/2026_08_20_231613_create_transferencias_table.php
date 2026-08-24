<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transferencias', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('conta_origem_id')
                ->constrained('contas')
                ->restrictOnDelete();

            $table->foreignId('conta_destino_id')
                ->constrained('contas')
                ->restrictOnDelete();

            $table->decimal('valor', 14, 2);

            $table->date('data_transferencia');

            $table->string('descricao', 180)->nullable();

            $table->text('observacao')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'data_transferencia']);
            $table->index(['user_id', 'conta_origem_id']);
            $table->index(['user_id', 'conta_destino_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transferencias');
    }
};