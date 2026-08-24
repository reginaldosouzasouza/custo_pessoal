@extends('layouts.custo-pessoal')

@section('title', 'Nova Conta')

@push('styles')
<style>
    .form-page-header {
        margin-bottom: 22px;
    }

    .form-card {
        max-width: 850px;
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
        Nova Conta
    </h1>

    <p class="cp-page-subtitle">
        Cadastre uma conta, carteira ou dinheiro em espécie.
    </p>
</div>

<div class="cp-card form-card">

    <form
        action="{{ route('contas.store') }}"
        method="POST"
    >
        @csrf

        <div class="form-grid">

            <div class="form-group">
                <label class="form-label">
                    Nome da conta *
                </label>

                <input
                    type="text"
                    name="nome"
                    class="form-control"
                    value="{{ old('nome') }}"
                    placeholder="Ex.: Nubank"
                    required
                >

                @error('nome')
                    <span class="form-error">
                        {{ $message }}
                    </span>
                @enderror
            </div>


            <div class="form-group">
                <label class="form-label">
                    Tipo *
                </label>

                <select
                    name="tipo"
                    class="form-control"
                    required
                >
                    <option value="">
                        Selecione
                    </option>

                    <option value="conta_corrente"
                        @selected(old('tipo') === 'conta_corrente')>
                        Conta corrente
                    </option>

                    <option value="conta_digital"
                        @selected(old('tipo') === 'conta_digital')>
                        Conta digital
                    </option>

                    <option value="poupanca"
                        @selected(old('tipo') === 'poupanca')>
                        Poupança
                    </option>

                    <option value="dinheiro"
                        @selected(old('tipo') === 'dinheiro')>
                        Dinheiro
                    </option>

                    <option value="carteira"
                        @selected(old('tipo') === 'carteira')>
                        Carteira
                    </option>

                    <option value="outro"
                        @selected(old('tipo') === 'outro')>
                        Outro
                    </option>
                </select>

                @error('tipo')
                    <span class="form-error">
                        {{ $message }}
                    </span>
                @enderror
            </div>


            <div class="form-group">
                <label class="form-label">
                    Instituição
                </label>

                <input
                    type="text"
                    name="instituicao"
                    class="form-control"
                    value="{{ old('instituicao') }}"
                    placeholder="Ex.: Nubank, Itaú, Caixa"
                >

                @error('instituicao')
                    <span class="form-error">
                        {{ $message }}
                    </span>
                @enderror
            </div>


            <div class="form-group">
                <label class="form-label">
                    Saldo inicial
                </label>

                <input
                    type="number"
                    step="0.01"
                    name="saldo_inicial"
                    class="form-control"
                    value="{{ old('saldo_inicial', '0.00') }}"
                >

                <span class="form-hint">
                    Pode deixar zerado e informar depois por ajuste.
                </span>

                @error('saldo_inicial')
                    <span class="form-error">
                        {{ $message }}
                    </span>
                @enderror
            </div>


            <div class="form-group">
                <label class="form-label">
                    Data do saldo inicial
                </label>

                <input
                    type="date"
                    name="data_saldo_inicial"
                    class="form-control"
                    value="{{ old('data_saldo_inicial') }}"
                >

                @error('data_saldo_inicial')
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
                    placeholder="Informação opcional sobre esta conta."
                >{{ old('observacao') }}</textarea>

                @error('observacao')
                    <span class="form-error">
                        {{ $message }}
                    </span>
                @enderror
            </div>

        </div>


        <div class="form-actions">

            <a
                href="{{ route('contas.index') }}"
                class="btn-cancelar"
            >
                Cancelar
            </a>

            <button
                type="submit"
                class="btn-salvar"
            >
                Salvar conta
            </button>

        </div>

    </form>

</div>

@endsection