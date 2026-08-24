@extends('layouts.custo-pessoal')

@section('title', 'Receitas')

@push('styles')
<style>
    .receitas-header {
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
        color: white;
        cursor: pointer;
        font-size: 12px;
    }

    .receitas-card {
        padding: 18px;
        overflow-x: auto;
    }

    .receitas-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 850px;
        font-size: 12px;
    }

    .receitas-table th,
    .receitas-table td {
        padding: 11px 9px;
        border-bottom: 1px solid #edf0f3;
        text-align: left;
    }

    .receitas-table th {
        color: #6b7280;
        font-weight: 600;
    }

    .valor-receita {
        color: #16a34a;
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

    .status-recebida {
        background: #dcfce7;
        color: #166534;
    }

    .status-atrasada {
        background: #fee2e2;
        color: #991b1b;
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
        background: white;
        color: #374151;
        text-decoration: none;
        font-size: 10px;
        cursor: pointer;
    }

    .btn-receber {
        color: #15803d;
        border-color: #bbf7d0;
    }

    .empty {
        text-align: center;
        padding: 40px;
        color: #6b7280;
    }

    @media(max-width: 650px) {
        .btn-novo {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')

<div class="receitas-header">

    <div>
        <h1 class="cp-page-title">
            Receitas
        </h1>

        <p class="cp-page-subtitle">
            Controle suas entradas e valores a receber.
        </p>
    </div>

    <a
        href="{{ route('receitas.create') }}"
        class="btn-novo"
    >
        + Nova receita
    </a>

</div>


<form method="GET"
      class="cp-card filtros">

    <div class="filtro-grupo">
        <label class="filtro-label">
            Mês
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

            <option value="pendente"
                @selected(request('situacao') === 'pendente')>
                Pendente
            </option>

            <option value="recebida"
                @selected(request('situacao') === 'recebida')>
                Recebida
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


<div class="cp-card receitas-card">

    <table class="receitas-table">

        <thead>
            <tr>
                <th>Descrição</th>
                <th>Categoria</th>
                <th>Prevista</th>
                <th>Recebimento</th>
                <th>Conta</th>
                <th>Valor</th>
                <th>Situação</th>
                <th>Ações</th>
            </tr>
        </thead>

        <tbody>

        @forelse($receitas as $receita)

            @php
                $atrasada =
                    $receita->situacao === 'pendente'
                    && $receita->data_prevista->isPast()
                    && !$receita->data_prevista->isToday();
            @endphp

            <tr>

                <td>
                    {{ $receita->descricao }}
                </td>

                <td>
                    {{ $receita->categoria?->nome ?? '-' }}
                </td>

                <td>
                    {{ $receita->data_prevista->format('d/m/Y') }}
                </td>

                <td>
                    {{ $receita->data_recebimento
                        ? $receita->data_recebimento->format('d/m/Y')
                        : '-' }}
                </td>

                <td>
                    {{ $receita->conta?->nome ?? '-' }}
                </td>

                <td class="valor-receita">
                    R$
                    {{ number_format(
                        $receita->valor,
                        2,
                        ',',
                        '.'
                    ) }}
                </td>

                <td>

                    @if($receita->situacao === 'recebida')

                        <span class="status status-recebida">
                            Recebida
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

                        @if($receita->situacao === 'pendente')

                            <a
                                href="{{ route(
                                    'receitas.edit',
                                    $receita
                                ) }}"
                                class="btn-acao"
                            >
                                Editar
                            </a>

                            <button
                                type="button"
                                class="btn-acao btn-receber"
                                onclick="
                                    document
                                    .getElementById(
                                        'receber-{{ $receita->id }}'
                                    )
                                    .showModal()
                                "
                            >
                                Receber
                            </button>

                        @endif

                    </div>

                </td>

            </tr>


            @if($receita->situacao === 'pendente')

                <dialog id="receber-{{ $receita->id }}">

                    <form
                        method="POST"
                        action="{{ route(
                            'receitas.receber',
                            $receita
                        ) }}"
                    >
                        @csrf

                        <h3>
                            Receber receita
                        </h3>

                        <p>
                            {{ $receita->descricao }}
                        </p>

                        <div style="margin-bottom:12px;">
                            <label>
                                Conta
                            </label>

                            <select
                                name="conta_id"
                                required
                            >
                                @foreach(
                                    \App\Models\Conta::where(
                                        'user_id',
                                        auth()->id()
                                    )
                                    ->where('ativa', true)
                                    ->orderBy('nome')
                                    ->get()
                                    as $conta
                                )
                                    <option value="{{ $conta->id }}">
                                        {{ $conta->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div style="margin-bottom:12px;">
                            <label>
                                Data do recebimento
                            </label>

                            <input
                                type="date"
                                name="data_recebimento"
                                value="{{ now()->format('Y-m-d') }}"
                                required
                            >
                        </div>

                        <div style="display:flex;gap:8px;">

                            <button type="submit">
                                Confirmar
                            </button>

                            <button
                                type="button"
                                onclick="
                                    this
                                    .closest('dialog')
                                    .close()
                                "
                            >
                                Cancelar
                            </button>

                        </div>

                    </form>

                </dialog>

            @endif

        @empty

            <tr>
                <td
                    colspan="8"
                    class="empty"
                >
                    Nenhuma receita cadastrada.
                </td>
            </tr>

        @endforelse

        </tbody>

    </table>

</div>

@endsection