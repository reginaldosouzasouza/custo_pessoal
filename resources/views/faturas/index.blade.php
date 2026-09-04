@extends('layouts.custo-pessoal')

@section('title', 'Faturas')

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

    .btn-voltar {
        min-height:40px;
        padding:0 15px;
        border-radius:8px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        text-decoration:none;
        font-size:13px;
        font-weight:600;
        background:#fff;
        border:1px solid #dfe3e8;
        color:#374151;
    }

    .filtros-card {
        padding:18px;
        margin-bottom:18px;
    }

    .filtros-grid {
        display:grid;
        grid-template-columns:
            1fr 130px 170px 180px auto;
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

    .table-card {
        padding:18px;
        overflow-x:auto;
    }

    .faturas-table {
        width:100%;
        min-width:920px;
        border-collapse:collapse;
        font-size:12px;
    }

    .faturas-table th {
        padding:10px 9px;
        text-align:left;
        color:#4b5563;
        font-weight:600;
        border-bottom:1px solid #e5e7eb;
    }

    .faturas-table td {
        padding:12px 9px;
        border-bottom:1px solid #edf0f3;
        color:#374151;
        vertical-align:middle;
    }

    .faturas-table tr:last-child td {
        border-bottom:none;
    }

    .valor {
        font-weight:700;
        color:#7653cb;
        white-space:nowrap;
    }

    .status {
        display:inline-block;
        padding:4px 8px;
        border-radius:999px;
        font-size:10px;
        font-weight:700;
        white-space:nowrap;
    }

    .status-aberta {
        background:#eee9ff;
        color:#7653cb;
    }

    .status-fechada {
        background:#fff0df;
        color:#c46c00;
    }

    .status-paga {
        background:#dcfce7;
        color:#166534;
    }

    .status-vencida {
        background:#fee2e2;
        color:#b91c1c;
    }

    .acoes {
        display:flex;
        gap:7px;
        justify-content:flex-end;
        flex-wrap:wrap;
    }

    .btn-detalhes,
    .btn-pagar {
        min-height:34px;
        padding:0 10px;
        border-radius:7px;
        font-size:11px;
        font-weight:600;
        cursor:pointer;
    }

    .btn-detalhes {
        border:1px solid #d9dee5;
        background:#fff;
        color:#374151;
    }

    .btn-pagar {
        border:none;
        background:#16a34a;
        color:#fff;
    }

    .empty-state {
        text-align:center;
        padding:45px 15px;
        color:#6b7280;
    }

    dialog {
        border:none;
        border-radius:12px;
        padding:0;
        width:min(650px, calc(100% - 30px));
        box-shadow:0 20px 60px rgba(15,23,42,.25);
    }

    dialog::backdrop {
        background:rgba(15,23,42,.45);
    }

    .dialog-content {
        padding:22px;
    }

    .dialog-title {
        margin:0 0 5px;
        font-size:20px;
        color:#111827;
    }

    .dialog-subtitle {
        margin:0 0 18px;
        color:#6b7280;
        font-size:12px;
    }

    .parcelas-table {
        width:100%;
        border-collapse:collapse;
        font-size:11px;
    }

    .parcelas-table th,
    .parcelas-table td {
        padding:9px 7px;
        border-bottom:1px solid #edf0f3;
        text-align:left;
    }

    .parcela-valor {
        text-align:right !important;
        font-weight:700;
        color:#7653cb;
    }

    .dialog-actions {
        display:flex;
        justify-content:flex-end;
        gap:10px;
        margin-top:18px;
    }

    .btn-fechar {
        min-height:38px;
        padding:0 14px;
        border-radius:8px;
        border:1px solid #d9dee5;
        background:#fff;
        color:#374151;
        cursor:pointer;
    }

    .pagamento-info {
        font-size:10px;
        color:#166534;
        margin-top:3px;
    }


    .faturas-mobile-list {
        display:none;
    }

    .fatura-mobile-card {
        border:1px solid #e5e7eb;
        border-radius:12px;
        padding:14px;
        background:#fff;
    }

    .fatura-mobile-card + .fatura-mobile-card {
        margin-top:12px;
    }

    .fatura-mobile-top {
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:12px;
        margin-bottom:10px;
    }

    .fatura-mobile-cartao {
        min-width:0;
        flex:1;
        color:#111827;
        font-size:13px;
        font-weight:700;
        line-height:1.35;
        word-break:break-word;
    }

    .fatura-mobile-valor {
        color:#7653cb;
        font-size:14px;
        font-weight:700;
        white-space:nowrap;
        text-align:right;
    }

    .fatura-mobile-dados {
        display:grid;
        grid-template-columns:1fr;
        gap:7px;
        margin-top:8px;
    }

    .fatura-mobile-linha {
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:14px;
        padding-top:7px;
        border-top:1px solid #f1f5f9;
        font-size:11px;
    }

    .fatura-mobile-label {
        color:#6b7280;
        font-weight:600;
        flex-shrink:0;
    }

    .fatura-mobile-conteudo {
        color:#374151;
        text-align:right;
        min-width:0;
        word-break:break-word;
    }

    .fatura-mobile-acoes {
        display:flex;
        flex-wrap:wrap;
        gap:8px;
        margin-top:12px;
        padding-top:10px;
        border-top:1px solid #e5e7eb;
    }

    .fatura-mobile-acoes .btn-detalhes,
    .fatura-mobile-acoes .btn-pagar {
        flex:1 1 0;
        min-height:36px;
    }

    .parcelas-mobile-list {
        display:none;
    }

    .parcela-mobile-card {
        border:1px solid #e5e7eb;
        border-radius:10px;
        padding:11px;
        background:#fff;
    }

    .parcela-mobile-card + .parcela-mobile-card {
        margin-top:9px;
    }

    .parcela-mobile-top {
        display:flex;
        justify-content:space-between;
        gap:10px;
        align-items:flex-start;
        margin-bottom:8px;
    }

    .parcela-mobile-compra {
        min-width:0;
        flex:1;
        color:#111827;
        font-size:11px;
        font-weight:700;
        word-break:break-word;
    }

    .parcela-mobile-valor {
        color:#7653cb;
        font-size:12px;
        font-weight:700;
        white-space:nowrap;
    }

    .parcela-mobile-linha {
        display:flex;
        justify-content:space-between;
        gap:12px;
        padding-top:6px;
        border-top:1px solid #f1f5f9;
        font-size:10px;
    }

    .parcela-mobile-label {
        color:#6b7280;
        font-weight:600;
    }

    .parcela-mobile-conteudo {
        text-align:right;
        color:#374151;
        min-width:0;
        word-break:break-word;
    }

    @media(max-width:900px) {
        .filtros-grid {
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

        .btn-voltar {
            width:100%;
        }

        .table-card {
            padding:12px;
            overflow-x:visible;
        }

        .faturas-table {
            display:none;
        }

        .faturas-mobile-list {
            display:block;
        }

        dialog {
            width:calc(100% - 20px);
        }

        .dialog-content {
            padding:16px;
        }

        .parcelas-table {
            display:none;
        }

        .parcelas-mobile-list {
            display:block;
        }

        .dialog-subtitle {
            line-height:1.5;
        }
    }
</style>
@endpush


@section('content')

<div class="page-header">

    <div>
        <h1 class="cp-page-title">
            Faturas
        </h1>

        <p class="cp-page-subtitle">
            Acompanhe as faturas dos seus cartões.
        </p>
    </div>

    <a
        href="{{ route('cartoes.index') }}"
        class="btn-voltar"
    >
        Voltar para Cartões
    </a>

</div>


<div class="cp-card filtros-card">

    <form
        method="GET"
        action="{{ route('faturas.index') }}"
    >

        <div class="filtros-grid">

            <div class="form-group">

                <label class="form-label">
                    Cartão
                </label>

                <select
                    name="cartao_id"
                    class="form-control"
                >
                    <option value="">
                        Todos
                    </option>

                    @foreach($cartoes as $cartao)

                        <option
                            value="{{ $cartao->id }}"
                            @selected(
                                $cartaoId == $cartao->id
                            )
                        >
                            {{ $cartao->nome }}
                        </option>

                    @endforeach

                </select>

            </div>


            <div class="form-group">

                <label class="form-label">
                    Ano
                </label>

                <select
                    name="ano"
                    class="form-control"
                >

                    @foreach($anos as $anoOpcao)

                        <option
                            value="{{ $anoOpcao }}"
                            @selected(
                                $ano == $anoOpcao
                            )
                        >
                            {{ $anoOpcao }}
                        </option>

                    @endforeach

                </select>

            </div>


            <div class="form-group">

                <label class="form-label">
                    Mês do vencimento
                </label>

                <select
                    name="mes"
                    class="form-control"
                >
                    <option value="">
                        Todos
                    </option>

                    @php
                        $meses = [
                            1 => 'Janeiro',
                            2 => 'Fevereiro',
                            3 => 'Março',
                            4 => 'Abril',
                            5 => 'Maio',
                            6 => 'Junho',
                            7 => 'Julho',
                            8 => 'Agosto',
                            9 => 'Setembro',
                            10 => 'Outubro',
                            11 => 'Novembro',
                            12 => 'Dezembro',
                        ];
                    @endphp

                    @foreach($meses as $numeroMes => $nomeMes)

                        <option
                            value="{{ $numeroMes }}"
                            @selected(
                                (string) request('mes') === (string) $numeroMes
                            )
                        >
                            {{ $nomeMes }}
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
                        value="paga"
                        @selected($situacao === 'paga')
                    >
                        Paga
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
                    href="{{ route('faturas.index') }}"
                    class="btn-limpar"
                >
                    Limpar
                </a>

            </div>

        </div>

    </form>

</div>


<div class="cp-card table-card">

    @if($faturas->count() > 0)

        <table class="faturas-table">

            <thead>
                <tr>
                    <th>Cartão</th>
                    <th>Competência</th>
                    <th>Vencimento</th>
                    <th>Valor</th>
                    <th>Restante</th>
                    <th>Situação</th>
                    <th style="text-align:right;">
                        Ações
                    </th>
                </tr>
            </thead>

            <tbody>

                @foreach($faturas as $fatura)

                    @php
                        $hoje = \Carbon\Carbon::today();

                        $vencida =
                            $fatura->situacao !== 'paga'
                            && $fatura->data_vencimento
                            && $fatura->data_vencimento->lt($hoje);

                        $valorRestante = max(
                            0,
                            (float) $fatura->valor_total
                            - (float) $fatura->valor_pago
                        );

                        $classeStatus =
                            $fatura->situacao === 'paga'
                                ? 'status-paga'
                                : (
                                    $vencida
                                        ? 'status-vencida'
                                        : (
                                            $fatura->situacao === 'fechada'
                                                ? 'status-fechada'
                                                : 'status-aberta'
                                        )
                                );

                        $textoStatus =
                            $fatura->situacao === 'paga'
                                ? 'Paga'
                                : (
                                    $vencida
                                        ? 'Vencida'
                                        : ucfirst($fatura->situacao)
                                );
                    @endphp


                    <tr>

                        <td>
                            <strong>
                                {{ $fatura->cartao?->nome ?? 'Cartão' }}
                            </strong>
                        </td>


                        <td>
                            {{ \Carbon\Carbon::createFromFormat(
                                'Y-m',
                                $fatura->competencia
                            )->format('m/Y') }}
                        </td>


                        <td>
                            {{ $fatura->data_vencimento
                                ? $fatura->data_vencimento->format('d/m/Y')
                                : '-' }}
                        </td>


                        <td class="valor">
                            R$
                            {{ number_format(
                                $fatura->valor_total,
                                2,
                                ',',
                                '.'
                            ) }}
                        </td>


                        <td>
                            R$
                            {{ number_format(
                                $valorRestante,
                                2,
                                ',',
                                '.'
                            ) }}

                            @if($fatura->situacao === 'paga')

                                <div class="pagamento-info">
                                    Pago em
                                    {{ $fatura->data_pagamento
                                        ? $fatura->data_pagamento->format('d/m/Y')
                                        : '-' }}
                                </div>

                            @endif
                        </td>


                        <td>

                            <span class="status {{ $classeStatus }}">
                                {{ $textoStatus }}
                            </span>

                        </td>


                        <td>

                            <div class="acoes">

                                <button
                                    type="button"
                                    class="btn-detalhes"
                                    onclick="
                                        document
                                            .getElementById(
                                                'detalhesFatura{{ $fatura->id }}'
                                            )
                                            .showModal()
                                    "
                                >
                                    Ver detalhes
                                </button>


                                @if($fatura->situacao !== 'paga')

                                    <button
                                        type="button"
                                        class="btn-pagar"
                                        onclick="
                                            document
                                                .getElementById(
                                                    'pagamentoFatura{{ $fatura->id }}'
                                                )
                                                .showModal()
                                        "
                                    >
                                        Pagar
                                    </button>

                                @endif

                            </div>

                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>

        <div class="faturas-mobile-list">

            @foreach($faturas as $fatura)

                @php
                    $hoje = \Carbon\Carbon::today();

                    $vencida =
                        $fatura->situacao !== 'paga'
                        && $fatura->data_vencimento
                        && $fatura->data_vencimento->lt($hoje);

                    $valorRestante = max(
                        0,
                        (float) $fatura->valor_total
                        - (float) $fatura->valor_pago
                    );

                    $classeStatus =
                        $fatura->situacao === 'paga'
                            ? 'status-paga'
                            : (
                                $vencida
                                    ? 'status-vencida'
                                    : (
                                        $fatura->situacao === 'fechada'
                                            ? 'status-fechada'
                                            : 'status-aberta'
                                    )
                            );

                    $textoStatus =
                        $fatura->situacao === 'paga'
                            ? 'Paga'
                            : (
                                $vencida
                                    ? 'Vencida'
                                    : ucfirst($fatura->situacao)
                            );
                @endphp

                <div class="fatura-mobile-card">

                    <div class="fatura-mobile-top">

                        <div class="fatura-mobile-cartao">
                            {{ $fatura->cartao?->nome ?? 'Cartão' }}
                        </div>

                        <div class="fatura-mobile-valor">
                            R$
                            {{ number_format(
                                $fatura->valor_total,
                                2,
                                ',',
                                '.'
                            ) }}
                        </div>

                    </div>


                    <div class="fatura-mobile-dados">

                        <div class="fatura-mobile-linha">
                            <span class="fatura-mobile-label">
                                Competência
                            </span>

                            <span class="fatura-mobile-conteudo">
                                {{ \Carbon\Carbon::createFromFormat(
                                    'Y-m',
                                    $fatura->competencia
                                )->format('m/Y') }}
                            </span>
                        </div>


                        <div class="fatura-mobile-linha">
                            <span class="fatura-mobile-label">
                                Vencimento
                            </span>

                            <span class="fatura-mobile-conteudo">
                                {{ $fatura->data_vencimento
                                    ? $fatura->data_vencimento->format('d/m/Y')
                                    : '-' }}
                            </span>
                        </div>


                        <div class="fatura-mobile-linha">
                            <span class="fatura-mobile-label">
                                Restante
                            </span>

                            <span class="fatura-mobile-conteudo">
                                R$
                                {{ number_format(
                                    $valorRestante,
                                    2,
                                    ',',
                                    '.'
                                ) }}

                                @if($fatura->situacao === 'paga')
                                    <div class="pagamento-info">
                                        Pago em
                                        {{ $fatura->data_pagamento
                                            ? $fatura->data_pagamento->format('d/m/Y')
                                            : '-' }}
                                    </div>
                                @endif
                            </span>
                        </div>


                        <div class="fatura-mobile-linha">
                            <span class="fatura-mobile-label">
                                Situação
                            </span>

                            <span class="fatura-mobile-conteudo">
                                <span class="status {{ $classeStatus }}">
                                    {{ $textoStatus }}
                                </span>
                            </span>
                        </div>

                    </div>


                    <div class="fatura-mobile-acoes">

                        <button
                            type="button"
                            class="btn-detalhes"
                            onclick="
                                document
                                    .getElementById(
                                        'detalhesFatura{{ $fatura->id }}'
                                    )
                                    .showModal()
                            "
                        >
                            Ver detalhes
                        </button>


                        @if($fatura->situacao !== 'paga')

                            <button
                                type="button"
                                class="btn-pagar"
                                onclick="
                                    document
                                        .getElementById(
                                            'pagamentoFatura{{ $fatura->id }}'
                                        )
                                        .showModal()
                                "
                            >
                                Pagar
                            </button>

                        @endif

                    </div>

                </div>

            @endforeach

        </div>



        {{-- =========================================================
             MODAIS FORA DA TABELA
        ========================================================== --}}

        @foreach($faturas as $fatura)

            @php
                $valorRestante = max(
                    0,
                    (float) $fatura->valor_total
                    - (float) $fatura->valor_pago
                );
            @endphp

            {{-- DETALHES --}}
                    <dialog id="detalhesFatura{{ $fatura->id }}">

                        <div class="dialog-content">

                            <h2 class="dialog-title">
                                {{ $fatura->cartao?->nome ?? 'Cartão' }}
                                -
                                {{ \Carbon\Carbon::createFromFormat(
                                    'Y-m',
                                    $fatura->competencia
                                )->format('m/Y') }}
                            </h2>

                            <p class="dialog-subtitle">
                                Compras e parcelas que compõem esta fatura.
                            </p>


                            <table class="parcelas-table">

                                <thead>
                                    <tr>
                                        <th>Compra</th>
                                        <th>Parcela</th>
                                        <th>Categoria</th>
                                        <th class="parcela-valor">
                                            Valor
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @forelse($fatura->parcelas as $parcela)

                                        <tr>

                                            <td>
                                                {{ $parcela
                                                    ->compra
                                                    ?->descricao
                                                    ?? 'Compra' }}
                                            </td>

                                            <td>
                                                {{ $parcela->numero_parcela }}
                                                /
                                                {{ $parcela->total_parcelas }}
                                            </td>

                                            <td>
                                                {{ $parcela
                                                    ->compra
                                                    ?->categoria
                                                    ?->nome
                                                    ?? '-' }}
                                            </td>

                                            <td class="parcela-valor">
                                                R$
                                                {{ number_format(
                                                    $parcela->valor,
                                                    2,
                                                    ',',
                                                    '.'
                                                ) }}
                                            </td>

                                        </tr>

                                    @empty

                                        <tr>
                                            <td colspan="4">
                                                Nenhuma parcela vinculada.
                                            </td>
                                        </tr>

                                    @endforelse

                                </tbody>

                            </table>

                            <div class="parcelas-mobile-list">

                                @forelse($fatura->parcelas as $parcela)

                                    <div class="parcela-mobile-card">

                                        <div class="parcela-mobile-top">

                                            <div class="parcela-mobile-compra">
                                                {{ $parcela
                                                    ->compra
                                                    ?->descricao
                                                    ?? 'Compra' }}
                                            </div>

                                            <div class="parcela-mobile-valor">
                                                R$
                                                {{ number_format(
                                                    $parcela->valor,
                                                    2,
                                                    ',',
                                                    '.'
                                                ) }}
                                            </div>

                                        </div>


                                        <div class="parcela-mobile-linha">

                                            <span class="parcela-mobile-label">
                                                Parcela
                                            </span>

                                            <span class="parcela-mobile-conteudo">
                                                {{ $parcela->numero_parcela }}
                                                /
                                                {{ $parcela->total_parcelas }}
                                            </span>

                                        </div>


                                        <div class="parcela-mobile-linha">

                                            <span class="parcela-mobile-label">
                                                Categoria
                                            </span>

                                            <span class="parcela-mobile-conteudo">
                                                {{ $parcela
                                                    ->compra
                                                    ?->categoria
                                                    ?->nome
                                                    ?? '-' }}
                                            </span>

                                        </div>

                                    </div>

                                @empty

                                    <div class="empty-state">
                                        Nenhuma parcela vinculada.
                                    </div>

                                @endforelse

                            </div>


                            <div class="dialog-actions">

                                <button
                                    type="button"
                                    class="btn-fechar"
                                    onclick="
                                        document
                                            .getElementById(
                                                'detalhesFatura{{ $fatura->id }}'
                                            )
                                            .close()
                                    "
                                >
                                    Fechar
                                </button>

                            </div>

                        </div>

                    </dialog>


                    {{-- PAGAMENTO --}}
                    @if($fatura->situacao !== 'paga')

                        <dialog id="pagamentoFatura{{ $fatura->id }}">

                            <div class="dialog-content">

                                <h2 class="dialog-title">
                                    Pagar fatura
                                </h2>

                                <p class="dialog-subtitle">

                                    {{ $fatura->cartao?->nome ?? 'Cartão' }}

                                    •

                                    R$
                                    {{ number_format(
                                        $valorRestante,
                                        2,
                                        ',',
                                        '.'
                                    ) }}

                                </p>


                                <form
                                    action="{{ route(
                                        'faturas.pagar',
                                        $fatura
                                    ) }}"
                                    method="POST"
                                >
                                    @csrf


                                    <div class="form-group">

                                        <label class="form-label">
                                            Conta de pagamento
                                        </label>

                                        <select
                                            name="conta_pagamento_id"
                                            class="form-control"
                                            required
                                        >

                                            <option value="">
                                                Selecione
                                            </option>

                                            @foreach(
                                                \App\Models\Conta::query()
                                                    ->where(
                                                        'user_id',
                                                        auth()->id()
                                                    )
                                                    ->where(
                                                        'ativa',
                                                        true
                                                    )
                                                    ->orderBy('nome')
                                                    ->get()
                                                as $conta
                                            )

                                                <option
                                                    value="{{ $conta->id }}"
                                                    @selected(
                                                        $fatura
                                                            ->cartao
                                                            ?->conta_pagamento_id
                                                        == $conta->id
                                                    )
                                                >
                                                    {{ $conta->nome }}
                                                </option>

                                            @endforeach

                                        </select>

                                    </div>


                                    <div class="form-group">

                                        <label class="form-label">
                                            Data do pagamento
                                        </label>

                                        <input
                                            type="date"
                                            name="data_pagamento"
                                            class="form-control"
                                            value="{{ now()->format('Y-m-d') }}"
                                            required
                                        >

                                    </div>


                                    <div class="dialog-actions">

                                        <button
                                            type="button"
                                            class="btn-fechar"
                                            onclick="
                                                document
                                                    .getElementById(
                                                        'pagamentoFatura{{ $fatura->id }}'
                                                    )
                                                    .close()
                                            "
                                        >
                                            Cancelar
                                        </button>

                                        <button
                                            type="submit"
                                            class="btn-pagar"
                                        >
                                            Confirmar pagamento
                                        </button>

                                    </div>

                                </form>

                            </div>

                        </dialog>

                    @endif


        @endforeach


    @else

        <div class="empty-state">
            Nenhuma fatura encontrada.
        </div>

    @endif

</div>

@endsection