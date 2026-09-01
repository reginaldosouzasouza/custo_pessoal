@extends('layouts.custo-pessoal')

@section('title', 'Nova Conta Fixa')

@push('styles')
<style>
    .form-header {
        margin-bottom:22px;
    }

    .form-card {
        max-width:950px;
        padding:24px;
    }

    .form-grid {
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:18px;
    }

    .form-group {
        display:flex;
        flex-direction:column;
        gap:7px;
    }

    .form-group.full {
        grid-column:1 / -1;
    }

    .form-label {
        font-size:13px;
        font-weight:600;
        color:#374151;
    }

    .form-control {
        width:100%;
        min-height:42px;
        border:1px solid #d9dee5;
        border-radius:8px;
        padding:9px 11px;
        background:#fff;
        color:#111827;
        font-size:14px;
    }

    textarea.form-control {
        min-height:95px;
        resize:vertical;
    }

    .form-error {
        color:#dc2626;
        font-size:11px;
    }

    .form-hint {
        color:#6b7280;
        font-size:11px;
    }

    .form-actions {
        margin-top:24px;
        display:flex;
        justify-content:flex-end;
        gap:10px;
    }

    .btn-cancelar,
    .btn-salvar {
        min-height:40px;
        padding:0 16px;
        border-radius:8px;
        font-size:13px;
        font-weight:600;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        text-decoration:none;
        cursor:pointer;
    }

    .btn-cancelar {
        border:1px solid #d9dee5;
        background:#fff;
        color:#374151;
    }

    .btn-salvar {
        border:none;
        background:#0d6efd;
        color:#fff;
    }

    @media(max-width:700px) {
        .form-grid {
            grid-template-columns:1fr;
        }

        .form-group.full {
            grid-column:auto;
        }

        .form-actions {
            flex-direction:column-reverse;
        }

        .btn-cancelar,
        .btn-salvar {
            width:100%;
        }
    }
</style>
@endpush


@section('content')

<div class="form-header">

    <h1 class="cp-page-title">
        Nova Conta Fixa
    </h1>

    <p class="cp-page-subtitle">
        Cadastre uma receita ou despesa recorrente.
    </p>

</div>


<div class="cp-card form-card">

    <form
        action="{{ route('recorrencias.store') }}"
        method="POST"
    >
        @csrf

        <div class="form-grid">

            <div class="form-group">

                <label class="form-label">
                    Tipo *
                </label>

                <select
                    name="tipo"
                    id="tipoRecorrencia"
                    class="form-control"
                    required
                >
                    <option value="despesa"
                        @selected(old('tipo', 'despesa') === 'despesa')>
                        Despesa
                    </option>

                    <option value="receita"
                        @selected(old('tipo') === 'receita')>
                        Receita
                    </option>
                </select>

            </div>


            <div class="form-group">

                <label class="form-label">
                    Categoria *
                </label>

                <select
                    name="categoria_id"
                    id="categoriaRecorrencia"
                    class="form-control"
                    required
                >

                    <option value="">
                        Selecione
                    </option>

                    @foreach($categorias as $categoria)

                        <option
                            value="{{ $categoria->id }}"
                            data-tipo="{{ $categoria->tipo }}"
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


            <div class="form-group full">

                <label class="form-label">
                    Descrição *
                </label>

                <input
                    type="text"
                    name="descricao"
                    class="form-control"
                    value="{{ old('descricao') }}"
                    placeholder="Ex.: Internet"
                    required
                >

            </div>


            <div class="form-group">

                <label class="form-label">
                    Tipo de valor *
                </label>

                <select
                    name="tipo_valor"
                    id="tipoValor"
                    class="form-control"
                    required
                >
                    <option value="fixo"
                        @selected(old('tipo_valor', 'fixo') === 'fixo')>
                        Fixo
                    </option>

                    <option value="variavel"
                        @selected(old('tipo_valor') === 'variavel')>
                        Variável
                    </option>
                </select>

            </div>


            <div class="form-group">

                <label class="form-label">
                    Valor padrão
                </label>

                <input
                    type="number"
                    step="0.01"
                    min="0"
                    name="valor_padrao"
                    id="valorPadrao"
                    class="form-control"
                    value="{{ old('valor_padrao') }}"
                >

                <span class="form-hint">
                    Para valor variável, pode deixar em branco.
                </span>

                @error('valor_padrao')
                    <span class="form-error">
                        {{ $message }}
                    </span>
                @enderror

            </div>


            <div class="form-group">

                <label class="form-label">
                    Frequência *
                </label>

                <select
                    name="frequencia"
                    id="frequenciaRecorrencia"
                    class="form-control"
                    required
                >
                    <option value="diaria"
                        @selected(old('frequencia') === 'diaria')>
                        Diária
                    </option>

                    <option value="cada_3_dias"
                        @selected(old('frequencia') === 'cada_3_dias')>
                        A cada 3 dias
                    </option>

                    <option value="cada_5_dias"
                        @selected(old('frequencia') === 'cada_5_dias')>
                        A cada 5 dias
                    </option>

                    <option value="semanal"
                        @selected(old('frequencia') === 'semanal')>
                        Semanal
                    </option>

                    <option value="mensal"
                        @selected(old('frequencia', 'mensal') === 'mensal')>
                        Mensal
                    </option>

                    <option value="trimestral"
                        @selected(old('frequencia') === 'trimestral')>
                        Trimestral
                    </option>

                    <option value="semestral"
                        @selected(old('frequencia') === 'semestral')>
                        Semestral
                    </option>

                    <option value="anual"
                        @selected(old('frequencia') === 'anual')>
                        Anual
                    </option>
                </select>

                <span class="form-hint">
                    Diária, 3 dias, 5 dias e semanal usam a Data de início como referência.
                </span>

            </div>


            <div class="form-group" id="grupoDiaVencimento">

                <label class="form-label">
                    Dia do vencimento
                </label>

                <input
                    type="number"
                    min="1"
                    max="31"
                    name="dia_vencimento"
                    id="diaVencimento"
                    class="form-control"
                    value="{{ old('dia_vencimento') }}"
                    placeholder="Ex.: 10"
                >

                <span class="form-hint">
                    Usado nas frequências mensal, trimestral, semestral e anual.
                </span>

            </div>


            <div class="form-group">

                <label class="form-label">
                    Data de início *
                </label>

                <input
                    type="date"
                    name="data_inicio"
                    class="form-control"
                    value="{{ old(
                        'data_inicio',
                        now()->format('Y-m-d')
                    ) }}"
                    required
                >

            </div>


            <div class="form-group">

                <label class="form-label">
                    Data final
                </label>

                <input
                    type="date"
                    name="data_fim"
                    class="form-control"
                    value="{{ old('data_fim') }}"
                >

                <span class="form-hint">
                    Deixe em branco se não houver data final.
                </span>

            </div>


            <div class="form-group">

                <label class="form-label">
                    Conta padrão
                </label>

                <select
                    name="conta_padrao_id"
                    class="form-control"
                >
                    <option value="">
                        Não definir
                    </option>

                    @foreach($contas as $conta)

                        <option
                            value="{{ $conta->id }}"
                            @selected(
                                old('conta_padrao_id')
                                == $conta->id
                            )
                        >
                            {{ $conta->nome }}
                        </option>

                    @endforeach

                </select>

            </div>


            <div class="form-group">

                <label class="form-label">
                    Forma de pagamento
                </label>

                <select
                    name="forma_pagamento_id"
                    class="form-control"
                >

                    <option value="">
                        Não definir
                    </option>

                    @foreach($formasPagamento as $forma)

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


            <div class="form-group full">

                <label class="form-label">
                    Observação
                </label>

                <textarea
                    name="observacao"
                    class="form-control"
                >{{ old('observacao') }}</textarea>

            </div>

        </div>


        <div class="form-actions">

            <a
                href="{{ route('recorrencias.index') }}"
                class="btn-cancelar"
            >
                Cancelar
            </a>

            <button
                type="submit"
                class="btn-salvar"
            >
                Salvar conta fixa
            </button>

        </div>

    </form>

</div>

@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const tipo =
        document.getElementById('tipoRecorrencia');

    const categoria =
        document.getElementById('categoriaRecorrencia');

    const tipoValor =
        document.getElementById('tipoValor');

    const valor =
        document.getElementById('valorPadrao');

    const frequencia =
        document.getElementById('frequenciaRecorrencia');

    const grupoDiaVencimento =
        document.getElementById('grupoDiaVencimento');

    const diaVencimento =
        document.getElementById('diaVencimento');


    function atualizarCategorias() {

        const tipoSelecionado = tipo.value;

        Array.from(categoria.options)
            .forEach(function(option) {

                if (!option.value) {
                    return;
                }

                option.hidden =
                    option.dataset.tipo
                    !== tipoSelecionado;
            });


        const selecionada =
            categoria.options[
                categoria.selectedIndex
            ];

        if (
            selecionada
            && selecionada.value
            && selecionada.dataset.tipo
                !== tipoSelecionado
        ) {
            categoria.value = '';
        }
    }


    function atualizarValor() {

        const fixo =
            tipoValor.value === 'fixo';

        valor.required = fixo;
    }


    function atualizarDiaVencimento() {

        const frequenciasPorDias = [
            'diaria',
            'cada_3_dias',
            'cada_5_dias',
            'semanal'
        ];

        const usaDataInicio =
            frequenciasPorDias.includes(
                frequencia.value
            );

        grupoDiaVencimento.style.display =
            usaDataInicio
                ? 'none'
                : 'flex';

        diaVencimento.disabled =
            usaDataInicio;

        if (usaDataInicio) {
            diaVencimento.value = '';
        }
    }


    tipo.addEventListener(
        'change',
        atualizarCategorias
    );

    tipoValor.addEventListener(
        'change',
        atualizarValor
    );

    frequencia.addEventListener(
        'change',
        atualizarDiaVencimento
    );

    atualizarCategorias();
    atualizarValor();
    atualizarDiaVencimento();
});
</script>
@endpush
