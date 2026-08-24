<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cartao extends Model
{
    use HasFactory;

    protected $table = 'cartoes';

    protected $fillable = [
        'user_id',
        'nome',
        'instituicao',
        'ultimos_digitos',
        'limite_total',
        'dia_fechamento',
        'dia_vencimento',
        'conta_pagamento_id',
        'ativo',
        'observacao',
    ];

    protected $casts = [
        'limite_total' => 'decimal:2',
        'ativo' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contaPagamento()
    {
        return $this->belongsTo(
            Conta::class,
            'conta_pagamento_id'
        );
    }

    public function faturas()
    {
        return $this->hasMany(Fatura::class);
    }

    public function compras()
    {
        return $this->hasMany(CompraCartao::class);
    }
}