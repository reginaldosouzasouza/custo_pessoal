<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planos', function (Blueprint $table) {
            $table->id();

            $table->string('nome', 100);

            $table->unsignedInteger('duracao_meses');

            $table->decimal('valor', 12, 2);

            $table->text('descricao')->nullable();

            $table->boolean('ativo')->default(true);

            $table->timestamps();

            $table->unique('nome');
            $table->index('ativo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planos');
    }
};