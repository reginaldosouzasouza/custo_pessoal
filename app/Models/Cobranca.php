<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cobranca extends Model
{
    use HasFactory;

    protected $table = 'cobrancas';

    protected $fillable = [
        'user_id',
        'assinatura_id',
        'plano_id',
        'valor',
        'data_vencimento',
        'data_pagamento',
        'situacao',
        'forma_pagamento',
        'gateway',
        'gateway_id',
        'link_pagamento',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data_vencimento' => 'date',
        'data_pagamento' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assinatura()
    {
        return $this->belongsTo(Assinatura::class);
    }

    public function plano()
    {
        return $this->belongsTo(Plano::class);
    }
}