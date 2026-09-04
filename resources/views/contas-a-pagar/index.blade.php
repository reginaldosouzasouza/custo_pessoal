@extends('layouts.custo-pessoal')

@section('title', 'Contas a Pagar')

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
            180px
            170px
            minmax(170px, 1fr)
            150px
            minmax(190px, 1.3fr)
            150px
            auto;
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
        box-sizing:border-box;
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
        grid-template-columns:repeat(3, 1fr);
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

    .resumo-vencido .resumo-valor {
        color:#dc2626;
    }

    .resumo-hoje .resumo-valor {
        color:#d97706;
    }

    .table-card {
        padding:18px;
        overflow-x:auto;
    }

    .contas-table {
        width:100%;
        min-width:1050px;
        border-collapse:collapse;
        font-size:12px;
    }

    .contas-table th {
        padding:10px 9px;
        text-align:left;
        color:#4b5563;
        font-weight:600;
        border-bottom:1px solid #e5e7eb;
    }

    .contas-table td {
        padding:12px 9px;
        border-bottom:1px solid #edf0f3;
        color:#374151;
        vertical-align:middle;
    }

    .contas-table tr:last-child td {
        border-bottom:none;
    }

    .valor {
        font-weight:700;
        color:#7653cb;
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

    .origem-fatura {
        background:#eee9ff;
        color:#7653cb;
    }

    .origem-parcela {
        background:#ede9fe;
        color:#6d28d9;
    }

    .status-pendente {
        background:#fff7ed;
        color:#c2410c;
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

    .vencido {
        color:#dc2626 !important;
        font-weight:700;
    }

    .btn-pagar {
        min-height:32px;
        border:none;
        background:#16a34a;
        color:#fff;
        padding:0 14px;
        border-radius:7px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        font-size:11px;
        font-weight:600;
        cursor:pointer;
        white-space:nowrap;
    }

    .btn-pagar:hover {
        background:#15803d;
    }

    /*
    |--------------------------------------------------------------------------
    | MODAL
    |--------------------------------------------------------------------------
    */

    .modal-pagamento {
        display:none;
        position:fixed;
        z-index:99999;
        inset:0;
        background:rgba(0,0,0,.45);
        align-items:center;
        justify-content:center;
        padding:15px;
    }

    .modal-pagamento.ativo {
        display:flex;
    }

    .modal-box {
        background:#fff;
        width:100%;
        max-width:460px;
        border-radius:14px;
        box-shadow:0 15px 45px rgba(0,0,0,.25);
        overflow:hidden;
    }

    .modal-header {
        padding:18px 20px;
        border-bottom:1px solid #e5e7eb;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:15px;
    }

    .modal-title {
        margin:0;
        font-size:17px;
        font-weight:700;
        color:#111827;
    }

    .modal-close {
        border:none;
        background:transparent;
        font-size:24px;
        line-height:1;
        color:#6b7280;
        cursor:pointer;
    }

    .modal-body {
        padding:20px;
    }

    .modal-info {
        background:#f8fafc;
        border:1px solid #e5e7eb;
        border-radius:9px;
        padding:12px;
        margin-bottom:18px;
        font-size:12px;
        line-height:1.7;
        color:#4b5563;
    }

    .modal-info strong {
        color:#111827;
    }

    .modal-grid {
        display:grid;
        gap:14px;
    }

    .modal-footer {
        padding:16px 20px;
        border-top:1px solid #e5e7eb;
        display:flex;
        justify-content:flex-end;
        gap:9px;
    }

    .btn-cancelar-modal {
        min-height:38px;
        border:1px solid #d1d5db;
        background:#fff;
        color:#374151;
        border-radius:8px;
        padding:0 15px;
        cursor:pointer;
        font-size:12px;
        font-weight:600;
    }

    .btn-confirmar {
        min-height:38px;
        border:none;
        background:#16a34a;
        color:#fff;
        border-radius:8px;
        padding:0 16px;
        cursor:pointer;
        font-size:12px;
        font-weight:600;
    }

    .empty-state {
        text-align:center;
        padding:45px 15px;
        color:#6b7280;
    }

    @media(max-width:1250px) {
        .filtros-grid {
            grid-template-columns:repeat(3, minmax(0, 1fr));
        }

        .acoes-filtro {
            grid-column:span 3;
        }
    }

    @media(max-width:1050px) {
        .cards-resumo {
            grid-template-columns:repeat(2, 1fr);
        }
    }

    @media(max-width:600px) {
        .filtros-grid {
            grid-template-columns:1fr;
        }

        .acoes-filtro {
            width:100%;
            grid-column:auto;
        }

        .btn-filtrar,
        .btn-limpar {
            flex:1;
        }

        .cards-resumo {
            grid-template-columns:1fr;
        }

        .modal-box {
            max-width:100%;
        }
    }
</style>
@endpush


@section('content')

<div class="page-header">

    <div>

        <h1 class="cp-page-title">
            Contas a Pagar
        </h1>

        <p class="cp-page-subtitle">
            Todas as obrigações financeiras reunidas em um único lugar.
        </p>

    </div>

</div>


@if(session('success'))

    <div
        class="cp-card"
        style="
            padding:12px 16px;
            margin-bottom:15px;
            color:#166534;
            background:#dcfce7;
        "
    >
        {{ session('success') }}
    </div>

@endif


@if(session('error'))

    <div
        class="cp-card"
        style="
            padding:12px 16px;
            margin-bottom:15px;
            color:#b91c1c;
            background:#fee2e2;
        "
    >
        {{ session('error') }}
    </div>

@endif


@if($errors->any())

    <div
        class="cp-card"
        style="
            padding:12px 16px;
            margin-bottom:15px;
            color:#b91c1c;
            background:#fee2e2;
        "
    >

        @foreach($errors->all() as $erro)

            <div>
                {{ $erro }}
            </div>

        @endforeach

    </div>

@endif


<div class="cp-card filtros-card">

    <form
        method="GET"
        action="{{ route('contas-a-pagar.index') }}"
    >

        <div class="filtros-grid">

            <div class="form-group">

                <label class="form-label">
                    Mês
                </label>

                <input
                    type="month"
                    name="mes"
                    value="{{ $mes }}"
                    class="form-control"
                >

            </div>


            <div class="form-group">

                <label class="form-label">
                    Data de vencimento
                </label>

                <input
                    type="date"
                    name="data_vencimento"
                    value="{{ $dataVencimento }}"
                    class="form-control"
                >

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
                                ===
                                (string) $categoria->id
                            )
                        >
                            {{ $categoria->nome }}
                        </option>

                    @endforeach

                </select>

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
                        @selected($origemFiltro === 'despesa')
                    >
                        Despesa
                    </option>

                    <option
                        value="recorrente"
                        @selected($origemFiltro === 'recorrente')
                    >
                        Recorrente
                    </option>

                    <option
                        value="parcela"
                        @selected($origemFiltro === 'parcela')
                    >
                        Parcela
                    </option>

                    <option
                        value="fatura"
                        @selected($origemFiltro === 'fatura')
                    >
                        Cartão
                    </option>

                </select>

            </div>


            <div class="form-group">

                <label class="form-label">
                    Descrição
                </label>

                <input
                    type="text"
                    name="descricao"
                    value="{{ $descricao }}"
                    placeholder="Ex.: mercado, internet..."
                    class="form-control"
                >

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
                        @selected($situacaoFiltro === 'pendente')
                    >
                        Pendente
                    </option>

                    <option
                        value="prevista"
                        @selected($situacaoFiltro === 'prevista')
                    >
                        Prevista
                    </option>

                    <option
                        value="vencida"
                        @selected($situacaoFiltro === 'vencida')
                    >
                        Vencida
                    </option>

                    <option
                        value="aberta"
                        @selected($situacaoFiltro === 'aberta')
                    >
                        Aberta
                    </option>

                    <option
                        value="fechada"
                        @selected($situacaoFiltro === 'fechada')
                    >
                        Fechada
                    </option>

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
                    href="{{ route('contas-a-pagar.index') }}"
                    class="btn-limpar"
                >
                    Limpar
                </a>

            </div>

        </div>

    </form>

</div>


<div class="cards-resumo">

    <div class="cp-card resumo-card resumo-total">

        <div class="resumo-label">
            Total a pagar
        </div>

        <div class="resumo-valor">

            R$
            {{ number_format(
                $totalPagar,
                2,
                ',',
                '.'
            ) }}

        </div>

    </div>


    <div class="cp-card resumo-card resumo-vencido">

        <div class="resumo-label">
            Total vencido
        </div>

        <div class="resumo-valor">

            R$
            {{ number_format(
                $totalVencido,
                2,
                ',',
                '.'
            ) }}

        </div>

    </div>


    <div class="cp-card resumo-card resumo-hoje">

        <div class="resumo-label">
            Vence hoje
        </div>

        <div class="resumo-valor">

            R$
            {{ number_format(
                $totalHoje,
                2,
                ',',
                '.'
            ) }}

        </div>

    </div>

</div>


<div class="cp-card table-card">

    @if($itens->count() > 0)

        <table class="contas-table">

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

                    <th style="text-align:center;">
                        Ação
                    </th>
                </tr>

            </thead>


            <tbody>

                @foreach($itens as $index => $item)

                    @php

                        $tipo =
                            $item['tipo']
                            ?? 'despesa';


                        $classeOrigem =
                            match($tipo) {

                                'recorrente' =>
                                    'origem-recorrente',

                                'parcela' =>
                                    'origem-parcela',

                                'fatura' =>
                                    'origem-fatura',

                                default =>
                                    'origem-despesa',
                            };


                        $situacao =
                            strtolower(
                                $item['situacao']
                                ?? 'pendente'
                            );


                        $dataVencimento =
                            !empty(
                                $item['vencimento']
                            )
                                ? \Carbon\Carbon::parse(
                                    $item['vencimento']
                                )->startOfDay()
                                : null;


                        $estaVencida =
                            $dataVencimento
                            &&
                            $dataVencimento->lt(
                                today()
                            );


                        $classeSituacao =
                            $estaVencida
                                ? 'status-vencida'
                                : match($situacao) {

                                    'aberta' =>
                                        'status-aberta',

                                    'fechada' =>
                                        'status-fechada',

                                    'prevista' =>
                                        'status-prevista',

                                    default =>
                                        'status-pendente',
                                };


                        $textoSituacao =
                            $estaVencida
                                ? 'Vencida'
                                : match($situacao) {

                                    'aberta' =>
                                        'Aberta',

                                    'fechada' =>
                                        'Fechada',

                                    'prevista' =>
                                        'Prevista',

                                    default =>
                                        'Pendente',
                                };


                        $vencimentoFormatado =
                            $dataVencimento
                                ? $dataVencimento
                                    ->format('d/m/Y')
                                : '-';


                        $vencimentoBanco =
                            $dataVencimento
                                ? $dataVencimento
                                    ->format('Y-m-d')
                                : null;


                        $modalId =
                            'modal-pagamento-'
                            . $tipo
                            . '-'
                            . $item['id']
                            . '-'
                            . $index;

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


                        <td
                            class="{{ $estaVencida ? 'vencido' : '' }}"
                        >
                            {{ $vencimentoFormatado }}
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
                                $tipo === 'recorrente'
                                &&
                                ($item['tipo_valor'] ?? null)
                                    === 'variavel'
                                &&
                                (float) $item['valor'] <= 0
                            )

                                A informar

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


                        <td style="text-align:center;">

                            <button
                                type="button"
                                class="btn-pagar"
                                onclick="abrirModal('{{ $modalId }}')"
                            >
                                Pagar
                            </button>

                        </td>

                    </tr>


                    {{-- MODAL --}}

                    <div
                        id="{{ $modalId }}"
                        class="modal-pagamento"
                    >

                        <div class="modal-box">

                            @if($tipo === 'despesa')

                                <form
                                    method="POST"
                                    action="{{ route(
                                        'despesas.pagar',
                                        $item['id']
                                    ) }}"
                                >

                                    @csrf


                            @elseif($tipo === 'parcela')

                                <form
                                    method="POST"
                                    action="{{ route(
                                        'parcelas.pagar',
                                        $item['id']
                                    ) }}"
                                >

                                    @csrf


                            @elseif($tipo === 'fatura')

                                <form
                                    method="POST"
                                    action="{{ route(
                                        'faturas.pagar',
                                        $item['id']
                                    ) }}"
                                >

                                    @csrf


                            @elseif($tipo === 'recorrente')

                                <form
                                    method="POST"
                                    action="{{ route(
                                        'contas-a-pagar.recorrencias.pagar',
                                        $item['id']
                                    ) }}"
                                >

                                    @csrf

                                    <input
                                        type="hidden"
                                        name="vencimento"
                                        value="{{ $vencimentoBanco }}"
                                    >

                            @endif


                                <div class="modal-header">

                                    <h3 class="modal-title">
                                        Confirmar pagamento
                                    </h3>

                                    <button
                                        type="button"
                                        class="modal-close"
                                        onclick="fecharModal('{{ $modalId }}')"
                                    >
                                        &times;
                                    </button>

                                </div>


                                <div class="modal-body">

                                    <div class="modal-info">

                                        <div>
                                            <strong>
                                                {{ $item['descricao'] }}
                                            </strong>
                                        </div>

                                        <div>
                                            Vencimento:
                                            <strong>
                                                {{ $vencimentoFormatado }}
                                            </strong>
                                        </div>

                                        @if(
                                            !(
                                                $tipo === 'recorrente'
                                                &&
                                                ($item['tipo_valor'] ?? null)
                                                    === 'variavel'
                                                &&
                                                (float) $item['valor'] <= 0
                                            )
                                        )

                                            <div>
                                                Valor:
                                                <strong>
                                                    R$
                                                    {{ number_format(
                                                        $item['valor'],
                                                        2,
                                                        ',',
                                                        '.'
                                                    ) }}
                                                </strong>
                                            </div>

                                        @endif

                                    </div>


                                    <div class="modal-grid">


                                        {{-- VALOR DA RECORRÊNCIA --}}

                                        @if($tipo === 'recorrente')

                                            <div class="form-group">

                                                <label class="form-label">
                                                    Valor pago
                                                </label>

                                                <input
                                                    type="number"
                                                    name="valor"
                                                    class="form-control"
                                                    step="0.01"
                                                    min="0.01"
                                                    value="{{
                                                        (float) $item['valor'] > 0
                                                            ? number_format(
                                                                $item['valor'],
                                                                2,
                                                                '.',
                                                                ''
                                                            )
                                                            : ''
                                                    }}"
                                                    required
                                                >

                                            </div>

                                        @endif


                                        {{-- CONTA --}}

                                        <div class="form-group">

                                            <label class="form-label">
                                                Conta utilizada
                                            </label>

                                            <select
                                                name="{{
                                                    $tipo === 'fatura'
                                                        ? 'conta_pagamento_id'
                                                        : 'conta_id'
                                                }}"
                                                class="form-control"
                                                required
                                            >

                                                <option value="">
                                                    Selecione...
                                                </option>

                                                @foreach($contas as $conta)

                                                    <option
                                                        value="{{ $conta->id }}"
                                                    >
                                                        {{ $conta->nome }}
                                                    </option>

                                                @endforeach

                                            </select>

                                        </div>


                                        {{-- FORMA DE PAGAMENTO --}}

                                        @if(
                                            $tipo === 'despesa'
                                            ||
                                            $tipo === 'recorrente'
                                        )

                                            <div class="form-group">

                                                <label class="form-label">
                                                    Forma de pagamento
                                                </label>

                                                <select
                                                    name="forma_pagamento_id"
                                                    class="form-control"
                                                >

                                                    <option value="">
                                                        Selecione...
                                                    </option>

                                                    @foreach(
                                                        $formasPagamento
                                                        as $forma
                                                    )

                                                        <option
                                                            value="{{ $forma->id }}"
                                                        >
                                                            {{ $forma->nome }}
                                                        </option>

                                                    @endforeach

                                                </select>

                                            </div>

                                        @endif


                                        {{-- DATA PAGAMENTO --}}

                                        <div class="form-group">

                                            <label class="form-label">
                                                Data do pagamento
                                            </label>

                                            <input
                                                type="date"
                                                name="data_pagamento"
                                                value="{{ now()->format('Y-m-d') }}"
                                                class="form-control"
                                                required
                                            >

                                        </div>

                                    </div>

                                </div>


                                <div class="modal-footer">

                                    <button
                                        type="button"
                                        class="btn-cancelar-modal"
                                        onclick="fecharModal('{{ $modalId }}')"
                                    >
                                        Cancelar
                                    </button>

                                    <button
                                        type="submit"
                                        class="btn-confirmar"
                                    >
                                        Confirmar pagamento
                                    </button>

                                </div>

                            </form>

                        </div>

                    </div>

                @endforeach

            </tbody>

        </table>

    @else

        <div class="empty-state">

            Nenhuma conta a pagar encontrada para
            {{ $inicioMes->translatedFormat('F/Y') }}.

        </div>

    @endif

</div>


<script>
    function abrirModal(id) {
        const modal =
            document.getElementById(id);

        if (modal) {
            modal.classList.add('ativo');

            document.body.style.overflow =
                'hidden';
        }
    }


    function fecharModal(id) {
        const modal =
            document.getElementById(id);

        if (modal) {
            modal.classList.remove('ativo');

            document.body.style.overflow =
                '';
        }
    }


    document.addEventListener(
        'click',
        function(event) {

            if (
                event.target.classList.contains(
                    'modal-pagamento'
                )
            ) {

                event.target.classList.remove(
                    'ativo'
                );

                document.body.style.overflow =
                    '';
            }
        }
    );


    document.addEventListener(
        'keydown',
        function(event) {

            if (event.key === 'Escape') {

                document
                    .querySelectorAll(
                        '.modal-pagamento.ativo'
                    )
                    .forEach(function(modal) {

                        modal.classList.remove(
                            'ativo'
                        );
                    });

                document.body.style.overflow =
                    '';
            }
        }
    );
</script>

@endsection