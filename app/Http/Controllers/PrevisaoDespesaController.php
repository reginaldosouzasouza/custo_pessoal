<?php

namespace App\Http\Controllers;

use App\Models\Despesa;
use App\Models\Fatura;
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
                'Y-m',
                $mes
            )->startOfMonth();

        } catch (\Throwable $e) {

            $inicioMes = now()->startOfMonth();

            $mes = $inicioMes->format('Y-m');
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
        |
        | Entram despesas com vencimento no mês.
        |
        | Se a tabela possuir recorrencia_id,
        | excluímos despesas originadas de recorrência
        | para evitar contagem duplicada.
        |
        */

        $queryDespesas = Despesa::query()
            ->with([
                'categoria',
                'conta',
            ])
            ->where('user_id', $userId)
            ->whereBetween(
                'data_vencimento',
                [
                    $inicioMes->toDateString(),
                    $fimMes->toDateString(),
                ]
            )
            ->where(
                'situacao',
                '!=',
                'cancelada'
            );

        if (
            Schema::hasColumn(
                'despesas',
                'recorrencia_id'
            )
        ) {
            $queryDespesas
                ->whereNull('recorrencia_id');
        }

        $despesas = $queryDespesas->get();


        foreach ($despesas as $despesa) {

            $itens->push([
                'tipo' =>
                    'despesa',

                'origem' =>
                    'Despesa',

                'descricao' =>
                    $despesa->descricao,

                'categoria' =>
                    $despesa->categoria?->nome ?? '-',

                'vencimento' =>
                    $despesa->data_vencimento,

                'situacao' =>
                    $despesa->situacao,

                'valor' =>
                    (float) $despesa->valor,

                'id' =>
                    $despesa->id,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | 2. DESPESAS RECORRENTES
        |--------------------------------------------------------------------------
        */

        $recorrencias = Recorrencia::query()
            ->with('categoria')
            ->where('user_id', $userId)
            ->where('tipo', 'despesa')
            ->where('ativa', true)
            ->whereDate(
                'data_inicio',
                '<=',
                $fimMes->toDateString()
            )
            ->where(function ($query) use ($inicioMes) {

                $query
                    ->whereNull('data_fim')
                    ->orWhereDate(
                        'data_fim',
                        '>=',
                        $inicioMes->toDateString()
                    );
            })
            ->get();


        foreach ($recorrencias as $recorrencia) {

            $vencimentos =
                $this->vencimentosDaRecorrenciaNoMes(
                    $recorrencia,
                    $inicioMes,
                    $fimMes
                );


            foreach ($vencimentos as $vencimento) {

                /*
                 * Se existir uma despesa gerada pela própria
                 * recorrência para esse vencimento, não projetamos
                 * novamente.
                 */
                if (
                    Schema::hasColumn(
                        'despesas',
                        'recorrencia_id'
                    )
                ) {

                    $jaGerada = Despesa::query()
                        ->where('user_id', $userId)
                        ->where(
                            'recorrencia_id',
                            $recorrencia->id
                        )
                        ->whereDate(
                            'data_vencimento',
                            $vencimento->toDateString()
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
                        $recorrencia->descricao,

                    'categoria' =>
                        $recorrencia->categoria?->nome
                        ?? '-',

                    'vencimento' =>
                        $vencimento,

                    'situacao' =>
                        'prevista',

                    'valor' =>
                        (float) (
                            $recorrencia->valor_padrao
                            ?? 0
                        ),

                    'tipo_valor' =>
                        $recorrencia->tipo_valor,

                    'id' =>
                        $recorrencia->id,
                ]);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 3. FATURAS DOS CARTÕES
        |--------------------------------------------------------------------------
        |
        | Aqui entra somente a FATURA.
        | As compras do cartão não são somadas separadamente,
        | evitando duplicidade.
        |
        */

        $faturas = Fatura::query()
            ->with('cartao')
            ->where('user_id', $userId)
            ->whereBetween(
                'data_vencimento',
                [
                    $inicioMes->toDateString(),
                    $fimMes->toDateString(),
                ]
            )
            ->get();


        foreach ($faturas as $fatura) {

            /*
             * Para fatura paga mostramos o valor efetivamente
             * comprometido naquele mês.
             *
             * Para fatura não paga usamos o valor total atual.
             */

            $valor = (float) $fatura->valor_total;

            $itens->push([
                'tipo' =>
                    'cartao',

                'origem' =>
                    'Cartão',

                'descricao' =>
                    'Fatura '
                    . (
                        $fatura->cartao?->nome
                        ?? 'Cartão'
                    ),

                'categoria' =>
                    'Cartão de crédito',

                'vencimento' =>
                    $fatura->data_vencimento,

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
            ->sortBy(function ($item) {

                if (
                    empty($item['vencimento'])
                ) {
                    return '9999-12-31';
                }

                return Carbon::parse(
                    $item['vencimento']
                )->format('Y-m-d');
            })
            ->values();


        /*
        |--------------------------------------------------------------------------
        | TOTAIS
        |--------------------------------------------------------------------------
        */

        $totalDespesas = $itens
            ->where('tipo', 'despesa')
            ->sum('valor');

        $totalRecorrentes = $itens
            ->where('tipo', 'recorrente')
            ->sum('valor');

        $totalCartoes = $itens
            ->where('tipo', 'cartao')
            ->sum('valor');

        $totalPrevisto =
            $totalDespesas
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
                'totalRecorrentes',
                'totalCartoes',
                'totalPrevisto'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CALCULA OS VENCIMENTOS DA RECORRÊNCIA NO MÊS
    |--------------------------------------------------------------------------
    */

    private function vencimentosDaRecorrenciaNoMes(
        Recorrencia $recorrencia,
        Carbon $inicioMes,
        Carbon $fimMes
    ): Collection {

        $resultado = collect();

        $inicioRecorrencia =
            Carbon::parse(
                $recorrencia->data_inicio
            )->startOfDay();

        $fimRecorrencia =
            $recorrencia->data_fim
                ? Carbon::parse(
                    $recorrencia->data_fim
                )->endOfDay()
                : null;


        /*
        |--------------------------------------------------------------------------
        | SEMANAL
        |--------------------------------------------------------------------------
        |
        | Usa data_inicio como referência e repete
        | de 7 em 7 dias.
        |
        */

        if (
            $recorrencia->frequencia
            === 'semanal'
        ) {

            $data = $inicioRecorrencia->copy();

            while ($data->lt($inicioMes)) {
                $data->addWeek();
            }

            while ($data->lte($fimMes)) {

                if (
                    !$fimRecorrencia
                    || $data->lte($fimRecorrencia)
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
        | MENSAL / TRIMESTRAL / SEMESTRAL / ANUAL
        |--------------------------------------------------------------------------
        */

        $intervaloMeses = match (
            $recorrencia->frequencia
        ) {
            'mensal' => 1,
            'trimestral' => 3,
            'semestral' => 6,
            'anual' => 12,
            default => null,
        };


        if (!$intervaloMeses) {
            return $resultado;
        }


        /*
         * Verifica se o mês pesquisado corresponde
         * a uma ocorrência da recorrência.
         */

        $mesInicio = $inicioRecorrencia
            ->copy()
            ->startOfMonth();

        $diferencaMeses =
            $mesInicio->diffInMonths(
                $inicioMes,
                false
            );


        if ($diferencaMeses < 0) {
            return $resultado;
        }


        if (
            $diferencaMeses
            % $intervaloMeses
            !== 0
        ) {
            return $resultado;
        }


        /*
         * Dia do vencimento.
         *
         * Exemplo:
         * dia 31 em fevereiro vira o último
         * dia disponível daquele mês.
         */

        $dia =
            (int) (
                $recorrencia->dia_vencimento
                ?: $inicioRecorrencia->day
            );

        $dia = min(
            $dia,
            $inicioMes->daysInMonth
        );


        $vencimento = $inicioMes
            ->copy()
            ->day($dia);


        /*
         * Não pode acontecer antes do início
         * ou depois do fim da recorrência.
         */

        if (
            $vencimento->lt(
                $inicioRecorrencia
            )
        ) {
            return $resultado;
        }


        if (
            $fimRecorrencia
            && $vencimento->gt(
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