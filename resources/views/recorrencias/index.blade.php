@extends('layouts.custo-pessoal')

@section('title', 'Contas Fixas')

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

    .btn-novo {
        min-height:40px;
        padding:0 15px;
        border-radius:8px;
        background:#0d6efd;
        color:#fff;
        text-decoration:none;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        font-size:13px;
        font-weight:600;
    }

    .table-card {
        padding:18px;
        overflow-x:auto;
    }

    .recorrencias-table {
        width:100%;
        min-width:1050px;
        border-collapse:collapse;
        font-size:12px;
    }

    .recorrencias-table th {
        text-align:left;
        padding:10px 9px;
        border-bottom:1px solid #e5e7eb;
        color:#4b5563;
        font-weight:600;
    }

    .recorrencias-table td {
        padding:12px 9px;
        border-bottom:1px solid #edf0f3;
        color:#374151;
        vertical-align:middle;
    }

    .recorrencias-table tr:last-child td {
        border-bottom:none;
    }

    .descricao {
        font-weight:700;
        color:#111827;
    }

    .descricao small {
        display:block;
        margin-top:3px;
        color:#6b7280;
        font-weight:400;
        font-size:10px;
    }

    .valor {
        font-weight:700;
        white-space:nowrap;
    }

    .valor-despesa {
        color:#ef4444;
    }

    .valor-receita {
        color:#16a34a;
    }

    .badge {
        display:inline-block;
        border-radius:999px;
        padding:4px 8px;
        font-size:10px;
        font-weight:700;
        white-space:nowrap;
    }

    .badge-ativo {
        background:#dcfce7;
        color:#166534;
    }

    .badge-inativo {
        background:#e5e7eb;
        color:#4b5563;
    }

    .badge-despesa {
        background:#fee2e2;
        color:#b91c1c;
    }

    .badge-receita {
        background:#dcfce7;
        color:#166534;
    }

    .badge-essencial {
        background:#e0f2fe;
        color:#0369a1;
    }

    .badge-nao-essencial {
        background:#f3f4f6;
        color:#4b5563;
    }

    .acoes {
        display:flex;
        gap:7px;
        justify-content:flex-end;
    }

    .btn-acao {
        min-height:34px;
        padding:0 10px;
        border-radius:7px;
        border:1px solid #d9dee5;
        background:#fff;
        color:#374151;
        font-size:11px;
        text-decoration:none;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        cursor:pointer;
    }

    .btn-status {
        color:#b45309;
    }

    .empty-state {
        text-align:center;
        padding:50px 20px;
        color:#6b7280;
    }

    .recorrencias-mobile-list {
        display:none;
    }

    .recorrencia-mobile-card {
        border:1px solid #e5e7eb;
        border-radius:12px;
        padding:14px;
        background:#fff;
    }

    .recorrencia-mobile-card + .recorrencia-mobile-card {
        margin-top:12px;
    }

    .recorrencia-mobile-top {
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:12px;
        margin-bottom:10px;
    }

    .recorrencia-mobile-descricao {
        min-width:0;
        flex:1;
        color:#111827;
        font-size:13px;
        font-weight:700;
        line-height:1.35;
        word-break:break-word;
    }

    .recorrencia-mobile-descricao small {
        display:block;
        margin-top:3px;
        color:#6b7280;
        font-size:10px;
        font-weight:400;
    }

    .recorrencia-mobile-valor {
        font-size:14px;
        font-weight:700;
        white-space:nowrap;
        text-align:right;
    }

    .recorrencia-mobile-dados {
        display:grid;
        grid-template-columns:1fr;
        gap:7px;
        margin-top:8px;
    }

    .recorrencia-mobile-linha {
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:14px;
        padding-top:7px;
        border-top:1px solid #f1f5f9;
        font-size:11px;
    }

    .recorrencia-mobile-label {
        color:#6b7280;
        font-weight:600;
        flex-shrink:0;
    }

    .recorrencia-mobile-conteudo {
        color:#374151;
        text-align:right;
        min-width:0;
        word-break:break-word;
    }

    .recorrencia-mobile-acoes {
        display:flex;
        flex-wrap:wrap;
        gap:8px;
        margin-top:12px;
        padding-top:10px;
        border-top:1px solid #e5e7eb;
    }

    .recorrencia-mobile-acoes .btn-acao,
    .recorrencia-mobile-acoes form {
        flex:1 1 0;
    }

    .recorrencia-mobile-acoes .btn-acao {
        width:100%;
        min-height:36px;
    }

    @media(max-width:650px) {
        .btn-novo { width:100%; }
        .table-card { padding:12px; overflow-x:visible; }
        .recorrencias-table { display:none; }
        .recorrencias-mobile-list { display:block; }
        .page-header > .cp-card { width:100%; }
    }
</style>
@endpush


@section('content')

<div class="page-header">

    <div>
        <h1 class="cp-page-title">
            Contas Fixas
        </h1>

        <p class="cp-page-subtitle">
            Cadastre receitas e despesas recorrentes.
        </p>
    </div>

    <div class="cp-card" style="
        padding:18px 20px;
        margin-bottom:18px;
    ">

        <div style="
            font-size:11px;
            color:#6b7280;
            margin-bottom:5px;
        ">
            Previsão mensal das contas fixas ativas
        </div>

        <div style="
            font-size:24px;
            font-weight:700;
            color:#ef4444;
        ">
            R$
            {{ number_format(
                $totalMensalFixas ?? 0,
                2,
                ',',
                '.'
            ) }}
        </div>

        <div style="
            margin-top:4px;
            font-size:10px;
            color:#6b7280;
        ">
            Soma dos valores padrão das despesas recorrentes ativas.
        </div>

    </div>

    <a
        href="{{ route('recorrencias.create') }}"
        class="btn-novo"
    >
        + Nova conta fixa
    </a>

</div>


<div class="cp-card table-card">

    @if($recorrencias->count() > 0)

        <table class="recorrencias-table">

            <thead>
                <tr>
                    <th>Descrição</th>
                    <th>Tipo</th>
                    <th>Categoria</th>
                    <th>Valor</th>
                    <th>Frequência</th>
                    <th>Vencimento</th>
                    <th>Conta padrão</th>
                    <th>Classificação</th>
                    <th>Situação</th>
                    <th style="text-align:right;">
                        Ações
                    </th>
                </tr>
            </thead>

            <tbody>

                @foreach($recorrencias as $recorrencia)

                    <tr>

                        <td class="descricao">

                            {{ $recorrencia->descricao }}

                            @if($recorrencia->tipo_valor === 'variavel')
                                <small>
                                    Valor variável
                                </small>
                            @endif

                        </td>


                        <td>

                            <span class="
                                badge
                                {{ $recorrencia->tipo === 'receita'
                                    ? 'badge-receita'
                                    : 'badge-despesa' }}
                            ">
                                {{ $recorrencia->tipo === 'receita'
                                    ? 'Receita'
                                    : 'Despesa' }}
                            </span>

                        </td>


                        <td>
                            {{ $recorrencia->categoria?->icone }}
                            {{ $recorrencia->categoria?->nome ?? '-' }}
                        </td>


                        <td class="
                            valor
                            {{ $recorrencia->tipo === 'receita'
                                ? 'valor-receita'
                                : 'valor-despesa' }}
                        ">

                            @if($recorrencia->valor_padrao !== null)

                                R$
                                {{ number_format(
                                    $recorrencia->valor_padrao,
                                    2,
                                    ',',
                                    '.'
                                ) }}

                            @else
                                -
                            @endif

                        </td>


                        <td>
                            {{ ucfirst($recorrencia->frequencia) }}
                        </td>


                        <td>

                            @if($recorrencia->dia_vencimento)
                                Dia {{ $recorrencia->dia_vencimento }}
                            @else
                                -
                            @endif

                        </td>


                        <td>
                            {{ $recorrencia->contaPadrao?->nome ?? '-' }}
                        </td>


                        <td>

                            @if($recorrencia->tipo === 'despesa')

                                <span class="
                                    badge
                                    {{ $recorrencia->essencial
                                        ? 'badge-essencial'
                                        : 'badge-nao-essencial' }}
                                ">
                                    {{ $recorrencia->essencial
                                        ? 'Essencial'
                                        : 'Não essencial' }}
                                </span>

                            @else
                                -
                            @endif

                        </td>


                        <td>

                            <span class="
                                badge
                                {{ $recorrencia->ativa
                                    ? 'badge-ativo'
                                    : 'badge-inativo' }}
                            ">
                                {{ $recorrencia->ativa
                                    ? 'Ativa'
                                    : 'Inativa' }}
                            </span>

                        </td>


                        <td>

                            <div class="acoes">

                                <a
                                    href="{{ route(
                                        'recorrencias.edit',
                                        $recorrencia
                                    ) }}"
                                    class="btn-acao"
                                >
                                    Editar
                                </a>


                                <form
                                    method="POST"
                                    action="{{ route(
                                        'recorrencias.status',
                                        $recorrencia
                                    ) }}"
                                    style="margin:0;"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="btn-acao btn-status"
                                    >
                                        {{ $recorrencia->ativa
                                            ? 'Desativar'
                                            : 'Ativar' }}
                                    </button>

                                </form>

                            </div>

                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>

        <div class="recorrencias-mobile-list">

            @foreach($recorrencias as $recorrencia)

                <div class="recorrencia-mobile-card">

                    <div class="recorrencia-mobile-top">

                        <div class="recorrencia-mobile-descricao">

                            {{ $recorrencia->descricao }}

                            @if($recorrencia->tipo_valor === 'variavel')
                                <small>
                                    Valor variável
                                </small>
                            @endif

                        </div>

                        <div class="
                            recorrencia-mobile-valor
                            {{ $recorrencia->tipo === 'receita'
                                ? 'valor-receita'
                                : 'valor-despesa' }}
                        ">

                            @if($recorrencia->valor_padrao !== null)

                                R$
                                {{ number_format(
                                    $recorrencia->valor_padrao,
                                    2,
                                    ',',
                                    '.'
                                ) }}

                            @else
                                -
                            @endif

                        </div>

                    </div>


                    <div class="recorrencia-mobile-dados">

                        <div class="recorrencia-mobile-linha">
                            <span class="recorrencia-mobile-label">
                                Tipo
                            </span>

                            <span class="recorrencia-mobile-conteudo">

                                <span class="
                                    badge
                                    {{ $recorrencia->tipo === 'receita'
                                        ? 'badge-receita'
                                        : 'badge-despesa' }}
                                ">
                                    {{ $recorrencia->tipo === 'receita'
                                        ? 'Receita'
                                        : 'Despesa' }}
                                </span>

                            </span>
                        </div>


                        <div class="recorrencia-mobile-linha">
                            <span class="recorrencia-mobile-label">
                                Categoria
                            </span>

                            <span class="recorrencia-mobile-conteudo">
                                {{ $recorrencia->categoria?->icone }}
                                {{ $recorrencia->categoria?->nome ?? '-' }}
                            </span>
                        </div>


                        <div class="recorrencia-mobile-linha">
                            <span class="recorrencia-mobile-label">
                                Frequência
                            </span>

                            <span class="recorrencia-mobile-conteudo">
                                {{ ucfirst($recorrencia->frequencia) }}
                            </span>
                        </div>


                        <div class="recorrencia-mobile-linha">
                            <span class="recorrencia-mobile-label">
                                Vencimento
                            </span>

                            <span class="recorrencia-mobile-conteudo">
                                @if($recorrencia->dia_vencimento)
                                    Dia {{ $recorrencia->dia_vencimento }}
                                @else
                                    -
                                @endif
                            </span>
                        </div>


                        <div class="recorrencia-mobile-linha">
                            <span class="recorrencia-mobile-label">
                                Conta padrão
                            </span>

                            <span class="recorrencia-mobile-conteudo">
                                {{ $recorrencia->contaPadrao?->nome ?? '-' }}
                            </span>
                        </div>


                        <div class="recorrencia-mobile-linha">
                            <span class="recorrencia-mobile-label">
                                Classificação
                            </span>

                            <span class="recorrencia-mobile-conteudo">

                                @if($recorrencia->tipo === 'despesa')

                                    <span class="
                                        badge
                                        {{ $recorrencia->essencial
                                            ? 'badge-essencial'
                                            : 'badge-nao-essencial' }}
                                    ">
                                        {{ $recorrencia->essencial
                                            ? 'Essencial'
                                            : 'Não essencial' }}
                                    </span>

                                @else
                                    -
                                @endif

                            </span>
                        </div>


                        <div class="recorrencia-mobile-linha">
                            <span class="recorrencia-mobile-label">
                                Situação
                            </span>

                            <span class="recorrencia-mobile-conteudo">

                                <span class="
                                    badge
                                    {{ $recorrencia->ativa
                                        ? 'badge-ativo'
                                        : 'badge-inativo' }}
                                ">
                                    {{ $recorrencia->ativa
                                        ? 'Ativa'
                                        : 'Inativa' }}
                                </span>

                            </span>
                        </div>

                    </div>


                    <div class="recorrencia-mobile-acoes">

                        <a
                            href="{{ route(
                                'recorrencias.edit',
                                $recorrencia
                            ) }}"
                            class="btn-acao"
                        >
                            Editar
                        </a>


                        <form
                            method="POST"
                            action="{{ route(
                                'recorrencias.status',
                                $recorrencia
                            ) }}"
                            style="margin:0;"
                        >
                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="btn-acao btn-status"
                            >
                                {{ $recorrencia->ativa
                                    ? 'Desativar'
                                    : 'Ativar' }}
                            </button>

                        </form>

                    </div>

                </div>

            @endforeach

        </div>


    @else

        <div class="empty-state">
            Nenhuma conta fixa cadastrada ainda.
        </div>

    @endif

</div>

@endsection