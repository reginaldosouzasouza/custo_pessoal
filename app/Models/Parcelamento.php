<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parcelamento extends Model
{
    use HasFactory;

    protected $table = 'parcelamentos';

    protected $fillable = [
        'user_id',
        'categoria_id',
        'forma_pagamento_id',
        'conta_padrao_id',
        'descricao',
        'valor_total',
        'quantidade_parcelas',
        'valor_parcela',
        'primeiro_vencimento',
        'situacao',
        'observacao',
    ];

    protected $casts = [
        'valor_total' => 'decimal:2',
        'valor_parcela' => 'decimal:2',
        'primeiro_vencimento' => 'date',
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

    public function parcelas()
    {
        return $this->hasMany(Parcela::class);
    }
}