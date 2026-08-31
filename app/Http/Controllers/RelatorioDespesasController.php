<?php

namespace App\Http\Controllers;

use App\Exports\RelatorioDespesasExport;
use App\Models\Categoria;
use App\Models\Conta;
use App\Models\Despesa;
use App\Models\Fatura;
use App\Models\Parcela;
use App\Models\Recorrencia;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;

class RelatorioDespesasController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | TELA DO RELATÓRIO
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $dados = $this->montarRelatorio($request);

        $userId = auth()->id();

        $categorias = Categoria::query()
            ->where('user_id', $userId)
            ->where('tipo', 'despesa')
            ->where('ativa', true)
            ->orderBy('nome')
            ->get();

        $contas = Conta::query()
            ->where('user_id', $userId)
            ->where('ativa', true)
            ->orderBy('nome')
            ->get();

        return view(
            'relatorios.despesas',
            array_merge(
                $dados,
                [
                    'categorias' => $categorias,
                    'contas' => $contas,
                ]
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EXPORTAÇÃO EXCEL
    |--------------------------------------------------------------------------
    */

    public function excel(Request $request)
    {
        $dados = $this->montarRelatorio($request);

        $nomeArquivo =
            'relatorio-despesas-'
            . Carbon::parse($dados['dataInicio'])->format('Ymd')
            . '-a-'
            . Carbon::parse($dados['dataFim'])->format('Ymd')
            . '.xlsx';

        return Excel::download(
            new RelatorioDespesasExport(
                $dados['itens']
            ),
            $nomeArquivo
        );
    }


    /*
    |--------------------------------------------------------------------------
    | MONTA RELATÓRIO
    |--------------------------------------------------------------------------
    |
    | Esta função é usada tanto pela tela quanto pelo Excel.
    | Dessa forma os dois sempre utilizam exatamente os mesmos filtros
    | e regras de cálculo.
    |
    */

    private function montarRelatorio(Request $request): array
    {
        $userId = auth()->id();

        /*
        |--------------------------------------------------------------------------
        | FILTROS
        |--------------------------------------------------------------------------
        */

        $dataInicio = $request->input(
            'data_inicio',
            now()->startOfMonth()->format('Y-m-d')
        );

        $dataFim = $request->input(
            'data_fim',
            now()->endOfMonth()->format('Y-m-d')
        );

        $origem = $request->input('origem');
        $categoriaId = $request->input('categoria_id');
        $situacao = $request->input('situacao');
        $contaId = $request->input('conta_id');


        /*
        |--------------------------------------------------------------------------
        | DATAS
        |--------------------------------------------------------------------------
        */

        try {
            $inicio = Carbon::parse($dataInicio)->startOfDay();
        } catch (\Throwable $e) {
            $inicio = now()->startOfMonth()->startOfDay();
            $dataInicio = $inicio->format('Y-m-d');
        }


        try {
            $fim = Carbon::parse($dataFim)->endOfDay();
        } catch (\Throwable $e) {
            $fim = now()->endOfMonth()->endOfDay();
            $dataFim = $fim->format('Y-m-d');
        }


        /*
        |--------------------------------------------------------------------------
        | LISTA CONSOLIDADA
        |--------------------------------------------------------------------------
        */

        $itens = collect();


        /*
        |--------------------------------------------------------------------------
        | 1. DESPESAS
        |--------------------------------------------------------------------------
        */

        if (
            !$origem
            || $origem === 'despesa'
        ) {

            $queryDespesas = Despesa::query()
                ->with([
                    'categoria',
                    'conta',
                ])
                ->where('user_id', $userId)
                ->whereBetween(
                    'data_vencimento',
                    [
                        $inicio->toDateString(),
                        $fim->toDateString(),
                    ]
                );


            if ($categoriaId) {
                $queryDespesas->where(
                    'categoria_id',
                    $categoriaId
                );
            }


            if ($contaId) {
                $queryDespesas->where(
                    'conta_id',
                    $contaId
                );
            }


            if ($situacao) {
                $queryDespesas->where(
                    'situacao',
                    $situacao
                );
            }


            $despesas = $queryDespesas->get();


            foreach ($despesas as $despesa) {

                $itens->push([

                    'tipo' => 'despesa',

                    'origem' => 'Despesa',

                    'descricao' =>
                        $despesa->descricao,

                    'categoria_id' =>
                        $despesa->categoria_id,

                    'categoria' =>
                        $despesa->categoria?->nome
                        ?? '-',

                    'vencimento' =>
                        $despesa->data_vencimento,

                    'pagamento' =>
                        $despesa->data_pagamento,

                    'conta_id' =>
                        $despesa->conta_id,

                    'conta' =>
                        $despesa->conta?->nome
                        ?? '-',

                    'situacao' =>
                        $despesa->situacao,

                    'valor' =>
                        (float) $despesa->valor,

                    'id' =>
                        $despesa->id,
                ]);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 2. PARCELAS
        |--------------------------------------------------------------------------
        */

        if (
            !$origem
            || $origem === 'parcela'
        ) {

            $queryParcelas = Parcela::query()
                ->with([
                    'parcelamento.categoria',
                    'conta',
                ])
                ->where('user_id', $userId)
                ->whereBetween(
                    'data_vencimento',
                    [
                        $inicio->toDateString(),
                        $fim->toDateString(),
                    ]
                );


            if ($contaId) {
                $queryParcelas->where(
                    'conta_id',
                    $contaId
                );
            }


            if ($situacao) {
                $queryParcelas->where(
                    'situacao',
                    $situacao
                );
            }


            if ($categoriaId) {

                $queryParcelas->whereHas(
                    'parcelamento',
                    function ($query) use ($categoriaId) {

                        $query->where(
                            'categoria_id',
                            $categoriaId
                        );
                    }
                );
            }


            $parcelas = $queryParcelas->get();


            foreach ($parcelas as $parcela) {

                $parcelamento =
                    $parcela->parcelamento;

                $categoria =
                    $parcelamento?->categoria;


                $itens->push([

                    'tipo' => 'parcela',

                    'origem' => 'Parcela',

                    'descricao' =>
                        (
                            $parcelamento?->descricao
                            ?? 'Parcelamento'
                        )
                        . ' - '
                        . $parcela->numero_parcela
                        . '/'
                        . $parcela->total_parcelas,

                    'categoria_id' =>
                        $parcelamento?->categoria_id,

                    'categoria' =>
                        $categoria?->nome
                        ?? '-',

                    'vencimento' =>
                        $parcela->data_vencimento,

                    'pagamento' =>
                        $parcela->data_pagamento,

                    'conta_id' =>
                        $parcela->conta_id,

                    'conta' =>
                        $parcela->conta?->nome
                        ?? '-',

                    'situacao' =>
                        $parcela->situacao,

                    'valor' =>
                        (float) $parcela->valor,

                    'id' =>
                        $parcela->id,
                ]);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 3. RECORRÊNCIAS
        |--------------------------------------------------------------------------
        */

        if (
            !$origem
            || $origem === 'recorrente'
        ) {

            $queryRecorrencias = Recorrencia::query()
                ->with('categoria')
                ->where('user_id', $userId)
                ->where('tipo', 'despesa')
                ->where('ativa', true)
                ->whereDate(
                    'data_inicio',
                    '<=',
                    $fim->toDateString()
                )
                ->where(
                    function ($query) use ($inicio) {

                        $query
                            ->whereNull('data_fim')
                            ->orWhereDate(
                                'data_fim',
                                '>=',
                                $inicio->toDateString()
                            );
                    }
                );


            if ($categoriaId) {

                $queryRecorrencias->where(
                    'categoria_id',
                    $categoriaId
                );
            }


            $recorrencias =
                $queryRecorrencias->get();


            foreach ($recorrencias as $recorrencia) {

                $mesAtual =
                    $inicio
                        ->copy()
                        ->startOfMonth();

                $ultimoMes =
                    $fim
                        ->copy()
                        ->startOfMonth();


                while (
                    $mesAtual->lte($ultimoMes)
                ) {

                    $fimDoMes =
                        $mesAtual
                            ->copy()
                            ->endOfMonth();


                    $vencimentos =
                        $this
                            ->vencimentosDaRecorrenciaNoMes(
                                $recorrencia,
                                $mesAtual,
                                $fimDoMes
                            );


                    foreach ($vencimentos as $vencimento) {

                        /*
                         * Respeita período informado
                         */
                        if (
                            $vencimento->lt($inicio)
                            ||
                            $vencimento->gt($fim)
                        ) {
                            continue;
                        }


                        /*
                         * Não duplica recorrência
                         * já transformada em despesa.
                         */
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


                        /*
                         * Recorrência ainda não materializada
                         * tem situação "prevista".
                         */
                        if (
                            $situacao
                            &&
                            $situacao !== 'prevista'
                        ) {
                            continue;
                        }


                        /*
                         * Filtro por conta padrão
                         */
                        if (
                            $contaId
                            &&
                            (string) $recorrencia->conta_padrao_id
                                !== (string) $contaId
                        ) {
                            continue;
                        }


                        $itens->push([

                            'tipo' =>
                                'recorrente',

                            'origem' =>
                                'Recorrente',

                            'descricao' =>
                                $recorrencia->descricao,

                            'categoria_id' =>
                                $recorrencia->categoria_id,

                            'categoria' =>
                                $recorrencia
                                    ->categoria?->nome
                                ?? '-',

                            'vencimento' =>
                                $vencimento,

                            'pagamento' =>
                                null,

                            'conta_id' =>
                                $recorrencia
                                    ->conta_padrao_id,

                            'conta' =>
                                '-',

                            'situacao' =>
                                'prevista',

                            'valor' =>
                                (float) (
                                    $recorrencia
                                        ->valor_padrao
                                    ?? 0
                                ),

                            'id' =>
                                $recorrencia->id,
                        ]);
                    }


                    $mesAtual->addMonth();
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 4. FATURAS
        |--------------------------------------------------------------------------
        */

        if (
            !$origem
            || $origem === 'fatura'
        ) {

            $queryFaturas = Fatura::query()
                ->with('cartao')
                ->where('user_id', $userId)
                ->whereBetween(
                    'data_vencimento',
                    [
                        $inicio->toDateString(),
                        $fim->toDateString(),
                    ]
                );


            /*
             * Fatura não possui categoria financeira
             * como as demais despesas.
             *
             * Portanto, quando uma categoria específica
             * estiver sendo filtrada, não mostramos faturas.
             */
            if ($categoriaId) {

                $queryFaturas->whereRaw('1 = 0');
            }


            if ($situacao) {

                $queryFaturas->where(
                    'situacao',
                    $situacao
                );
            }


            if ($contaId) {

                $queryFaturas->where(
                    'conta_id',
                    $contaId
                );
            }


            $faturas =
                $queryFaturas->get();


            foreach ($faturas as $fatura) {

                $itens->push([

                    'tipo' =>
                        'fatura',

                    'origem' =>
                        'Fatura',

                    'descricao' =>
                        'Fatura '
                        . (
                            $fatura->cartao?->nome
                            ?? 'Cartão'
                        ),

                    'categoria_id' =>
                        null,

                    'categoria' =>
                        'Cartão de crédito',

                    'vencimento' =>
                        $fatura->data_vencimento,

                    'pagamento' =>
                        $fatura->data_pagamento,

                    'conta_id' =>
                        $fatura->conta_id
                        ?? null,

                    'conta' =>
                        '-',

                    'situacao' =>
                        $fatura->situacao,

                    'valor' =>
                        (float) $fatura->valor_total,

                    'id' =>
                        $fatura->id,
                ]);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | ORDENAÇÃO
        |--------------------------------------------------------------------------
        */

        $itens = $itens
            ->sortBy(
                function ($item) {

                    return Carbon::parse(
                        $item['vencimento']
                    )->format('Y-m-d');
                }
            )
            ->values();


        /*
        |--------------------------------------------------------------------------
        | TOTAIS
        |--------------------------------------------------------------------------
        */

        $totalGeral =
            (float) $itens->sum('valor');


        $totalPago =
            (float)
            $itens
                ->filter(
                    function ($item) {

                        return in_array(
                            $item['situacao'],
                            [
                                'paga',
                                'pago',
                            ],
                            true
                        );
                    }
                )
                ->sum('valor');


        $totalPendente =
            (float)
            $itens
                ->filter(
                    function ($item) {

                        return in_array(
                            $item['situacao'],
                            [
                                'pendente',
                                'aberta',
                                'fechada',
                                'prevista',
                                'vencida',
                            ],
                            true
                        );
                    }
                )
                ->sum('valor');


        $totalCancelado =
            (float)
            $itens
                ->where(
                    'situacao',
                    'cancelada'
                )
                ->sum('valor');


        return [
            'itens' =>
                $itens,

            'dataInicio' =>
                $dataInicio,

            'dataFim' =>
                $dataFim,

            'origem' =>
                $origem,

            'categoriaId' =>
                $categoriaId,

            'situacao' =>
                $situacao,

            'contaId' =>
                $contaId,

            'totalGeral' =>
                $totalGeral,

            'totalPago' =>
                $totalPago,

            'totalPendente' =>
                $totalPendente,

            'totalCancelado' =>
                $totalCancelado,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | VENCIMENTOS DA RECORRÊNCIA NO MÊS
    |--------------------------------------------------------------------------
    */

    private function vencimentosDaRecorrenciaNoMes(
        Recorrencia $recorrencia,
        Carbon $inicioMes,
        Carbon $fimMes
    ) {
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
        */

        if (
            $recorrencia->frequencia
            === 'semanal'
        ) {

            $data =
                $inicioRecorrencia->copy();


            while (
                $data->lt($inicioMes)
            ) {
                $data->addWeek();
            }


            while (
                $data->lte($fimMes)
            ) {

                if (
                    !$fimRecorrencia
                    ||
                    $data->lte($fimRecorrencia)
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
            $recorrencia->frequencia
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


        $dia =
            (int) (
                $recorrencia->dia_vencimento
                ?: $inicioRecorrencia->day
            );


        $dia = min(
            $dia,
            $inicioMes->daysInMonth
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