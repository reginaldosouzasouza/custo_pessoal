<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompraCartao extends Model
{
    use HasFactory;

    protected $table = 'compras_cartao';

    protected $fillable = [
        'user_id',
        'cartao_id',
        'categoria_id',
        'descricao',
        'valor_total',
        'data_compra',
        'quantidade_parcelas',
        'essencial',
        'situacao',
        'observacao',
    ];

    protected $casts = [
        'valor_total' => 'decimal:2',
        'data_compra' => 'date',
        'essencial' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cartao()
    {
        return $this->belongsTo(Cartao::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function parcelas()
    {
        return $this->hasMany(
            ParcelaCartao::class,
            'compra_cartao_id'
        );
    }
}