@extends('layouts.custo-pessoal')

@section('title', 'Compras no Cartão')

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

    .header-actions {
        display:flex;
        gap:10px;
        flex-wrap:wrap;
    }

    .btn-primary,
    .btn-secondary {
        min-height:40px;
        padding:0 15px;
        border-radius:8px;

        display:inline-flex;
        align-items:center;
        justify-content:center;

        text-decoration:none;

        font-size:13px;
        font-weight:600;
    }

    .btn-primary {
        background:#0d6efd;
        color:#fff;
    }

    .btn-secondary {
        background:#fff;
        border:1px solid #dfe3e8;
        color:#374151;
    }

    .table-card {
        padding:18px;
        overflow-x:auto;
    }

    .compras-table {
        width:100%;
        min-width:950px;
        border-collapse:collapse;
        font-size:12px;
    }

    .compras-table th {
        text-align:left;
        padding:10px 9px;

        border-bottom:1px solid #e5e7eb;

        color:#4b5563;
        font-weight:600;
    }

    .compras-table td {
        padding:12px 9px;

        border-bottom:1px solid #edf0f3;

        color:#374151;
        vertical-align:middle;
    }

    .compras-table tr:last-child td {
        border-bottom:none;
    }

    .compra-descricao {
        font-weight:700;
        color:#111827;
    }

    .compra-data {
        color:#6b7280;
        font-size:10px;
        margin-top:3px;
    }

    .valor-total {
        font-weight:700;
        color:#7653cb;
        white-space:nowrap;
    }

    .badge {
        display:inline-block;

        padding:4px 8px;
        border-radius:999px;

        font-size:10px;
        font-weight:700;

        white-space:nowrap;
    }

    .badge-parcelado {
        background:#eee9ff;
        color:#7653cb;
    }

    .badge-avista {
        background:#e0f2fe;
        color:#0369a1;
    }

    .badge-pago {
        background:#dcfce7;
        color:#166534;
    }

    .badge-aberto {
        background:#fff0df;
        color:#b45309;
    }

    .parcelas-resumo {
        color:#6b7280;
        font-size:10px;
        margin-top:4px;
    }

    .btn-detalhes {
        min-height:34px;
        padding:0 10px;

        border-radius:7px;
        border:1px solid #d9dee5;

        background:#fff;
        color:#374151;

        font-size:11px;
        font-weight:600;

        cursor:pointer;
    }

    .empty-state {
        text-align:center;
        padding:50px 20px;
        color:#6b7280;
    }

    .pagination-wrap {
        margin-top:18px;
    }

    dialog {
        border:none;
        border-radius:12px;
        padding:0;

        width:min(
            700px,
            calc(100% - 30px)
        );

        box-shadow:
            0 20px 60px
            rgba(15,23,42,.25);
    }

    dialog::backdrop {
        background:
            rgba(15,23,42,.45);
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

        border-bottom:
            1px solid #edf0f3;

        text-align:left;
    }

    .parcela-valor {
        text-align:right !important;
        font-weight:700;
        color:#7653cb;
    }

    .status {
        display:inline-block;

        padding:4px 8px;
        border-radius:999px;

        font-size:10px;
        font-weight:700;
    }

    .status-paga {
        background:#dcfce7;
        color:#166534;
    }

    .status-pendente {
        background:#fff0df;
        color:#b45309;
    }

    .dialog-total {
        margin-top:14px;
        padding-top:12px;

        border-top:1px solid #e5e7eb;

        display:flex;
        justify-content:flex-end;
        gap:10px;

        font-size:12px;
    }

    .dialog-total strong {
        font-size:14px;
        color:#111827;
    }

    .dialog-actions {
        display:flex;
        justify-content:flex-end;

        margin-top:18px;
    }

    .btn-fechar {
        min-height:38px;
        padding:0 14px;

        border-radius:8px;
        border:1px solid #d9dee5;

        background:#fff;
        color:#374151;

        font-size:12px;
        font-weight:600;

        cursor:pointer;
    }

    @media(max-width:700px) {

        .header-actions {
            width:100%;
        }

        .btn-primary,
        .btn-secondary {
            flex:1;
        }
    }
</style>
@endpush


@section('content')

<div class="page-header">

    <div>

        <h1 class="cp-page-title">
            Compras no Cartão
        </h1>

        <p class="cp-page-subtitle">
            Consulte suas compras e acompanhe as parcelas.
        </p>

    </div>


    <div class="header-actions">

        <a
            href="{{ route('cartoes.index') }}"
            class="btn-secondary"
        >
            Cartões
        </a>

        <a
            href="{{ route('compras-cartao.create') }}"
            class="btn-primary"
        >
            + Nova compra
        </a>

    </div>

</div>


<div class="cp-card table-card">

    @if($compras->count() > 0)

        <table class="compras-table">

            <thead>

                <tr>
                    <th>Compra</th>
                    <th>Cartão</th>
                    <th>Categoria</th>
                    <th>Valor total</th>
                    <th>Parcelas</th>
                    <th>Situação</th>
                    <th style="text-align:right;">
                        Ações
                    </th>
                </tr>

            </thead>


            <tbody>

                @foreach($compras as $compra)

                    @php

                        $totalParcelas =
                            $compra
                                ->parcelas
                                ->count();

                        $parcelasPagas =
                            $compra
                                ->parcelas
                                ->where(
                                    'situacao',
                                    'paga'
                                )
                                ->count();

                        $parcelasPendentes =
                            $totalParcelas
                            - $parcelasPagas;

                        $todasPagas =
                            $totalParcelas > 0
                            && $parcelasPendentes === 0;

                    @endphp


                    <tr>

                        <td>

                            <div class="compra-descricao">
                                {{ $compra->descricao }}
                            </div>

                            <div class="compra-data">

                                {{ $compra
                                    ->data_compra
                                    ->format('d/m/Y') }}

                            </div>

                        </td>


                        <td>

                            {{ $compra
                                ->cartao
                                ?->nome
                                ?? '-' }}

                        </td>


                        <td>

                            @if($compra->categoria?->icone)

                                {{ $compra
                                    ->categoria
                                    ->icone }}

                            @endif

                            {{ $compra
                                ->categoria
                                ?->nome
                                ?? '-' }}

                        </td>


                        <td class="valor-total">

                            R$
                            {{ number_format(
                                $compra->valor_total,
                                2,
                                ',',
                                '.'
                            ) }}

                        </td>


                        <td>

                            @if(
                                $compra->quantidade_parcelas
                                > 1
                            )

                                <span class="
                                    badge
                                    badge-parcelado
                                ">
                                    {{ $compra
                                        ->quantidade_parcelas }}
                                    x
                                </span>

                            @else

                                <span class="
                                    badge
                                    badge-avista
                                ">
                                    1x
                                </span>

                            @endif

                            <div class="parcelas-resumo">

                                {{ $parcelasPagas }}
                                paga(s)

                                •

                                {{ $parcelasPendentes }}
                                aberta(s)

                            </div>

                        </td>


                        <td>

                            @if($todasPagas)

                                <span class="
                                    badge
                                    badge-pago
                                ">
                                    Quitada
                                </span>

                            @else

                                <span class="
                                    badge
                                    badge-aberto
                                ">
                                    Em andamento
                                </span>

                            @endif

                        </td>


                        <td>

                            <div
                                style="
                                    display:flex;
                                    justify-content:flex-end;
                                "
                            >

                                <button
                                    type="button"
                                    class="btn-detalhes"
                                    onclick="
                                        document
                                            .getElementById(
                                                'parcelasCompra{{ $compra->id }}'
                                            )
                                            .showModal()
                                    "
                                >
                                    Ver parcelas
                                </button>

                            </div>

                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>


        <div class="pagination-wrap">

            {{ $compras->links() }}

        </div>

    @else

        <div class="empty-state">

            Nenhuma compra no cartão cadastrada.

        </div>

    @endif

</div>


{{-- =========================================================
     MODAIS FORA DA TABELA
========================================================= --}}

@foreach($compras as $compra)

    <dialog id="parcelasCompra{{ $compra->id }}">

        <div class="dialog-content">

            <h2 class="dialog-title">

                {{ $compra->descricao }}

            </h2>


            <p class="dialog-subtitle">

                {{ $compra->cartao?->nome ?? 'Cartão' }}

                •

                Compra em

                {{ $compra
                    ->data_compra
                    ->format('d/m/Y') }}

                •

                R$

                {{ number_format(
                    $compra->valor_total,
                    2,
                    ',',
                    '.'
                ) }}

            </p>


            <table class="parcelas-table">

                <thead>

                    <tr>
                        <th>Parcela</th>
                        <th>Competência</th>
                        <th>Vencimento</th>
                        <th>Situação</th>
                        <th class="parcela-valor">
                            Valor
                        </th>
                    </tr>

                </thead>


                <tbody>

                    @forelse(
                        $compra
                            ->parcelas
                            ->sortBy(
                                'numero_parcela'
                            )
                        as $parcela
                    )

                        <tr>

                            <td>

                                {{ $parcela
                                    ->numero_parcela }}

                                /

                                {{ $parcela
                                    ->total_parcelas }}

                            </td>


                            <td>

                                {{ \Carbon\Carbon::createFromFormat(
                                    'Y-m',
                                    $parcela->competencia
                                )->format('m/Y') }}

                            </td>


                            <td>

                                {{ $parcela
                                    ->data_vencimento
                                    ->format('d/m/Y') }}

                            </td>


                            <td>

                                @if(
                                    $parcela->situacao
                                    === 'paga'
                                )

                                    <span class="
                                        status
                                        status-paga
                                    ">
                                        Paga
                                    </span>

                                @else

                                    <span class="
                                        status
                                        status-pendente
                                    ">
                                        Aberta
                                    </span>

                                @endif

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

                            <td colspan="5">
                                Nenhuma parcela encontrada.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>


            <div class="dialog-total">

                Total da compra:

                <strong>

                    R$
                    {{ number_format(
                        $compra->valor_total,
                        2,
                        ',',
                        '.'
                    ) }}

                </strong>

            </div>


            <div class="dialog-actions">

                <button
                    type="button"
                    class="btn-fechar"
                    onclick="
                        document
                            .getElementById(
                                'parcelasCompra{{ $compra->id }}'
                            )
                            .close()
                    "
                >
                    Fechar
                </button>

            </div>

        </div>

    </dialog>

@endforeach

@endsection