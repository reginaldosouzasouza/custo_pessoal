<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fatura extends Model
{
    use HasFactory;

    protected $table = 'faturas';

    protected $fillable = [
        'user_id',
        'cartao_id',
        'competencia',
        'data_fechamento',
        'data_vencimento',
        'valor_total',
        'valor_pago',
        'situacao',
        'data_pagamento',
        'conta_pagamento_id',
        'paga_em',
    ];

    protected $casts = [
        'data_fechamento' => 'date',
        'data_vencimento' => 'date',
        'valor_total' => 'decimal:2',
        'valor_pago' => 'decimal:2',
        'data_pagamento' => 'date',
        'paga_em' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cartao()
    {
        return $this->belongsTo(Cartao::class);
    }

    public function contaPagamento()
    {
        return $this->belongsTo(
            Conta::class,
            'conta_pagamento_id'
        );
    }

    public function parcelas()
    {
        return $this->hasMany(ParcelaCartao::class);
    }
}