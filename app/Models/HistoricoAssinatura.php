<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoricoAssinatura extends Model
{
    use HasFactory;

    protected $table = 'historico_assinaturas';

    protected $fillable = [
        'user_id',
        'assinatura_id',
        'plano_id',
        'data_inicio',
        'data_fim',
        'valor',
        'situacao',
        'forma_pagamento',
        'data_pagamento',
        'referencia_externa',
        'observacao',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'data_pagamento' => 'date',
        'valor' => 'decimal:2',
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