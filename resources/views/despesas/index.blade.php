@extends('layouts.custo-pessoal')

@section('title', 'Despesas')

@push('styles')
<style>
    .despesas-header {
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

    .filtros {
        padding: 16px;
        margin-bottom: 18px;

        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: end;
    }

    .filtros-grid {
        display: grid;
        grid-template-columns:
            170px 180px 160px auto;
        gap: 12px;
        align-items: end;
    }

    .filtro-grupo {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .filtro-label {
        font-size: 11px;
        color: #6b7280;
    }

    .filtro-control {
        height: 36px;

        border: 1px solid #d9dee5;
        border-radius: 7px;

        padding: 0 9px;

        background: #fff;

        font-size: 12px;
    }

    .btn-filtrar {
        height: 36px;

        border: none;
        border-radius: 7px;

        padding: 0 14px;

        background: #374151;
        color: #fff;

        cursor: pointer;

        font-size: 12px;
    }

    .despesas-card {
        padding: 18px;
        overflow-x: auto;
    }

    .despesas-table {
        width: 100%;
        min-width: 950px;

        border-collapse: collapse;

        font-size: 12px;
    }

    .despesas-table th,
    .despesas-table td {
        padding: 11px 9px;

        border-bottom: 1px solid #edf0f3;

        text-align: left;
    }

    .despesas-table th {
        color: #6b7280;
        font-weight: 600;
    }

    .valor-despesa {
        color: #dc2626;
        font-weight: 700;
    }

    .status {
        display: inline-block;

        padding: 4px 8px;

        border-radius: 999px;

        font-size: 10px;
        font-weight: 600;
    }

    .status-pendente {
        background: #fff0df;
        color: #b45309;
    }

    .status-paga {
        background: #dcfce7;
        color: #166534;
    }

    .status-atrasada {
        background: #fee2e2;
        color: #991b1b;
    }

    .status-cancelada {
        background: #e5e7eb;
        color: #4b5563;
    }

    .essencial {
        display: inline-block;

        padding: 3px 7px;

        border-radius: 999px;

        font-size: 9px;
        font-weight: 600;

        background: #e0f2fe;
        color: #075985;
    }

    .nao-essencial {
        display: inline-block;

        padding: 3px 7px;

        border-radius: 999px;

        font-size: 9px;
        font-weight: 600;

        background: #f3f4f6;
        color: #6b7280;
    }

    .acoes {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .btn-acao {
        display: inline-flex;
        align-items: center;
        justify-content: center;

        min-height: 30px;

        padding: 0 9px;

        border: 1px solid #dfe3e8;
        border-radius: 6px;

        background: #fff;
        color: #374151;

        text-decoration: none;

        font-size: 10px;

        cursor: pointer;
    }

    .btn-pagar {
        color: #15803d;
        border-color: #bbf7d0;
    }

    .btn-cancelar-despesa {
        color: #b91c1c;
        border-color: #fecaca;
    }

    .empty {
        text-align: center;
        padding: 40px;
        color: #6b7280;
    }

    dialog {
        border: none;
        border-radius: 12px;
        padding: 22px;

        width: min(420px, calc(100% - 30px));

        box-shadow:
            0 15px 50px rgba(0,0,0,.25);
    }

    dialog::backdrop {
        background: rgba(15,23,42,.45);
    }

    .dialog-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-bottom: 14px;
    }

    .dialog-group label {
        font-size: 12px;
        font-weight: 600;
    }

    .dialog-control {
        min-height: 39px;

        border: 1px solid #d9dee5;
        border-radius: 7px;

        padding: 7px 9px;
    }

    .dialog-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;

        margin-top: 18px;
    }

    @media(max-width:650px) {
        .btn-novo {
            width: 100%;
        }
    }

    @media(max-width: 900px) {
        .filtros-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media(max-width: 600px) {
        .filtros-grid {
            grid-template-columns: 1fr;
        }
    }

</style>
@endpush


@section('content')

<div class="despesas-header">

    <div>

        <h1 class="cp-page-title">
            Despesas
        </h1>

        <p class="cp-page-subtitle">
            Controle seus gastos e contas a pagar.
        </p>

    </div>


    <a
        href="{{ route('despesas.create') }}"
        class="btn-novo"
    >
        + Nova despesa
    </a>

</div>


<form
    method="GET"
    class="cp-card filtros"
>

    <div class="filtro-grupo">

        <label class="filtro-label">
            Mês do vencimento
        </label>

        <input
            type="month"
            name="mes"
            class="filtro-control"
            value="{{ request('mes') }}"
        >

    </div>


    <div class="filtro-grupo">

        <label class="filtro-label">
            Situação
        </label>

        <select
            name="situacao"
            class="filtro-control"
        >

            <option value="">
                Todas
            </option>

            <option
                value="pendente"
                @selected(
                    request('situacao') === 'pendente'
                )
            >
                Pendente
            </option>

            <option
                value="paga"
                @selected(
                    request('situacao') === 'paga'
                )
            >
                Paga
            </option>

            <option
                value="cancelada"
                @selected(
                    request('situacao') === 'cancelada'
                )
            >
                Cancelada
            </option>

        </select>

    </div>


    <button
        type="submit"
        class="btn-filtrar"
    >
        Filtrar
    </button>

</form>


<div class="cp-card despesas-card">

    <table class="despesas-table">

        <thead>

            <tr>
                <th>Descrição</th>
                <th>Categoria</th>
                <th>Vencimento</th>
                <th>Pagamento</th>
                <th>Conta</th>
                <th>Valor</th>
                <th>Classificação</th>
                <th>Situação</th>
                <th>Ações</th>
            </tr>

        </thead>


        <tbody>

        @forelse($despesas as $despesa)

            @php
                $atrasada =
                    $despesa->situacao === 'pendente'
                    && $despesa->data_vencimento->isPast()
                    && !$despesa->data_vencimento->isToday();
            @endphp


            <tr>

                <td>
                    {{ $despesa->descricao }}
                </td>


                <td>
                    {{ $despesa->categoria?->nome ?? '-' }}
                </td>


                <td>
                    {{ $despesa
                        ->data_vencimento
                        ->format('d/m/Y') }}
                </td>


                <td>

                    {{ $despesa->data_pagamento
                        ? $despesa
                            ->data_pagamento
                            ->format('d/m/Y')
                        : '-' }}

                </td>


                <td>
                    {{ $despesa->conta?->nome ?? '-' }}
                </td>


                <td class="valor-despesa">

                    R$
                    {{ number_format(
                        $despesa->valor,
                        2,
                        ',',
                        '.'
                    ) }}

                </td>


                <td>

                    @if($despesa->essencial)

                        <span class="essencial">
                            Essencial
                        </span>

                    @else

                        <span class="nao-essencial">
                            Não essencial
                        </span>

                    @endif

                </td>


                <td>

                    @if($despesa->situacao === 'paga')

                        <span class="status status-paga">
                            Paga
                        </span>

                    @elseif(
                        $despesa->situacao === 'cancelada'
                    )

                        <span class="status status-cancelada">
                            Cancelada
                        </span>

                    @elseif($atrasada)

                        <span class="status status-atrasada">
                            Atrasada
                        </span>

                    @else

                        <span class="status status-pendente">
                            Pendente
                        </span>

                    @endif

                </td>


                <td>

                    <div class="acoes">

                        @if($despesa->situacao === 'pendente')

                            <a
                                href="{{ route(
                                    'despesas.edit',
                                    $despesa
                                ) }}"
                                class="btn-acao"
                            >
                                Editar
                            </a>


                            <button
                                type="button"
                                class="btn-acao btn-pagar"
                                onclick="
                                    document
                                        .getElementById(
                                            'pagar-{{ $despesa->id }}'
                                        )
                                        .showModal()
                                "
                            >
                                Pagar
                            </button>


                            <form
                                method="POST"
                                action="{{ route(
                                    'despesas.cancelar',
                                    $despesa
                                ) }}"
                                style="margin:0;"
                                onsubmit="
                                    return confirm(
                                        'Deseja cancelar esta despesa?'
                                    );
                                "
                            >
                                @csrf

                                <button
                                    type="submit"
                                    class="
                                        btn-acao
                                        btn-cancelar-despesa
                                    "
                                >
                                    Cancelar
                                </button>

                            </form>

                        @endif

                    </div>

                </td>

            </tr>


            @if($despesa->situacao === 'pendente')

                <dialog
                    id="pagar-{{ $despesa->id }}"
                >

                    <form
                        method="POST"
                        action="{{ route(
                            'despesas.pagar',
                            $despesa
                        ) }}"
                    >
                        @csrf


                        <h3 style="margin-top:0;">
                            Pagar despesa
                        </h3>


                        <p>
                            {{ $despesa->descricao }}
                        </p>


                        <p style="
                            font-weight:700;
                            color:#dc2626;
                        ">
                            R$
                            {{ number_format(
                                $despesa->valor,
                                2,
                                ',',
                                '.'
                            ) }}
                        </p>


                        <div class="dialog-group">

                            <label>
                                Conta utilizada
                            </label>

                            <select
                                name="conta_id"
                                class="dialog-control"
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
                                    >
                                        {{ $conta->nome }}
                                    </option>

                                @endforeach

                            </select>

                        </div>


                        <div class="dialog-group">

                            <label>
                                Data do pagamento
                            </label>

                            <input
                                type="date"
                                name="data_pagamento"
                                class="dialog-control"
                                value="{{ now()->format('Y-m-d') }}"
                                required
                            >

                        </div>


                        <div class="dialog-actions">

                            <button
                                type="button"
                                class="btn-acao"
                                onclick="
                                    this
                                        .closest('dialog')
                                        .close()
                                "
                            >
                                Voltar
                            </button>


                            <button
                                type="submit"
                                class="btn-acao btn-pagar"
                            >
                                Confirmar pagamento
                            </button>

                        </div>

                    </form>

                </dialog>

            @endif

        @empty

            <tr>

                <td
                    colspan="9"
                    class="empty"
                >
                    Nenhuma despesa cadastrada.
                </td>

            </tr>

        @endforelse

        </tbody>

    </table>

</div>

@endsection