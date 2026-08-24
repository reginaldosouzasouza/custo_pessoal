@extends('layouts.custo-pessoal')

@section('title', 'Editar Cartão')

@push('styles')
<style>
    .form-page-header {
        margin-bottom: 22px;
    }

    .form-card {
        max-width: 900px;
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

    .form-hint {
        color: #6b7280;
        font-size: 11px;
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

    @media(max-width:700px) {
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
        Editar Cartão
    </h1>

    <p class="cp-page-subtitle">
        Altere os dados cadastrais do cartão.
    </p>

</div>


<div class="cp-card form-card">

    <form
        action="{{ route(
            'cartoes.update',
            $cartao
        ) }}"
        method="POST"
    >
        @csrf
        @method('PUT')

        <div class="form-grid">

            <div class="form-group">

                <label class="form-label">
                    Nome do cartão *
                </label>

                <input
                    type="text"
                    name="nome"
                    class="form-control"
                    value="{{ old(
                        'nome',
                        $cartao->nome
                    ) }}"
                    required
                >

            </div>


            <div class="form-group">

                <label class="form-label">
                    Instituição
                </label>

                <input
                    type="text"
                    name="instituicao"
                    class="form-control"
                    value="{{ old(
                        'instituicao',
                        $cartao->instituicao
                    ) }}"
                >

            </div>


            <div class="form-group">

                <label class="form-label">
                    Últimos 4 dígitos
                </label>

                <input
                    type="text"
                    name="ultimos_digitos"
                    maxlength="4"
                    class="form-control"
                    value="{{ old(
                        'ultimos_digitos',
                        $cartao->ultimos_digitos
                    ) }}"
                >

            </div>


            <div class="form-group">

                <label class="form-label">
                    Limite total *
                </label>

                <input
                    type="number"
                    step="0.01"
                    min="0"
                    name="limite_total"
                    class="form-control"
                    value="{{ old(
                        'limite_total',
                        $cartao->limite_total
                    ) }}"
                    required
                >

            </div>


            <div class="form-group">

                <label class="form-label">
                    Dia do fechamento *
                </label>

                <input
                    type="number"
                    min="1"
                    max="31"
                    name="dia_fechamento"
                    class="form-control"
                    value="{{ old(
                        'dia_fechamento',
                        $cartao->dia_fechamento
                    ) }}"
                    required
                >

            </div>


            <div class="form-group">

                <label class="form-label">
                    Dia do vencimento *
                </label>

                <input
                    type="number"
                    min="1"
                    max="31"
                    name="dia_vencimento"
                    class="form-control"
                    value="{{ old(
                        'dia_vencimento',
                        $cartao->dia_vencimento
                    ) }}"
                    required
                >

            </div>


            <div class="form-group full">

                <label class="form-label">
                    Conta padrão para pagamento
                </label>

                <select
                    name="conta_pagamento_id"
                    class="form-control"
                >
                    <option value="">
                        Não definir
                    </option>

                    @foreach($contas as $conta)

                        <option
                            value="{{ $conta->id }}"
                            @selected(
                                old(
                                    'conta_pagamento_id',
                                    $cartao->conta_pagamento_id
                                )
                                == $conta->id
                            )
                        >
                            {{ $conta->nome }}
                        </option>

                    @endforeach

                </select>

            </div>


            <div class="form-group full">

                <label class="form-label">
                    Observação
                </label>

                <textarea
                    name="observacao"
                    class="form-control"
                >{{ old(
                    'observacao',
                    $cartao->observacao
                ) }}</textarea>

            </div>

        </div>


        <div class="form-actions">

            <a
                href="{{ route('cartoes.index') }}"
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