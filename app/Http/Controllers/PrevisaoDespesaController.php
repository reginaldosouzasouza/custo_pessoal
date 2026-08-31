<?php

namespace App\Http\Controllers;

use App\Models\Despesa;
use App\Models\Fatura;
use App\Models\Parcela;
use App\Models\Recorrencia;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class PrevisaoDespesaController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();

        /*
        |--------------------------------------------------------------------------
        | MÊS DA PREVISÃO
        |--------------------------------------------------------------------------
        */

        $mes = $request->input(
            'mes',
            now()->format('Y-m')
        );

        try {

            $inicioMes = Carbon::createFromFormat(
                'Y-m-d',
                $mes . '-01'
            )->startOfMonth();

        } catch (\Throwable $e) {

            $inicioMes = now()
                ->startOfMonth();

            $mes = $inicioMes
                ->format('Y-m');
        }


        $fimMes = $inicioMes
            ->copy()
            ->endOfMonth();


        /*
        |--------------------------------------------------------------------------
        | ITENS DA PREVISÃO
        |--------------------------------------------------------------------------
        */

        $itens = collect();


        /*
        |--------------------------------------------------------------------------
        | 1. DESPESAS JÁ LANÇADAS
        |--------------------------------------------------------------------------
        */

        $queryDespesas = Despesa::query()
            ->with([
                'categoria',
                'conta',
            ])
            ->where(
                'user_id',
                $userId
            )
            ->whereBetween(
                'data_vencimento',
                [
                    $inicioMes->toDateString(),
                    $fimMes->toDateString(),
                ]
            )
            ->where(
                'situacao',
                'pendente'
            );


        if (
            Schema::hasColumn(
                'despesas',
                'recorrencia_id'
            )
        ) {

            $queryDespesas
                ->whereNull(
                    'recorrencia_id'
                );
        }


        $despesas =
            $queryDespesas->get();


        foreach ($despesas as $despesa) {

            $itens->push([

                'tipo' =>
                    'despesa',

                'origem' =>
                    'Despesa',

                'descricao' =>
                    $despesa->descricao,

                'categoria' =>
                    $despesa
                        ->categoria?->nome
                    ?? '-',

                'vencimento' =>
                    $despesa
                        ->data_vencimento,

                'situacao' =>
                    $despesa->situacao,

                'valor' =>
                    (float)
                    $despesa->valor,

                'id' =>
                    $despesa->id,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | 2. PARCELAS
        |--------------------------------------------------------------------------
        */

        $parcelas = Parcela::query()
            ->with([
                'parcelamento.categoria',
                'conta',
            ])
            ->where(
                'user_id',
                $userId
            )
            ->whereBetween(
                'data_vencimento',
                [
                    $inicioMes->toDateString(),
                    $fimMes->toDateString(),
                ]
            )
            ->where(
                'situacao',
                'pendente'
            )
            ->get();


        foreach ($parcelas as $parcela) {

            $parcelamento =
                $parcela->parcelamento;

            $categoria =
                $parcelamento?->categoria;


            $itens->push([

                'tipo' =>
                    'parcela',

                'origem' =>
                    'Parcela',

                'descricao' =>
                    (
                        $parcelamento?->descricao
                        ?? 'Parcelamento'
                    )
                    . ' - '
                    . $parcela->numero_parcela
                    . '/'
                    . $parcela->total_parcelas,

                'categoria' =>
                    $categoria?->nome
                    ?? '-',

                'vencimento' =>
                    $parcela
                        ->data_vencimento,

                'situacao' =>
                    $parcela->situacao,

                'valor' =>
                    (float)
                    $parcela->valor,

                'id' =>
                    $parcela->id,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | 3. DESPESAS RECORRENTES
        |--------------------------------------------------------------------------
        */

        $recorrencias = Recorrencia::query()
            ->with('categoria')
            ->where(
                'user_id',
                $userId
            )
            ->where(
                'tipo',
                'despesa'
            )
            ->where(
                'ativa',
                true
            )
            ->whereDate(
                'data_inicio',
                '<=',
                $fimMes->toDateString()
            )
            ->where(
                function ($query) use (
                    $inicioMes
                ) {

                    $query
                        ->whereNull(
                            'data_fim'
                        )
                        ->orWhereDate(
                            'data_fim',
                            '>=',
                            $inicioMes
                                ->toDateString()
                        );
                }
            )
            ->get();


        foreach (
            $recorrencias
            as $recorrencia
        ) {

            $vencimentos =
                $this
                    ->vencimentosDaRecorrenciaNoMes(
                        $recorrencia,
                        $inicioMes,
                        $fimMes
                    );


            foreach (
                $vencimentos
                as $vencimento
            ) {

                if (
                    Schema::hasColumn(
                        'despesas',
                        'recorrencia_id'
                    )
                ) {

                    $jaGerada =
                        Despesa::query()
                            ->where(
                                'user_id',
                                $userId
                            )
                            ->where(
                                'recorrencia_id',
                                $recorrencia->id
                            )
                            ->whereDate(
                                'data_vencimento',
                                $vencimento
                                    ->toDateString()
                            )
                            ->where(
                                'situacao',
                                '!=',
                                'cancelada'
                            )
                            ->exists();


                    if ($jaGerada) {
                        continue;
                    }
                }


                $itens->push([

                    'tipo' =>
                        'recorrente',

                    'origem' =>
                        'Recorrente',

                    'descricao' =>
                        $recorrencia
                            ->descricao,

                    'categoria' =>
                        $recorrencia
                            ->categoria?->nome
                        ?? '-',

                    'vencimento' =>
                        $vencimento,

                    'situacao' =>
                        'prevista',

                    'valor' =>
                        (float) (
                            $recorrencia
                                ->valor_padrao
                            ?? 0
                        ),

                    'tipo_valor' =>
                        $recorrencia
                            ->tipo_valor,

                    'id' =>
                        $recorrencia->id,
                ]);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 4. FATURAS DOS CARTÕES
        |--------------------------------------------------------------------------
        */

        $faturas = Fatura::query()
            ->with('cartao')
            ->where(
                'user_id',
                $userId
            )
            ->whereBetween(
                'data_vencimento',
                [
                    $inicioMes->toDateString(),
                    $fimMes->toDateString(),
                ]
            )
            ->get();


        foreach ($faturas as $fatura) {

            $valor =
                (float)
                $fatura->valor_total;


            $itens->push([

                'tipo' =>
                    'cartao',

                'origem' =>
                    'Cartão',

                'descricao' =>
                    'Fatura '
                    . (
                        $fatura
                            ->cartao?->nome
                        ?? 'Cartão'
                    ),

                'categoria' =>
                    'Cartão de crédito',

                'vencimento' =>
                    $fatura
                        ->data_vencimento,

                'situacao' =>
                    $fatura->situacao,

                'valor' =>
                    $valor,

                'id' =>
                    $fatura->id,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | ORDENAÇÃO
        |--------------------------------------------------------------------------
        */

        $itens = $itens
            ->sortBy(
                function ($item) {

                    if (
                        empty(
                            $item['vencimento']
                        )
                    ) {
                        return '9999-12-31';
                    }

                    return Carbon::parse(
                        $item['vencimento']
                    )->format(
                        'Y-m-d'
                    );
                }
            )
            ->values();


        /*
        |--------------------------------------------------------------------------
        | TOTAIS
        |--------------------------------------------------------------------------
        */

        $totalDespesas =
            $itens
                ->where(
                    'tipo',
                    'despesa'
                )
                ->sum('valor');


        $totalParcelas =
            $itens
                ->where(
                    'tipo',
                    'parcela'
                )
                ->sum('valor');


        $totalRecorrentes =
            $itens
                ->where(
                    'tipo',
                    'recorrente'
                )
                ->sum('valor');


        $totalCartoes =
            $itens
                ->where(
                    'tipo',
                    'cartao'
                )
                ->sum('valor');


        $totalPrevisto =
            $totalDespesas
            + $totalParcelas
            + $totalRecorrentes
            + $totalCartoes;


        return view(
            'previsao-despesas.index',
            compact(
                'mes',
                'inicioMes',
                'fimMes',
                'itens',
                'totalDespesas',
                'totalParcelas',
                'totalRecorrentes',
                'totalCartoes',
                'totalPrevisto'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | VENCIMENTOS DA RECORRÊNCIA
    |--------------------------------------------------------------------------
    */

    private function vencimentosDaRecorrenciaNoMes(
        Recorrencia $recorrencia,
        Carbon $inicioMes,
        Carbon $fimMes
    ): Collection {

        $resultado =
            collect();


        $inicioRecorrencia =
            Carbon::parse(
                $recorrencia
                    ->data_inicio
            )->startOfDay();


        $fimRecorrencia =
            $recorrencia->data_fim
                ? Carbon::parse(
                    $recorrencia
                        ->data_fim
                )->endOfDay()
                : null;


        /*
        |--------------------------------------------------------------------------
        | SEMANAL
        |--------------------------------------------------------------------------
        */

        if (
            $recorrencia
                ->frequencia
            === 'semanal'
        ) {

            $data =
                $inicioRecorrencia
                    ->copy();


            while (
                $data->lt(
                    $inicioMes
                )
            ) {
                $data->addWeek();
            }


            while (
                $data->lte(
                    $fimMes
                )
            ) {

                if (
                    !$fimRecorrencia
                    ||
                    $data->lte(
                        $fimRecorrencia
                    )
                ) {

                    $resultado->push(
                        $data->copy()
                    );
                }


                $data->addWeek();
            }


            return $resultado;
        }


        /*
        |--------------------------------------------------------------------------
        | OUTRAS FREQUÊNCIAS
        |--------------------------------------------------------------------------
        */

        $intervaloMeses = match (
            $recorrencia
                ->frequencia
        ) {
            'mensal' =>
                1,

            'trimestral' =>
                3,

            'semestral' =>
                6,

            'anual' =>
                12,

            default =>
                null,
        };


        if (!$intervaloMeses) {
            return $resultado;
        }


        $mesInicio =
            $inicioRecorrencia
                ->copy()
                ->startOfMonth();


        $diferencaMeses =
            $mesInicio
                ->diffInMonths(
                    $inicioMes,
                    false
                );


        if (
            $diferencaMeses < 0
        ) {
            return $resultado;
        }


        if (
            $diferencaMeses
            % $intervaloMeses
            !== 0
        ) {
            return $resultado;
        }


        $dia =
            (int) (
                $recorrencia
                    ->dia_vencimento
                ?: $inicioRecorrencia
                    ->day
            );


        $dia = min(
            $dia,
            $inicioMes
                ->daysInMonth
        );


        $vencimento =
            $inicioMes
                ->copy()
                ->day($dia);


        if (
            $vencimento->lt(
                $inicioRecorrencia
            )
        ) {
            return $resultado;
        }


        if (
            $fimRecorrencia
            &&
            $vencimento->gt(
                $fimRecorrencia
            )
        ) {
            return $resultado;
        }


        $resultado->push(
            $vencimento
        );


        return $resultado;
    }
}