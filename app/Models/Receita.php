<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receita extends Model
{
    use HasFactory;

    protected $table = 'receitas';

    protected $fillable = [
        'user_id',
        'categoria_id',
        'conta_id',
        'forma_pagamento_id',
        'recorrencia_id',
        'descricao',
        'valor',
        'data_prevista',
        'data_recebimento',
        'situacao',
        'observacao',
        'recebida_em',
        'cancelada_em',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data_prevista' => 'date',
        'data_recebimento' => 'date',
        'recebida_em' => 'datetime',
        'cancelada_em' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function conta()
    {
        return $this->belongsTo(Conta::class);
    }

    public function formaPagamento()
    {
        return $this->belongsTo(
            FormaPagamento::class,
            'forma_pagamento_id'
        );
    }

    public function recorrencia()
    {
        return $this->belongsTo(Recorrencia::class);
    }
}