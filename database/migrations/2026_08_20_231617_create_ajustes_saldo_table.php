<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ajustes_saldo', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('conta_id')
                ->constrained('contas')
                ->restrictOnDelete();

            $table->enum('tipo', [
                'credito',
                'debito',
            ]);

            $table->decimal('valor', 14, 2);

            $table->date('data_ajuste');

            $table->string('motivo', 180);

            $table->text('observacao')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'data_ajuste']);
            $table->index(['user_id', 'conta_id']);
            $table->index(['user_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ajustes_saldo');
    }
};