@extends('layouts.custo-pessoal')

@section('title', 'Nova Receita')

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

    .form-control:focus {
        border-color: #2f80ed;
        box-shadow: 0 0 0 3px rgba(47,128,237,.10);
    }

    textarea.form-control {
        min-height: 100px;
        resize: vertical;
    }

    .form-error {
        color: #dc2626;
        font-size: 11px;
    }

    .form-hint {
        color: #6b7280;
        font-size: 11px;
    }

    .form-actions {
        margin-top: 24px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: wrap;
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

    .recebimento-box {
        display: none;
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
        Nova Receita
    </h1>

    <p class="cp-page-subtitle">
        Cadastre uma entrada ou valor a receber.
    </p>

</div>


<div class="cp-card form-card">

    <form
        action="{{ route('receitas.store') }}"
        method="POST"
    >
        @csrf

        <div class="form-grid">

            <div class="form-group full">

                <label class="form-label">
                    Descrição *
                </label>

                <input
                    type="text"
                    name="descricao"
                    class="form-control"
                    value="{{ old('descricao') }}"
                    placeholder="Ex.: Salário do mês"
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
                    <option value="">
                        Selecione
                    </option>

                    @foreach($categorias as $categoria)

                        <option
                            value="{{ $categoria->id }}"
                            @selected(
                                old('categoria_id')
                                == $categoria->id
                            )
                        >
                            {{ $categoria->icone }}
                            {{ $categoria->nome }}
                        </option>

                    @endforeach
                </select>

                @error('categoria_id')
                    <span class="form-error">
                        {{ $message }}
                    </span>
                @enderror

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
                    value="{{ old('valor') }}"
                    required
                >

                @error('valor')
                    <span class="form-error">
                        {{ $message }}
                    </span>
                @enderror

            </div>


            <div class="form-group">

                <label class="form-label">
                    Data prevista *
                </label>

                <input
                    type="date"
                    name="data_prevista"
                    class="form-control"
                    value="{{ old(
                        'data_prevista',
                        now()->format('Y-m-d')
                    ) }}"
                    required
                >

                @error('data_prevista')
                    <span class="form-error">
                        {{ $message }}
                    </span>
                @enderror

            </div>


            <div class="form-group">

                <label class="form-label">
                    Situação *
                </label>

                <select
                    name="situacao"
                    id="situacaoReceita"
                    class="form-control"
                    required
                >
                    <option
                        value="pendente"
                        @selected(
                            old('situacao', 'pendente')
                            === 'pendente'
                        )
                    >
                        Pendente
                    </option>

                    <option
                        value="recebida"
                        @selected(
                            old('situacao')
                            === 'recebida'
                        )
                    >
                        Recebida
                    </option>
                </select>

            </div>


            <div
                class="form-group recebimento-box"
                id="contaRecebimentoBox"
            >

                <label class="form-label">
                    Conta de recebimento *
                </label>

                <select
                    name="conta_id"
                    id="contaRecebimento"
                    class="form-control"
                >
                    <option value="">
                        Selecione
                    </option>

                    @foreach($contas as $conta)
                        <option
                            value="{{ $conta->id }}"
                            @selected(
                                old('conta_id')
                                == $conta->id
                            )
                        >
                            {{ $conta->nome }}
                        </option>
                    @endforeach
                </select>

                @error('conta_id')
                    <span class="form-error">
                        {{ $message }}
                    </span>
                @enderror

            </div>


            <div
                class="form-group recebimento-box"
                id="dataRecebimentoBox"
            >

                <label class="form-label">
                    Data do recebimento *
                </label>

                <input
                    type="date"
                    name="data_recebimento"
                    id="dataRecebimento"
                    class="form-control"
                    value="{{ old(
                        'data_recebimento',
                        now()->format('Y-m-d')
                    ) }}"
                >

                @error('data_recebimento')
                    <span class="form-error">
                        {{ $message }}
                    </span>
                @enderror

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

                        @foreach(
                            $formasPagamento as $forma
                        )

                            <option
                                value="{{ $forma->id }}"
                                @selected(
                                    old('forma_pagamento_id')
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
                    placeholder="Informação opcional."
                >{{ old('observacao') }}</textarea>

            </div>

        </div>


        <div class="form-actions">

            <a
                href="{{ route('receitas.index') }}"
                class="btn-cancelar"
            >
                Cancelar
            </a>

            <button
                type="submit"
                class="btn-salvar"
            >
                Salvar receita
            </button>

        </div>

    </form>

</div>

@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const situacao =
        document.getElementById('situacaoReceita');

    const contaBox =
        document.getElementById('contaRecebimentoBox');

    const dataBox =
        document.getElementById('dataRecebimentoBox');

    const conta =
        document.getElementById('contaRecebimento');

    const data =
        document.getElementById('dataRecebimento');


    function atualizarRecebimento() {

        const recebida =
            situacao.value === 'recebida';

        contaBox.style.display =
            recebida ? 'flex' : 'none';

        dataBox.style.display =
            recebida ? 'flex' : 'none';

        if (conta) {
            conta.required = recebida;
        }

        if (data) {
            data.required = recebida;
        }
    }

    situacao.addEventListener(
        'change',
        atualizarRecebimento
    );

    atualizarRecebimento();
});
</script>
@endpush