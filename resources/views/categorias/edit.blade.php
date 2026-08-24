@extends('layouts.custo-pessoal')

@section('title', 'Editar Categoria')

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

    .classificacao-box {
        display: none;
    }

    @media(max-width: 700px) {
        .form-grid {
            grid-template-columns: 1fr;
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
        Editar Categoria
    </h1>

    <p class="cp-page-subtitle">
        Altere os dados da categoria.
    </p>

</div>


<div class="cp-card form-card">

    <form
        action="{{ route('categorias.update', $categoria) }}"
        method="POST"
    >
        @csrf
        @method('PUT')

        <div class="form-grid">

            <div class="form-group">

                <label class="form-label">
                    Nome *
                </label>

                <input
                    type="text"
                    name="nome"
                    class="form-control"
                    value="{{ old('nome', $categoria->nome) }}"
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
                    id="tipoCategoria"
                    class="form-control"
                    required
                >
                    <option
                        value="receita"
                        @selected(old('tipo', $categoria->tipo) === 'receita')
                    >
                        Receita
                    </option>

                    <option
                        value="despesa"
                        @selected(old('tipo', $categoria->tipo) === 'despesa')
                    >
                        Despesa
                    </option>

                </select>

            </div>


            <div
                class="form-group classificacao-box"
                id="classificacaoBox"
            >

                <label class="form-label">
                    Classificação
                </label>

                <select
                    name="classificacao"
                    class="form-control"
                >
                    <option value="">
                        Sem classificação
                    </option>

                    <option
                        value="essencial"
                        @selected(old('classificacao', $categoria->classificacao) === 'essencial')
                    >
                        Essencial
                    </option>

                    <option
                        value="nao_essencial"
                        @selected(old('classificacao', $categoria->classificacao) === 'nao_essencial')
                    >
                        Não essencial
                    </option>
                </select>

            </div>


            <div class="form-group">

                <label class="form-label">
                    Ícone
                </label>

                <input
                    type="text"
                    name="icone"
                    class="form-control"
                    value="{{ old('icone', $categoria->icone) }}"
                >

                <span class="form-hint">
                    Opcional.
                </span>

            </div>

        </div>


        <div class="form-actions">

            <a
                href="{{ route('categorias.index') }}"
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
        document.getElementById('tipoCategoria');

    const classificacao =
        document.getElementById('classificacaoBox');

    function atualizarClassificacao() {

        if (tipo.value === 'despesa') {
            classificacao.style.display = 'flex';
        } else {
            classificacao.style.display = 'none';
        }
    }

    tipo.addEventListener(
        'change',
        atualizarClassificacao
    );

    atualizarClassificacao();
});
</script>
@endpush