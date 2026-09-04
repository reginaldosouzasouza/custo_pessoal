<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Conta;
use App\Models\Despesa;
use App\Models\Fatura;
use App\Models\FormaPagamento;
use App\Models\MovimentacaoConta;
use App\Models\Parcela;
use App\Models\Recorrencia;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ContaPagarController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();

        $mes = $request->input('mes');

        $descricao =
            trim(
                (string) $request->input(
                    'descricao',
                    ''
                )
            );

        $categoriaId =
            $request->input(
                'categoria_id'
            );

        $origemFiltro =
            $request->input(
                'origem'
            );

        $dataVencimento =
            $request->input(
                'data_vencimento'
            );

        $situacaoFiltro =
            $request->input(
                'situacao'
            );
            

        /*
        |--------------------------------------------------------------------------
        | PERÍODO
        |--------------------------------------------------------------------------
        |
        | Com mês preenchido, filtra somente o mês escolhido.
        |
        | Com mês em branco:
        | - despesas, parcelas e faturas pendentes: sem limite de mês;
        | - recorrências: mês atual + próximos 12 meses.
        |
        */

        $inicioMes = null;
        $fimMes = null;

        if ($mes) {

            try {

                $inicioMes = Carbon::createFromFormat(
                    'Y-m-d',
                    $mes . '-01'
                )->startOfMonth();

                $fimMes = $inicioMes
                    ->copy()
                    ->endOfMonth();

            } catch (\Throwable $e) {

                $mes = null;
                $inicioMes = null;
                $fimMes = null;
            }
        }

        $inicioHorizonte =
            now()
                ->startOfMonth();

        $fimHorizonte =
            now()
                ->copy()
                ->addMonthsNoOverflow(12)
                ->endOfMonth();

        $itens = collect();


        /*
        |--------------------------------------------------------------------------
        | DESPESAS
        |--------------------------------------------------------------------------
        */

        $queryDespesas = Despesa::query()
            ->with('categoria')
            ->where('user_id', $userId)
            ->where('situacao', 'pendente');

        if ($inicioMes && $fimMes) {

            $queryDespesas->whereBetween(
                'data_vencimento',
                [
                    $inicioMes->toDateString(),
                    $fimMes->toDateString(),
                ]
            );
        }

        $despesas =
            $queryDespesas->get();


        foreach ($despesas as $despesa) {

            $itens->push([

                'id' =>
                    $despesa->id,

                'tipo' =>
                    'despesa',

                'origem' =>
                    'Despesa',

                'descricao' =>
                    $despesa->descricao,

                'categoria' =>
                    $despesa->categoria?->nome
                    ?? '-',

                'categoria_id' =>
                    $despesa->categoria_id,

                'vencimento' =>
                    $despesa->data_vencimento,

                'valor' =>
                    (float) $despesa->valor,

                'situacao' =>
                    $despesa->situacao,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | PARCELAS
        |--------------------------------------------------------------------------
        */

        $queryParcelas = Parcela::query()
            ->with('parcelamento.categoria')
            ->where('user_id', $userId)
            ->where('situacao', 'pendente');

        if ($inicioMes && $fimMes) {

            $queryParcelas->whereBetween(
                'data_vencimento',
                [
                    $inicioMes->toDateString(),
                    $fimMes->toDateString(),
                ]
            );
        }

        $parcelas =
            $queryParcelas->get();


        foreach ($parcelas as $parcela) {

            $parcelamento =
                $parcela->parcelamento;

            $itens->push([

                'id' =>
                    $parcela->id,

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
                    $parcelamento?->categoria?->nome
                    ?? '-',

                'categoria_id' =>
                    $parcelamento?->categoria_id,

                'vencimento' =>
                    $parcela->data_vencimento,

                'valor' =>
                    (float) $parcela->valor,

                'situacao' =>
                    $parcela->situacao,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | FATURAS
        |--------------------------------------------------------------------------
        */

        $queryFaturas = Fatura::query()
            ->with('cartao')
            ->where('user_id', $userId)
            ->whereIn(
                'situacao',
                [
                    'aberta',
                    'fechada',
                ]
            );

        if ($inicioMes && $fimMes) {

            $queryFaturas->whereBetween(
                'data_vencimento',
                [
                    $inicioMes->toDateString(),
                    $fimMes->toDateString(),
                ]
            );
        }

        $faturas =
            $queryFaturas->get();


        foreach ($faturas as $fatura) {

            $valorRestante =
                (float) $fatura->valor_total
                - (float) $fatura->valor_pago;


            if ($valorRestante <= 0) {
                continue;
            }


            $itens->push([

                'id' =>
                    $fatura->id,

                'tipo' =>
                    'fatura',

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

                'categoria_id' =>
                    null,

                'vencimento' =>
                    $fatura->data_vencimento,

                'valor' =>
                    $valorRestante,

                'situacao' =>
                    $fatura->situacao,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | RECORRÊNCIAS
        |--------------------------------------------------------------------------
        */

        $inicioRecorrencias =
            $inicioMes
                ?: $inicioHorizonte;

        $fimRecorrencias =
            $fimMes
                ?: $fimHorizonte;

        $recorrencias = Recorrencia::query()
            ->with('categoria')
            ->where('user_id', $userId)
            ->where('tipo', 'despesa')
            ->where('ativa', true)
            ->whereDate(
                'data_inicio',
                '<=',
                $fimRecorrencias->toDateString()
            )
            ->where(
                function ($query) use ($inicioRecorrencias) {

                    $query
                        ->whereNull('data_fim')
                        ->orWhereDate(
                            'data_fim',
                            '>=',
                            $inicioRecorrencias->toDateString()
                        );
                }
            )
            ->get();


        foreach ($recorrencias as $recorrencia) {

            $vencimentos =
                $this->vencimentosDaRecorrenciaNoPeriodo(
                    $recorrencia,
                    $inicioRecorrencias,
                    $fimRecorrencias
                );


            foreach ($vencimentos as $vencimento) {

                /*
                |--------------------------------------------------------------------------
                | VERIFICA SE A OCORRÊNCIA JÁ VIROU DESPESA
                |--------------------------------------------------------------------------
                */

                $jaGerada = false;


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
                }


                if ($jaGerada) {
                    continue;
                }


                $itens->push([

                    'id' =>
                        $recorrencia->id,

                    'tipo' =>
                        'recorrente',

                    'origem' =>
                        'Recorrente',

                    'descricao' =>
                        $recorrencia->descricao,

                    'categoria' =>
                        $recorrencia
                            ->categoria?->nome
                        ?? '-',

                    'categoria_id' =>
                        $recorrencia->categoria_id,

                    'vencimento' =>
                        $vencimento,

                    'valor' =>
                        (float) (
                            $recorrencia
                                ->valor_padrao
                            ?? 0
                        ),

                    'tipo_valor' =>
                        $recorrencia
                            ->tipo_valor,

                    'situacao' =>
                        'prevista',
                ]);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | FILTROS DA LISTA ÚNICA
        |--------------------------------------------------------------------------
        */

        if ($descricao !== '') {

            $termo =
                mb_strtolower($descricao);

            $itens =
                $itens
                    ->filter(
                        function ($item) use ($termo) {

                            return str_contains(
                                mb_strtolower(
                                    (string) (
                                        $item['descricao']
                                        ?? ''
                                    )
                                ),
                                $termo
                            );
                        }
                    );
        }


        if ($categoriaId) {

            $itens =
                $itens
                    ->filter(
                        function ($item) use ($categoriaId) {

                            return
                                (string) (
                                    $item['categoria_id']
                                    ?? ''
                                )
                                ===
                                (string) $categoriaId;
                        }
                    );
        }


        if ($origemFiltro) {

            $itens =
                $itens
                    ->filter(
                        function ($item) use ($origemFiltro) {

                            return
                                ($item['tipo'] ?? '')
                                === $origemFiltro;
                        }
                    );
        }


        if ($dataVencimento) {

            $itens =
                $itens
                    ->filter(
                        function ($item) use ($dataVencimento) {

                            if (
                                empty(
                                    $item['vencimento']
                                )
                            ) {
                                return false;
                            }

                            return Carbon::parse(
                                $item['vencimento']
                            )->format('Y-m-d')
                            === $dataVencimento;
                        }
                    );
        }


        if ($situacaoFiltro) {

            $itens =
                $itens
                    ->filter(
                        function ($item) use ($situacaoFiltro) {

                            $situacao =
                                strtolower(
                                    (string) (
                                        $item['situacao']
                                        ?? ''
                                    )
                                );

                            $vencimento =
                                !empty(
                                    $item['vencimento']
                                )
                                    ? Carbon::parse(
                                        $item['vencimento']
                                    )->startOfDay()
                                    : null;

                            $estaVencida =
                                $vencimento
                                &&
                                $vencimento->lt(
                                    today()
                                );

                            if (
                                $situacaoFiltro
                                === 'vencida'
                            ) {
                                return $estaVencida;
                            }

                            if (
                                $situacaoFiltro
                                === 'prevista'
                            ) {
                                return
                                    $situacao
                                    === 'prevista'
                                    &&
                                    !$estaVencida;
                            }

                            if (
                                $situacaoFiltro
                                === 'pendente'
                            ) {
                                return
                                    $situacao
                                    === 'pendente'
                                    &&
                                    !$estaVencida;
                            }

                            if (
                                $situacaoFiltro
                                === 'aberta'
                            ) {
                                return
                                    $situacao
                                    === 'aberta'
                                    &&
                                    !$estaVencida;
                            }

                            if (
                                $situacaoFiltro
                                === 'fechada'
                            ) {
                                return
                                    $situacao
                                    === 'fechada'
                                    &&
                                    !$estaVencida;
                            }

                            return true;
                        }
                    );
        }


        $itens =
            $itens->values();



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

        $totalPagar =
            (float) $itens
                ->sum('valor');


        $totalVencido =
            (float) $itens
                ->filter(
                    function ($item) {

                        return Carbon::parse(
                            $item['vencimento']
                        )->startOfDay()
                        ->lt(
                            today()
                        );
                    }
                )
                ->sum('valor');


        $totalHoje =
            (float) $itens
                ->filter(
                    function ($item) {

                        return Carbon::parse(
                            $item['vencimento']
                        )->isToday();
                    }
                )
                ->sum('valor');


        /*
        |--------------------------------------------------------------------------
        | CATEGORIAS DO FILTRO
        |--------------------------------------------------------------------------
        */

        $categorias =
            Categoria::query()
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
                ->orderBy('nome')
                ->get();



        /*
        |--------------------------------------------------------------------------
        | CONTAS PARA PAGAMENTO
        |--------------------------------------------------------------------------
        */

        $contas = Conta::query()
            ->where(
                'user_id',
                $userId
            )
            ->where(
                'ativa',
                true
            )
            ->orderBy('nome')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | FORMAS DE PAGAMENTO
        |--------------------------------------------------------------------------
        */

        $formasPagamento =
            FormaPagamento::query()
                ->where(
                    'ativa',
                    true
                )
                ->orderBy('nome')
                ->get();


        return view(
            'contas-a-pagar.index',
            compact(
                'mes',
                'inicioMes',
                'fimMes',
                'itens',
                'totalPagar',
                'totalVencido',
                'totalHoje',
                'contas',
                'formasPagamento',
                'categorias',
                'descricao',
                'categoriaId',
                'origemFiltro',
                'dataVencimento',
                'situacaoFiltro'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PAGAR OCORRÊNCIA RECORRENTE
    |--------------------------------------------------------------------------
    */

    public function pagarRecorrencia(
        Request $request,
        Recorrencia $recorrencia
    ) {

        abort_unless(
            $recorrencia->user_id
            === auth()->id(),
            403
        );


        abort_unless(
            $recorrencia->tipo
            === 'despesa',
            403
        );


        $dados = $request->validate([

            'vencimento' => [
                'required',
                'date',
            ],

            'conta_id' => [
                'required',
                'integer',
            ],

            'forma_pagamento_id' => [
                'nullable',
                'integer',
            ],

            'data_pagamento' => [
                'required',
                'date',
            ],

            'valor' => [
                'required',
                'numeric',
                'gt:0',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | CONTA
        |--------------------------------------------------------------------------
        */

        $conta = Conta::query()
            ->where(
                'id',
                $dados['conta_id']
            )
            ->where(
                'user_id',
                auth()->id()
            )
            ->where(
                'ativa',
                true
            )
            ->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | EVITA PAGAR A MESMA OCORRÊNCIA DUAS VEZES
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasColumn(
                'despesas',
                'recorrencia_id'
            )
        ) {

            $jaExiste =
                Despesa::query()
                    ->where(
                        'user_id',
                        auth()->id()
                    )
                    ->where(
                        'recorrencia_id',
                        $recorrencia->id
                    )
                    ->whereDate(
                        'data_vencimento',
                        $dados['vencimento']
                    )
                    ->where(
                        'situacao',
                        '!=',
                        'cancelada'
                    )
                    ->exists();


            if ($jaExiste) {

                return back()->with(
                    'error',
                    'Esta ocorrência recorrente já foi gerada.'
                );
            }
        }


        DB::transaction(
            function () use (
                $recorrencia,
                $dados,
                $conta
            ) {

                /*
                |--------------------------------------------------------------------------
                | CRIA A DESPESA REAL
                |--------------------------------------------------------------------------
                */

                $despesa =
                    new Despesa();


                $despesa->user_id =
                    auth()->id();


                $despesa->categoria_id =
                    $recorrencia->categoria_id;


                $despesa->conta_id =
                    $conta->id;


                $despesa->forma_pagamento_id =
                    $dados[
                        'forma_pagamento_id'
                    ]
                    ?? null;


                $despesa->descricao =
                    $recorrencia->descricao;


                $despesa->valor =
                    $dados['valor'];


                $despesa->data_despesa =
                    $dados['vencimento'];


                $despesa->data_vencimento =
                    $dados['vencimento'];


                $despesa->data_pagamento =
                    $dados['data_pagamento'];


                $despesa->situacao =
                    'paga';


                $despesa->essencial =
                    $recorrencia
                        ->categoria?->classificacao
                    === 'essencial';


                $despesa->valor_estimado =
                    false;


                $despesa->paga_em =
                    now();


                $despesa->observacao =
                    'Gerada automaticamente pela recorrência #'
                    . $recorrencia->id;


                if (
                    Schema::hasColumn(
                        'despesas',
                        'recorrencia_id'
                    )
                ) {

                    $despesa->recorrencia_id =
                        $recorrencia->id;
                }


                $despesa->save();


                /*
                |--------------------------------------------------------------------------
                | MOVIMENTAÇÃO DA CONTA
                |--------------------------------------------------------------------------
                */

                MovimentacaoConta::create([

                    'user_id' =>
                        auth()->id(),

                    'conta_id' =>
                        $conta->id,

                    'tipo' =>
                        'saida',

                    'origem_tipo' =>
                        'despesa',

                    'origem_id' =>
                        $despesa->id,

                    'valor' =>
                        $despesa->valor,

                    'data_movimentacao' =>
                        $dados[
                            'data_pagamento'
                        ],

                    'descricao' =>
                        'Despesa: '
                        . $despesa
                            ->descricao,

                    'estornada' =>
                        false,
                ]);
            }
        );


        return back()->with(
            'success',
            'Despesa recorrente paga com sucesso.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | VENCIMENTOS DA RECORRÊNCIA
    |--------------------------------------------------------------------------
    */

    private function vencimentosDaRecorrenciaNoPeriodo(
        Recorrencia $recorrencia,
        Carbon $inicioPeriodo,
        Carbon $fimPeriodo
    ): Collection {

        $resultado =
            collect();

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
        | FREQUÊNCIAS BASEADAS EM DIAS
        |--------------------------------------------------------------------------
        */

        $intervaloDias =
            match ($recorrencia->frequencia) {
                'diaria' => 1,
                'cada_3_dias' => 3,
                'cada_5_dias' => 5,
                'semanal' => 7,
                default => null,
            };


        if ($intervaloDias !== null) {

            $data =
                $inicioRecorrencia->copy();

            if ($data->lt($inicioPeriodo)) {

                $diasDecorridos =
                    (int) $inicioRecorrencia
                        ->diffInDays(
                            $inicioPeriodo
                        );

                $resto =
                    $diasDecorridos
                    % $intervaloDias;

                $data =
                    $inicioPeriodo->copy();

                if ($resto !== 0) {

                    $data->addDays(
                        $intervaloDias
                        - $resto
                    );
                }
            }


            while ($data->lte($fimPeriodo)) {

                if (
                    $data->gte($inicioPeriodo)
                    &&
                    (
                        !$fimRecorrencia
                        || $data->lte(
                            $fimRecorrencia
                        )
                    )
                ) {

                    $resultado->push(
                        $data->copy()
                    );
                }

                $data->addDays(
                    $intervaloDias
                );
            }

            return $resultado;
        }


        /*
        |--------------------------------------------------------------------------
        | FREQUÊNCIAS BASEADAS EM MESES
        |--------------------------------------------------------------------------
        */

        $intervaloMeses =
            match ($recorrencia->frequencia) {
                'mensal' => 1,
                'trimestral' => 3,
                'semestral' => 6,
                'anual' => 12,
                default => null,
            };


        if (!$intervaloMeses) {
            return $resultado;
        }


        $mesInicioRecorrencia =
            $inicioRecorrencia
                ->copy()
                ->startOfMonth();

        $mesAtual =
            $inicioPeriodo
                ->copy()
                ->startOfMonth();

        $mesFinal =
            $fimPeriodo
                ->copy()
                ->startOfMonth();


        while ($mesAtual->lte($mesFinal)) {

            $diferencaMeses =
                $mesInicioRecorrencia
                    ->diffInMonths(
                        $mesAtual,
                        false
                    );

            if (
                $diferencaMeses >= 0
                &&
                $diferencaMeses
                % $intervaloMeses
                === 0
            ) {

                $dia =
                    (int) (
                        $recorrencia->dia_vencimento
                        ?: $inicioRecorrencia->day
                    );

                $dia =
                    min(
                        $dia,
                        $mesAtual->daysInMonth
                    );

                $vencimento =
                    $mesAtual
                        ->copy()
                        ->day($dia);

                if (
                    $vencimento->gte(
                        $inicioPeriodo
                    )
                    &&
                    $vencimento->lte(
                        $fimPeriodo
                    )
                    &&
                    $vencimento->gte(
                        $inicioRecorrencia
                    )
                    &&
                    (
                        !$fimRecorrencia
                        ||
                        $vencimento->lte(
                            $fimRecorrencia
                        )
                    )
                ) {

                    $resultado->push(
                        $vencimento
                    );
                }
            }

            $mesAtual->addMonthNoOverflow();
        }

        return $resultado;
    }

}