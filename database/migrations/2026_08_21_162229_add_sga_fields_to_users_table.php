<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('sga_user_id')
                ->nullable()
                ->unique()
                ->after('id');

            $table->string('usuario', 150)
                ->nullable()
                ->after('name');

            $table->timestamp('ultimo_acesso_sga')
                ->nullable()
                ->after('ultimo_acesso_em');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['sga_user_id']);

            $table->dropColumn([
                'sga_user_id',
                'usuario',
                'ultimo_acesso_sga',
            ]);
        });
    }
};