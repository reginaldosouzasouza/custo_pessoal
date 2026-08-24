<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parcela extends Model
{
    use HasFactory;

    protected $table = 'parcelas';

    protected $fillable = [
        'user_id',
        'parcelamento_id',
        'numero_parcela',
        'total_parcelas',
        'valor',
        'data_vencimento',
        'data_pagamento',
        'situacao',
        'conta_id',
        'forma_pagamento_id',
        'paga_em',
        'observacao',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data_vencimento' => 'date',
        'data_pagamento' => 'date',
        'paga_em' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parcelamento()
    {
        return $this->belongsTo(Parcelamento::class);
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
}