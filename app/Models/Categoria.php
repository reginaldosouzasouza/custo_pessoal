<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;

    protected $table = 'categorias';

    protected $fillable = [
        'user_id',
        'nome',
        'tipo',
        'classificacao',
        'icone',
        'ativa',
        'padrao_sistema',
    ];

    protected $casts = [
        'ativa' => 'boolean',
        'padrao_sistema' => 'boolean',
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
        return $this->hasMany(Recorrencia::class);
    }
}