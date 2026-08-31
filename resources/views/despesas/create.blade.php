@extends('layouts.custo-pessoal')

@section('title', 'Nova Despesa')

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

    .pagamento-box {
        display: none;
    }

    /*
    |--------------------------------------------------------------------------
    | TIPO DE LANÇAMENTO
    |--------------------------------------------------------------------------
    */

    .tipo-lancamento-box {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .tipo-option {
        position: relative;
        border: 1px solid #d9dee5;
        border-radius: 10px;
        padding: 15px;
        cursor: pointer;
        background: #fff;
        transition: all .2s ease;
    }

    .tipo-option:hover {
        border-color: #2f80ed;
    }

    .tipo-option input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .tipo-option.ativo {
        border-color: #2f80ed;
        background: #f5f9ff;
        box-shadow: 0 0 0 2px rgba(47,128,237,.08);
    }

    .tipo-title {
        display: block;
        font-size: 14px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 3px;
    }

    .tipo-description {
        display: block;
        font-size: 11px;
        color: #6b7280;
        line-height: 1.4;
    }

    /*
    |--------------------------------------------------------------------------
    | PARCELAMENTO
    |--------------------------------------------------------------------------
    */

    .parcelamento-area {
        display: none;
        grid-column: 1 / -1;
    }

    .parcelamento-area.ativo {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
    }

    .parcelamento-info {
        grid-column: 1 / -1;
        padding: 13px 15px;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 12px;
        color: #4b5563;
        line-height: 1.5;
    }

    .resumo-parcelamento {
        grid-column: 1 / -1;
        display: none;
        padding: 13px 15px;
        border: 1px solid #bfdbfe;
        background: #eff6ff;
        border-radius: 8px;
        font-size: 12px;
        color: #1e40af;
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

    @media(max-width: 700px) {

        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-group.full {
            grid-column: auto;
        }

        .tipo-lancamento-box {
            grid-template-columns: 1fr;
        }

        .parcelamento-area.ativo {
            grid-template-columns: 1fr;
        }

        .parcelamento-area {
            grid-column: auto;
        }

        .parcelamento-info,
        .resumo-parcelamento {
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
        Nova Despesa
    </h1>

    <p class="cp-page-subtitle">
        Cadastre uma despesa única ou um compromisso parcelado.
    </p>

</div>


<div class="cp-card form-card">

    <form
        id="formDespesa"
        action="{{ route('despesas.store') }}"
        method="POST"
    >
        @csrf

        <div class="form-grid">

            {{-- TIPO DE LANÇAMENTO --}}
            <div class="form-group full">

                <label class="form-label">
                    Tipo de lançamento *
                </label>

                <div class="tipo-lancamento-box">

                    <label
                        class="tipo-option"
                        id="opcaoUnica"
                    >
                        <input
                            type="radio"
                            name="tipo_lancamento"
                            value="unica"
                            id="tipoUnica"
                            checked
                        >

                        <span class="tipo-title">
                            Despesa única
                        </span>

                        <span class="tipo-description">
                            Para uma despesa com apenas um vencimento.
                        </span>
                    </label>


                    <label
                        class="tipo-option"
                        id="opcaoParcelada"
                    >
                        <input
                            type="radio"
                            name="tipo_lancamento"
                            value="parcelada"
                            id="tipoParcelada"
                        >

                        <span class="tipo-title">
                            Despesa parcelada
                        </span>

                        <span class="tipo-description">
                            Para empréstimos, financiamentos e outros compromissos em parcelas.
                        </span>
                    </label>

                </div>

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
                    value="{{ old('descricao') }}"
                    placeholder="Ex.: Empréstimo pessoal"
                    required
                >

                @error('descricao')
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


            {{-- FORMA DE PAGAMENTO --}}
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


            {{-- ============================================================ --}}
            {{-- DESPESA ÚNICA --}}
            {{-- ============================================================ --}}

            <div
                class="form-group unica-box"
                id="valorUnicoBox"
            >

                <label class="form-label">
                    Valor *
                </label>

                <input
                    type="number"
                    step="0.01"
                    min="0.01"
                    name="valor"
                    id="valorUnico"
                    class="form-control"
                    value="{{ old('valor') }}"
                >

                @error('valor')
                    <span class="form-error">
                        {{ $message }}
                    </span>
                @enderror

            </div>


            <div
                class="form-group unica-box"
                id="dataDespesaBox"
            >

                <label class="form-label">
                    Data da despesa *
                </label>

                <input
                    type="date"
                    name="data_despesa"
                    id="dataDespesa"
                    class="form-control"
                    value="{{ old(
                        'data_despesa',
                        now()->format('Y-m-d')
                    ) }}"
                >

                @error('data_despesa')
                    <span class="form-error">
                        {{ $message }}
                    </span>
                @enderror

            </div>


            <div
                class="form-group unica-box"
                id="vencimentoUnicoBox"
            >

                <label class="form-label">
                    Data de vencimento *
                </label>

                <input
                    type="date"
                    name="data_vencimento"
                    id="vencimentoUnico"
                    class="form-control"
                    value="{{ old(
                        'data_vencimento',
                        now()->format('Y-m-d')
                    ) }}"
                >

                @error('data_vencimento')
                    <span class="form-error">
                        {{ $message }}
                    </span>
                @enderror

            </div>


            <div
                class="form-group unica-box"
                id="situacaoBox"
            >

                <label class="form-label">
                    Situação *
                </label>

                <select
                    name="situacao"
                    id="situacaoDespesa"
                    class="form-control"
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
                        value="paga"
                        @selected(
                            old('situacao')
                            === 'paga'
                        )
                    >
                        Paga
                    </option>

                </select>

            </div>


            {{-- CONTA DA DESPESA PAGA --}}
            <div
                class="form-group pagamento-box unica-box"
                id="contaPagamentoBox"
            >

                <label class="form-label">
                    Conta utilizada *
                </label>

                <select
                    name="conta_id"
                    id="contaPagamento"
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


            {{-- DATA DO PAGAMENTO --}}
            <div
                class="form-group pagamento-box unica-box"
                id="dataPagamentoBox"
            >

                <label class="form-label">
                    Data do pagamento *
                </label>

                <input
                    type="date"
                    name="data_pagamento"
                    id="dataPagamento"
                    class="form-control"
                    value="{{ old(
                        'data_pagamento',
                        now()->format('Y-m-d')
                    ) }}"
                >

                @error('data_pagamento')
                    <span class="form-error">
                        {{ $message }}
                    </span>
                @enderror

            </div>


            {{-- ============================================================ --}}
            {{-- PARCELAMENTO --}}
            {{-- ============================================================ --}}

            <div
                class="parcelamento-area"
                id="parcelamentoArea"
            >

                <div class="parcelamento-info">

                    Informe o valor total do compromisso,
                    a quantidade de parcelas e o primeiro vencimento.

                    As próximas parcelas serão geradas
                    automaticamente mês a mês.

                </div>


                {{-- VALOR TOTAL --}}
                <div class="form-group">

                    <label class="form-label">
                        Valor total *
                    </label>

                    <input
                        type="number"
                        step="0.01"
                        min="0.01"
                        name="valor_total"
                        id="valorTotal"
                        class="form-control"
                        value="{{ old('valor_total') }}"
                        placeholder="0,00"
                        disabled
                    >

                    @error('valor_total')
                        <span class="form-error">
                            {{ $message }}
                        </span>
                    @enderror

                </div>


                {{-- QUANTIDADE --}}
                <div class="form-group">

                    <label class="form-label">
                        Quantidade de parcelas *
                    </label>

                    <input
                        type="number"
                        min="2"
                        max="120"
                        name="quantidade_parcelas"
                        id="quantidadeParcelas"
                        class="form-control"
                        value="{{ old('quantidade_parcelas') }}"
                        placeholder="Ex.: 10"
                        disabled
                    >

                    @error('quantidade_parcelas')
                        <span class="form-error">
                            {{ $message }}
                        </span>
                    @enderror

                </div>


                {{-- PRIMEIRO VENCIMENTO --}}
                <div class="form-group">

                    <label class="form-label">
                        Primeiro vencimento *
                    </label>

                    <input
                        type="date"
                        name="primeiro_vencimento"
                        id="primeiroVencimento"
                        class="form-control"
                        value="{{ old(
                            'primeiro_vencimento',
                            now()->format('Y-m-d')
                        ) }}"
                        disabled
                    >

                    @error('primeiro_vencimento')
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
                        id="contaPadrao"
                        class="form-control"
                        disabled
                    >

                        <option value="">
                            Não informar
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

                    <span class="form-hint">
                        Opcional. A conta pode ser definida posteriormente no pagamento.
                    </span>

                </div>


                {{-- RESUMO --}}
                <div
                    class="resumo-parcelamento"
                    id="resumoParcelamento"
                >
                </div>

            </div>


            {{-- OBSERVAÇÃO --}}
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
                href="{{ route('despesas.index') }}"
                class="btn-cancelar"
            >
                Cancelar
            </a>

            <button
                type="submit"
                class="btn-salvar"
                id="btnSalvar"
            >
                Salvar despesa
            </button>

        </div>

    </form>

</div>

@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | ELEMENTOS
    |--------------------------------------------------------------------------
    */

    const form =
        document.getElementById('formDespesa');

    const tipoUnica =
        document.getElementById('tipoUnica');

    const tipoParcelada =
        document.getElementById('tipoParcelada');

    const opcaoUnica =
        document.getElementById('opcaoUnica');

    const opcaoParcelada =
        document.getElementById('opcaoParcelada');

    const parcelamentoArea =
        document.getElementById('parcelamentoArea');

    const btnSalvar =
        document.getElementById('btnSalvar');


    /*
    |--------------------------------------------------------------------------
    | CAMPOS DESPESA ÚNICA
    |--------------------------------------------------------------------------
    */

    const valorUnico =
        document.getElementById('valorUnico');

    const dataDespesa =
        document.getElementById('dataDespesa');

    const vencimentoUnico =
        document.getElementById('vencimentoUnico');

    const situacao =
        document.getElementById('situacaoDespesa');

    const contaPagamento =
        document.getElementById('contaPagamento');

    const dataPagamento =
        document.getElementById('dataPagamento');

    const contaBox =
        document.getElementById('contaPagamentoBox');

    const dataBox =
        document.getElementById('dataPagamentoBox');


    /*
    |--------------------------------------------------------------------------
    | CAMPOS PARCELAMENTO
    |--------------------------------------------------------------------------
    */

    const valorTotal =
        document.getElementById('valorTotal');

    const quantidadeParcelas =
        document.getElementById('quantidadeParcelas');

    const primeiroVencimento =
        document.getElementById('primeiroVencimento');

    const contaPadrao =
        document.getElementById('contaPadrao');

    const resumoParcelamento =
        document.getElementById('resumoParcelamento');


    /*
    |--------------------------------------------------------------------------
    | ROTAS
    |--------------------------------------------------------------------------
    */

    const rotaDespesa =
        @json(route('despesas.store'));

    const rotaParcelamento =
        @json(route('parcelamentos.store'));


    /*
    |--------------------------------------------------------------------------
    | PAGAMENTO DESPESA ÚNICA
    |--------------------------------------------------------------------------
    */

    function atualizarPagamento() {

        if (!tipoUnica.checked) {

            contaBox.style.display = 'none';
            dataBox.style.display = 'none';

            contaPagamento.required = false;
            dataPagamento.required = false;

            return;
        }

        const paga =
            situacao.value === 'paga';

        contaBox.style.display =
            paga ? 'flex' : 'none';

        dataBox.style.display =
            paga ? 'flex' : 'none';

        contaPagamento.required =
            paga;

        dataPagamento.required =
            paga;
    }


    /*
    |--------------------------------------------------------------------------
    | ATUALIZA TIPO DE LANÇAMENTO
    |--------------------------------------------------------------------------
    */

    function atualizarTipoLancamento() {

        const parcelada =
            tipoParcelada.checked;


        /*
         * Visual dos cards.
         */
        opcaoUnica.classList.toggle(
            'ativo',
            !parcelada
        );

        opcaoParcelada.classList.toggle(
            'ativo',
            parcelada
        );


        /*
         * Área de parcelamento.
         */
        parcelamentoArea.classList.toggle(
            'ativo',
            parcelada
        );


        /*
         * DESPESA PARCELADA
         */
        if (parcelada) {

            form.action =
                rotaParcelamento;

            btnSalvar.textContent =
                'Salvar parcelamento';


            /*
             * Desabilita campos exclusivos
             * da despesa única.
             */
            valorUnico.disabled = true;
            dataDespesa.disabled = true;
            vencimentoUnico.disabled = true;
            situacao.disabled = true;
            contaPagamento.disabled = true;
            dataPagamento.disabled = true;


            valorUnico.required = false;
            dataDespesa.required = false;
            vencimentoUnico.required = false;
            situacao.required = false;


            /*
             * Ativa parcelamento.
             */
            valorTotal.disabled = false;
            quantidadeParcelas.disabled = false;
            primeiroVencimento.disabled = false;
            contaPadrao.disabled = false;


            valorTotal.required = true;
            quantidadeParcelas.required = true;
            primeiroVencimento.required = true;

        }

        /*
         * DESPESA ÚNICA
         */
        else {

            form.action =
                rotaDespesa;

            btnSalvar.textContent =
                'Salvar despesa';


            /*
             * Ativa campos normais.
             */
            valorUnico.disabled = false;
            dataDespesa.disabled = false;
            vencimentoUnico.disabled = false;
            situacao.disabled = false;
            contaPagamento.disabled = false;
            dataPagamento.disabled = false;


            valorUnico.required = true;
            dataDespesa.required = true;
            vencimentoUnico.required = true;
            situacao.required = true;


            /*
             * Desativa parcelamento.
             */
            valorTotal.disabled = true;
            quantidadeParcelas.disabled = true;
            primeiroVencimento.disabled = true;
            contaPadrao.disabled = true;


            valorTotal.required = false;
            quantidadeParcelas.required = false;
            primeiroVencimento.required = false;
        }


        atualizarPagamento();
        atualizarResumoParcelamento();
    }


    /*
    |--------------------------------------------------------------------------
    | RESUMO DAS PARCELAS
    |--------------------------------------------------------------------------
    */

    function atualizarResumoParcelamento() {

        if (!tipoParcelada.checked) {

            resumoParcelamento.style.display =
                'none';

            resumoParcelamento.innerHTML =
                '';

            return;
        }


        const total =
            parseFloat(valorTotal.value);

        const quantidade =
            parseInt(
                quantidadeParcelas.value
            );


        if (
            !total
            ||
            !quantidade
            ||
            quantidade < 2
        ) {

            resumoParcelamento.style.display =
                'none';

            resumoParcelamento.innerHTML =
                '';

            return;
        }


        const valorMedio =
            total / quantidade;


        const totalFormatado =
            total.toLocaleString(
                'pt-BR',
                {
                    style: 'currency',
                    currency: 'BRL'
                }
            );


        const parcelaFormatada =
            valorMedio.toLocaleString(
                'pt-BR',
                {
                    style: 'currency',
                    currency: 'BRL'
                }
            );


        resumoParcelamento.innerHTML =
            '<strong>Resumo:</strong> '
            + quantidade
            + ' parcelas de aproximadamente '
            + parcelaFormatada
            + ', totalizando '
            + totalFormatado
            + '.';


        resumoParcelamento.style.display =
            'block';
    }


    /*
    |--------------------------------------------------------------------------
    | EVENTOS
    |--------------------------------------------------------------------------
    */

    tipoUnica.addEventListener(
        'change',
        atualizarTipoLancamento
    );

    tipoParcelada.addEventListener(
        'change',
        atualizarTipoLancamento
    );

    situacao.addEventListener(
        'change',
        atualizarPagamento
    );

    valorTotal.addEventListener(
        'input',
        atualizarResumoParcelamento
    );

    quantidadeParcelas.addEventListener(
        'input',
        atualizarResumoParcelamento
    );


    /*
    |--------------------------------------------------------------------------
    | INICIALIZAÇÃO
    |--------------------------------------------------------------------------
    */

    atualizarTipoLancamento();

});
</script>
@endpush