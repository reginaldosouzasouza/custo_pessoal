@extends('layouts.custo-pessoal')

@section('title', 'Nova Compra no Cartão')

@push('styles')
<style>
    .form-header {
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

    .info-box {
        grid-column: 1 / -1;

        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;

        padding: 13px 15px;

        color: #475569;
        font-size: 12px;
        line-height: 1.5;
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

    @media(max-width:700px) {

        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-group.full,
        .info-box {
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

<div class="form-header">

    <h1 class="cp-page-title">
        Nova Compra no Cartão
    </h1>

    <p class="cp-page-subtitle">
        O sistema criará automaticamente as parcelas e as faturas.
    </p>

</div>


<div class="cp-card form-card">

    <form
        action="{{ route('compras-cartao.store') }}"
        method="POST"
    >
        @csrf


        <div class="form-grid">

            <div class="form-group">

                <label class="form-label">
                    Cartão *
                </label>

                <select
                    name="cartao_id"
                    class="form-control"
                    required
                >

                    <option value="">
                        Selecione
                    </option>

                    @foreach($cartoes as $cartao)

                        <option
                            value="{{ $cartao->id }}"
                            @selected(
                                old('cartao_id')
                                == $cartao->id
                            )
                        >
                            {{ $cartao->nome }}

                            @if($cartao->ultimos_digitos)
                                - final
                                {{ $cartao->ultimos_digitos }}
                            @endif

                        </option>

                    @endforeach

                </select>

                @error('cartao_id')
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


            <div class="form-group full">

                <label class="form-label">
                    Descrição *
                </label>

                <input
                    type="text"
                    name="descricao"
                    class="form-control"
                    value="{{ old('descricao') }}"
                    placeholder="Ex.: Notebook"
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
                    Valor total *
                </label>

                <input
                    type="number"
                    step="0.01"
                    min="0.01"
                    name="valor_total"
                    class="form-control"
                    value="{{ old('valor_total') }}"
                    placeholder="0,00"
                    required
                >

                @error('valor_total')
                    <span class="form-error">
                        {{ $message }}
                    </span>
                @enderror

            </div>


            <div class="form-group">

                <label class="form-label">
                    Data da compra *
                </label>

                <input
                    type="date"
                    name="data_compra"
                    class="form-control"
                    value="{{ old(
                        'data_compra',
                        now()->format('Y-m-d')
                    ) }}"
                    required
                >

                @error('data_compra')
                    <span class="form-error">
                        {{ $message }}
                    </span>
                @enderror

            </div>


            <div class="form-group">

                <label class="form-label">
                    Quantidade de parcelas *
                </label>

                <input
                    type="number"
                    name="quantidade_parcelas"
                    class="form-control"
                    min="1"
                    max="60"
                    value="{{ old(
                        'quantidade_parcelas',
                        1
                    ) }}"
                    required
                >

                <span class="form-hint">
                    Informe 1 para compra à vista no cartão.
                </span>

                @error('quantidade_parcelas')
                    <span class="form-error">
                        {{ $message }}
                    </span>
                @enderror

            </div>


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


            <div class="info-box">

                A compra no cartão
                <strong>não altera o saldo da conta agora</strong>.

                O dinheiro somente sairá da conta quando a
                <strong>fatura for paga</strong>.

                O sistema usará o dia de fechamento do cartão
                para definir em qual fatura cada parcela entrará.

            </div>

        </div>


        <div class="form-actions">

            <a
                href="{{ route('compras-cartao.index') }}"
                class="btn-cancelar"
            >
                Cancelar
            </a>

            <button
                type="submit"
                class="btn-salvar"
            >
                Registrar compra
            </button>

        </div>

    </form>

</div>

@endsection