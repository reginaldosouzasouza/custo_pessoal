@extends('layouts.custo-pessoal')

@section('title', 'Editar Conta Fixa')

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

    .form-control:focus {
        border-color:#2f80ed;
        outline:none;
        box-shadow:0 0 0 3px rgba(47,128,237,.10);
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
        Editar Conta Fixa
    </h1>

    <p class="cp-page-subtitle">
        Altere os dados da receita ou despesa recorrente.
    </p>

</div>


<div class="cp-card form-card">

    <form
        action="{{ route(
            'recorrencias.update',
            $recorrencia
        ) }}"
        method="POST"
    >
        @csrf
        @method('PUT')


        <div class="form-grid">

            {{-- TIPO --}}
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

                    <option
                        value="despesa"
                        @selected(
                            old(
                                'tipo',
                                $recorrencia->tipo
                            ) === 'despesa'
                        )
                    >
                        Despesa
                    </option>


                    <option
                        value="receita"
                        @selected(
                            old(
                                'tipo',
                                $recorrencia->tipo
                            ) === 'receita'
                        )
                    >
                        Receita
                    </option>

                </select>

                @error('tipo')
                    <span class="form-error">
                        {{ $message }}
                    </span>
                @enderror

            </div>


            {{-- CATEGORIA --}}
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
                                old(
                                    'categoria_id',
                                    $recorrencia->categoria_id
                                )
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


            {{-- DESCRIÇÃO --}}
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
                        $recorrencia->descricao
                    ) }}"
                    required
                >

                @error('descricao')
                    <span class="form-error">
                        {{ $message }}
                    </span>
                @enderror

            </div>


            {{-- TIPO DE VALOR --}}
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

                    <option
                        value="fixo"
                        @selected(
                            old(
                                'tipo_valor',
                                $recorrencia->tipo_valor
                            ) === 'fixo'
                        )
                    >
                        Fixo
                    </option>

                    <option
                        value="variavel"
                        @selected(
                            old(
                                'tipo_valor',
                                $recorrencia->tipo_valor
                            ) === 'variavel'
                        )
                    >
                        Variável
                    </option>

                </select>

                @error('tipo_valor')
                    <span class="form-error">
                        {{ $message }}
                    </span>
                @enderror

            </div>


            {{-- VALOR PADRÃO --}}
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
                    value="{{ old(
                        'valor_padrao',
                        $recorrencia->valor_padrao
                    ) }}"
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


            {{-- FREQUÊNCIA --}}
            <div class="form-group">

                <label class="form-label">
                    Frequência *
                </label>

                <select
                    name="frequencia"
                    class="form-control"
                    required
                >

                    <option
                        value="semanal"
                        @selected(
                            old(
                                'frequencia',
                                $recorrencia->frequencia
                            ) === 'semanal'
                        )
                    >
                        Semanal
                    </option>

                    <option
                        value="mensal"
                        @selected(
                            old(
                                'frequencia',
                                $recorrencia->frequencia
                            ) === 'mensal'
                        )
                    >
                        Mensal
                    </option>

                    <option
                        value="trimestral"
                        @selected(
                            old(
                                'frequencia',
                                $recorrencia->frequencia
                            ) === 'trimestral'
                        )
                    >
                        Trimestral
                    </option>

                    <option
                        value="semestral"
                        @selected(
                            old(
                                'frequencia',
                                $recorrencia->frequencia
                            ) === 'semestral'
                        )
                    >
                        Semestral
                    </option>

                    <option
                        value="anual"
                        @selected(
                            old(
                                'frequencia',
                                $recorrencia->frequencia
                            ) === 'anual'
                        )
                    >
                        Anual
                    </option>

                </select>

                @error('frequencia')
                    <span class="form-error">
                        {{ $message }}
                    </span>
                @enderror

            </div>


            {{-- DIA DO VENCIMENTO --}}
            <div class="form-group">

                <label class="form-label">
                    Dia do vencimento
                </label>

                <input
                    type="number"
                    min="1"
                    max="31"
                    name="dia_vencimento"
                    class="form-control"
                    value="{{ old(
                        'dia_vencimento',
                        $recorrencia->dia_vencimento
                    ) }}"
                >

                <span class="form-hint">
                    Se o mês tiver menos dias, depois trataremos pelo último dia válido.
                </span>

                @error('dia_vencimento')
                    <span class="form-error">
                        {{ $message }}
                    </span>
                @enderror

            </div>


            {{-- DATA INÍCIO --}}
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
                        $recorrencia
                            ->data_inicio
                            ?->format('Y-m-d')
                    ) }}"
                    required
                >

                @error('data_inicio')
                    <span class="form-error">
                        {{ $message }}
                    </span>
                @enderror

            </div>


            {{-- DATA FINAL --}}
            <div class="form-group">

                <label class="form-label">
                    Data final
                </label>

                <input
                    type="date"
                    name="data_fim"
                    class="form-control"
                    value="{{ old(
                        'data_fim',
                        $recorrencia
                            ->data_fim
                            ?->format('Y-m-d')
                    ) }}"
                >

                <span class="form-hint">
                    Deixe em branco se não houver data final.
                </span>

                @error('data_fim')
                    <span class="form-error">
                        {{ $message }}
                    </span>
                @enderror

            </div>


            {{-- CONTA PADRÃO --}}
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
                                old(
                                    'conta_padrao_id',
                                    $recorrencia->conta_padrao_id
                                )
                                == $conta->id
                            )
                        >
                            {{ $conta->nome }}
                        </option>

                    @endforeach

                </select>

                @error('conta_padrao_id')
                    <span class="form-error">
                        {{ $message }}
                    </span>
                @enderror

            </div>


            {{-- FORMA DE PAGAMENTO --}}
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
                                old(
                                    'forma_pagamento_id',
                                    $recorrencia->forma_pagamento_id
                                )
                                == $forma->id
                            )
                        >
                            {{ $forma->nome }}
                        </option>

                    @endforeach

                </select>

                @error('forma_pagamento_id')
                    <span class="form-error">
                        {{ $message }}
                    </span>
                @enderror

            </div>


            {{-- OBSERVAÇÃO --}}
            <div class="form-group full">

                <label class="form-label">
                    Observação
                </label>

                <textarea
                    name="observacao"
                    class="form-control"
                >{{ old(
                    'observacao',
                    $recorrencia->observacao
                ) }}</textarea>

                @error('observacao')
                    <span class="form-error">
                        {{ $message }}
                    </span>
                @enderror

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
                Salvar alterações
            </button>

        </div>

    </form>

</div>

@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const tipo =
        document.getElementById(
            'tipoRecorrencia'
        );

    const categoria =
        document.getElementById(
            'categoriaRecorrencia'
        );

    const tipoValor =
        document.getElementById(
            'tipoValor'
        );

    const valor =
        document.getElementById(
            'valorPadrao'
        );


    function atualizarCategorias() {

        const tipoSelecionado =
            tipo.value;

        Array.from(
            categoria.options
        ).forEach(function(option) {

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


    tipo.addEventListener(
        'change',
        atualizarCategorias
    );

    tipoValor.addEventListener(
        'change',
        atualizarValor
    );


    atualizarCategorias();
    atualizarValor();
});
</script>
@endpush
