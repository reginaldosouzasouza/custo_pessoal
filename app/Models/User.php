<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'sga_user_id',
        'name',
        'usuario',
        'email',
        'password',
        'ativo',
        'ultimo_acesso_em',
        'ultimo_acesso_sga',
        'solicitou_exclusao_em',
        'excluido_em',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'ativo' => 'boolean',
            'ultimo_acesso_em' => 'datetime',
            'ultimo_acesso_sga' => 'datetime',
            'solicitou_exclusao_em' => 'datetime',
            'excluido_em' => 'datetime',
        ];
    }

    public function contas(): HasMany
    {
        return $this->hasMany(Conta::class);
    }

    public function categorias(): HasMany
    {
        return $this->hasMany(Categoria::class);
    }

    public function receitas(): HasMany
    {
        return $this->hasMany(Receita::class);
    }

    public function despesas(): HasMany
    {
        return $this->hasMany(Despesa::class);
    }

    public function recorrencias(): HasMany
    {
        return $this->hasMany(Recorrencia::class);
    }

    public function parcelamentos(): HasMany
    {
        return $this->hasMany(Parcelamento::class);
    }

    public function parcelas(): HasMany
    {
        return $this->hasMany(Parcela::class);
    }

    public function cartoes(): HasMany
    {
        return $this->hasMany(Cartao::class);
    }

    public function faturas(): HasMany
    {
        return $this->hasMany(Fatura::class);
    }

    public function comprasCartao(): HasMany
    {
        return $this->hasMany(CompraCartao::class);
    }

    public function parcelasCartao(): HasMany
    {
        return $this->hasMany(ParcelaCartao::class);
    }

    public function transferencias(): HasMany
    {
        return $this->hasMany(Transferencia::class);
    }

    public function ajustesSaldo(): HasMany
    {
        return $this->hasMany(AjusteSaldo::class);
    }

    public function movimentacoesConta(): HasMany
    {
        return $this->hasMany(MovimentacaoConta::class);
    }

    public function assinaturas(): HasMany
    {
        return $this->hasMany(Assinatura::class);
    }

    public function assinaturaAtual(): HasOne
    {
        return $this->hasOne(Assinatura::class)
            ->latestOfMany();
    }

    public function historicoAssinaturas(): HasMany
    {
        return $this->hasMany(HistoricoAssinatura::class);
    }

    public function cobrancas(): HasMany
    {
        return $this->hasMany(Cobranca::class);
    }
}