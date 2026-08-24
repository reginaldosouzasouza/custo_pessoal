<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Despesa extends Model
{
    use HasFactory;

    protected $table = 'despesas';

    protected $fillable = [
        'user_id',
        'categoria_id',
        'conta_id',
        'forma_pagamento_id',
        'recorrencia_id',
        'descricao',
        'valor',
        'data_despesa',
        'data_vencimento',
        'data_pagamento',
        'situacao',
        'essencial',
        'valor_estimado',
        'observacao',
        'paga_em',
        'cancelada_em',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data_despesa' => 'date',
        'data_vencimento' => 'date',
        'data_pagamento' => 'date',
        'essencial' => 'boolean',
        'valor_estimado' => 'boolean',
        'paga_em' => 'datetime',
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