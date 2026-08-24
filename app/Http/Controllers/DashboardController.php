<?php

namespace App\Http\Controllers;

use App\Models\Despesa;
use App\Models\Fatura;
use App\Models\MovimentacaoConta;
use App\Models\Parcela;
use App\Models\Receita;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $hoje = Carbon::today();

        $inicioMes = $hoje->copy()->startOfMonth();
        $fimMes = $hoje->copy()->endOfMonth();

        /*
        |--------------------------------------------------------------------------
        | RECEITAS DO MÊS
        |--------------------------------------------------------------------------
        |
        | Somente receitas efetivamente recebidas no mês.
        |
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
        |
        | NOVA REGRA:
        |
        | Uma despesa somente aparece neste card quando
        | realmente houve saída de dinheiro.
        |
        | Portanto usamos movimentacoes_conta.
        |
        | Entram:
        | - pagamento de despesa;
        | - pagamento de parcela;
        | - pagamento de fatura.
        |
        | NÃO entram:
        | - despesas pendentes;
        | - parcelas pendentes;
        | - faturas ainda abertas.
        |
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


        /*
        |--------------------------------------------------------------------------
        | SALDO ATUAL
        |--------------------------------------------------------------------------
        |
        | Saldo inicial
        | +
        | entradas realizadas
        | -
        | saídas realizadas
        |
        */

        $saldoInicial = DB::table('contas')
            ->where('user_id', $userId)
            ->where('ativa', true)
            ->sum('saldo_inicial');


        $entradas = MovimentacaoConta::query()
            ->where('user_id', $userId)
            ->where('tipo', 'entrada')
            ->where('estornada', false)
            ->sum('valor');


        $saidas = MovimentacaoConta::query()
            ->where('user_id', $userId)
            ->where('tipo', 'saida')
            ->where('estornada', false)
            ->sum('valor');


        $saldoAtual =
            (float) $saldoInicial
            + (float) $entradas
            - (float) $saidas;


        /*
        |--------------------------------------------------------------------------
        | A VENCER
        |--------------------------------------------------------------------------
        |
        | Somente obrigações:
        |
        | - ainda pendentes;
        | - vencendo de hoje até o último dia deste mês.
        |
        | Faturas de cartão ficam no card próprio.
        |
        | Despesas do próximo mês ficam no card "Próximo mês".
        |
        */

        $despesasAVencer = Despesa::query()
            ->where('user_id', $userId)
            ->where('situacao', 'pendente')
            ->whereBetween(
                'data_vencimento',
                [
                    $hoje,
                    $fimMes,
                ]
            )
            ->sum('valor');


        $parcelasAVencer = Parcela::query()
            ->where('user_id', $userId)
            ->where('situacao', 'pendente')
            ->whereBetween(
                'data_vencimento',
                [
                    $hoje,
                    $fimMes,
                ]
            )
            ->sum('valor');


        $aVencer =
            (float) $despesasAVencer
            + (float) $parcelasAVencer;


        /*
        |--------------------------------------------------------------------------
        | ATRASADOS
        |--------------------------------------------------------------------------
        |
        | Mantemos calculado porque despesas vencidas continuam
        | fazendo parte da previsão total a pagar.
        |
        | Não existe necessidade de gravar "atrasado" no banco.
        |
        */

        $despesasAtrasadas = Despesa::query()
            ->where('user_id', $userId)
            ->where('situacao', 'pendente')
            ->whereDate(
                'data_vencimento',
                '<',
                $hoje
            )
            ->sum('valor');


        $parcelasAtrasadas = Parcela::query()
            ->where('user_id', $userId)
            ->where('situacao', 'pendente')
            ->whereDate(
                'data_vencimento',
                '<',
                $hoje
            )
            ->sum('valor');


        $atrasados =
            (float) $despesasAtrasadas
            + (float) $parcelasAtrasadas;


        /*
        |--------------------------------------------------------------------------
        | CARTÃO EM ABERTO
        |--------------------------------------------------------------------------
        |
        | Aqui consideramos a dívida da fatura.
        |
        | Não somamos parcelas_cartao separadamente,
        | porque elas fazem parte das próprias faturas.
        |
        */

        $cartaoEmAberto = Fatura::query()
            ->where('user_id', $userId)
            ->whereIn(
                'situacao',
                [
                    'aberta',
                    'fechada',
                ]
            )
            ->sum('valor_total');


        /*
        |--------------------------------------------------------------------------
        | PRÓXIMO MÊS
        |--------------------------------------------------------------------------
        |
        | Tudo que vencer especificamente no próximo mês:
        |
        | - despesas pendentes;
        | - parcelas pendentes;
        | - faturas de cartão em aberto.
        |
        */

        $inicioProximoMes = $hoje
            ->copy()
            ->addMonthNoOverflow()
            ->startOfMonth();

        $fimProximoMes = $inicioProximoMes
            ->copy()
            ->endOfMonth();


        $despesasProximoMes = Despesa::query()
            ->where('user_id', $userId)
            ->where('situacao', 'pendente')
            ->whereBetween(
                'data_vencimento',
                [
                    $inicioProximoMes,
                    $fimProximoMes,
                ]
            )
            ->sum('valor');


        $parcelasProximoMes = Parcela::query()
            ->where('user_id', $userId)
            ->where('situacao', 'pendente')
            ->whereBetween(
                'data_vencimento',
                [
                    $inicioProximoMes,
                    $fimProximoMes,
                ]
            )
            ->sum('valor');


        $faturasProximoMes = Fatura::query()
            ->where('user_id', $userId)
            ->whereIn(
                'situacao',
                [
                    'aberta',
                    'fechada',
                ]
            )
            ->whereBetween(
                'data_vencimento',
                [
                    $inicioProximoMes,
                    $fimProximoMes,
                ]
            )
            ->sum('valor_total');


        $proximoMes =
            (float) $despesasProximoMes
            + (float) $parcelasProximoMes
            + (float) $faturasProximoMes;


        /*
        |--------------------------------------------------------------------------
        | PREVISÃO A PAGAR
        |--------------------------------------------------------------------------
        |
        | Total de obrigações que ainda precisam ser pagas.
        |
        | Incluímos:
        |
        | - despesas pendentes;
        | - parcelas pendentes;
        | - faturas abertas/fechadas.
        |
        | Inclui vencidas e futuras.
        |
        | IMPORTANTE:
        | parcelas de cartão NÃO são somadas novamente.
        | O compromisso do cartão é representado pela fatura.
        |
        */

        $todasDespesasPendentes = Despesa::query()
            ->where('user_id', $userId)
            ->where('situacao', 'pendente')
            ->sum('valor');


        $todasParcelasPendentes = Parcela::query()
            ->where('user_id', $userId)
            ->where('situacao', 'pendente')
            ->sum('valor');


        $previsaoAPagar =
            (float) $todasDespesasPendentes
            + (float) $todasParcelasPendentes
            + (float) $cartaoEmAberto;


        /*
        |--------------------------------------------------------------------------
        | DESPESAS PAGAS POR CATEGORIA
        |--------------------------------------------------------------------------
        |
        | O gráfico acompanha a mesma lógica do card:
        |
        | somente aquilo que foi efetivamente pago no mês.
        |
        */

        $categorias = [];


        /*
         * Despesas comuns pagas.
         *
         * Ligamos a despesa à movimentação financeira,
         * garantindo que houve saída real.
         */

        $despesasCategorias = DB::table(
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
                    $inicioMes,
                    $fimMes,
                ]
            )
            ->select(
                'c.nome',
                DB::raw(
                    'SUM(m.valor) as total'
                )
            )
            ->groupBy(
                'c.id',
                'c.nome'
            )
            ->get();


        foreach ($despesasCategorias as $item) {

            $categorias[$item->nome] =
                ($categorias[$item->nome] ?? 0)
                + (float) $item->total;
        }


        /*
         * Parcelamentos pagos.
         */

        $parcelasCategorias = DB::table(
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
                    $inicioMes,
                    $fimMes,
                ]
            )
            ->select(
                'c.nome',
                DB::raw(
                    'SUM(m.valor) as total'
                )
            )
            ->groupBy(
                'c.id',
                'c.nome'
            )
            ->get();


        foreach ($parcelasCategorias as $item) {

            $categorias[$item->nome] =
                ($categorias[$item->nome] ?? 0)
                + (float) $item->total;
        }

        /*
|--------------------------------------------------------------------------
| FATURAS DE CARTÃO PAGAS POR CATEGORIA
|--------------------------------------------------------------------------
|
| Quando uma fatura é paga, a movimentação bancária aparece como
| "pagamento_fatura".
|
| Para o gráfico, porém, distribuímos o valor pelas categorias
| das compras que compõem aquela fatura.
|
*/

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
                $inicioMes,
                $fimMes,
            ]
        )
        ->select(
            'c.nome',
            DB::raw(
                'SUM(pc.valor) as total'
            )
        )
        ->groupBy(
            'c.id',
            'c.nome'
        )
        ->get();


    foreach ($faturasCategorias as $item) {

        $categorias[$item->nome] =
            ($categorias[$item->nome] ?? 0)
            + (float) $item->total;
    }


        /*
         * Neste momento pagamento de fatura não é quebrado
         * por categoria neste gráfico.
         *
         * Quando avançarmos no módulo Cartões,
         * poderemos distribuir a fatura pelas categorias
         * das compras que a compõem.
         */


        arsort($categorias);


        /*
        |--------------------------------------------------------------------------
        | RECEITAS X DESPESAS - ÚLTIMOS 6 MESES
        |--------------------------------------------------------------------------
        |
        | Receitas:
        | somente recebidas.
        |
        | Despesas:
        | somente saídas efetivamente realizadas.
        |
        */

        $graficoMeses = [];
        $graficoReceitas = [];
        $graficoDespesas = [];


        for ($i = 5; $i >= 0; $i--) {

            $mes = $hoje
                ->copy()
                ->subMonthsNoOverflow($i);

            $inicio = $mes
                ->copy()
                ->startOfMonth();

            $fim = $mes
                ->copy()
                ->endOfMonth();


            $graficoMeses[] = ucfirst(
                $mes
                    ->locale('pt_BR')
                    ->translatedFormat('M/y')
            );


            /*
             * Receitas efetivamente recebidas.
             */

            $totalReceitas = Receita::query()
                ->where(
                    'user_id',
                    $userId
                )
                ->where(
                    'situacao',
                    'recebida'
                )
                ->whereBetween(
                    'data_recebimento',
                    [
                        $inicio,
                        $fim,
                    ]
                )
                ->sum('valor');


            /*
             * Saídas efetivamente realizadas.
             */

            $totalDespesas = MovimentacaoConta::query()
                ->where(
                    'user_id',
                    $userId
                )
                ->where(
                    'tipo',
                    'saida'
                )
                ->where(
                    'estornada',
                    false
                )
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
                        $inicio,
                        $fim,
                    ]
                )
                ->sum('valor');


            $graficoReceitas[] =
                (float) $totalReceitas;

            $graficoDespesas[] =
                (float) $totalDespesas;
        }


        /*
        |--------------------------------------------------------------------------
        | PRÓXIMAS CONTAS
        |--------------------------------------------------------------------------
        |
        | Continuamos trazendo:
        |
        | - despesas;
        | - parcelas;
        | - faturas.
        |
        | Ordenadas por vencimento.
        |
        */

        $proximasDespesas = Despesa::query()
            ->where('user_id', $userId)
            ->where('situacao', 'pendente')
            ->orderBy('data_vencimento')
            ->limit(10)
            ->get()
            ->map(
                function ($item) use ($hoje) {

                    return [

                        'descricao' =>
                            $item->descricao,

                        'vencimento' =>
                            $item->data_vencimento,

                        'valor' =>
                            (float) $item->valor,

                        'situacao' =>
                            $item
                                ->data_vencimento
                                ->lt($hoje)
                                ? 'Atrasado'
                                : 'A vencer',

                        'tipo' =>
                            'despesa',
                    ];
                }
            );


        $proximasParcelas = Parcela::query()
            ->with('parcelamento')
            ->where('user_id', $userId)
            ->where('situacao', 'pendente')
            ->orderBy('data_vencimento')
            ->limit(10)
            ->get()
            ->map(
                function ($item) use ($hoje) {

                    return [

                        'descricao' =>
                            (
                                $item
                                    ->parcelamento
                                    ?->descricao
                                ?? 'Parcelamento'
                            )
                            . ' '
                            . $item->numero_parcela
                            . '/'
                            . $item->total_parcelas,

                        'vencimento' =>
                            $item->data_vencimento,

                        'valor' =>
                            (float) $item->valor,

                        'situacao' =>
                            $item
                                ->data_vencimento
                                ->lt($hoje)
                                ? 'Atrasado'
                                : 'A vencer',

                        'tipo' =>
                            'parcela',
                    ];
                }
            );


        $proximasFaturas = Fatura::query()
            ->with('cartao')
            ->where('user_id', $userId)
            ->whereIn(
                'situacao',
                [
                    'aberta',
                    'fechada',
                ]
            )
            ->orderBy('data_vencimento')
            ->limit(10)
            ->get()
            ->map(
                function ($item) use ($hoje) {

                    return [

                        'descricao' =>
                            'Fatura '
                            . (
                                $item
                                    ->cartao
                                    ?->nome
                                ?? 'Cartão'
                            ),

                        'vencimento' =>
                            $item->data_vencimento,

                        'valor' =>
                            (float) $item->valor_total,

                        'situacao' =>
                            $item
                                ->data_vencimento
                                ->lt($hoje)
                                ? 'Atrasado'
                                : 'Em aberto',

                        'tipo' =>
                            'fatura',
                    ];
                }
            );


        $proximasContas =
            $proximasDespesas
                ->concat(
                    $proximasParcelas
                )
                ->concat(
                    $proximasFaturas
                )
                ->sortBy(
                    'vencimento'
                )
                ->take(5)
                ->values();


        /*
        |--------------------------------------------------------------------------
        | ÚLTIMAS MOVIMENTAÇÕES
        |--------------------------------------------------------------------------
        */

        $ultimasMovimentacoes =
            MovimentacaoConta::query()
                ->with('conta')
                ->where(
                    'user_id',
                    $userId
                )
                ->where(
                    'estornada',
                    false
                )
                ->orderByDesc(
                    'data_movimentacao'
                )
                ->orderByDesc('id')
                ->limit(10)
                ->get();


        /*
        |--------------------------------------------------------------------------
        | ENVIA PARA O DASHBOARD
        |--------------------------------------------------------------------------
        */

        $contasAtivas = \App\Models\Conta::query()
            ->where('user_id', $userId)
            ->where('ativa', true)
            ->orderBy('nome')
            ->get()
            ->map(function ($conta) use ($userId) {

                $entradas = \App\Models\MovimentacaoConta::query()
                    ->where('user_id', $userId)
                    ->where('conta_id', $conta->id)
                    ->where('tipo', 'entrada')
                    ->where('estornada', false)
                    ->sum('valor');

                $saidas = \App\Models\MovimentacaoConta::query()
                    ->where('user_id', $userId)
                    ->where('conta_id', $conta->id)
                    ->where('tipo', 'saida')
                    ->where('estornada', false)
                    ->sum('valor');

                $conta->saldo_atual =
                    (float) $conta->saldo_inicial
                    + (float) $entradas
                    - (float) $saidas;

                return $conta;
            });

        return view(
            'dashboard',
            compact(
                'receitasMes',
                'despesasMes',
                'saldoAtual',
                'aVencer',
                'atrasados',
                'cartaoEmAberto',
                'proximoMes',
                'previsaoAPagar',
                'categorias',
                'graficoMeses',
                'graficoReceitas',
                'graficoDespesas',
                'proximasContas',
                'ultimasMovimentacoes',
                'contasAtivas'
            )
        );
    }
}