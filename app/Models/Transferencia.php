<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transferencia extends Model
{
    use HasFactory;

    protected $table = 'transferencias';

    protected $fillable = [
        'user_id',
        'conta_origem_id',
        'conta_destino_id',
        'valor',
        'data_transferencia',
        'descricao',
        'observacao',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data_transferencia' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contaOrigem()
    {
        return $this->belongsTo(
            Conta::class,
            'conta_origem_id'
        );
    }

    public function contaDestino()
    {
        return $this->belongsTo(
            Conta::class,
            'conta_destino_id'
        );
    }
}