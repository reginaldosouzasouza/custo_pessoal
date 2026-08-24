<?php

namespace App\Http\Controllers;

use App\Models\MovimentacaoConta;
use App\Models\Receita;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelatorioController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();

        $dataInicial = $request->input(
            'data_inicial',
            now()->startOfMonth()->format('Y-m-d')
        );

        $dataFinal = $request->input(
            'data_final',
            now()->endOfMonth()->format('Y-m-d')
        );

        $inicio = Carbon::parse($dataInicial)->startOfDay();
        $fim = Carbon::parse($dataFinal)->endOfDay();


        // RECEITAS RECEBIDAS
        $totalReceitas = Receita::query()
            ->where('user_id', $userId)
            ->where('situacao', 'recebida')
            ->whereBetween(
                'data_recebimento',
                [$inicio, $fim]
            )
            ->sum('valor');


        // DESPESAS REALMENTE PAGAS
        $totalDespesas = MovimentacaoConta::query()
            ->where('user_id', $userId)
            ->where('tipo', 'saida')
            ->where('estornada', false)
            ->whereIn(
                'origem_tipo',
                [
                    'despesa',
                    'parcela',
                    'pagamento_fatura',
                ]
            )
            ->whereBetween(
                'data_movimentacao',
                [$inicio, $fim]
            )
            ->sum('valor');


        $resultadoPeriodo =
            (float) $totalReceitas
            - (float) $totalDespesas;


        // RECEITAS POR CATEGORIA
        $receitasPorCategoria = DB::table('receitas as r')
            ->join(
                'categorias as c',
                'c.id',
                '=',
                'r.categoria_id'
            )
            ->where('r.user_id', $userId)
            ->where('r.situacao', 'recebida')
            ->whereBetween(
                'r.data_recebimento',
                [$inicio, $fim]
            )
            ->select(
                'c.nome',
                DB::raw('SUM(r.valor) as total')
            )
            ->groupBy('c.id', 'c.nome')
            ->orderByDesc('total')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | DESPESAS POR CATEGORIA
        |--------------------------------------------------------------------------
        */

        $despesasCategorias = [];


        // DESPESAS COMUNS
        $despesasComuns = DB::table(
            'movimentacoes_conta as m'
        )
            ->join(
                'despesas as d',
                function ($join) {
                    $join->on(
                        'd.id',
                        '=',
                        'm.origem_id'
                    );

                    $join->where(
                        'm.origem_tipo',
                        '=',
                        'despesa'
                    );
                }
            )
            ->join(
                'categorias as c',
                'c.id',
                '=',
                'd.categoria_id'
            )
            ->where('m.user_id', $userId)
            ->where('m.tipo', 'saida')
            ->where('m.estornada', false)
            ->whereBetween(
                'm.data_movimentacao',
                [$inicio, $fim]
            )
            ->select(
                'c.nome',
                DB::raw('SUM(m.valor) as total')
            )
            ->groupBy('c.id', 'c.nome')
            ->get();


        foreach ($despesasComuns as $item) {
            $despesasCategorias[$item->nome] =
                ($despesasCategorias[$item->nome] ?? 0)
                + (float) $item->total;
        }


        // PARCELAS COMUNS
        $parcelasComuns = DB::table(
            'movimentacoes_conta as m'
        )
            ->join(
                'parcelas as pa',
                function ($join) {
                    $join->on(
                        'pa.id',
                        '=',
                        'm.origem_id'
                    );

                    $join->where(
                        'm.origem_tipo',
                        '=',
                        'parcela'
                    );
                }
            )
            ->join(
                'parcelamentos as p',
                'p.id',
                '=',
                'pa.parcelamento_id'
            )
            ->join(
                'categorias as c',
                'c.id',
                '=',
                'p.categoria_id'
            )
            ->where('m.user_id', $userId)
            ->where('m.tipo', 'saida')
            ->where('m.estornada', false)
            ->whereBetween(
                'm.data_movimentacao',
                [$inicio, $fim]
            )
            ->select(
                'c.nome',
                DB::raw('SUM(m.valor) as total')
            )
            ->groupBy('c.id', 'c.nome')
            ->get();


        foreach ($parcelasComuns as $item) {
            $despesasCategorias[$item->nome] =
                ($despesasCategorias[$item->nome] ?? 0)
                + (float) $item->total;
        }


        // FATURAS PAGAS, DISTRIBUÍDAS POR CATEGORIA
        $faturasCategorias = DB::table(
            'movimentacoes_conta as m'
        )
            ->join(
                'faturas as f',
                function ($join) {
                    $join->on(
                        'f.id',
                        '=',
                        'm.origem_id'
                    );

                    $join->where(
                        'm.origem_tipo',
                        '=',
                        'pagamento_fatura'
                    );
                }
            )
            ->join(
                'parcelas_cartao as pc',
                'pc.fatura_id',
                '=',
                'f.id'
            )
            ->join(
                'compras_cartao as cc',
                'cc.id',
                '=',
                'pc.compra_cartao_id'
            )
            ->join(
                'categorias as c',
                'c.id',
                '=',
                'cc.categoria_id'
            )
            ->where('m.user_id', $userId)
            ->where('m.tipo', 'saida')
            ->where('m.estornada', false)
            ->whereBetween(
                'm.data_movimentacao',
                [$inicio, $fim]
            )
            ->select(
                'c.nome',
                DB::raw('SUM(pc.valor) as total')
            )
            ->groupBy('c.id', 'c.nome')
            ->get();


        foreach ($faturasCategorias as $item) {
            $despesasCategorias[$item->nome] =
                ($despesasCategorias[$item->nome] ?? 0)
                + (float) $item->total;
        }


        arsort($despesasCategorias);


        // MOVIMENTAÇÕES
        $movimentacoes = MovimentacaoConta::query()
            ->with('conta')
            ->where('user_id', $userId)
            ->where('estornada', false)
            ->whereBetween(
                'data_movimentacao',
                [$inicio, $fim]
            )
            ->orderByDesc('data_movimentacao')
            ->orderByDesc('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | DESPESAS ESSENCIAIS / NÃO ESSENCIAIS
        |--------------------------------------------------------------------------
        */

        $totalEssenciais = 0;
        $totalNaoEssenciais = 0;


        /*
        |--------------------------------------------------------------------------
        | DESPESAS COMUNS
        |--------------------------------------------------------------------------
        */

        $despesasClassificacao = DB::table(
            'movimentacoes_conta as m'
        )
            ->join(
                'despesas as d',
                function ($join) {

                    $join->on(
                        'd.id',
                        '=',
                        'm.origem_id'
                    );

                    $join->where(
                        'm.origem_tipo',
                        '=',
                        'despesa'
                    );
                }
            )
            ->where(
                'm.user_id',
                $userId
            )
            ->where(
                'm.tipo',
                'saida'
            )
            ->where(
                'm.estornada',
                false
            )
            ->whereBetween(
                'm.data_movimentacao',
                [
                    $inicio,
                    $fim,
                ]
            )
            ->select(
                'd.essencial',
                DB::raw(
                    'SUM(m.valor) as total'
                )
            )
            ->groupBy('d.essencial')
            ->get();


        foreach ($despesasClassificacao as $item) {

            if ((bool) $item->essencial) {

                $totalEssenciais +=
                    (float) $item->total;

            } else {

                $totalNaoEssenciais +=
                    (float) $item->total;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | PARCELAMENTOS COMUNS
        |--------------------------------------------------------------------------
        |
        | A tabela parcelamentos não possui a coluna "essencial".
        | Por isso usamos a classificação da categoria vinculada.
        |
        */

        $parcelasClassificacao = DB::table(
            'movimentacoes_conta as m'
        )
            ->join(
                'parcelas as pa',
                function ($join) {

                    $join->on(
                        'pa.id',
                        '=',
                        'm.origem_id'
                    );

                    $join->where(
                        'm.origem_tipo',
                        '=',
                        'parcela'
                    );
                }
            )
            ->join(
                'parcelamentos as p',
                'p.id',
                '=',
                'pa.parcelamento_id'
            )
            ->join(
                'categorias as c',
                'c.id',
                '=',
                'p.categoria_id'
            )
            ->where(
                'm.user_id',
                $userId
            )
            ->where(
                'm.tipo',
                'saida'
            )
            ->where(
                'm.estornada',
                false
            )
            ->whereBetween(
                'm.data_movimentacao',
                [
                    $inicio,
                    $fim,
                ]
            )
            ->select(
                'c.classificacao',
                DB::raw(
                    'SUM(m.valor) as total'
                )
            )
            ->groupBy('c.classificacao')
            ->get();


        foreach ($parcelasClassificacao as $item) {

            if ($item->classificacao === 'essencial') {

                $totalEssenciais +=
                    (float) $item->total;

            } else {

                $totalNaoEssenciais +=
                    (float) $item->total;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | CARTÃO DE CRÉDITO
        |--------------------------------------------------------------------------
        |
        | Distribuímos o pagamento da fatura conforme
        | a classificação da compra correspondente.
        |
        */

        $cartaoClassificacao = DB::table(
            'movimentacoes_conta as m'
        )
            ->join(
                'faturas as f',
                function ($join) {

                    $join->on(
                        'f.id',
                        '=',
                        'm.origem_id'
                    );

                    $join->where(
                        'm.origem_tipo',
                        '=',
                        'pagamento_fatura'
                    );
                }
            )
            ->join(
                'parcelas_cartao as pc',
                'pc.fatura_id',
                '=',
                'f.id'
            )
            ->join(
                'compras_cartao as cc',
                'cc.id',
                '=',
                'pc.compra_cartao_id'
            )
            ->where(
                'm.user_id',
                $userId
            )
            ->where(
                'm.tipo',
                'saida'
            )
            ->where(
                'm.estornada',
                false
            )
            ->whereBetween(
                'm.data_movimentacao',
                [
                    $inicio,
                    $fim,
                ]
            )
            ->select(
                'cc.essencial',
                DB::raw(
                    'SUM(pc.valor) as total'
                )
            )
            ->groupBy('cc.essencial')
            ->get();


        foreach ($cartaoClassificacao as $item) {

            if ((bool) $item->essencial) {

                $totalEssenciais +=
                    (float) $item->total;

            } else {

                $totalNaoEssenciais +=
                    (float) $item->total;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | PERCENTUAIS
        |--------------------------------------------------------------------------
        */

        $totalClassificado =
            $totalEssenciais
            + $totalNaoEssenciais;


        $percentualEssenciais =
            $totalClassificado > 0
                ? (
                    $totalEssenciais
                    / $totalClassificado
                ) * 100
                : 0;


        $percentualNaoEssenciais =
            $totalClassificado > 0
                ? (
                    $totalNaoEssenciais
                    / $totalClassificado
                ) * 100
                : 0;    

                /*
        |--------------------------------------------------------------------------
        | EVOLUÇÃO MENSAL - ÚLTIMOS 6 MESES
        |--------------------------------------------------------------------------
        */

        $graficoMeses = [];
        $graficoReceitas = [];
        $graficoDespesas = [];

        for ($i = 5; $i >= 0; $i--) {

            $mesRef = now()
                ->copy()
                ->subMonths($i);

            $inicioMes = $mesRef
                ->copy()
                ->startOfMonth();

            $fimMes = $mesRef
                ->copy()
                ->endOfMonth();


            /*
            |--------------------------------------------------------------------------
            | RECEITAS DO MÊS
            |--------------------------------------------------------------------------
            */

            $receitasMes = Receita::query()
                ->where('user_id', $userId)
                ->where('situacao', 'recebida')
                ->whereBetween(
                    'data_recebimento',
                    [
                        $inicioMes,
                        $fimMes,
                    ]
                )
                ->sum('valor');


            /*
            |--------------------------------------------------------------------------
            | DESPESAS DO MÊS
            |--------------------------------------------------------------------------
            */

            $despesasMes = MovimentacaoConta::query()
                ->where('user_id', $userId)
                ->where('tipo', 'saida')
                ->where('estornada', false)
                ->whereIn(
                    'origem_tipo',
                    [
                        'despesa',
                        'parcela',
                        'pagamento_fatura',
                    ]
                )
                ->whereBetween(
                    'data_movimentacao',
                    [
                        $inicioMes,
                        $fimMes,
                    ]
                )
                ->sum('valor');


            $graficoMeses[] =
                $mesRef
                    ->locale('pt_BR')
                    ->translatedFormat('M/y');

            $graficoReceitas[] =
                (float) $receitasMes;

            $graficoDespesas[] =
                (float) $despesasMes;
        }
                
        return view(
            'relatorios.index',
            compact(
                'dataInicial',
                'dataFinal',
                'totalReceitas',
                'totalDespesas',
                'resultadoPeriodo',
                'receitasPorCategoria',
                'despesasCategorias',
                'movimentacoes',
                'totalEssenciais',
                'totalNaoEssenciais',
                'percentualEssenciais',
                'percentualNaoEssenciais',
                'graficoMeses',
                'graficoReceitas',
                'graficoDespesas'
            )
        );
    }
}