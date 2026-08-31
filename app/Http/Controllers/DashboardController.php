<?php

namespace App\Http\Controllers;

use App\Models\Despesa;
use App\Models\Fatura;
use App\Models\MovimentacaoConta;
use App\Models\Parcela;
use App\Models\Receita;
use App\Models\Recorrencia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        | Mesma regra utilizada na tela "Previsão de Despesas":
        |
        | - despesas pendentes;
        | - parcelas pendentes;
        | - despesas recorrentes previstas;
        | - faturas do cartão;
        | - sem duplicar recorrências já materializadas.
        |
        */

        $inicioProximoMes = $hoje
            ->copy()
            ->addMonthNoOverflow()
            ->startOfMonth();

        $fimProximoMes = $inicioProximoMes
            ->copy()
            ->endOfMonth();


        /*
        |--------------------------------------------------------------------------
        | DESPESAS LANÇADAS
        |--------------------------------------------------------------------------
        */

        $queryDespesasProximoMes = Despesa::query()
            ->where(
                'user_id',
                $userId
            )
            ->where(
                'situacao',
                'pendente'
            )
            ->whereBetween(
                'data_vencimento',
                [
                    $inicioProximoMes,
                    $fimProximoMes,
                ]
            );


        /*
        * Se a despesa nasceu de uma recorrência,
        * ela não entra novamente aqui.
        */
       


        $despesasProximoMes =
            (float)
            $queryDespesasProximoMes
                ->sum('valor');


        /*
        |--------------------------------------------------------------------------
        | PARCELAS
        |--------------------------------------------------------------------------
        */

        $parcelasProximoMes =
            (float)
            Parcela::query()
                ->where(
                    'user_id',
                    $userId
                )
                ->where(
                    'situacao',
                    'pendente'
                )
                ->whereBetween(
                    'data_vencimento',
                    [
                        $inicioProximoMes,
                        $fimProximoMes,
                    ]
                )
                ->sum('valor');


        /*
        |--------------------------------------------------------------------------
        | DESPESAS RECORRENTES
        |--------------------------------------------------------------------------
        */

        $recorrenciasProximoMes = Recorrencia::query()
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
                $fimProximoMes->toDateString()
            )
            ->where(
                function ($query) use (
                    $inicioProximoMes
                ) {

                    $query
                        ->whereNull(
                            'data_fim'
                        )
                        ->orWhereDate(
                            'data_fim',
                            '>=',
                            $inicioProximoMes
                                ->toDateString()
                        );
                }
            )
            ->get();


        $totalRecorrentesProximoMes = 0.0;


        foreach (
            $recorrenciasProximoMes
            as $recorrencia
        ) {

            $vencimentos =
                $this->vencimentosRecorrenciaNoMes(
                    $recorrencia,
                    $inicioProximoMes,
                    $fimProximoMes
                );


            foreach (
                $vencimentos
                as $vencimento
            ) {

                /*
                * Evita duplicidade caso essa recorrência
                * já tenha sido materializada em despesa.
                */
                if (
                    Schema::hasColumn(
                        'despesas',
                        'recorrencia_id'
                    )
                ) {

                    $jaGerada = Despesa::query()
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


                $totalRecorrentesProximoMes +=
                    (float) (
                        $recorrencia
                            ->valor_padrao
                        ?? 0
                    );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | FATURAS
        |--------------------------------------------------------------------------
        */

        $faturasProximoMes =
            (float)
            Fatura::query()
                ->where(
                    'user_id',
                    $userId
                )
                ->whereBetween(
                    'data_vencimento',
                    [
                        $inicioProximoMes,
                        $fimProximoMes,
                    ]
                )
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
        | TOTAL DO PRÓXIMO MÊS
        |--------------------------------------------------------------------------
        */

        $proximoMes =
            $despesasProximoMes
            + $parcelasProximoMes
            + $totalRecorrentesProximoMes
            + $faturasProximoMes;


        /*
        |--------------------------------------------------------------------------
        | PREVISÃO DE DESPESAS DO MÊS
        |--------------------------------------------------------------------------
        |
        | Mesma lógica da tela "Previsão de Despesas":
        |
        | - despesas lançadas com vencimento no mês;
        | - despesas recorrentes previstas para o mês;
        | - faturas de cartão com vencimento no mês;
        | - sem duplicar recorrências já materializadas em despesas.
        |
        */

        $queryDespesasPrevistasMes = Despesa::query()
            ->where('user_id', $userId)
            ->whereBetween(
                'data_vencimento',
                [
                    $inicioMes,
                    $fimMes,
                ]
            )
            ->where(
                'situacao',
                'pendente'
            );

        

        $totalDespesasPrevistasMes =
            (float) $queryDespesasPrevistasMes
                ->sum('valor');


        $recorrenciasMes = Recorrencia::query()
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


        $totalRecorrentesPrevistasMes = 0.0;


        foreach ($recorrenciasMes as $recorrencia) {

            $vencimentos =
                $this->vencimentosRecorrenciaNoMes(
                    $recorrencia,
                    $inicioMes,
                    $fimMes
                );


            foreach ($vencimentos as $vencimento) {

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


                $totalRecorrentesPrevistasMes +=
                    (float) (
                        $recorrencia->valor_padrao
                        ?? 0
                    );
            }
        }


        $totalFaturasPrevistasMes = Fatura::query()
            ->where('user_id', $userId)
            ->whereBetween(
                'data_vencimento',
                [
                    $inicioMes,
                    $fimMes,
                ]
            )
            ->sum('valor_total');


        $previsaoDespesasMes =
            (float) $totalDespesasPrevistasMes
            + (float) $totalRecorrentesPrevistasMes
            + (float) $totalFaturasPrevistasMes;


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
                'previsaoDespesasMes',
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

    /*
    |--------------------------------------------------------------------------
    | VENCIMENTOS DA RECORRÊNCIA NO MÊS
    |--------------------------------------------------------------------------
    */

    private function vencimentosRecorrenciaNoMes(
        Recorrencia $recorrencia,
        Carbon $inicioMes,
        Carbon $fimMes
    )
    {
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