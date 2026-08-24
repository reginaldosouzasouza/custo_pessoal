@extends('layouts.custo-pessoal')

@section('title', 'Movimentações')

@push('styles')
<style>
    .page-header {
        margin-bottom: 22px;
    }

    .filtros-card {
        padding: 18px;
        margin-bottom: 18px;
    }

    .filtros-grid {
        display: grid;
        grid-template-columns:
            180px 180px 1fr auto;
        gap: 12px;
        align-items: end;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .form-label {
        font-size: 11px;
        font-weight: 600;
        color: #4b5563;
    }

    .form-control {
        min-height: 40px;
        border: 1px solid #d9dee5;
        border-radius: 8px;
        padding: 8px 10px;
        background: #fff;
        font-size: 12px;
        color: #374151;
    }

    .btn-filtrar {
        min-height: 40px;
        border: none;
        border-radius: 8px;
        padding: 0 16px;
        background: #0d6efd;
        color: #fff;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
    }

    .btn-limpar {
        min-height: 40px;
        border: 1px solid #d9dee5;
        border-radius: 8px;
        padding: 0 14px;
        background: #fff;
        color: #374151;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .resumo-grid {
        display: grid;
        grid-template-columns:
            repeat(3, minmax(0,1fr));
        gap: 18px;
        margin-bottom: 18px;
    }

    .resumo-card {
        padding: 18px;
    }

    .resumo-label {
        color: #6b7280;
        font-size: 11px;
        margin-bottom: 5px;
    }

    .resumo-valor {
        font-size: 22px;
        font-weight: 700;
    }

    .valor-entrada {
        color: #16a34a;
    }

    .valor-saida {
        color: #ef4444;
    }

    .valor-saldo-positivo {
        color: #16a34a;
    }

    .valor-saldo-negativo {
        color: #ef4444;
    }

    .table-card {
        padding: 18px;
        overflow-x: auto;
    }

    .mov-table {
        width: 100%;
        min-width: 850px;
        border-collapse: collapse;
        font-size: 12px;
    }

    .mov-table th {
        text-align: left;
        color: #4b5563;
        font-weight: 600;
        padding: 10px 8px;
        border-bottom: 1px solid #e5e7eb;
    }

    .mov-table td {
    padding: 11px 8px;
    border-bottom: 1px solid #edf0f3;
    color: #374151;
    }

    .mov-table td.valor-entrada {
        color: #16a34a !important;
        font-weight: 700;
    }

    .mov-table td.valor-saida {
        color: #ef4444 !important;
        font-weight: 700;
    }

    .mov-table tr:last-child td {
        border-bottom: none;
    }

    .mov-descricao strong {
        display: block;
        color: #111827;
        font-size: 12px;
    }

    .mov-descricao small {
        display: block;
        color: #6b7280;
        margin-top: 3px;
        font-size: 10px;
    }

    .tipo-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 4px 8px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 700;
    }

    .tipo-entrada {
        background: #dcfce7;
        color: #166534;
    }

    .tipo-saida {
        background: #fee2e2;
        color: #b91c1c;
    }

    .valor-mov {
        font-weight: 700;
        text-align: right;
        white-space: nowrap;
    }

    .empty-state {
        text-align: center;
        padding: 45px 15px;
        color: #6b7280;
    }

    .pagination-wrap {
        margin-top: 18px;
    }

    @media(max-width:900px) {
        .filtros-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media(max-width:650px) {
        .filtros-grid,
        .resumo-grid {
            grid-template-columns: 1fr;
        }

        .btn-filtrar,
        .btn-limpar {
            width: 100%;
        }
    }
</style>
@endpush


@section('content')

<div class="page-header">

    <h1 class="cp-page-title">
        Movimentações
    </h1>

    <p class="cp-page-subtitle">
        Consulte todas as entradas e saídas das suas contas.
    </p>

</div>


<div class="cp-card filtros-card">

    <form
        method="GET"
        action="{{ route('movimentacoes.index') }}"
    >

        <div class="filtros-grid">

            <div class="form-group">

                <label class="form-label">
                    Mês
                </label>

                <input
                    type="month"
                    name="mes"
                    class="form-control"
                    value="{{ $mes }}"
                >

            </div>


            <div class="form-group">

                <label class="form-label">
                    Tipo
                </label>

                <select
                    name="tipo"
                    class="form-control"
                >

                    <option value="">
                        Todos
                    </option>

                    <option
                        value="entrada"
                        @selected(
                            $tipo === 'entrada'
                        )
                    >
                        Entradas
                    </option>

                    <option
                        value="saida"
                        @selected(
                            $tipo === 'saida'
                        )
                    >
                        Saídas
                    </option>

                </select>

            </div>


            <div class="form-group">

                <label class="form-label">
                    Conta / Carteira
                </label>

                <select
                    name="conta_id"
                    class="form-control"
                >

                    <option value="">
                        Todas
                    </option>

                    @foreach($contas as $conta)

                        <option
                            value="{{ $conta->id }}"
                            @selected(
                                $contaId
                                == $conta->id
                            )
                        >
                            {{ $conta->nome }}

                            @if(!$conta->ativa)
                                (inativa)
                            @endif
                        </option>

                    @endforeach

                </select>

            </div>


            <div
                style="
                    display:flex;
                    gap:8px;
                "
            >

                <button
                    type="submit"
                    class="btn-filtrar"
                >
                    Filtrar
                </button>

                <a
                    href="{{ route(
                        'movimentacoes.index'
                    ) }}"
                    class="btn-limpar"
                >
                    Limpar
                </a>

            </div>

        </div>

    </form>

</div>


<div class="resumo-grid">

    <div class="cp-card resumo-card">

        <div class="resumo-label">
            Entradas no período
        </div>

        <div class="
            resumo-valor
            valor-entrada
        ">
            R$
            {{ number_format(
                $totalEntradas,
                2,
                ',',
                '.'
            ) }}
        </div>

    </div>


    <div class="cp-card resumo-card">

        <div class="resumo-label">
            Saídas no período
        </div>

        <div class="
            resumo-valor
            valor-saida
        ">
            R$
            {{ number_format(
                $totalSaidas,
                2,
                ',',
                '.'
            ) }}
        </div>

    </div>


    <div class="cp-card resumo-card">

        <div class="resumo-label">
            Resultado do período
        </div>

        <div class="
            resumo-valor
            {{ $saldoPeriodo >= 0
                ? 'valor-saldo-positivo'
                : 'valor-saldo-negativo' }}
        ">
            R$
            {{ number_format(
                $saldoPeriodo,
                2,
                ',',
                '.'
            ) }}
        </div>

    </div>

</div>


<div class="cp-card table-card">

    @if($movimentacoes->count() > 0)

        <table class="mov-table">

            <thead>

                <tr>
                    <th>Data</th>
                    <th>Descrição</th>
                    <th>Conta</th>
                    <th>Origem</th>
                    <th>Tipo</th>
                    <th style="text-align:right;">
                        Valor
                    </th>
                </tr>

            </thead>


            <tbody>

                @foreach(
                    $movimentacoes
                    as $movimentacao
                )

                    @php
                        $entrada =
                            $movimentacao->tipo
                            === 'entrada';
                    @endphp

                    <tr>

                        <td>
                            {{ $movimentacao
                                ->data_movimentacao
                                ->format('d/m/Y') }}
                        </td>


                        <td class="mov-descricao">

                            <strong>
                                {{ $movimentacao->descricao }}
                            </strong>

                        </td>


                        <td>

                            {{ $movimentacao
                                ->conta
                                ?->nome
                                ?? '-' }}

                        </td>


                        <td>

                            {{ ucfirst(
                                str_replace(
                                    '_',
                                    ' ',
                                    $movimentacao
                                        ->origem_tipo
                                )
                            ) }}

                        </td>


                        <td>

                            <span
                                class="
                                    tipo-badge
                                    {{ $entrada
                                        ? 'tipo-entrada'
                                        : 'tipo-saida' }}
                                "
                            >
                                {{ $entrada
                                    ? 'Entrada'
                                    : 'Saída' }}
                            </span>

                        </td>


                        <td
                            class="
                                valor-mov
                                {{ $entrada
                                    ? 'valor-entrada'
                                    : 'valor-saida' }}
                            "
                        >

                            {{ $entrada ? '+' : '-' }}

                            R$
                            {{ number_format(
                                $movimentacao->valor,
                                2,
                                ',',
                                '.'
                            ) }}

                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>


        <div class="pagination-wrap">
            {{ $movimentacoes->links() }}
        </div>

    @else

        <div class="empty-state">
            Nenhuma movimentação encontrada
            para os filtros selecionados.
        </div>

    @endif

</div>

@endsection