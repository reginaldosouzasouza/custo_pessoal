<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParcelaCartao extends Model
{
    use HasFactory;

    protected $table = 'parcelas_cartao';

    protected $fillable = [
        'user_id',
        'compra_cartao_id',
        'fatura_id',
        'numero_parcela',
        'total_parcelas',
        'valor',
        'competencia',
        'data_vencimento',
        'situacao',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data_vencimento' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function compra()
    {
        return $this->belongsTo(
            CompraCartao::class,
            'compra_cartao_id'
        );
    }

    public function fatura()
    {
        return $this->belongsTo(Fatura::class);
    }
}