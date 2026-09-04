@extends('layouts.custo-pessoal')

@section('title', 'Transferências')

@push('styles')
<style>
    .page-header {
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:20px;
        flex-wrap:wrap;
        margin-bottom:22px;
    }

    .btn-novo {
        min-height:40px;
        padding:0 15px;
        border:none;
        border-radius:8px;
        background:#0d6efd;
        color:#fff;
        text-decoration:none;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        font-size:13px;
        font-weight:600;
    }

    .table-card {
        padding:18px;
        overflow-x:auto;
    }

    .transferencias-table {
        width:100%;
        min-width:850px;
        border-collapse:collapse;
        font-size:12px;
    }

    .transferencias-table th {
        text-align:left;
        padding:10px 9px;
        border-bottom:1px solid #e5e7eb;
        color:#4b5563;
        font-weight:600;
    }

    .transferencias-table td {
        padding:12px 9px;
        border-bottom:1px solid #edf0f3;
        color:#374151;
    }

    .transferencias-table tr:last-child td {
        border-bottom:none;
    }

    .valor {
        font-weight:700;
        color:#2879d7;
        white-space:nowrap;
    }

    .origem {
        color:#ef4444;
        font-weight:600;
    }

    .destino {
        color:#16a34a;
        font-weight:600;
    }

    .empty-state {
        text-align:center;
        padding:45px 15px;
        color:#6b7280;
    }

    .pagination-wrap {
        margin-top:18px;
    }
</style>
@endpush


@section('content')

<div class="page-header">

    <div>

        <h1 class="cp-page-title">
            Transferências
        </h1>

        <p class="cp-page-subtitle">
            Movimente dinheiro entre suas contas e carteiras.
        </p>

    </div>

    <a
        href="{{ route('transferencias.create') }}"
        class="btn-novo"
    >
        + Nova transferência
    </a>

</div>


<div class="cp-card table-card">

    @if($transferencias->count() > 0)

        <table class="transferencias-table">

            <thead>

                <tr>
                    <th>Data</th>
                    <th>Descrição</th>
                    <th>Origem</th>
                    <th>Destino</th>
                    <th>Valor</th>
                </tr>

            </thead>


            <tbody>

                @foreach($transferencias as $transferencia)

                    <tr>

                        <td>
                            {{ $transferencia
                                ->data_transferencia
                                ->format('d/m/Y') }}
                        </td>

                        <td>
                            {{ $transferencia->descricao }}
                        </td>

                        <td class="origem">
                            {{ $transferencia
                                ->contaOrigem
                                ?->nome
                                ?? '-' }}
                        </td>

                        <td class="destino">
                            {{ $transferencia
                                ->contaDestino
                                ?->nome
                                ?? '-' }}
                        </td>

                        <td class="valor">

                            R$
                            {{ number_format(
                                $transferencia->valor,
                                2,
                                ',',
                                '.'
                            ) }}

                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>


        <div class="pagination-wrap">
            {{ $transferencias->links() }}
        </div>

    @else

        <div class="empty-state">
            Nenhuma transferência realizada.
        </div>

    @endif

</div>

@endsection