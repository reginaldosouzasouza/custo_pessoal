<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conta extends Model
{
    use HasFactory;

    protected $table = 'contas';

    protected $fillable = [
        'user_id',
        'nome',
        'tipo',
        'instituicao',
        'saldo_inicial',
        'data_saldo_inicial',
        'ativa',
        'observacao',
    ];

    protected $casts = [
        'saldo_inicial' => 'decimal:2',
        'data_saldo_inicial' => 'date',
        'ativa' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function receitas()
    {
        return $this->hasMany(Receita::class);
    }

    public function despesas()
    {
        return $this->hasMany(Despesa::class);
    }

    public function recorrencias()
    {
        return $this->hasMany(
            Recorrencia::class,
            'conta_padrao_id'
        );
    }
}