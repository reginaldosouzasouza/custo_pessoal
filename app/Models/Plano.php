<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plano extends Model
{
    use HasFactory;

    protected $table = 'planos';

    protected $fillable = [
        'nome',
        'duracao_meses',
        'valor',
        'descricao',
        'ativo',
    ];

    protected $casts = [
        'duracao_meses' => 'integer',
        'valor' => 'decimal:2',
        'ativo' => 'boolean',
    ];

    public function assinaturas()
    {
        return $this->hasMany(Assinatura::class);
    }

    public function historicoAssinaturas()
    {
        return $this->hasMany(HistoricoAssinatura::class);
    }

    public function cobrancas()
    {
        return $this->hasMany(Cobranca::class);
    }
}