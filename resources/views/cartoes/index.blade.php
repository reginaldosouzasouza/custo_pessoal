@extends('layouts.custo-pessoal')

@section('title', 'Cartões')

@push('styles')
<style>
    .cartoes-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
        margin-bottom: 22px;
    }

    .btn-novo {
        display: inline-flex;
        align-items: center;
        justify-content: center;

        padding: 10px 16px;

        border-radius: 8px;

        background: #0d6efd;
        color: #fff;

        text-decoration: none;

        font-size: 14px;
        font-weight: 600;
    }

    .cartoes-grid {
        display: grid;
        grid-template-columns:
            repeat(3, minmax(0, 1fr));
        gap: 18px;
    }

    .cartao-card {
        padding: 20px;
    }

    .cartao-topo {
        display: flex;
        justify-content: space-between;
        gap: 15px;
        margin-bottom: 18px;
    }

    .cartao-nome {
        font-size: 17px;
        font-weight: 700;
        color: #111827;
    }

    .cartao-instituicao {
        color: #6b7280;
        font-size: 12px;
        margin-top: 3px;
    }

    .cartao-status {
        display: inline-block;
        border-radius: 999px;
        padding: 4px 9px;

        font-size: 10px;
        font-weight: 700;

        height: fit-content;
    }

    .status-ativo {
        background: #dcfce7;
        color: #166534;
    }

    .status-inativo {
        background: #e5e7eb;
        color: #4b5563;
    }

    .cartao-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-top: 15px;
    }

    .cartao-info-label {
        font-size: 10px;
        color: #6b7280;
    }

    .cartao-info-value {
        font-size: 14px;
        font-weight: 700;
        color: #111827;
        margin-top: 3px;
    }

    .cartao-limite {
        margin-top: 18px;
    }

    .cartao-limite-label {
        color: #6b7280;
        font-size: 11px;
    }

    .cartao-limite-valor {
        color: #7653cb;
        font-size: 24px;
        font-weight: 700;
        margin-top: 4px;
    }

    .cartao-limites-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-top: 16px;
    }

    .cartao-limite-box {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 11px 12px;
    }

    .cartao-limite-usado {
        margin-top: 4px;
        font-size: 15px;
        font-weight: 700;
        color: #ef4444;
    }

    .cartao-limite-disponivel {
        margin-top: 4px;
        font-size: 15px;
        font-weight: 700;
        color: #16a34a;
    }

    .cartao-limite-disponivel.negativo {
        color: #ef4444;
    }

    @media(max-width:500px) {
        .cartao-limites-grid {
            grid-template-columns: 1fr;
        }
    }

    .cartao-conta {
        margin-top: 12px;
        color: #6b7280;
        font-size: 11px;
    }

    .cartao-acoes {
        border-top: 1px solid #edf0f3;
        margin-top: 18px;
        padding-top: 14px;

        display: flex;
        gap: 8px;
        justify-content: flex-end;
        flex-wrap: wrap;
    }

    .btn-acao {
        border: 1px solid #dfe3e8;
        background: #fff;

        padding: 7px 10px;
        border-radius: 7px;

        color: #374151;

        font-size: 11px;
        text-decoration: none;

        cursor: pointer;
    }

    .btn-status {
        color: #b45309;
    }

    .empty-cartoes {
        grid-column: 1 / -1;

        text-align: center;

        padding: 55px 20px;

        color: #6b7280;
    }

    @media(max-width:1100px) {
        .cartoes-grid {
            grid-template-columns:
                repeat(2, minmax(0,1fr));
        }
    }

    @media(max-width:650px) {
        .cartoes-grid {
            grid-template-columns: 1fr;
        }

        .btn-novo {
            width: 100%;
        }
    }
</style>
@endpush


@section('content')

<div class="cartoes-header">

    <div>

        <h1 class="cp-page-title">
            Cartões
        </h1>

        <p class="cp-page-subtitle">
            Controle seus cartões de crédito.
        </p>

    </div>

    <a
        href="{{ route('cartoes.create') }}"
        class="btn-novo"
    >
        + Novo cartão
    </a>

</div>


<div class="cartoes-grid">

    @forelse($cartoes as $cartao)

        <div class="cp-card cartao-card">

            <div class="cartao-topo">

                <div>

                    <div class="cartao-nome">
                        {{ $cartao->nome }}
                    </div>

                    <div class="cartao-instituicao">

                        {{ $cartao->instituicao
                            ?: 'Sem instituição' }}

                        @if($cartao->ultimos_digitos)

                            • final
                            {{ $cartao->ultimos_digitos }}

                        @endif

                    </div>

                </div>


                <span class="
                    cartao-status
                    {{ $cartao->ativo
                        ? 'status-ativo'
                        : 'status-inativo' }}
                ">
                    {{ $cartao->ativo
                        ? 'Ativo'
                        : 'Inativo' }}
                </span>

            </div>


            <div class="cartao-limite">

                <div class="cartao-limite-label">
                    Limite total
                </div>

                <div class="cartao-limite-valor">

                    R$
                    {{ number_format(
                        $cartao->limite_total,
                        2,
                        ',',
                        '.'
                    ) }}

                </div>

            </div>


            <div class="cartao-limites-grid">

                <div class="cartao-limite-box">

                    <div class="cartao-info-label">
                        Limite utilizado
                    </div>

                    <div class="cartao-limite-usado">

                        R$
                        {{ number_format(
                            $cartao->limite_utilizado ?? 0,
                            2,
                            ',',
                            '.'
                        ) }}

                    </div>

                </div>


                <div class="cartao-limite-box">

                    <div class="cartao-info-label">
                        Limite disponível
                    </div>

                    <div
                        class="
                            cartao-limite-disponivel
                            {{ ($cartao->limite_disponivel ?? 0) < 0
                                ? 'negativo'
                                : '' }}
                        "
                    >

                        R$
                        {{ number_format(
                            $cartao->limite_disponivel ?? 0,
                            2,
                            ',',
                            '.'
                        ) }}

                    </div>

                </div>

            </div>


            <div class="cartao-info-grid">

                <div>

                    <div class="cartao-info-label">
                        Fechamento
                    </div>

                    <div class="cartao-info-value">
                        Dia {{ $cartao->dia_fechamento }}
                    </div>

                </div>


                <div>

                    <div class="cartao-info-label">
                        Vencimento
                    </div>

                    <div class="cartao-info-value">
                        Dia {{ $cartao->dia_vencimento }}
                    </div>

                </div>

            </div>


            <div class="cartao-conta">

                Conta padrão de pagamento:

                <strong>
                    {{ $cartao
                        ->contaPagamento
                        ?->nome
                        ?? 'Não definida' }}
                </strong>

            </div>


            <div class="cartao-acoes">

                <a
                    href="{{ route(
                        'cartoes.edit',
                        $cartao
                    ) }}"
                    class="btn-acao"
                >
                    Editar
                </a>


                <form
                    action="{{ route(
                        'cartoes.status',
                        $cartao
                    ) }}"
                    method="POST"
                    style="margin:0;"
                >
                    @csrf
                    @method('PATCH')

                    <button
                        type="submit"
                        class="btn-acao btn-status"
                    >
                        {{ $cartao->ativo
                            ? 'Desativar'
                            : 'Ativar' }}
                    </button>

                </form>

            </div>

        </div>

    @empty

        <div class="cp-card empty-cartoes">

            Nenhum cartão cadastrado ainda.

            <br><br>

            Cadastre seu primeiro cartão de crédito.

        </div>

    @endforelse

</div>

@endsection