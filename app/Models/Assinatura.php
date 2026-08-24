<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assinatura extends Model
{
    use HasFactory;

    protected $table = 'assinaturas';

    protected $fillable = [
        'user_id',
        'plano_id',
        'data_inicio',
        'data_vencimento',
        'situacao',
        'renovacao_automatica',
        'gateway',
        'referencia_externa',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_vencimento' => 'date',
        'renovacao_automatica' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plano()
    {
        return $this->belongsTo(Plano::class);
    }

    public function historicos()
    {
        return $this->hasMany(HistoricoAssinatura::class);
    }

    public function cobrancas()
    {
        return $this->hasMany(Cobranca::class);
    }
}