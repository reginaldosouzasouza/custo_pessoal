<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assinaturas', function (Blueprint $table) {
            $table->unsignedBigInteger('plano_id')
                ->nullable()
                ->change();
        });

        DB::statement("
            ALTER TABLE assinaturas
            MODIFY situacao
            ENUM(
                'teste',
                'ativa',
                'vencida',
                'suspensa',
                'cancelada'
            )
            NOT NULL
            DEFAULT 'teste'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE assinaturas
            MODIFY situacao
            ENUM(
                'ativa',
                'vencida',
                'suspensa',
                'cancelada'
            )
            NOT NULL
            DEFAULT 'ativa'
        ");

        Schema::table('assinaturas', function (Blueprint $table) {
            $table->unsignedBigInteger('plano_id')
                ->nullable(false)
                ->change();
        });
    }
};