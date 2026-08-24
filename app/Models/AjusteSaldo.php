<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AjusteSaldo extends Model
{
    use HasFactory;

    protected $table = 'ajustes_saldo';

    protected $fillable = [
        'user_id',
        'conta_id',
        'tipo',
        'valor',
        'data_ajuste',
        'motivo',
        'observacao',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data_ajuste' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function conta()
    {
        return $this->belongsTo(Conta::class);
    }
}