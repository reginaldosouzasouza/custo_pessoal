@extends('layouts.custo-pessoal')

@section('title', 'Contas e Carteiras')

@push('styles')
<style>
    .contas-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        margin-bottom: 22px;
        flex-wrap: wrap;
    }

    .btn-novo {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 10px 16px;
        border-radius: 8px;
        background: #0d6efd;
        color: white;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
    }

    .contas-grid {
        display: grid;
        grid-template-columns:
            repeat(3, minmax(0, 1fr));
        gap: 18px;
    }

    .conta-card {
        padding: 20px;
    }

    .conta-topo {
        display: flex;
        justify-content: space-between;
        gap: 15px;
        margin-bottom: 18px;
    }

    .conta-nome {
        font-size: 17px;
        font-weight: 700;
        color: #111827;
    }

    .conta-instituicao {
        color: #6b7280;
        font-size: 12px;
        margin-top: 3px;
    }

    .conta-status {
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

    .conta-saldo-label {
        color: #6b7280;
        font-size: 12px;
        margin-bottom: 5px;
    }

    .conta-saldo {
        font-size: 25px;
        font-weight: 700;
        color: #1775d1;
    }

    .conta-tipo {
        margin-top: 6px;
        font-size: 11px;
        color: #9ca3af;
    }

    .conta-acoes {
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

    .btn-desativar {
        color: #b45309;
    }

    .empty-contas {
        grid-column: 1 / -1;
        text-align: center;
        padding: 55px 20px;
        color: #6b7280;
    }

    @media(max-width: 1100px) {
        .contas-grid {
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
        }
    }

    @media(max-width: 650px) {
        .contas-grid {
            grid-template-columns: 1fr;
        }

        .btn-novo {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')

<div class="contas-header">

    <div>
        <h1 class="cp-page-title">
            Contas e Carteiras
        </h1>

        <p class="cp-page-subtitle">
            Organize onde o seu dinheiro está.
        </p>
    </div>

    <a href="{{ route('contas.create') }}"
       class="btn-novo">
        + Nova conta
    </a>

</div>


<div class="contas-grid">

    @forelse($contas as $conta)

        @php
            $tipos = [
                'conta_corrente' =>
                    'Conta corrente',

                'conta_digital' =>
                    'Conta digital',

                'poupanca' =>
                    'Poupança',

                'dinheiro' =>
                    'Dinheiro',

                'carteira' =>
                    'Carteira',

                'outro' =>
                    'Outro',
            ];
        @endphp

        <div class="cp-card conta-card">

            <div class="conta-topo">

                <div>

                    <div class="conta-nome">
                        {{ $conta->nome }}
                    </div>

                    <div class="conta-instituicao">
                        {{ $conta->instituicao
                            ?: 'Sem instituição' }}
                    </div>

                </div>


                <span class="
                    conta-status
                    {{ $conta->ativa
                        ? 'status-ativo'
                        : 'status-inativo' }}
                ">
                    {{ $conta->ativa
                        ? 'Ativa'
                        : 'Inativa' }}
                </span>

            </div>


            <div class="conta-saldo-label">
                Saldo atual
            </div>

            <div class="conta-saldo">
                R$
                {{ number_format(
                    $conta->saldo_atual,
                    2,
                    ',',
                    '.'
                ) }}
            </div>

            <div class="conta-tipo">
                {{ $tipos[$conta->tipo]
                    ?? $conta->tipo }}
            </div>


            <div class="conta-acoes">

                <a href="{{ route(
                    'contas.edit',
                    $conta
                ) }}"
                   class="btn-acao">
                    Editar
                </a>


                <form
                    action="{{ route(
                        'contas.status',
                        $conta
                    ) }}"
                    method="POST"
                    style="margin:0;"
                >
                    @csrf
                    @method('PATCH')

                    <button
                        type="submit"
                        class="btn-acao btn-desativar"
                    >
                        {{ $conta->ativa
                            ? 'Desativar'
                            : 'Ativar' }}
                    </button>

                </form>

            </div>

        </div>

    @empty

        <div class="cp-card empty-contas">

            Nenhuma conta cadastrada ainda.

            <br><br>

            Cadastre sua primeira conta,
            carteira ou dinheiro em espécie.

        </div>

    @endforelse

</div>

@endsection