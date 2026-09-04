@extends('layouts.custo-pessoal')

@section('title', 'Nova Transferência')

@push('styles')
<style>
    .form-header {
        margin-bottom:22px;
    }

    .form-card {
        max-width:850px;
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

    .info-box {
        grid-column:1 / -1;
        background:#f8fafc;
        border:1px solid #e2e8f0;
        border-radius:8px;
        padding:13px 15px;
        color:#475569;
        font-size:12px;
        line-height:1.5;
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

        .form-group.full,
        .info-box {
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
        Nova Transferência
    </h1>

    <p class="cp-page-subtitle">
        Transfira valores entre suas próprias contas.
    </p>

</div>


<div class="cp-card form-card">

    <form
        action="{{ route('transferencias.store') }}"
        method="POST"
    >
        @csrf


        <div class="form-grid">

            <div class="form-group">

                <label class="form-label">
                    Conta de origem *
                </label>

                <select
                    name="conta_origem_id"
                    class="form-control"
                    required
                >

                    <option value="">
                        Selecione
                    </option>

                    @foreach($contas as $conta)

                        <option
                            value="{{ $conta->id }}"
                            @selected(
                                old('conta_origem_id')
                                == $conta->id
                            )
                        >
                            {{ $conta->nome }}
                        </option>

                    @endforeach

                </select>

                @error('conta_origem_id')
                    <span class="form-error">
                        {{ $message }}
                    </span>
                @enderror

            </div>


            <div class="form-group">

                <label class="form-label">
                    Conta de destino *
                </label>

                <select
                    name="conta_destino_id"
                    class="form-control"
                    required
                >

                    <option value="">
                        Selecione
                    </option>

                    @foreach($contas as $conta)

                        <option
                            value="{{ $conta->id }}"
                            @selected(
                                old('conta_destino_id')
                                == $conta->id
                            )
                        >
                            {{ $conta->nome }}
                        </option>

                    @endforeach

                </select>

                @error('conta_destino_id')
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
                    Data *
                </label>

                <input
                    type="date"
                    name="data_transferencia"
                    class="form-control"
                    value="{{ old(
                        'data_transferencia',
                        now()->format('Y-m-d')
                    ) }}"
                    required
                >

            </div>


            <div class="form-group full">

                <label class="form-label">
                    Descrição
                </label>

                <input
                    type="text"
                    name="descricao"
                    class="form-control"
                    value="{{ old('descricao') }}"
                    placeholder="Ex.: Saque para carteira"
                >

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


            <div class="info-box">

                Transferência entre suas próprias contas
                <strong>não é receita nem despesa</strong>.

                O valor sai da conta de origem e entra na conta de destino,
                sem alterar o saldo total geral.

            </div>

        </div>


        <div class="form-actions">

            <a
                href="{{ route('transferencias.index') }}"
                class="btn-cancelar"
            >
                Cancelar
            </a>

            <button
                type="submit"
                class="btn-salvar"
            >
                Transferir
            </button>

        </div>

    </form>

</div>

@endsection