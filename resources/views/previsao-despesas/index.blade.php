@extends('layouts.custo-pessoal')

@section('title', 'Previsão de Despesas')

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
        grid-template-columns:220px auto;
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
    }

    .acoes-filtro {
        display:flex;
        gap:8px;
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
    }

    .btn-filtrar {
        border:none;
        background:#0d6efd;
        color:#fff;
        cursor:pointer;
    }

    .btn-limpar {
        border:1px solid #d9dee5;
        background:#fff;
        color:#374151;
        text-decoration:none;
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

    .resumo-total .resumo-valor {
        color:#7653cb;
    }

    .table-card {
        padding:18px;
        overflow-x:auto;
    }

    .previsao-table {
        width:100%;
        min-width:900px;
        border-collapse:collapse;
        font-size:12px;
    }

    .previsao-table th {
        padding:10px 9px;
        text-align:left;
        color:#4b5563;
        font-weight:600;
        border-bottom:1px solid #e5e7eb;
    }

    .previsao-table td {
        padding:12px 9px;
        border-bottom:1px solid #edf0f3;
        color:#374151;
        vertical-align:middle;
    }

    .previsao-table tr:last-child td {
        border-bottom:none;
    }

    .valor {
        font-weight:700;
        color:#7653cb;
        white-space:nowrap;
        text-align:right;
    }

    .origem {
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

    .origem-cartao {
        background:#eee9ff;
        color:#7653cb;
    }

    .status {
        display:inline-block;
        padding:4px 8px;
        border-radius:999px;
        font-size:10px;
        font-weight:700;
        white-space:nowrap;
    }

    .status-pendente {
        background:#fff7ed;
        color:#c2410c;
    }

    .status-paga {
        background:#dcfce7;
        color:#166534;
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

    .valor-variavel {
        font-size:10px;
        color:#6b7280;
        margin-top:3px;
    }

    @media(max-width:950px) {
        .cards-resumo {
            grid-template-columns:1fr 1fr;
        }
    }

    @media(max-width:600px) {
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
            Previsão de Despesas
        </h1>

        <p class="cp-page-subtitle">
            Veja os compromissos financeiros previstos para cada mês.
        </p>
    </div>

</div>


<div class="cp-card filtros-card">

    <form
        method="GET"
        action="{{ route('previsao-despesas.index') }}"
    >

        <div class="filtros-grid">

            <div class="form-group">

                <label class="form-label">
                    Mês da previsão
                </label>

                <input
                    type="month"
                    name="mes"
                    value="{{ $mes }}"
                    class="form-control"
                >

            </div>

            <div class="acoes-filtro">

                <button
                    type="submit"
                    class="btn-filtrar"
                >
                    Filtrar
                </button>

                <a
                    href="{{ route('previsao-despesas.index') }}"
                    class="btn-limpar"
                >
                    Limpar
                </a>

            </div>

        </div>

    </form>

</div>


<div class="cards-resumo">

    <div class="cp-card resumo-card">

        <div class="resumo-label">
            Despesas lançadas
        </div>

        <div class="resumo-valor">
            R$
            {{ number_format(
                $totalDespesas,
                2,
                ',',
                '.'
            ) }}
        </div>

    </div>


    <div class="cp-card resumo-card">

        <div class="resumo-label">
            Despesas recorrentes
        </div>

        <div class="resumo-valor">
            R$
            {{ number_format(
                $totalRecorrentes,
                2,
                ',',
                '.'
            ) }}
        </div>

    </div>


    <div class="cp-card resumo-card">

        <div class="resumo-label">
            Faturas dos cartões
        </div>

        <div class="resumo-valor">
            R$
            {{ number_format(
                $totalCartoes,
                2,
                ',',
                '.'
            ) }}
        </div>

    </div>


    <div class="cp-card resumo-card resumo-total">

        <div class="resumo-label">
            Total previsto
        </div>

        <div class="resumo-valor">
            R$
            {{ number_format(
                $totalPrevisto,
                2,
                ',',
                '.'
            ) }}
        </div>

    </div>

</div>


<div class="cp-card table-card">

    @if($itens->count() > 0)

        <table class="previsao-table">

            <thead>

                <tr>
                    <th>Descrição</th>
                    <th>Categoria</th>
                    <th>Origem</th>
                    <th>Vencimento</th>
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
                            'cartao' =>
                                'origem-cartao',

                            'recorrente' =>
                                'origem-recorrente',

                            default =>
                                'origem-despesa',
                        };


                        $situacao = strtolower(
                            $item['situacao']
                            ?? 'prevista'
                        );


                        $classeSituacao = match(
                            $situacao
                        ) {
                            'paga' =>
                                'status-paga',

                            'aberta' =>
                                'status-aberta',

                            'fechada' =>
                                'status-fechada',

                            'vencida' =>
                                'status-vencida',

                            'pendente' =>
                                'status-pendente',

                            default =>
                                'status-prevista',
                        };


                        $textoSituacao = match(
                            $situacao
                        ) {
                            'paga' =>
                                'Paga',

                            'aberta' =>
                                'Aberta',

                            'fechada' =>
                                'Fechada',

                            'vencida' =>
                                'Vencida',

                            'pendente' =>
                                'Pendente',

                            default =>
                                'Prevista',
                        };


                        $vencimento =
                            !empty($item['vencimento'])
                                ? \Carbon\Carbon::parse(
                                    $item['vencimento']
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

                            <span
                                class="status {{ $classeSituacao }}"
                            >
                                {{ $textoSituacao }}
                            </span>

                        </td>


                        <td class="valor">

                            @if(
                                ($item['tipo'] ?? null)
                                    === 'recorrente'
                                &&
                                ($item['tipo_valor'] ?? null)
                                    === 'variavel'
                                &&
                                (float) $item['valor'] <= 0
                            )

                                Valor a informar

                            @else

                                R$
                                {{ number_format(
                                    $item['valor'],
                                    2,
                                    ',',
                                    '.'
                                ) }}

                            @endif

                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>

    @else

        <div class="empty-state">

            Nenhuma despesa prevista para
            {{ $inicioMes->translatedFormat('F/Y') }}.

        </div>

    @endif

</div>

@endsection