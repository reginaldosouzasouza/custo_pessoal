<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormaPagamento extends Model
{
    use HasFactory;

    protected $table = 'formas_pagamento';

    protected $fillable = [
        'nome',
        'codigo',
        'tipo',
        'ativa',
    ];

    protected $casts = [
        'ativa' => 'boolean',
    ];

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
        return $this->hasMany(Recorrencia::class);
    }
}