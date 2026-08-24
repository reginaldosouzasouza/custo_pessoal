<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimentacaoConta extends Model
{
    use HasFactory;

    protected $table = 'movimentacoes_conta';

    protected $fillable = [
        'user_id',
        'conta_id',
        'tipo',
        'origem_tipo',
        'origem_id',
        'valor',
        'data_movimentacao',
        'descricao',
        'estornada',
        'movimentacao_estorno_id',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data_movimentacao' => 'date',
        'estornada' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function conta()
    {
        return $this->belongsTo(Conta::class);
    }

    public function movimentacaoEstorno()
    {
        return $this->belongsTo(
            MovimentacaoConta::class,
            'movimentacao_estorno_id'
        );
    }

    public function estornos()
    {
        return $this->hasMany(
            MovimentacaoConta::class,
            'movimentacao_estorno_id'
        );
    }
}