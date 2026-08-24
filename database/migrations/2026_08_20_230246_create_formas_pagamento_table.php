<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formas_pagamento', function (Blueprint $table) {
            $table->id();

            $table->string('nome', 100);

            $table->string('codigo', 50)->unique();

            $table->enum('tipo', [
                'pagamento',
                'recebimento',
                'ambos',
            ])->default('ambos');

            $table->boolean('ativa')->default(true);

            $table->timestamps();

            $table->index('ativa');
            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formas_pagamento');
    }
};