@extends('layouts.custo-pessoal')

@section('title', 'Dashboard - Custo Pessoal')

@push('styles')
<style>
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        margin-bottom: 22px;
        flex-wrap: wrap;
    }

    .btn-nova-movimentacao {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;

        background: #0d6efd;
        color: #fff;

        text-decoration: none;

        padding: 11px 18px;
        border-radius: 8px;

        font-size: 14px;
        font-weight: 600;

        box-shadow: 0 4px 10px rgba(13,110,253,.18);
    }

    .btn-nova-movimentacao:hover {
        background: #0b5ed7;
        color: #fff;
    }

    /* =========================================================
       CARDS
    ========================================================= */

    .dashboard-cards {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 18px;
    }

    .summary-card {
        min-height: 105px;
        padding: 18px;

        display: flex;
        align-items: center;
        gap: 15px;
    }

    .summary-icon {
        width: 55px;
        height: 55px;

        border-radius: 50%;

        display: flex;
        justify-content: center;
        align-items: center;

        flex-shrink: 0;

        color: #fff;
        font-size: 24px;
        font-weight: 700;
    }

    .icon-green {
        background: #20b96b;
    }

    .icon-red {
        background: #ff4d4f;
    }

    .icon-blue {
        background: #2f80ed;
    }

    .icon-orange {
        background: #ff9418;
    }

    .icon-purple {
        background: #7b61d1;
    }

    .icon-cyan {
        background: #1ba8b5;
    }

    .summary-title {
        font-size: 13px;
        font-weight: 600;
        color: #4b5563;
        margin-bottom: 4px;
    }

    .summary-value {
        font-size: 21px;
        line-height: 1.15;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .value-green {
        color: #1eb563;
    }

    .value-red {
        color: #f04444;
    }

    .value-blue {
        color: #2879d7;
    }

    .value-orange {
        color: #ff8a18;
    }

    .value-purple {
        color: #7653cb;
    }

    .value-cyan {
        color: #1d9cae;
    }

    .summary-note {
        color: #6b7280;
        font-size: 11px;
    }

    .summary-link {
        display:inline-block;
        margin-top:5px;
        color:#2777d7;
        font-size:11px;
        font-weight:600;
        text-decoration:none;
    }

    .summary-link:hover {
        text-decoration:underline;
    }

    /* =========================================================
       GRÁFICOS
    ========================================================= */

    .dashboard-charts {
        display: grid;
        grid-template-columns: .95fr 1.15fr;
        gap: 18px;
        margin-bottom: 18px;
    }

    .dashboard-panel {
        padding: 16px 18px 18px;
    }

    .panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 12px;
    }

    .panel-title {
        margin: 0;
        color: #222;
        font-size: 15px;
        font-weight: 700;
    }

    .panel-select {
        height: 31px;
        border: 1px solid #e1e5ea;
        background: #fff;
        border-radius: 6px;
        padding: 0 8px;
        color: #555;
        font-size: 11px;
    }

    .chart-area {
        position: relative;
        min-height: 255px;
    }

    .category-content {
        display: grid;
        grid-template-columns: 230px 1fr;
        gap: 15px;
        align-items: center;
    }

    .category-chart-wrap {
        width: 220px;
        height: 220px;
        margin: auto;
    }

    .category-list {
        font-size: 12px;
    }

    .category-row {
        display: grid;
        grid-template-columns: 10px 1fr auto;
        gap: 9px;
        align-items: center;

        padding: 7px 0;
    }

    .category-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
    }

    .category-total {
        margin-top: 8px;
        padding-top: 10px;
        border-top: 1px solid #e6e9ed;

        text-align: right;

        font-weight: 700;
        color: #333;
        font-size: 12px;
    }

    .empty-dashboard {
        color: #6b7280;
        text-align: center;
        padding: 35px 10px;
        font-size: 12px;
    }

    /* =========================================================
       BLOCOS INFERIORES
    ========================================================= */

    .dashboard-bottom {
        display: grid;
        grid-template-columns: 1fr 1.15fr;
        gap: 18px;
    }

    .list-header-link {
        font-size: 11px;
        color: #2777d7;
        font-weight: 600;
        text-decoration: none;
    }

    .dashboard-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
    }

    .dashboard-table th {
        color: #555;
        font-weight: 600;
        text-align: left;

        padding: 9px 8px;

        border-bottom: 1px solid #e5e7eb;
    }

    .dashboard-table td {
        padding: 9px 8px;

        border-bottom: 1px solid #eceff3;

        color: #3f4650;
    }

    .dashboard-table tbody tr:last-child td {
        border-bottom: none;
    }

    .status {
        display: inline-block;

        padding: 4px 8px;

        border-radius: 999px;

        font-size: 10px;
        font-weight: 600;

        white-space: nowrap;
    }

    .status-vencer {
        background: #fff0df;
        color: #eb8719;
    }

    .status-aberto {
        background: #eee9ff;
        color: #7658cb;
    }

    .status-atrasado {
        background: #fee2e2;
        color: #c83b3b;
    }

    .mov-list {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .mov-item {
        display: grid;
        grid-template-columns: 38px 1fr 100px 110px;
        gap: 10px;
        align-items: center;

        min-height: 55px;

        border-bottom: 1px solid #eceff3;
    }

    .mov-item:last-child {
        border-bottom: none;
    }

    .mov-icon {
        width: 31px;
        height: 31px;

        border-radius: 50%;

        display: flex;
        align-items: center;
        justify-content: center;

        font-size: 14px;
    }

    .mov-icon.green {
        color: #19a75b;
        background: #dbf5e5;
    }

    .mov-icon.red {
        color: #ef4444;
        background: #fee2e2;
    }

    .mov-description strong {
        display: block;
        font-size: 11px;
        color: #333;
    }

    .mov-description small {
        color: #6b7280;
        font-size: 10px;
    }

    .mov-date {
        color: #555;
        font-size: 10px;
        text-align: right;
    }

    .mov-value {
        text-align: right;
        font-size: 12px;
        font-weight: 700;
    }

    .mov-value.green {
        color: #20b563;
    }

    .mov-value.red {
        color: #ef4444;
    }

    .saldo-title {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .saldo-help {
        position: relative;

        width: 17px;
        height: 17px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        border-radius: 50%;

        background: #e5e7eb;
        color: #475569;

        font-size: 10px;
        font-weight: 700;

        cursor: help;
    }

    .saldo-tooltip {
        position: absolute;

        left: 50%;
        bottom: calc(100% + 9px);

        transform: translateX(-50%);

        width: 270px;

        padding: 10px 12px;

        border-radius: 8px;

        background: #1f2937;
        color: #fff;

        font-size: 11px;
        font-weight: 400;
        line-height: 1.45;

        box-shadow: 0 8px 25px rgba(15,23,42,.20);

        opacity: 0;
        visibility: hidden;

        transition: .15s ease;

        z-index: 50;

        pointer-events: none;
    }

    .saldo-help:hover .saldo-tooltip {
        opacity: 1;
        visibility: visible;
    }

    @media(max-width: 1100px) {
        .dashboard-cards {
            grid-template-columns: repeat(2, minmax(0,1fr));
        }
    }

    @media(max-width: 1000px) {
        .dashboard-charts,
        .dashboard-bottom {
            grid-template-columns: 1fr;
        }
    }

    @media(max-width: 700px) {
        .dashboard-cards {
            grid-template-columns: 1fr;
        }

        .category-content {
            grid-template-columns: 1fr;
        }

        .mov-item {
            grid-template-columns: 38px 1fr auto;
        }

        .mov-date {
            display: none;
        }

        .dashboard-table {
            min-width: 520px;
        }

        .dashboard-panel {
            overflow-x: auto;
        }
    }
</style>
@endpush


@section('content')

<div class="dashboard-header">

    <div>
        <h1 class="cp-page-title">
            Dashboard
        </h1>
    </div>

    <a
        href="{{ route('movimentacoes.index') }}"
        class="btn-nova-movimentacao"
    >
        Ver movimentações
    </a>

</div>


{{-- =========================================================
     CARDS PRINCIPAIS
========================================================= --}}

<div class="dashboard-cards">

    {{-- RECEITAS --}}
    <div class="cp-card summary-card">

        <div class="summary-icon icon-green">
            ↗
        </div>

        <div>
            <div class="summary-title saldo-title">
                Receitas do mês

                <span class="saldo-help">
                    ?
                    <span class="saldo-tooltip">
                        Total das receitas efetivamente recebidas no mês.
                        Receitas ainda pendentes não entram neste valor.
                    </span>
                </span>
            </div>

            <div class="summary-value value-blue">
                R$ {{ number_format($receitasMes ?? 0, 2, ',', '.') }}
            </div>

            <div class="summary-note">
                Total recebido no mês
            </div>
        </div>

    </div>


    {{-- DESPESAS --}}
    <div class="cp-card summary-card">

        <div class="summary-icon icon-red">
            ↘
        </div>

        <div>
            <div class="summary-title saldo-title">
                Despesas do mês

                <span class="saldo-help">
                    ?
                    <span class="saldo-tooltip">
                        Total de despesas efetivamente pagas no mês.
                        Despesas ainda pendentes não entram neste valor.
                    </span>
                </span>
            </div>

            <div class="summary-value value-red">
                R$ {{ number_format($despesasMes ?? 0, 2, ',', '.') }}
            </div>

            <div class="summary-note">
                Total gasto no mês
            </div>
        </div>

    </div>


    {{-- PREVISÃO DE DESPESAS DO MÊS --}}
    <div class="cp-card summary-card">

        <div class="summary-icon icon-purple">
            ◫
        </div>

        <div>
            <div class="summary-title saldo-title">
                Previsão de despesas

                <span class="saldo-help">
                    ?
                    <span class="saldo-tooltip">
                        Soma das despesas lançadas, contas recorrentes
                        e faturas de cartão com vencimento no mês atual.
                        Compras do cartão não são somadas novamente.
                    </span>
                </span>
            </div>

            <div class="summary-value value-purple">
                R$ {{ number_format($previsaoDespesasMes ?? 0, 2, ',', '.') }}
            </div>

            <div class="summary-note">
                Total previsto para o mês
            </div>

            <a
                href="{{ route('previsao-despesas.index') }}"
                class="summary-link"
            >
                Ver previsão
            </a>
        </div>

    </div>


    {{-- SALDO ATUAL --}}
    <div class="cp-card summary-card">

        <div class="summary-icon {{ ($saldoAtual ?? 0) < 0 ? 'icon-red' : 'icon-green' }}">
            $
        </div>

        <div>

            <div class="summary-title saldo-title">
                Saldo atual

                <span class="saldo-help">
                    ?
                    <span class="saldo-tooltip">
                        Saldo atual é a soma do dinheiro disponível
                        em todas as suas contas e carteiras ativas.
                        Não representa apenas receitas menos despesas do mês.
                    </span>
                </span>
            </div>

            <div class="summary-value {{ ($saldoAtual ?? 0) < 0 ? 'value-red' : 'value-green' }}">
                R$
                {{ number_format(
                    $saldoAtual ?? 0,
                    2,
                    ',',
                    '.'
                ) }}
            </div>

            <div class="summary-note">
                Saldo disponível nas contas
            </div>

            @if(!empty($contasAtivas) && $contasAtivas->count() > 0)

            <div style="
                margin-top:6px;
                font-size:10px;
                color:#6b7280;
                line-height:1.5;
            ">

                @foreach($contasAtivas as $conta)

                    <span>
                        {{ $conta->nome }}:
                        R$
                        {{ number_format(
                            $conta->saldo_atual,
                            2,
                            ',',
                            '.'
                        ) }}
                    </span>

                    @if(!$loop->last)
                        •
                    @endif

                @endforeach

            </div>

        @endif

        </div>

    </div>


    {{-- A VENCER --}}
    <div class="cp-card summary-card">

        <div class="summary-icon icon-orange">
            ◷
        </div>

        <div>
            <div class="summary-title saldo-title">
                A vencer

                <span class="saldo-help">
                    ?
                    <span class="saldo-tooltip">
                        Mostra as despesas e parcelas pendentes
                        que vencem de hoje até o final do mês atual.
                    </span>
                </span>
            </div>

            <div class="summary-value value-orange">
                R$ {{ number_format($aVencer ?? 0, 2, ',', '.') }}
            </div>

            <div class="summary-note">
                Contas a pagar
            </div>
        </div>

    </div>




    {{-- CARTÃO --}}
    <div class="cp-card summary-card">

        <div class="summary-icon icon-purple">
            ▤
        </div>

        <div>
            
            <div class="summary-title saldo-title">
                Cartão em aberto

                <span class="saldo-help">
                    ?
                    <span class="saldo-tooltip">
                        Soma das faturas de cartão que ainda não foram pagas.
                        A compra no cartão não reduz o saldo da conta até o pagamento da fatura.
                    </span>
                </span>
            </div>

            <div class="summary-value value-purple">
                R$ {{ number_format($cartaoEmAberto ?? 0, 2, ',', '.') }}
            </div>

            <div class="summary-note">
                Faturas em aberto
            </div>
        </div>

    </div>


    {{-- PRÓXIMO MÊS --}}
    <div class="cp-card summary-card">

        <div class="summary-icon icon-cyan">
            ▣
        </div>

        <div>
            <div class="summary-title saldo-title">
                Próximo mês

                <span class="saldo-help">
                    ?
                    <span class="saldo-tooltip">
                        Mostra os compromissos financeiros previstos
                        para o próximo mês, incluindo despesas,
                        parcelas e faturas com vencimento naquele período.
                    </span>
                </span>
            </div>

            <div class="summary-value value-cyan">
                R$ {{ number_format($proximoMes ?? 0, 2, ',', '.') }}
            </div>

            <div class="summary-note">
                Despesas previstas
            </div>
        </div>

    </div>

</div>


{{-- =========================================================
     GRÁFICOS

</div>


{{-- =========================================================
     GRÁFICOS
========================================================= --}}

<div class="dashboard-charts">

    {{-- DESPESAS POR CATEGORIA --}}
    <div class="cp-card dashboard-panel">

        <div class="panel-header">

            <h2 class="panel-title">
                Despesas por categoria
            </h2>

            <select class="panel-select">
                <option>Este mês</option>
            </select>

        </div>


        @if(!empty($categorias) && count($categorias) > 0)

            <div class="category-content">

                <div class="category-chart-wrap">
                    <canvas id="graficoCategorias"></canvas>
                </div>


                <div class="category-list">

                    @php
                        $coresCategorias = [
                            '#2f80ed',
                            '#20b96b',
                            '#ff9418',
                            '#7b61d1',
                            '#1ba8b5',
                            '#ef4444',
                            '#64748b',
                        ];
                    @endphp


                    @foreach($categorias as $nome => $valor)

                        @php
                            $indiceCor =
                                $loop->index
                                % count($coresCategorias);
                        @endphp

                        <div class="category-row">

                            <span
                                class="category-dot"
                                style="
                                    background:
                                    {{ $coresCategorias[$indiceCor] }};
                                ">
                            </span>

                            <span>
                                {{ $nome }}
                            </span>

                            <strong>
                                R$
                                {{ number_format($valor, 2, ',', '.') }}
                            </strong>

                        </div>

                    @endforeach


                    <div class="category-total">

                        Total:
                        R$
                        {{ number_format(array_sum($categorias), 2, ',', '.') }}

                    </div>

                </div>

            </div>

        @else

            <div class="empty-dashboard">
                Nenhuma despesa registrada neste mês.
            </div>

        @endif

    </div>


    {{-- RECEITAS X DESPESAS --}}
    <div class="cp-card dashboard-panel">

        <div class="panel-header">

            <h2 class="panel-title">
                Receitas x Despesas
            </h2>

            <select class="panel-select">
                <option>Últimos 6 meses</option>
            </select>

        </div>

        <div class="chart-area">
            <canvas id="graficoReceitasDespesas"></canvas>
        </div>

    </div>

</div>


{{-- =========================================================
     PARTE INFERIOR
========================================================= --}}

<div class="dashboard-bottom">

    {{-- PRÓXIMAS CONTAS --}}
    <div class="cp-card dashboard-panel">

        <div class="panel-header">

            <h2 class="panel-title">
                Próximas contas
            </h2>

            <a
                href="{{ route('despesas.index') }}"
                class="list-header-link"
            >
                Ver todas
            </a>

        </div>


        <table class="dashboard-table">

            <thead>
                <tr>
                    <th>Descrição</th>
                    <th>Vencimento</th>
                    <th>Valor</th>
                    <th>Situação</th>
                </tr>
            </thead>

            <tbody>

                @forelse($proximasContas ?? [] as $conta)

                    <tr>

                        <td>
                            {{ $conta['descricao'] }}
                        </td>

                        <td>
                            {{ \Carbon\Carbon::parse($conta['vencimento'])->format('d/m/Y') }}
                        </td>

                        <td>
                            R$
                            {{ number_format($conta['valor'], 2, ',', '.') }}
                        </td>

                        <td>

                            @if($conta['situacao'] === 'Atrasado')

                                <span class="status status-atrasado">
                                    Atrasado
                                </span>

                            @elseif($conta['situacao'] === 'Em aberto')

                                <span class="status status-aberto">
                                    Em aberto
                                </span>

                            @else

                                <span class="status status-vencer">
                                    A vencer
                                </span>

                            @endif

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="4"
                            class="empty-dashboard">

                            Nenhuma conta pendente.

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>


    {{-- ÚLTIMAS MOVIMENTAÇÕES --}}
    <div class="cp-card dashboard-panel">

        <div class="panel-header">

            <h2 class="panel-title">
                Últimas movimentações
            </h2>

            <a href="{{ route('movimentacoes.index') }}"
               class="list-header-link">
                Ver todas
            </a>

        </div>


        <ul class="mov-list">

            @forelse($ultimasMovimentacoes ?? [] as $movimentacao)

                @php
                    $entrada =
                        $movimentacao->tipo === 'entrada';

                    $classeCor =
                        $entrada
                            ? 'green'
                            : 'red';

                    $simbolo =
                        $entrada
                            ? '+'
                            : '-';
                @endphp


                <li class="mov-item">

                    <div class="mov-icon {{ $classeCor }}">
                        {{ $entrada ? '↓' : '↑' }}
                    </div>


                    <div class="mov-description">

                        <strong>
                            {{ $movimentacao->descricao }}
                        </strong>

                        <small>

                            {{ ucfirst(
                                str_replace(
                                    '_',
                                    ' ',
                                    $movimentacao->origem_tipo
                                )
                            ) }}

                            @if($movimentacao->conta)
                                • {{ $movimentacao->conta->nome }}
                            @endif

                        </small>

                    </div>


                    <div class="mov-date">

                        {{ \Carbon\Carbon::parse(
                            $movimentacao->data_movimentacao
                        )->format('d/m/Y') }}

                    </div>


                    <div class="mov-value {{ $classeCor }}">

                        {{ $simbolo }}
                        R$
                        {{ number_format(
                            $movimentacao->valor,
                            2,
                            ',',
                            '.'
                        ) }}

                    </div>

                </li>

            @empty

                <li class="empty-dashboard">
                    Nenhuma movimentação registrada.
                </li>

            @endforelse

        </ul>

    </div>

</div>

@endsection


@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | CORES PADRÃO DAS CATEGORIAS
    |--------------------------------------------------------------------------
    */

    const coresCategorias = [
        '#2f80ed',
        '#20b96b',
        '#ff9418',
        '#7b61d1',
        '#1ba8b5',
        '#ef4444',
        '#64748b'
    ];


    /*
    |--------------------------------------------------------------------------
    | DESPESAS POR CATEGORIA
    |--------------------------------------------------------------------------
    */

    const categoriasCanvas =
        document.getElementById(
            'graficoCategorias'
        );

    const categoriasLabels =
        @json(array_keys($categorias ?? []));

    const categoriasValores =
        @json(array_values($categorias ?? []));


    if (
        categoriasCanvas
        && categoriasValores.length > 0
    ) {

        new Chart(
            categoriasCanvas,
            {
                type: 'doughnut',

                data: {

                    labels:
                        categoriasLabels,

                    datasets: [{
                        data:
                            categoriasValores,

                        backgroundColor:
                            categoriasLabels.map(
                                function (_, index) {

                                    return coresCategorias[
                                        index
                                        % coresCategorias.length
                                    ];
                                }
                            ),

                        borderWidth: 2,

                        borderColor:
                            '#ffffff'
                    }]
                },

                options: {

                    responsive: true,

                    maintainAspectRatio: false,

                    cutout: '60%',

                    plugins: {

                        legend: {
                            display: false
                        },

                        tooltip: {

                            callbacks: {

                                label:
                                    function(context) {

                                        return context.label
                                            + ': R$ '
                                            + Number(
                                                context.raw
                                            ).toLocaleString(
                                                'pt-BR',
                                                {
                                                    minimumFractionDigits: 2,
                                                    maximumFractionDigits: 2
                                                }
                                            );
                                    }
                            }
                        }
                    }
                }
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | RECEITAS X DESPESAS
    |--------------------------------------------------------------------------
    */

    const receitasDespesasCanvas =
        document.getElementById(
            'graficoReceitasDespesas'
        );


    if (receitasDespesasCanvas) {

        new Chart(
            receitasDespesasCanvas,
            {

                type: 'bar',

                data: {

                    labels:
                        @json($graficoMeses ?? []),

                    datasets: [

                        {
                            label:
                                'Receitas',

                            data:
                                @json($graficoReceitas ?? []),

                            backgroundColor:
                                '#20b96b',

                            borderRadius: 1,

                            barPercentage: .58,

                            categoryPercentage: .68
                        },

                        {
                            label:
                                'Despesas',

                            data:
                                @json($graficoDespesas ?? []),

                            backgroundColor:
                                '#ef4444',

                            borderRadius: 1,

                            barPercentage: .58,

                            categoryPercentage: .68
                        }

                    ]
                },

                options: {

                    responsive: true,

                    maintainAspectRatio: false,

                    scales: {

                        x: {

                            grid: {
                                display: false
                            },

                            border: {
                                display: false
                            },

                            ticks: {

                                font: {
                                    size: 10
                                }
                            }
                        },

                        y: {

                            beginAtZero: true,

                            border: {
                                display: false
                            },

                            grid: {
                                color: '#e9ecef'
                            },

                            ticks: {

                                font: {
                                    size: 10
                                },

                                callback:
                                    function(value) {

                                        return 'R$ '
                                            + Number(
                                                value
                                            ).toLocaleString(
                                                'pt-BR'
                                            );
                                    }
                            }
                        }
                    },

                    plugins: {

                        legend: {

                            position:
                                'bottom',

                            labels: {

                                usePointStyle:
                                    true,

                                pointStyle:
                                    'rect',

                                boxWidth:
                                    8,

                                boxHeight:
                                    8,

                                font: {
                                    size: 10
                                }
                            }
                        },

                        tooltip: {

                            callbacks: {

                                label:
                                    function(context) {

                                        return context
                                            .dataset
                                            .label
                                            + ': R$ '
                                            + Number(
                                                context.raw
                                            ).toLocaleString(
                                                'pt-BR',
                                                {
                                                    minimumFractionDigits: 2,
                                                    maximumFractionDigits: 2
                                                }
                                            );
                                    }
                            }
                        }
                    }
                }
            }
        );
    }

});
</script>

@endpush