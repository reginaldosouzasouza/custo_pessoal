@extends('layouts.custo-pessoal')

@section('title', 'Assinaturas')

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
        font-size:12px;
        font-weight:600;
    }

    .btn-primary {
        background:#0d6efd;
        color:#fff;
        border:none;
    }

    .btn-secondary {
        background:#fff;
        color:#374151;
        border:1px solid #d9dee5;
    }

    .resumo-grid {
        display:grid;
        grid-template-columns:repeat(4, minmax(0,1fr));
        gap:15px;
        margin-bottom:18px;
    }

    .resumo-card {
        padding:18px;
    }

    .resumo-label {
        font-size:11px;
        color:#6b7280;
        margin-bottom:6px;
    }

    .resumo-valor {
        font-size:22px;
        font-weight:700;
        color:#111827;
    }

    .assinaturas-card {
        padding:18px;
        overflow-x:auto;
    }

    .assinaturas-table {
        width:100%;
        min-width:1050px;
        border-collapse:collapse;
        font-size:12px;
    }

    .assinaturas-table th,
    .assinaturas-table td {
        padding:11px 9px;
        border-bottom:1px solid #edf0f3;
        text-align:left;
        vertical-align:middle;
    }

    .assinaturas-table th {
        color:#6b7280;
        font-weight:600;
    }

    .badge {
        display:inline-block;
        padding:4px 8px;
        border-radius:999px;
        font-size:10px;
        font-weight:700;
        white-space:nowrap;
    }

    .badge-teste {
        background:#e0f2fe;
        color:#0369a1;
    }

    .badge-ativa {
        background:#dcfce7;
        color:#166534;
    }

    .badge-vencida {
        background:#fee2e2;
        color:#b91c1c;
    }

    .badge-suspensa {
        background:#fff0df;
        color:#b45309;
    }

    .badge-cancelada {
        background:#e5e7eb;
        color:#4b5563;
    }

    .sem-assinatura {
        color:#9ca3af;
        font-style:italic;
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

    .btn-teste {
        color:#0369a1;
        border-color:#bae6fd;
    }

    .btn-plano {
        color:#166534;
        border-color:#bbf7d0;
    }

    .btn-suspender {
        color:#b45309;
    }

    .btn-cancelar {
        color:#b91c1c;
    }

    dialog {
        border:none;
        border-radius:12px;
        padding:0;
        width:min(520px, calc(100% - 30px));
        box-shadow:0 20px 60px rgba(15,23,42,.25);
    }

    dialog::backdrop {
        background:rgba(15,23,42,.45);
    }

    .dialog-content {
        padding:20px;
    }

    .dialog-title {
        margin:0 0 5px;
        font-size:18px;
        color:#111827;
    }

    .dialog-subtitle {
        margin:0 0 18px;
        color:#6b7280;
        font-size:12px;
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

    .opcoes-teste {
        display:grid;
        grid-template-columns:repeat(3,1fr);
        gap:8px;
        margin-bottom:14px;
    }

    .radio-card {
        border:1px solid #d9dee5;
        border-radius:8px;
        padding:10px;
        text-align:center;
        cursor:pointer;
        font-size:12px;
    }

    .radio-card input {
        margin-right:4px;
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

    .btn-confirmar {
        min-height:38px;
        padding:0 14px;
        border-radius:8px;
        border:none;
        background:#0d6efd;
        color:#fff;
        cursor:pointer;
        font-weight:600;
    }

    .assinaturas-mobile {
        display:none;
    }

    @media(max-width:900px) {
        .resumo-grid {
            grid-template-columns:repeat(2,1fr);
        }
    }

    @media(max-width:650px) {
        .header-actions {
            width:100%;
        }

        .btn-primary,
        .btn-secondary {
            flex:1;
        }

        .resumo-grid {
            grid-template-columns:repeat(2,1fr);
            gap:10px;
        }

        .resumo-card {
            padding:14px;
        }

        .resumo-valor {
            font-size:18px;
        }

        .assinaturas-card {
            padding:12px;
            overflow-x:visible;
        }

        .assinaturas-table {
            display:none;
        }

        .assinaturas-mobile {
            display:block;
        }

        .usuario-card {
            border:1px solid #e5e7eb;
            border-radius:12px;
            padding:14px;
            margin-bottom:12px;
        }

        .usuario-card-topo {
            display:flex;
            justify-content:space-between;
            gap:10px;
            align-items:flex-start;
            margin-bottom:10px;
        }

        .usuario-nome {
            font-size:13px;
            font-weight:700;
            color:#111827;
        }

        .usuario-email {
            font-size:10px;
            color:#6b7280;
            margin-top:2px;
        }

        .usuario-linha {
            display:flex;
            justify-content:space-between;
            gap:12px;
            padding-top:7px;
            border-top:1px solid #f1f5f9;
            font-size:11px;
        }

        .usuario-label {
            color:#6b7280;
            font-weight:600;
        }

        .usuario-conteudo {
            text-align:right;
            color:#374151;
        }

        .usuario-acoes {
            display:flex;
            flex-wrap:wrap;
            gap:8px;
            margin-top:12px;
            padding-top:10px;
            border-top:1px solid #e5e7eb;
        }

        .usuario-acoes .btn-acao {
            flex:1 1 auto;
        }

        .opcoes-teste {
            grid-template-columns:1fr;
        }
    }
</style>
@endpush

@section('content')

@php
    $totalUsuarios = $usuarios->count();

    $totalTeste = $usuarios->filter(
        fn($usuario) =>
            $usuario->assinaturaAtual?->situacao === 'teste'
    )->count();

    $totalAtivas = $usuarios->filter(
        fn($usuario) =>
            $usuario->assinaturaAtual?->situacao === 'ativa'
    )->count();

    $totalSemAssinatura = $usuarios->filter(
        fn($usuario) =>
            !$usuario->assinaturaAtual
    )->count();
@endphp

<div class="page-header">

    <div>
        <h1 class="cp-page-title">
            Assinaturas
        </h1>

        <p class="cp-page-subtitle">
            Gerencie testes, planos, vencimentos e bloqueios dos usuários.
        </p>
    </div>

    <div class="header-actions">

        <a
            href="{{ route('admin.planos.index') }}"
            class="btn-secondary"
        >
            Planos
        </a>

    </div>

</div>

<div class="resumo-grid">

    <div class="cp-card resumo-card">
        <div class="resumo-label">
            Usuários ativos
        </div>

        <div class="resumo-valor">
            {{ $totalUsuarios }}
        </div>
    </div>

    <div class="cp-card resumo-card">
        <div class="resumo-label">
            Em teste
        </div>

        <div class="resumo-valor" style="color:#0369a1;">
            {{ $totalTeste }}
        </div>
    </div>

    <div class="cp-card resumo-card">
        <div class="resumo-label">
            Assinaturas ativas
        </div>

        <div class="resumo-valor" style="color:#16a34a;">
            {{ $totalAtivas }}
        </div>
    </div>

    <div class="cp-card resumo-card">
        <div class="resumo-label">
            Sem assinatura
        </div>

        <div class="resumo-valor" style="color:#dc2626;">
            {{ $totalSemAssinatura }}
        </div>
    </div>

</div>


<div class="cp-card assinaturas-card">

    <table class="assinaturas-table">

        <thead>
            <tr>
                <th>Usuário</th>
                <th>Situação</th>
                <th>Plano</th>
                <th>Início</th>
                <th>Vencimento</th>
                <th>Restante</th>
                <th>Ações</th>
            </tr>
        </thead>

        <tbody>

        @foreach($usuarios as $usuario)

            @php
                $assinatura =
                    $usuario->assinaturaAtual;

                $situacao =
                    $assinatura?->situacao;

                $classeStatus =
                    match($situacao) {
                        'teste' => 'badge-teste',
                        'ativa' => 'badge-ativa',
                        'vencida' => 'badge-vencida',
                        'suspensa' => 'badge-suspensa',
                        'cancelada' => 'badge-cancelada',
                        default => '',
                    };

                $diasRestantes = null;

                if (
                    $assinatura
                    && $assinatura->data_vencimento
                ) {
                    $diasRestantes =
                        today()->diffInDays(
                            $assinatura->data_vencimento,
                            false
                        );
                }
            @endphp

            <tr>

                <td>
                    <strong>
                        {{ $usuario->name }}
                    </strong>

                    <div style="font-size:10px;color:#6b7280;margin-top:3px;">
                        {{ $usuario->email ?? $usuario->usuario }}
                    </div>
                </td>

                <td>

                    @if($assinatura)

                        <span class="badge {{ $classeStatus }}">
                            {{ ucfirst($situacao) }}
                        </span>

                    @else

                        <span class="sem-assinatura">
                            Sem assinatura
                        </span>

                    @endif

                </td>

                <td>
                    {{ $assinatura?->plano?->nome ?? '-' }}
                </td>

                <td>
                    {{ $assinatura?->data_inicio
                        ?->format('d/m/Y') ?? '-' }}
                </td>

                <td>
                    {{ $assinatura?->data_vencimento
                        ?->format('d/m/Y') ?? '-' }}
                </td>

                <td>

                    @if($diasRestantes === null)

                        -

                    @elseif($diasRestantes < 0)

                        <span style="color:#dc2626;font-weight:700;">
                            Vencida
                        </span>

                    @elseif($diasRestantes === 0)

                        Hoje

                    @else

                        {{ $diasRestantes }}
                        {{ $diasRestantes === 1 ? 'dia' : 'dias' }}

                    @endif

                </td>

                <td>

                    <div class="acoes">

                        <button
                            type="button"
                            class="btn-acao btn-teste"
                            onclick="
                                document
                                    .getElementById(
                                        'testeUsuario{{ $usuario->id }}'
                                    )
                                    .showModal()
                            "
                        >
                            Liberar teste
                        </button>

                        <button
                            type="button"
                            class="btn-acao btn-plano"
                            onclick="
                                document
                                    .getElementById(
                                        'planoUsuario{{ $usuario->id }}'
                                    )
                                    .showModal()
                            "
                        >
                            Ativar plano
                        </button>

                        @if($assinatura)

                            <button
                                type="button"
                                class="btn-acao"
                                onclick="
                                    document
                                        .getElementById(
                                            'renovarAssinatura{{ $assinatura->id }}'
                                        )
                                        .showModal()
                                "
                            >
                                Renovar
                            </button>

                            @if(
                                !in_array(
                                    $assinatura->situacao,
                                    ['suspensa','cancelada'],
                                    true
                                )
                            )

                                <form
                                    method="POST"
                                    action="{{ route(
                                        'admin.assinaturas.suspender',
                                        $assinatura
                                    ) }}"
                                    style="margin:0;"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="btn-acao btn-suspender"
                                    >
                                        Suspender
                                    </button>
                                </form>

                            @endif

                        @endif

                    </div>

                </td>

            </tr>


            {{-- LIBERAR TESTE --}}

            <dialog id="testeUsuario{{ $usuario->id }}">

                <div class="dialog-content">

                    <h3 class="dialog-title">
                        Liberar teste
                    </h3>

                    <p class="dialog-subtitle">
                        {{ $usuario->name }}
                    </p>

                    <form
                        method="POST"
                        action="{{ route(
                            'admin.assinaturas.teste'
                        ) }}"
                    >
                        @csrf

                        <input
                            type="hidden"
                            name="user_id"
                            value="{{ $usuario->id }}"
                        >

                        <div class="form-group">

                            <label class="form-label">
                                Período
                            </label>

                            <div class="opcoes-teste">

                                <label class="radio-card">
                                    <input
                                        type="radio"
                                        name="dias"
                                        value="7"
                                    >
                                    7 dias
                                </label>

                                <label class="radio-card">
                                    <input
                                        type="radio"
                                        name="dias"
                                        value="15"
                                    >
                                    15 dias
                                </label>

                                <label class="radio-card">
                                    <input
                                        type="radio"
                                        name="dias"
                                        value="30"
                                    >
                                    30 dias
                                </label>

                            </div>

                        </div>

                        <div class="form-group">

                            <label class="form-label">
                                Ou vencimento personalizado
                            </label>

                            <input
                                type="date"
                                name="data_vencimento"
                                class="form-control"
                            >

                        </div>

                        <div class="dialog-actions">

                            <button
                                type="button"
                                class="btn-fechar"
                                onclick="
                                    this.closest('dialog').close()
                                "
                            >
                                Cancelar
                            </button>

                            <button
                                type="submit"
                                class="btn-confirmar"
                            >
                                Liberar teste
                            </button>

                        </div>

                    </form>

                </div>

            </dialog>


            {{-- ATIVAR PLANO --}}

            <dialog id="planoUsuario{{ $usuario->id }}">

                <div class="dialog-content">

                    <h3 class="dialog-title">
                        Ativar plano
                    </h3>

                    <p class="dialog-subtitle">
                        {{ $usuario->name }}
                    </p>

                    <form
                        method="POST"
                        action="{{ route(
                            'admin.assinaturas.ativar'
                        ) }}"
                    >
                        @csrf

                        <input
                            type="hidden"
                            name="user_id"
                            value="{{ $usuario->id }}"
                        >

                        <div class="form-group">

                            <label class="form-label">
                                Plano
                            </label>

                            <select
                                name="plano_id"
                                class="form-control"
                                required
                            >

                                <option value="">
                                    Selecione
                                </option>

                                @foreach($planos as $plano)

                                    <option value="{{ $plano->id }}">

                                        {{ $plano->nome }}

                                        -
                                        R$
                                        {{ number_format(
                                            $plano->valor,
                                            2,
                                            ',',
                                            '.'
                                        ) }}

                                    </option>

                                @endforeach

                            </select>

                        </div>

                        <div class="form-group">

                            <label class="form-label">
                                Data inicial
                            </label>

                            <input
                                type="date"
                                name="data_inicio"
                                class="form-control"
                                value="{{ now()->format('Y-m-d') }}"
                            >

                        </div>

                        <div class="dialog-actions">

                            <button
                                type="button"
                                class="btn-fechar"
                                onclick="
                                    this.closest('dialog').close()
                                "
                            >
                                Cancelar
                            </button>

                            <button
                                type="submit"
                                class="btn-confirmar"
                            >
                                Ativar plano
                            </button>

                        </div>

                    </form>

                </div>

            </dialog>


            @if($assinatura)

                {{-- RENOVAR --}}

                <dialog id="renovarAssinatura{{ $assinatura->id }}">

                    <div class="dialog-content">

                        <h3 class="dialog-title">
                            Renovar assinatura
                        </h3>

                        <p class="dialog-subtitle">
                            {{ $usuario->name }}
                        </p>

                        <form
                            method="POST"
                            action="{{ route(
                                'admin.assinaturas.renovar',
                                $assinatura
                            ) }}"
                        >
                            @csrf

                            <div class="form-group">

                                <label class="form-label">
                                    Plano
                                </label>

                                <select
                                    name="plano_id"
                                    class="form-control"
                                >

                                    <option value="">
                                        Manter atual
                                    </option>

                                    @foreach($planos as $plano)

                                        <option value="{{ $plano->id }}">
                                            {{ $plano->nome }}
                                        </option>

                                    @endforeach

                                </select>

                            </div>

                            <div class="form-group">

                                <label class="form-label">
                                    Acrescentar dias
                                </label>

                                <input
                                    type="number"
                                    name="dias"
                                    min="1"
                                    max="3650"
                                    class="form-control"
                                    placeholder="Ex.: 15"
                                >

                            </div>

                            <div class="form-group">

                                <label class="form-label">
                                    Ou nova data de vencimento
                                </label>

                                <input
                                    type="date"
                                    name="data_vencimento"
                                    class="form-control"
                                >

                            </div>

                            <div class="dialog-actions">

                                <button
                                    type="button"
                                    class="btn-fechar"
                                    onclick="
                                        this.closest('dialog').close()
                                    "
                                >
                                    Cancelar
                                </button>

                                <button
                                    type="submit"
                                    class="btn-confirmar"
                                >
                                    Renovar
                                </button>

                            </div>

                        </form>

                    </div>

                </dialog>

            @endif

        @endforeach

        </tbody>

    </table>


    <div class="assinaturas-mobile">

        @foreach($usuarios as $usuario)

            @php
                $assinatura =
                    $usuario->assinaturaAtual;

                $situacao =
                    $assinatura?->situacao;

                $classeStatus =
                    match($situacao) {
                        'teste' => 'badge-teste',
                        'ativa' => 'badge-ativa',
                        'vencida' => 'badge-vencida',
                        'suspensa' => 'badge-suspensa',
                        'cancelada' => 'badge-cancelada',
                        default => '',
                    };

                $diasRestantes = null;

                if (
                    $assinatura
                    && $assinatura->data_vencimento
                ) {
                    $diasRestantes =
                        today()->diffInDays(
                            $assinatura->data_vencimento,
                            false
                        );
                }
            @endphp

            <div class="usuario-card">

                <div class="usuario-card-topo">

                    <div>
                        <div class="usuario-nome">
                            {{ $usuario->name }}
                        </div>

                        <div class="usuario-email">
                            {{ $usuario->email ?? $usuario->usuario }}
                        </div>
                    </div>

                    @if($assinatura)

                        <span class="badge {{ $classeStatus }}">
                            {{ ucfirst($situacao) }}
                        </span>

                    @else

                        <span class="sem-assinatura">
                            Sem assinatura
                        </span>

                    @endif

                </div>

                <div class="usuario-linha">
                    <span class="usuario-label">
                        Plano
                    </span>

                    <span class="usuario-conteudo">
                        {{ $assinatura?->plano?->nome ?? '-' }}
                    </span>
                </div>

                <div class="usuario-linha">
                    <span class="usuario-label">
                        Vencimento
                    </span>

                    <span class="usuario-conteudo">
                        {{ $assinatura?->data_vencimento
                            ?->format('d/m/Y') ?? '-' }}
                    </span>
                </div>

                <div class="usuario-linha">
                    <span class="usuario-label">
                        Restante
                    </span>

                    <span class="usuario-conteudo">

                        @if($diasRestantes === null)

                            -

                        @elseif($diasRestantes < 0)

                            Vencida

                        @elseif($diasRestantes === 0)

                            Hoje

                        @else

                            {{ $diasRestantes }} dias

                        @endif

                    </span>
                </div>

                <div class="usuario-acoes">

                    <button
                        type="button"
                        class="btn-acao btn-teste"
                        onclick="
                            document
                                .getElementById(
                                    'testeUsuario{{ $usuario->id }}'
                                )
                                .showModal()
                        "
                    >
                        Liberar teste
                    </button>

                    <button
                        type="button"
                        class="btn-acao btn-plano"
                        onclick="
                            document
                                .getElementById(
                                    'planoUsuario{{ $usuario->id }}'
                                )
                                .showModal()
                        "
                    >
                        Ativar plano
                    </button>

                </div>

            </div>

        @endforeach

    </div>

</div>

@endsection