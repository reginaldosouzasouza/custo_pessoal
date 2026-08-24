@extends('layouts.custo-pessoal')

@section('title', 'Relatórios')

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
        grid-template-columns: 220px 220px auto;
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
        color: #374151;
        font-size: 12px;
    }

    .acoes-filtro {
        display: flex;
        gap: 8px;
    }

    .btn-filtrar,
    .btn-limpar {
        min-height: 40px;
        border-radius: 8px;
        padding: 0 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 600;
    }

    .btn-filtrar {
        border: none;
        background: #0d6efd;
        color: #fff;
        cursor: pointer;
    }

    .btn-limpar {
        border: 1px solid #d9dee5;
        background: #fff;
        color: #374151;
        text-decoration: none;
    }

    .resumo-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 18px;
    }

    .resumo-card {
        padding: 20px;
    }

    .resumo-label {
        color: #6b7280;
        font-size: 11px;
        margin-bottom: 6px;
    }

    .resumo-valor {
        font-size: 24px;
        font-weight: 700;
    }

    .receita {
        color: #16a34a;
    }

    .despesa {
        color: #ef4444;
    }

    .resultado-positivo {
        color: #16a34a;
    }

    .resultado-negativo {
        color: #ef4444;
    }

    .categorias-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
        margin-bottom: 18px;
    }

    .panel {
        padding: 18px;
    }

    .panel-title {
        margin: 0 0 15px;
        font-size: 15px;
        font-weight: 700;
        color: #111827;
    }

    .categoria-row {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 15px;
        align-items: center;
        padding: 9px 0;
        border-bottom: 1px solid #edf0f3;
        font-size: 12px;
    }

    .categoria-row:last-child {
        border-bottom: none;
    }

    .categoria-valor {
        font-weight: 700;
        white-space: nowrap;
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
        padding: 10px 8px;
        border-bottom: 1px solid #e5e7eb;
        color: #4b5563;
        font-weight: 600;
    }

    .mov-table td {
        padding: 11px 8px;
        border-bottom: 1px solid #edf0f3;
        color: #374151;
    }

    .mov-table tr:last-child td {
        border-bottom: none;
    }

    .mov-valor {
        text-align: right;
        font-weight: 700;
        white-space: nowrap;
    }

    .mov-entrada {
        color: #16a34a !important;
    }

    .mov-saida {
        color: #ef4444 !important;
    }

    .empty-state {
        color: #6b7280;
        text-align: center;
        padding: 30px 10px;
        font-size: 12px;
    }

    .analise-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1.6fr;
        gap: 18px;
        margin-bottom: 18px;
        align-items: stretch;
    }

    .classificacao-card,
    .grafico-card {
        padding: 20px;
        height: 100%;
    }

    .classificacao-label {
        color: #6b7280;
        font-size: 11px;
        margin-bottom: 6px;
    }

    .classificacao-valor {
        font-size: 22px;
        font-weight: 700;
    }

    .classificacao-valor.essencial {
        color: #2879d7;
    }

    .classificacao-valor.nao-essencial {
        color: #f59e0b;
    }

    .classificacao-percentual {
        margin-top: 5px;
        color: #6b7280;
        font-size: 11px;
    }

    .grafico-card {
        margin-bottom: 0;
    }

    .grafico-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        margin-bottom: 15px;
    }

    .grafico-subtitle {
        color: #6b7280;
        font-size: 11px;
        margin-top: 4px;
    }

    .grafico-area {
        position: relative;
        min-height: 260px;
    }

    @media(max-width: 1000px) {
        .analise-grid {
            grid-template-columns: 1fr;
        }
    }


    @media(max-width: 900px) {
        .categorias-grid {
            grid-template-columns: 1fr;
        }

        .resumo-grid {
            grid-template-columns: 1fr;
        }
    }

    @media(max-width: 650px) {
        .filtros-grid {
            grid-template-columns: 1fr;
        }

        .acoes-filtro {
            width: 100%;
        }

        .btn-filtrar,
        .btn-limpar {
            flex: 1;
        }
    }
</style>
@endpush


@section('content')

<div class="page-header">

    <h1 class="cp-page-title">
        Relatórios
    </h1>

    <p class="cp-page-subtitle">
        Analise suas receitas, despesas e movimentações por período.
    </p>

</div>


<div class="cp-card filtros-card">

    <form
        method="GET"
        action="{{ route('relatorios.index') }}"
    >

        <div class="filtros-grid">

            <div class="form-group">

                <label class="form-label">
                    Data inicial
                </label>

                <input
                    type="date"
                    name="data_inicial"
                    class="form-control"
                    value="{{ $dataInicial }}"
                    required
                >

            </div>


            <div class="form-group">

                <label class="form-label">
                    Data final
                </label>

                <input
                    type="date"
                    name="data_final"
                    class="form-control"
                    value="{{ $dataFinal }}"
                    required
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
                    href="{{ route('relatorios.index') }}"
                    class="btn-limpar"
                >
                    Este mês
                </a>

            </div>

        </div>

    </form>

</div>


<div class="resumo-grid">

    <div class="cp-card resumo-card">

        <div class="resumo-label">
            Receitas recebidas
        </div>

        <div class="resumo-valor receita">
            R$
            {{ number_format(
                $totalReceitas,
                2,
                ',',
                '.'
            ) }}
        </div>

    </div>


    <div class="cp-card resumo-card">

        <div class="resumo-label">
            Despesas pagas
        </div>

        <div class="resumo-valor despesa">
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
            Resultado do período
        </div>

        <div class="
            resumo-valor
            {{ $resultadoPeriodo >= 0
                ? 'resultado-positivo'
                : 'resultado-negativo' }}
        ">
            R$
            {{ number_format(
                $resultadoPeriodo,
                2,
                ',',
                '.'
            ) }}
        </div>

    </div>

</div>


<div class="analise-grid">

    <div class="cp-card classificacao-card">

        <div class="classificacao-label">
            Despesas essenciais
        </div>

        <div class="classificacao-valor essencial">
            R$
            {{ number_format(
                $totalEssenciais ?? 0,
                2,
                ',',
                '.'
            ) }}
        </div>

        <div class="classificacao-percentual">
            {{ number_format(
                $percentualEssenciais ?? 0,
                1,
                ',',
                '.'
            ) }}%
            das despesas pagas
        </div>

    </div>


    <div class="cp-card classificacao-card">

        <div class="classificacao-label">
            Despesas não essenciais
        </div>

        <div class="classificacao-valor nao-essencial">
            R$
            {{ number_format(
                $totalNaoEssenciais ?? 0,
                2,
                ',',
                '.'
            ) }}
        </div>

        <div class="classificacao-percentual">
            {{ number_format(
                $percentualNaoEssenciais ?? 0,
                1,
                ',',
                '.'
            ) }}%
            das despesas pagas
        </div>

    </div>


    <div class="cp-card grafico-card">

        <div class="grafico-header">

            <div>
                <h2 class="panel-title">
                    Evolução mensal
                </h2>

                <div class="grafico-subtitle">
                    Receitas x despesas dos últimos 6 meses.
                </div>
            </div>

        </div>

        <div class="grafico-area">
            <canvas id="graficoEvolucao"></canvas>
        </div>

    </div>

</div>


<div class="categorias-grid">

    <div class="cp-card panel">

        <h2 class="panel-title">
            Receitas por categoria
        </h2>

        @forelse($receitasPorCategoria as $item)

            <div class="categoria-row">

                <span>
                    {{ $item->nome }}
                </span>

                <span class="categoria-valor receita">
                    R$
                    {{ number_format(
                        $item->total,
                        2,
                        ',',
                        '.'
                    ) }}
                </span>

            </div>

        @empty

            <div class="empty-state">
                Nenhuma receita no período.
            </div>

        @endforelse

    </div>


    <div class="cp-card panel">

        <h2 class="panel-title">
            Despesas por categoria
        </h2>

        @forelse($despesasCategorias as $nome => $valor)

            <div class="categoria-row">

                <span>
                    {{ $nome }}
                </span>

                <span class="categoria-valor despesa">
                    R$
                    {{ number_format(
                        $valor,
                        2,
                        ',',
                        '.'
                    ) }}
                </span>

            </div>

        @empty

            <div class="empty-state">
                Nenhuma despesa no período.
            </div>

        @endforelse

    </div>

</div>


<div class="cp-card table-card">

    <h2 class="panel-title">
        Movimentações do período
    </h2>


    @if($movimentacoes->count() > 0)

        <table class="mov-table">

            <thead>
                <tr>
                    <th>Data</th>
                    <th>Descrição</th>
                    <th>Conta</th>
                    <th>Origem</th>
                    <th style="text-align:right;">
                        Valor
                    </th>
                </tr>
            </thead>

            <tbody>

                @foreach($movimentacoes as $movimentacao)

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

                        <td>
                            {{ $movimentacao->descricao }}
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

                        <td class="
                            mov-valor
                            {{ $entrada
                                ? 'mov-entrada'
                                : 'mov-saida' }}
                        ">

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

    @else

        <div class="empty-state">
            Nenhuma movimentação encontrada no período.
        </div>

    @endif

</div>

@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const canvas =
        document.getElementById(
            'graficoEvolucao'
        );

    if (!canvas) {
        return;
    }

    new Chart(
        canvas,
        {
            type: 'bar',

            data: {
                labels:
                    @json($graficoMeses ?? []),

                datasets: [
                    {
                        label: 'Receitas',

                        data:
                            @json($graficoReceitas ?? []),

                        backgroundColor:
                            '#20b96b',

                        borderRadius: 2
                    },

                    {
                        label: 'Despesas',

                        data:
                            @json($graficoDespesas ?? []),

                        backgroundColor:
                            '#ef4444',

                        borderRadius: 2
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
                        }
                    },

                    y: {
                        beginAtZero: true,

                        border: {
                            display: false
                        },

                        ticks: {
                            callback:
                                function(value) {

                                    return 'R$ '
                                        + Number(value)
                                            .toLocaleString(
                                                'pt-BR'
                                            );
                                }
                        }
                    }
                },

                plugins: {
                    legend: {
                        position: 'bottom'
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

});
</script>

@endpush

@endsection