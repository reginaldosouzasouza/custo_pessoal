@extends('layouts.custo-pessoal')

@section('title', 'Planos')

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

    .grid-planos {
        display:grid;
        grid-template-columns:360px 1fr;
        gap:18px;
        align-items:start;
    }

    .card-form,
    .card-lista {
        padding:18px;
    }

    .form-group {
        display:flex;
        flex-direction:column;
        gap:6px;
        margin-bottom:14px;
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

    textarea.form-control {
        min-height:90px;
        resize:vertical;
    }

    .btn-primary {
        min-height:40px;
        border:0;
        border-radius:8px;
        padding:0 15px;
        background:#0d6efd;
        color:#fff;
        font-size:12px;
        font-weight:600;
        cursor:pointer;
    }

    .planos-table {
        width:100%;
        border-collapse:collapse;
        font-size:12px;
    }

    .planos-table th,
    .planos-table td {
        padding:11px 9px;
        border-bottom:1px solid #edf0f3;
        text-align:left;
        vertical-align:middle;
    }

    .planos-table th {
        color:#6b7280;
        font-weight:600;
    }

    .valor {
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
    }

    .badge-ativo {
        background:#dcfce7;
        color:#166534;
    }

    .badge-inativo {
        background:#e5e7eb;
        color:#4b5563;
    }

    .acoes {
        display:flex;
        gap:7px;
        flex-wrap:wrap;
    }

    .btn-acao {
        min-height:32px;
        padding:0 10px;
        border-radius:7px;
        border:1px solid #d9dee5;
        background:#fff;
        color:#374151;
        font-size:11px;
        cursor:pointer;
    }

    .btn-status {
        color:#b45309;
    }

    .empty-state {
        text-align:center;
        padding:35px 10px;
        color:#6b7280;
    }

    dialog {
        border:none;
        border-radius:12px;
        padding:0;
        width:min(500px, calc(100% - 30px));
        box-shadow:0 20px 60px rgba(15,23,42,.25);
    }

    dialog::backdrop {
        background:rgba(15,23,42,.45);
    }

    .dialog-content {
        padding:20px;
    }

    .dialog-actions {
        display:flex;
        justify-content:flex-end;
        gap:8px;
        margin-top:18px;
    }

    .btn-fechar {
        min-height:38px;
        padding:0 14px;
        border-radius:8px;
        border:1px solid #d9dee5;
        background:#fff;
        cursor:pointer;
    }

    @media(max-width:900px) {
        .grid-planos {
            grid-template-columns:1fr;
        }
    }
</style>
@endpush

@section('content')

<div class="page-header">

    <div>
        <h1 class="cp-page-title">
            Planos
        </h1>

        <p class="cp-page-subtitle">
            Cadastre e gerencie os planos do SGA Finanças.
        </p>
    </div>

    <a
        href="{{ route('admin.assinaturas.index') }}"
        class="btn-primary"
        style="text-decoration:none;display:inline-flex;align-items:center;"
    >
        Ver Assinaturas
    </a>

</div>





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
            <div>{{ $erro }}</div>
        @endforeach

    </div>

@endif


<div class="grid-planos">

    <div class="cp-card card-form">

        <h2
            style="
                margin:0 0 16px;
                font-size:16px;
                color:#111827;
            "
        >
            Novo plano
        </h2>

        <form
            method="POST"
            action="{{ route('admin.planos.store') }}"
        >
            @csrf

            <div class="form-group">
                <label class="form-label">
                    Nome
                </label>

                <input
                    type="text"
                    name="nome"
                    class="form-control"
                    value="{{ old('nome') }}"
                    placeholder="Ex.: Mensal"
                    required
                >
            </div>

            <div class="form-group">
                <label class="form-label">
                    Duração em meses
                </label>

                <input
                    type="number"
                    name="duracao_meses"
                    class="form-control"
                    value="{{ old('duracao_meses') }}"
                    min="1"
                    max="120"
                    required
                >
            </div>

            <div class="form-group">
                <label class="form-label">
                    Valor
                </label>

                <input
                    type="number"
                    name="valor"
                    class="form-control"
                    step="0.01"
                    min="0"
                    value="{{ old('valor') }}"
                    required
                >
            </div>

            <div class="form-group">
                <label class="form-label">
                    Descrição
                </label>

                <textarea
                    name="descricao"
                    class="form-control"
                    placeholder="Descrição opcional do plano"
                >{{ old('descricao') }}</textarea>
            </div>

            <button
                type="submit"
                class="btn-primary"
            >
                Salvar plano
            </button>

        </form>

    </div>


    <div class="cp-card card-lista">

        <h2
            style="
                margin:0 0 16px;
                font-size:16px;
                color:#111827;
            "
        >
            Planos cadastrados
        </h2>


        @if($planos->count() > 0)

            <div style="overflow-x:auto;">

                <table class="planos-table">

                    <thead>
                        <tr>
                            <th>Plano</th>
                            <th>Duração</th>
                            <th>Valor</th>
                            <th>Situação</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($planos as $plano)

                            <tr>

                                <td>
                                    <strong>
                                        {{ $plano->nome }}
                                    </strong>

                                    @if($plano->descricao)
                                        <div
                                            style="
                                                margin-top:3px;
                                                font-size:10px;
                                                color:#6b7280;
                                            "
                                        >
                                            {{ $plano->descricao }}
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    {{ $plano->duracao_meses }}
                                    {{ $plano->duracao_meses === 1
                                        ? 'mês'
                                        : 'meses' }}
                                </td>

                                <td class="valor">
                                    R$
                                    {{ number_format(
                                        $plano->valor,
                                        2,
                                        ',',
                                        '.'
                                    ) }}
                                </td>

                                <td>
                                    <span class="
                                        badge
                                        {{ $plano->ativo
                                            ? 'badge-ativo'
                                            : 'badge-inativo' }}
                                    ">
                                        {{ $plano->ativo
                                            ? 'Ativo'
                                            : 'Inativo' }}
                                    </span>
                                </td>

                                <td>

                                    <div class="acoes">

                                        <button
                                            type="button"
                                            class="btn-acao"
                                            onclick="
                                                document
                                                    .getElementById(
                                                        'editarPlano{{ $plano->id }}'
                                                    )
                                                    .showModal()
                                            "
                                        >
                                            Editar
                                        </button>


                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'admin.planos.status',
                                                $plano
                                            ) }}"
                                            style="margin:0;"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="btn-acao btn-status"
                                            >
                                                {{ $plano->ativo
                                                    ? 'Desativar'
                                                    : 'Ativar' }}
                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>


                            <dialog id="editarPlano{{ $plano->id }}">

                                <div class="dialog-content">

                                    <h3 style="margin-top:0;">
                                        Editar plano
                                    </h3>

                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'admin.planos.update',
                                            $plano
                                        ) }}"
                                    >
                                        @csrf
                                        @method('PUT')

                                        <div class="form-group">
                                            <label class="form-label">
                                                Nome
                                            </label>

                                            <input
                                                type="text"
                                                name="nome"
                                                class="form-control"
                                                value="{{ $plano->nome }}"
                                                required
                                            >
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label">
                                                Duração em meses
                                            </label>

                                            <input
                                                type="number"
                                                name="duracao_meses"
                                                class="form-control"
                                                value="{{ $plano->duracao_meses }}"
                                                min="1"
                                                max="120"
                                                required
                                            >
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label">
                                                Valor
                                            </label>

                                            <input
                                                type="number"
                                                name="valor"
                                                class="form-control"
                                                step="0.01"
                                                min="0"
                                                value="{{ $plano->valor }}"
                                                required
                                            >
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label">
                                                Descrição
                                            </label>

                                            <textarea
                                                name="descricao"
                                                class="form-control"
                                            >{{ $plano->descricao }}</textarea>
                                        </div>

                                        <div class="dialog-actions">

                                            <button
                                                type="button"
                                                class="btn-fechar"
                                                onclick="
                                                    document
                                                        .getElementById(
                                                            'editarPlano{{ $plano->id }}'
                                                        )
                                                        .close()
                                                "
                                            >
                                                Cancelar
                                            </button>

                                            <button
                                                type="submit"
                                                class="btn-primary"
                                            >
                                                Salvar alterações
                                            </button>

                                        </div>

                                    </form>

                                </div>

                            </dialog>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @else

            <div class="empty-state">
                Nenhum plano cadastrado ainda.
            </div>

        @endif

    </div>

</div>

@endsection