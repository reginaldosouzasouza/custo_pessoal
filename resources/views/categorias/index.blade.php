@extends('layouts.custo-pessoal')

@section('title', 'Categorias')

@push('styles')
<style>
    .categorias-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        margin-bottom: 22px;
        flex-wrap: wrap;
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

    .categorias-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
    }

    .categoria-panel {
        padding: 20px;
    }

    .categoria-panel-title {
        margin: 0 0 5px;
        font-size: 17px;
        font-weight: 700;
        color: #111827;
    }

    .categoria-panel-subtitle {
        margin: 0 0 18px;
        color: #6b7280;
        font-size: 12px;
    }

    .categoria-item {
        display: grid;
        grid-template-columns: 42px 1fr auto;
        gap: 12px;
        align-items: center;

        min-height: 68px;

        border-bottom: 1px solid #edf0f3;
    }

    .categoria-item:last-child {
        border-bottom: none;
    }

    .categoria-icon {
        width: 38px;
        height: 38px;

        border-radius: 10px;

        display: flex;
        align-items: center;
        justify-content: center;

        font-size: 18px;
    }

    .categoria-icon.receita {
        background: #dcfce7;
        color: #16a34a;
    }

    .categoria-icon.despesa {
        background: #fee2e2;
        color: #dc2626;
    }

    .categoria-nome {
        font-size: 14px;
        font-weight: 600;
        color: #111827;
    }

    .categoria-info {
        margin-top: 3px;
        font-size: 11px;
        color: #6b7280;
    }

    .categoria-status {
        display: inline-block;
        border-radius: 999px;
        padding: 3px 8px;
        font-size: 10px;
        font-weight: 600;
    }

    .status-ativo {
        background: #dcfce7;
        color: #166534;
    }

    .status-inativo {
        background: #e5e7eb;
        color: #4b5563;
    }

    .categoria-actions {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .btn-acao {
        min-height: 30px;
        padding: 0 9px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        border: 1px solid #dfe3e8;
        border-radius: 6px;

        background: #fff;
        color: #374151;

        font-size: 10px;
        text-decoration: none;

        cursor: pointer;
    }

    .btn-status {
        color: #b45309;
    }

    .categoria-empty {
        color: #6b7280;
        text-align: center;
        padding: 40px 10px;
        font-size: 12px;
    }

    @media(max-width: 950px) {
        .categorias-grid {
            grid-template-columns: 1fr;
        }
    }

    @media(max-width: 650px) {
        .btn-novo {
            width: 100%;
        }

        .categoria-item {
            grid-template-columns: 40px 1fr;
            padding: 10px 0;
        }

        .categoria-actions {
            grid-column: 1 / -1;
            justify-content: flex-end;
        }
    }
</style>
@endpush

@section('content')

<div class="categorias-header">

    <div>
        <h1 class="cp-page-title">
            Categorias
        </h1>

        <p class="cp-page-subtitle">
            Organize suas receitas e despesas.
        </p>
    </div>

    <a
        href="{{ route('categorias.create') }}"
        class="btn-novo"
    >
        + Nova categoria
    </a>

</div>


<div class="categorias-grid">

    {{-- RECEITAS --}}
    <div class="cp-card categoria-panel">

        <h2 class="categoria-panel-title">
            Receitas
        </h2>

        <p class="categoria-panel-subtitle">
            Categorias utilizadas nas entradas.
        </p>


        @forelse($categoriasReceita as $categoria)

            <div class="categoria-item">

                <div class="categoria-icon receita">
                    {{ $categoria->icone ?: '↗' }}
                </div>


                <div>

                    <div class="categoria-nome">
                        {{ $categoria->nome }}
                    </div>

                    <div class="categoria-info">

                        <span class="
                            categoria-status
                            {{ $categoria->ativa
                                ? 'status-ativo'
                                : 'status-inativo' }}
                        ">
                            {{ $categoria->ativa
                                ? 'Ativa'
                                : 'Inativa' }}
                        </span>

                    </div>

                </div>


                <div class="categoria-actions">

                    <a
                        href="{{ route(
                            'categorias.edit',
                            $categoria
                        ) }}"
                        class="btn-acao"
                    >
                        Editar
                    </a>


                    <form
                        action="{{ route(
                            'categorias.status',
                            $categoria
                        ) }}"
                        method="POST"
                        style="margin:0;"
                    >
                        @csrf
                        @method('PATCH')

                        <button
                            type="submit"
                            class="btn-acao btn-status"
                        >
                            {{ $categoria->ativa
                                ? 'Desativar'
                                : 'Ativar' }}
                        </button>

                    </form>

                </div>

            </div>

        @empty

            <div class="categoria-empty">
                Nenhuma categoria de receita cadastrada.
            </div>

        @endforelse

    </div>


    {{-- DESPESAS --}}
    <div class="cp-card categoria-panel">

        <h2 class="categoria-panel-title">
            Despesas
        </h2>

        <p class="categoria-panel-subtitle">
            Categorias utilizadas nos gastos.
        </p>


        @forelse($categoriasDespesa as $categoria)

            <div class="categoria-item">

                <div class="categoria-icon despesa">
                    {{ $categoria->icone ?: '↘' }}
                </div>


                <div>

                    <div class="categoria-nome">
                        {{ $categoria->nome }}
                    </div>

                    <div class="categoria-info">

                        @if($categoria->classificacao === 'essencial')

                            Essencial

                        @elseif(
                            $categoria->classificacao
                            === 'nao_essencial'
                        )

                            Não essencial

                        @else

                            Sem classificação

                        @endif

                        &nbsp;•&nbsp;

                        <span class="
                            categoria-status
                            {{ $categoria->ativa
                                ? 'status-ativo'
                                : 'status-inativo' }}
                        ">
                            {{ $categoria->ativa
                                ? 'Ativa'
                                : 'Inativa' }}
                        </span>

                    </div>

                </div>


                <div class="categoria-actions">

                    <a
                        href="{{ route(
                            'categorias.edit',
                            $categoria
                        ) }}"
                        class="btn-acao"
                    >
                        Editar
                    </a>


                    <form
                        action="{{ route(
                            'categorias.status',
                            $categoria
                        ) }}"
                        method="POST"
                        style="margin:0;"
                    >
                        @csrf
                        @method('PATCH')

                        <button
                            type="submit"
                            class="btn-acao btn-status"
                        >
                            {{ $categoria->ativa
                                ? 'Desativar'
                                : 'Ativar' }}
                        </button>

                    </form>

                </div>

            </div>

        @empty

            <div class="categoria-empty">
                Nenhuma categoria de despesa cadastrada.
            </div>

        @endforelse

    </div>

</div>

@endsection