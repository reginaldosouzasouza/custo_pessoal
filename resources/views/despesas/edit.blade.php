@extends('layouts.custo-pessoal')

@section('title', 'Editar Despesa')

@push('styles')
<style>
    .form-page-header {
        margin-bottom: 22px;
    }

    .form-card {
        max-width: 950px;
        padding: 24px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }

    .form-group.full {
        grid-column: 1 / -1;
    }

    .form-label {
        font-size: 13px;
        font-weight: 600;
        color: #374151;
    }

    .form-control {
        width: 100%;
        min-height: 42px;
        border: 1px solid #d9dee5;
        border-radius: 8px;
        padding: 9px 11px;
        background: #fff;
        color: #111827;
        font-size: 14px;
        outline: none;
    }

    textarea.form-control {
        min-height: 100px;
        resize: vertical;
    }

    .form-error {
        color: #dc2626;
        font-size: 11px;
    }

    .form-actions {
        margin-top: 24px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .btn-cancelar,
    .btn-salvar {
        min-height: 40px;
        padding: 0 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        cursor: pointer;
    }

    .btn-cancelar {
        border: 1px solid #d9dee5;
        background: #fff;
        color: #374151;
    }

    .btn-salvar {
        border: none;
        background: #0d6efd;
        color: #fff;
    }

    @media(max-width: 700px) {
        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-group.full {
            grid-column: auto;
        }

        .form-actions {
            flex-direction: column-reverse;
        }

        .btn-cancelar,
        .btn-salvar {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')

<div class="form-page-header">

    <h1 class="cp-page-title">
        Editar Despesa
    </h1>

    <p class="cp-page-subtitle">
        Altere os dados da despesa pendente.
    </p>

</div>


<div class="cp-card form-card">

    <form
        action="{{ route(
            'despesas.update',
            $despesa
        ) }}"
        method="POST"
    >
        @csrf
        @method('PUT')

        <div class="form-grid">

            <div class="form-group full">

                <label class="form-label">
                    Descrição *
                </label>

                <input
                    type="text"
                    name="descricao"
                    class="form-control"
                    value="{{ old(
                        'descricao',
                        $despesa->descricao
                    ) }}"
                    required
                >

                @error('descricao')
                    <span class="form-error">
                        {{ $message }}
                    </span>
                @enderror

            </div>


            <div class="form-group">

                <label class="form-label">
                    Categoria *
                </label>

                <select
                    name="categoria_id"
                    class="form-control"
                    required
                >
                    @foreach($categorias as $categoria)

                        <option
                            value="{{ $categoria->id }}"
                            @selected(
                                old(
                                    'categoria_id',
                                    $despesa->categoria_id
                                )
                                == $categoria->id
                            )
                        >
                            {{ $categoria->icone }}
                            {{ $categoria->nome }}
                        </option>

                    @endforeach
                </select>

            </div>


            <div class="form-group">

                <label class="form-label">
                    Valor *
                </label>

                <input
                    type="number"
                    step="0.01"
                    min="0.01"
                    name="valor"
                    class="form-control"
                    value="{{ old(
                        'valor',
                        $despesa->valor
                    ) }}"
                    required
                >

            </div>


            <div class="form-group">

                <label class="form-label">
                    Data da despesa *
                </label>

                <input
                    type="date"
                    name="data_despesa"
                    class="form-control"
                    value="{{ old(
                        'data_despesa',
                        $despesa
                            ->data_despesa
                            ->format('Y-m-d')
                    ) }}"
                    required
                >

            </div>


            <div class="form-group">

                <label class="form-label">
                    Data de vencimento *
                </label>

                <input
                    type="date"
                    name="data_vencimento"
                    class="form-control"
                    value="{{ old(
                        'data_vencimento',
                        $despesa
                            ->data_vencimento
                            ->format('Y-m-d')
                    ) }}"
                    required
                >

            </div>


            <div class="form-group">

                <label class="form-label">
                    Conta prevista
                </label>

                <select
                    name="conta_id"
                    class="form-control"
                >
                    <option value="">
                        Não informar
                    </option>

                    @foreach($contas as $conta)

                        <option
                            value="{{ $conta->id }}"
                            @selected(
                                old(
                                    'conta_id',
                                    $despesa->conta_id
                                )
                                == $conta->id
                            )
                        >
                            {{ $conta->nome }}
                        </option>

                    @endforeach
                </select>

            </div>


            @if($formasPagamento->count() > 0)

                <div class="form-group">

                    <label class="form-label">
                        Forma de pagamento
                    </label>

                    <select
                        name="forma_pagamento_id"
                        class="form-control"
                    >
                        <option value="">
                            Não informar
                        </option>

                        @foreach($formasPagamento as $forma)

                            <option
                                value="{{ $forma->id }}"
                                @selected(
                                    old(
                                        'forma_pagamento_id',
                                        $despesa
                                            ->forma_pagamento_id
                                    )
                                    == $forma->id
                                )
                            >
                                {{ $forma->nome }}
                            </option>

                        @endforeach
                    </select>

                </div>

            @endif


            <div class="form-group full">

                <label class="form-label">
                    Observação
                </label>

                <textarea
                    name="observacao"
                    class="form-control"
                >{{ old(
                    'observacao',
                    $despesa->observacao
                ) }}</textarea>

            </div>

        </div>


        <div class="form-actions">

            <a
                href="{{ route('despesas.index') }}"
                class="btn-cancelar"
            >
                Cancelar
            </a>

            <button
                type="submit"
                class="btn-salvar"
            >
                Salvar alterações
            </button>

        </div>

    </form>

</div>

@endsection