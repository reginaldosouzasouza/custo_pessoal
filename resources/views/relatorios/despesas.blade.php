@extends('layouts.custo-pessoal')

@section('title', 'Relatório de Despesas')

@push('styles')
<style>
    .page-header {
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:20px;
        flex-wrap:wrap;
        margin-bottom:22px;
    }

    .filtros-card {
        padding:18px;
        margin-bottom:18px;
    }

    .filtros-grid {
        display:grid;
        grid-template-columns:
            repeat(6, minmax(150px, 1fr));
        gap:12px;
        align-items:end;
    }

    .form-group {
        display:flex;
        flex-direction:column;
        gap:6px;
    }

    .form-label {
        font-size:11px;
        font-weight:600;
        color:#4b5563;
    }

    .form-control {
        min-height:40px;
        border:1px solid #d9dee5;
        border-radius:8px;
        padding:8px 10px;
        background:#fff;
        color:#374151;
        font-size:12px;
        width:100%;
    }

    .acoes-filtro {
        display:flex;
        gap:8px;
        align-items:end;
    }

    .btn-filtrar,
    .btn-limpar {
        min-height:40px;
        border-radius:8px;
        padding:0 14px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        font-size:12px;
        font-weight:600;
        text-decoration:none;
        cursor:pointer;
    }

    .btn-filtrar {
        border:none;
        background:#0d6efd;
        color:#fff;
    }

    .btn-limpar {
        border:1px solid #d9dee5;
        background:#fff;
        color:#374151;
    }

    .cards-resumo {
        display:grid;
        grid-template-columns:repeat(4, 1fr);
        gap:15px;
        margin-bottom:18px;
    }

    .resumo-card {
        padding:18px;
    }

    .resumo-label {
        font-size:11px;
        font-weight:600;
        color:#6b7280;
        margin-bottom:8px;
    }

    .resumo-valor {
        font-size:23px;
        font-weight:700;
        color:#111827;
    }

    .resumo-pago .resumo-valor {
        color:#15803d;
    }

    .resumo-pendente .resumo-valor {
        color:#d97706;
    }

    .resumo-cancelado .resumo-valor {
        color:#6b7280;
    }

    .table-card {
        padding:18px;
        overflow-x:auto;
    }

    .relatorio-table {
        width:100%;
        min-width:1050px;
        border-collapse:collapse;
        font-size:12px;
    }

    .relatorio-table th {
        padding:10px 9px;
        text-align:left;
        color:#4b5563;
        font-weight:600;
        border-bottom:1px solid #e5e7eb;
    }

    .relatorio-table td {
        padding:12px 9px;
        border-bottom:1px solid #edf0f3;
        color:#374151;
        vertical-align:middle;
    }

    .relatorio-table tr:last-child td {
        border-bottom:none;
    }

    .valor {
        font-weight:700;
        color:#111827;
        white-space:nowrap;
        text-align:right;
    }

    .origem,
    .status {
        display:inline-block;
        padding:4px 8px;
        border-radius:999px;
        font-size:10px;
        font-weight:700;
        white-space:nowrap;
    }

    .origem-despesa {
        background:#fee2e2;
        color:#b91c1c;
    }

    .origem-recorrente {
        background:#e0f2fe;
        color:#0369a1;
    }

    .origem-parcela {
        background:#ede9fe;
        color:#6d28d9;
    }

    .origem-fatura {
        background:#f3e8ff;
        color:#7e22ce;
    }

    .status-paga {
        background:#dcfce7;
        color:#166534;
    }

    .status-pendente {
        background:#fff7ed;
        color:#c2410c;
    }

    .status-cancelada {
        background:#e5e7eb;
        color:#4b5563;
    }

    .status-prevista {
        background:#f3f4f6;
        color:#4b5563;
    }

    .status-aberta {
        background:#eee9ff;
        color:#7653cb;
    }

    .status-fechada {
        background:#fff0df;
        color:#c46c00;
    }

    .status-vencida {
        background:#fee2e2;
        color:#b91c1c;
    }

    .empty-state {
        text-align:center;
        padding:45px 15px;
        color:#6b7280;
    }

    .btn-excel {
        min-height:40px;
        border-radius:8px;
        padding:0 14px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        font-size:12px;
        font-weight:600;
        text-decoration:none;
        background:#198754;
        color:#fff;
        border:1px solid #198754;
    }

    .btn-excel:hover {
        color:#fff;
        opacity:.9;
    }

    @media(max-width:1200px) {
        .filtros-grid {
            grid-template-columns:repeat(3, 1fr);
        }

        .cards-resumo {
            grid-template-columns:repeat(2, 1fr);
        }
    }

    @media(max-width:700px) {
        .filtros-grid {
            grid-template-columns:1fr;
        }

        .acoes-filtro {
            width:100%;
        }

        .btn-filtrar,
        .btn-limpar {
            flex:1;
        }

        .cards-resumo {
            grid-template-columns:1fr;
        }
    }
</style>
@endpush


@section('content')

<div class="page-header">

    <div>

        <h1 class="cp-page-title">
            Relatório de Despesas
        </h1>

        <p class="cp-page-subtitle">
            Consulte despesas por período, origem, categoria, situação e conta.
        </p>

    </div>

</div>


<div class="cp-card filtros-card">

    <form
        method="GET"
        action="{{ route('relatorios.despesas') }}"
    >

        <div class="filtros-grid">

            <div class="form-group">

                <label class="form-label">
                    Data inicial
                </label>

                <input
                    type="date"
                    name="data_inicio"
                    value="{{ $dataInicio }}"
                    class="form-control"
                >

            </div>


            <div class="form-group">

                <label class="form-label">
                    Data final
                </label>

                <input
                    type="date"
                    name="data_fim"
                    value="{{ $dataFim }}"
                    class="form-control"
                >

            </div>


            <div class="form-group">

                <label class="form-label">
                    Origem
                </label>

                <select
                    name="origem"
                    class="form-control"
                >

                    <option value="">
                        Todas
                    </option>

                    <option
                        value="despesa"
                        @selected($origem === 'despesa')
                    >
                        Despesa
                    </option>

                    <option
                        value="recorrente"
                        @selected($origem === 'recorrente')
                    >
                        Recorrente
                    </option>

                    <option
                        value="parcela"
                        @selected($origem === 'parcela')
                    >
                        Parcela
                    </option>

                    <option
                        value="fatura"
                        @selected($origem === 'fatura')
                    >
                        Fatura
                    </option>

                </select>

            </div>


            <div class="form-group">

                <label class="form-label">
                    Categoria
                </label>

                <select
                    name="categoria_id"
                    class="form-control"
                >

                    <option value="">
                        Todas
                    </option>

                    @foreach($categorias as $categoria)

                        <option
                            value="{{ $categoria->id }}"
                            @selected(
                                (string) $categoriaId
                                === (string) $categoria->id
                            )
                        >
                            {{ $categoria->nome }}
                        </option>

                    @endforeach

                </select>

            </div>


            <div class="form-group">

                <label class="form-label">
                    Situação
                </label>

                <select
                    name="situacao"
                    class="form-control"
                >

                    <option value="">
                        Todas
                    </option>

                    <option
                        value="pendente"
                        @selected($situacao === 'pendente')
                    >
                        Pendente
                    </option>

                    <option
                        value="paga"
                        @selected($situacao === 'paga')
                    >
                        Paga
                    </option>

                    <option
                        value="cancelada"
                        @selected($situacao === 'cancelada')
                    >
                        Cancelada
                    </option>

                    <option
                        value="prevista"
                        @selected($situacao === 'prevista')
                    >
                        Prevista
                    </option>

                    <option
                        value="aberta"
                        @selected($situacao === 'aberta')
                    >
                        Aberta
                    </option>

                    <option
                        value="fechada"
                        @selected($situacao === 'fechada')
                    >
                        Fechada
                    </option>

                    <option
                        value="vencida"
                        @selected($situacao === 'vencida')
                    >
                        Vencida
                    </option>

                </select>

            </div>


            <div class="form-group">

                <label class="form-label">
                    Conta
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
                                (string) $contaId
                                === (string) $conta->id
                            )
                        >
                            {{ $conta->nome }}
                        </option>

                    @endforeach

                </select>

            </div>


            <div class="acoes-filtro">

                <button
                    type="submit"
                    class="btn-filtrar"
                >
                    Filtrar
                </button>

                <a
                    href="{{ route('relatorios.despesas') }}"
                    class="btn-limpar"
                >
                    Limpar
                </a>

                <a
                    href="{{ route(
                        'relatorios.despesas.excel',
                        request()->query()
                    ) }}"
                    class="btn-excel"
                >
                    Excel
                </a>

            </div>

        </div>

    </form>

</div>


<div class="cards-resumo">

    <div class="cp-card resumo-card">

        <div class="resumo-label">
            Total geral
        </div>

        <div class="resumo-valor">

            R$
            {{ number_format(
                $totalGeral,
                2,
                ',',
                '.'
            ) }}

        </div>

    </div>


    <div class="cp-card resumo-card resumo-pago">

        <div class="resumo-label">
            Total pago
        </div>

        <div class="resumo-valor">

            R$
            {{ number_format(
                $totalPago,
                2,
                ',',
                '.'
            ) }}

        </div>

    </div>


    <div class="cp-card resumo-card resumo-pendente">

        <div class="resumo-label">
            Total pendente
        </div>

        <div class="resumo-valor">

            R$
            {{ number_format(
                $totalPendente,
                2,
                ',',
                '.'
            ) }}

        </div>

    </div>


    <div class="cp-card resumo-card resumo-cancelado">

        <div class="resumo-label">
            Total cancelado
        </div>

        <div class="resumo-valor">

            R$
            {{ number_format(
                $totalCancelado,
                2,
                ',',
                '.'
            ) }}

        </div>

    </div>

</div>


<div class="cp-card table-card">

    @if($itens->count() > 0)

        <table class="relatorio-table">

            <thead>

                <tr>
                    <th>Descrição</th>
                    <th>Categoria</th>
                    <th>Origem</th>
                    <th>Vencimento</th>
                    <th>Pagamento</th>
                    <th>Conta</th>
                    <th>Situação</th>

                    <th style="text-align:right;">
                        Valor
                    </th>
                </tr>

            </thead>


            <tbody>

                @foreach($itens as $item)

                    @php

                        $classeOrigem = match(
                            $item['tipo']
                        ) {
                            'recorrente' =>
                                'origem-recorrente',

                            'parcela' =>
                                'origem-parcela',

                            'fatura' =>
                                'origem-fatura',

                            default =>
                                'origem-despesa',
                        };


                        $situacaoItem = strtolower(
                            $item['situacao']
                            ?? ''
                        );


                        $classeSituacao = match(
                            $situacaoItem
                        ) {
                            'paga',
                            'pago' =>
                                'status-paga',

                            'cancelada' =>
                                'status-cancelada',

                            'prevista' =>
                                'status-prevista',

                            'aberta' =>
                                'status-aberta',

                            'fechada' =>
                                'status-fechada',

                            'vencida' =>
                                'status-vencida',

                            default =>
                                'status-pendente',
                        };


                        $textoSituacao = match(
                            $situacaoItem
                        ) {
                            'paga',
                            'pago' =>
                                'Paga',

                            'cancelada' =>
                                'Cancelada',

                            'prevista' =>
                                'Prevista',

                            'aberta' =>
                                'Aberta',

                            'fechada' =>
                                'Fechada',

                            'vencida' =>
                                'Vencida',

                            default =>
                                'Pendente',
                        };


                        $vencimento =
                            !empty($item['vencimento'])
                                ? \Carbon\Carbon::parse(
                                    $item['vencimento']
                                )->format('d/m/Y')
                                : '-';


                        $pagamento =
                            !empty($item['pagamento'])
                                ? \Carbon\Carbon::parse(
                                    $item['pagamento']
                                )->format('d/m/Y')
                                : '-';

                    @endphp


                    <tr>

                        <td>

                            <strong>
                                {{ $item['descricao'] }}
                            </strong>

                        </td>


                        <td>
                            {{ $item['categoria'] ?? '-' }}
                        </td>


                        <td>

                            <span
                                class="origem {{ $classeOrigem }}"
                            >
                                {{ $item['origem'] }}
                            </span>

                        </td>


                        <td>
                            {{ $vencimento }}
                        </td>


                        <td>
                            {{ $pagamento }}
                        </td>


                        <td>
                            {{ $item['conta'] ?? '-' }}
                        </td>


                        <td>

                            <span
                                class="status {{ $classeSituacao }}"
                            >
                                {{ $textoSituacao }}
                            </span>

                        </td>


                        <td class="valor">

                            R$
                            {{ number_format(
                                $item['valor'],
                                2,
                                ',',
                                '.'
                            ) }}

                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>

    @else

        <div class="empty-state">
            Nenhuma despesa encontrada para os filtros informados.
        </div>

    @endif

</div>

@endsection