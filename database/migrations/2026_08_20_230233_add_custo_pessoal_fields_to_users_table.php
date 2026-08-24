<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('ativo')
                ->default(true)
                ->after('password');

            $table->timestamp('ultimo_acesso_em')
                ->nullable()
                ->after('ativo');

            $table->timestamp('solicitou_exclusao_em')
                ->nullable()
                ->after('ultimo_acesso_em');

            $table->timestamp('excluido_em')
                ->nullable()
                ->after('solicitou_exclusao_em');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'ativo',
                'ultimo_acesso_em',
                'solicitou_exclusao_em',
                'excluido_em',
            ]);
        });
    }
};