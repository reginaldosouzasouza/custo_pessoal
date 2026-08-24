<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recorrencia extends Model
{
    use HasFactory;

    protected $table = 'recorrencias';

    protected $fillable = [
        'user_id',
        'categoria_id',
        'forma_pagamento_id',
        'conta_padrao_id',
        'tipo',
        'descricao',
        'tipo_valor',
        'valor_padrao',
        'frequencia',
        'dia_vencimento',
        'data_inicio',
        'data_fim',
        'essencial',
        'ativa',
        'observacao',
    ];

    protected $casts = [
        'valor_padrao' => 'decimal:2',
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'essencial' => 'boolean',
        'ativa' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function formaPagamento()
    {
        return $this->belongsTo(
            FormaPagamento::class,
            'forma_pagamento_id'
        );
    }

    public function contaPadrao()
    {
        return $this->belongsTo(
            Conta::class,
            'conta_padrao_id'
        );
    }

    public function receitas()
    {
        return $this->hasMany(Receita::class);
    }

    public function despesas()
    {
        return $this->hasMany(Despesa::class);
    }
}