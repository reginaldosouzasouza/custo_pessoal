<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Conta;
use App\Models\Despesa;
use App\Models\FormaPagamento;
use App\Models\MovimentacaoConta;
use App\Models\Parcela;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DespesaController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();

        $mes = $request->input('mes');
        $situacao = $request->input('situacao');

        $filtrarPor = $request->input(
            'filtrar_por',
            'vencimento'
        );


        /*
        |--------------------------------------------------------------------------
        | DESPESAS NORMAIS
        |--------------------------------------------------------------------------
        */

        $queryDespesas = Despesa::query()
            ->with([
                'categoria',
                'conta',
                'formaPagamento',
            ])
            ->where('user_id', $userId);


        if ($mes) {

            [$ano, $numeroMes] = explode(
                '-',
                $mes
            );

            if ($filtrarPor === 'pagamento') {

                $queryDespesas
                    ->whereNotNull('data_pagamento')
                    ->whereYear(
                        'data_pagamento',
                        $ano
                    )
                    ->whereMonth(
                        'data_pagamento',
                        $numeroMes
                    );

            } else {

                $queryDespesas
                    ->whereYear(
                        'data_vencimento',
                        $ano
                    )
                    ->whereMonth(
                        'data_vencimento',
                        $numeroMes
                    );
            }
        }


        if (
            in_array(
                $situacao,
                [
                    'pendente',
                    'paga',
                    'cancelada',
                ],
                true
            )
        ) {
            $queryDespesas->where(
                'situacao',
                $situacao
            );
        }


        $despesasNormais =
            $queryDespesas->get();


        /*
        |--------------------------------------------------------------------------
        | PARCELAS
        |--------------------------------------------------------------------------
        */

        $queryParcelas = Parcela::query()
            ->with([
                'parcelamento.categoria',
                'conta',
                'formaPagamento',
            ])
            ->where('user_id', $userId);


        if ($mes) {

            [$ano, $numeroMes] = explode(
                '-',
                $mes
            );

            if ($filtrarPor === 'pagamento') {

                $queryParcelas
                    ->whereNotNull('data_pagamento')
                    ->whereYear(
                        'data_pagamento',
                        $ano
                    )
                    ->whereMonth(
                        'data_pagamento',
                        $numeroMes
                    );

            } else {

                $queryParcelas
                    ->whereYear(
                        'data_vencimento',
                        $ano
                    )
                    ->whereMonth(
                        'data_vencimento',
                        $numeroMes
                    );
            }
        }


        if (
            in_array(
                $situacao,
                [
                    'pendente',
                    'paga',
                    'cancelada',
                ],
                true
            )
        ) {
            $queryParcelas->where(
                'situacao',
                $situacao
            );
        }


        $parcelas =
            $queryParcelas->get();


        /*
        |--------------------------------------------------------------------------
        | LISTA ÚNICA
        |--------------------------------------------------------------------------
        */

        $lancamentos =
            collect();


        /*
        |--------------------------------------------------------------------------
        | DESPESAS NORMAIS
        |--------------------------------------------------------------------------
        */

        foreach (
            $despesasNormais
            as $despesa
        ) {

            $lancamentos->push([

                'tipo' =>
                    'despesa',

                'id' =>
                    $despesa->id,

                'descricao' =>
                    $despesa->descricao,

                'categoria' =>
                    $despesa
                        ->categoria?->nome
                    ?? '-',

                'data_vencimento' =>
                    $despesa
                        ->data_vencimento,

                'data_pagamento' =>
                    $despesa
                        ->data_pagamento,

                'conta' =>
                    $despesa
                        ->conta?->nome
                    ?? '-',

                'valor' =>
                    $despesa->valor,

                'essencial' =>
                    (bool)
                    $despesa->essencial,

                'situacao' =>
                    $despesa->situacao,

                'model' =>
                    $despesa,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | PARCELAS
        |--------------------------------------------------------------------------
        */

        foreach (
            $parcelas
            as $parcela
        ) {

            $parcelamento =
                $parcela->parcelamento;

            $categoria =
                $parcelamento?->categoria;


            $lancamentos->push([

                'tipo' =>
                    'parcela',

                'id' =>
                    $parcela->id,

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

                'data_vencimento' =>
                    $parcela
                        ->data_vencimento,

                'data_pagamento' =>
                    $parcela
                        ->data_pagamento,

                'conta' =>
                    $parcela
                        ->conta?->nome
                    ?? '-',

                'valor' =>
                    $parcela->valor,

                'essencial' =>
                    $categoria?->classificacao
                    === 'essencial',

                'situacao' =>
                    $parcela->situacao,

                'model' =>
                    $parcela,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | ORDENA
        |--------------------------------------------------------------------------
        */

        if (
            $filtrarPor
            === 'pagamento'
        ) {

            $lancamentos =
                $lancamentos
                    ->sortByDesc(
                        function ($item) {

                            return
                                $item['data_pagamento']
                                ? $item['data_pagamento']
                                    ->timestamp
                                : 0;
                        }
                    )
                    ->values();

        } else {

            $lancamentos =
                $lancamentos
                    ->sortByDesc(
                        function ($item) {

                            return
                                $item['data_vencimento']
                                ? $item['data_vencimento']
                                    ->timestamp
                                : 0;
                        }
                    )
                    ->values();
        }


        /*
        |--------------------------------------------------------------------------
        | CONTAS
        |--------------------------------------------------------------------------
        |
        | Carregadas uma única vez para os modais
        | de pagamento da tela.
        |
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


        return view(
            'despesas.index',
            compact(
                'lancamentos',
                'mes',
                'situacao',
                'filtrarPor',
                'contas'
            )
        );
    }


    public function create()
    {
        $userId =
            auth()->id();


        $categorias = Categoria::query()
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


        $formasPagamento =
            FormaPagamento::query()
                ->where(
                    'ativa',
                    true
                )
                ->orderBy('nome')
                ->get();


        return view(
            'despesas.create',
            compact(
                'categorias',
                'contas',
                'formasPagamento'
            )
        );
    }


    public function store(
        Request $request
    ) {
        $userId =
            auth()->id();


        $dados = $request->validate([

            'categoria_id' => [
                'required',
                'integer',
            ],

            'conta_id' => [
                'nullable',
                'integer',
            ],

            'forma_pagamento_id' => [
                'nullable',
                'integer',
            ],

            'descricao' => [
                'required',
                'string',
                'max:180',
            ],

            'valor' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'data_despesa' => [
                'required',
                'date',
            ],

            'data_vencimento' => [
                'required',
                'date',
            ],

            'data_pagamento' => [
                'nullable',
                'date',
            ],

            'situacao' => [
                'required',
                'in:pendente,paga',
            ],

            'observacao' => [
                'nullable',
                'string',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | CATEGORIA
        |--------------------------------------------------------------------------
        */

        $categoria = Categoria::query()
            ->where(
                'id',
                $dados['categoria_id']
            )
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
            ->firstOrFail();


        $dados['essencial'] =
            $categoria
                ->classificacao
            === 'essencial';


        $dados['valor_estimado'] =
            false;


        /*
        |--------------------------------------------------------------------------
        | CONTA
        |--------------------------------------------------------------------------
        */

        if (
            !empty(
                $dados['conta_id']
            )
        ) {

            Conta::query()
                ->where(
                    'id',
                    $dados['conta_id']
                )
                ->where(
                    'user_id',
                    $userId
                )
                ->where(
                    'ativa',
                    true
                )
                ->firstOrFail();
        }


        /*
        |--------------------------------------------------------------------------
        | DESPESA PAGA
        |--------------------------------------------------------------------------
        */

        if (
            $dados['situacao']
            === 'paga'
        ) {

            if (
                empty(
                    $dados['conta_id']
                )
            ) {

                return back()
                    ->withErrors([
                        'conta_id' =>
                            'Informe a conta utilizada no pagamento.',
                    ])
                    ->withInput();
            }


            $dados['data_pagamento'] =
                $dados['data_pagamento']
                ?? now()
                    ->toDateString();


            $dados['paga_em'] =
                now();

        } else {

            $dados['data_pagamento'] =
                null;

            $dados['paga_em'] =
                null;
        }


        $dados['user_id'] =
            $userId;


        DB::transaction(
            function () use (
                $dados
            ) {

                $despesa =
                    Despesa::create(
                        $dados
                    );


                /*
                |--------------------------------------------------------------------------
                | MOVIMENTAÇÃO
                |--------------------------------------------------------------------------
                */

                if (
                    $despesa->situacao
                    === 'paga'
                ) {

                    MovimentacaoConta::create([

                        'user_id' =>
                            $despesa
                                ->user_id,

                        'conta_id' =>
                            $despesa
                                ->conta_id,

                        'tipo' =>
                            'saida',

                        'origem_tipo' =>
                            'despesa',

                        'origem_id' =>
                            $despesa->id,

                        'valor' =>
                            $despesa
                                ->valor,

                        'data_movimentacao' =>
                            $despesa
                                ->data_pagamento,

                        'descricao' =>
                            'Despesa: '
                            . $despesa
                                ->descricao,

                        'estornada' =>
                            false,
                    ]);
                }
            }
        );


        return redirect()
            ->route(
                'despesas.index'
            )
            ->with(
                'success',
                'Despesa cadastrada com sucesso.'
            );
    }


    public function edit(
        Despesa $despesa
    ) {
        $this
            ->validarDespesaUsuario(
                $despesa
            );


        if (
            $despesa->situacao
            !== 'pendente'
        ) {

            return redirect()
                ->route(
                    'despesas.index'
                )
                ->with(
                    'error',
                    'Somente despesas pendentes podem ser editadas.'
                );
        }


        $userId =
            auth()->id();


        $categorias = Categoria::query()
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


        $formasPagamento =
            FormaPagamento::query()
                ->where(
                    'ativa',
                    true
                )
                ->orderBy('nome')
                ->get();


        return view(
            'despesas.edit',
            compact(
                'despesa',
                'categorias',
                'contas',
                'formasPagamento'
            )
        );
    }


    public function update(
        Request $request,
        Despesa $despesa
    ) {
        $this
            ->validarDespesaUsuario(
                $despesa
            );


        if (
            $despesa->situacao
            !== 'pendente'
        ) {
            abort(403);
        }


        $userId =
            auth()->id();


        $dados = $request->validate([

            'categoria_id' => [
                'required',
                'integer',
            ],

            'conta_id' => [
                'nullable',
                'integer',
            ],

            'forma_pagamento_id' => [
                'nullable',
                'integer',
            ],

            'descricao' => [
                'required',
                'string',
                'max:180',
            ],

            'valor' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'data_despesa' => [
                'required',
                'date',
            ],

            'data_vencimento' => [
                'required',
                'date',
            ],

            'observacao' => [
                'nullable',
                'string',
            ],
        ]);


        $categoria = Categoria::query()
            ->where(
                'id',
                $dados['categoria_id']
            )
            ->where(
                'user_id',
                $userId
            )
            ->where(
                'tipo',
                'despesa'
            )
            ->firstOrFail();


        $dados['essencial'] =
            $categoria
                ->classificacao
            === 'essencial';


        if (
            !empty(
                $dados['conta_id']
            )
        ) {

            Conta::query()
                ->where(
                    'id',
                    $dados['conta_id']
                )
                ->where(
                    'user_id',
                    $userId
                )
                ->firstOrFail();
        }


        $despesa->update(
            $dados
        );


        return redirect()
            ->route(
                'despesas.index'
            )
            ->with(
                'success',
                'Despesa atualizada com sucesso.'
            );
    }


    public function pagar(
        Request $request,
        Despesa $despesa
    ) {
        $this
            ->validarDespesaUsuario(
                $despesa
            );


        if (
            $despesa->situacao
            !== 'pendente'
        ) {

            return back()
                ->with(
                    'error',
                    'Somente despesas pendentes podem ser pagas.'
                );
        }


        $dados = $request->validate([

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
        ]);


        Conta::query()
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


        DB::transaction(
            function () use (
                $despesa,
                $dados
            ) {

                $despesa->update([

                    'conta_id' =>
                        $dados['conta_id'],

                    'forma_pagamento_id' =>
                        $dados[
                            'forma_pagamento_id'
                        ]
                        ?? null,

                    'data_pagamento' =>
                        $dados[
                            'data_pagamento'
                        ],

                    'situacao' =>
                        'paga',

                    'paga_em' =>
                        now(),
                ]);


                MovimentacaoConta::create([

                    'user_id' =>
                        $despesa->user_id,

                    'conta_id' =>
                        $dados['conta_id'],

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


        return back()
            ->with(
                'success',
                'Despesa paga com sucesso.'
            );
    }


    public function cancelar(
        Despesa $despesa
    ) {
        $this
            ->validarDespesaUsuario(
                $despesa
            );


        if (
            $despesa->situacao
            !== 'pendente'
        ) {

            return back()
                ->with(
                    'error',
                    'Somente despesas pendentes podem ser canceladas.'
                );
        }


        $despesa->update([

            'situacao' =>
                'cancelada',

            'cancelada_em' =>
                now(),
        ]);


        return back()
            ->with(
                'success',
                'Despesa cancelada com sucesso.'
            );
    }


    private function validarDespesaUsuario(
        Despesa $despesa
    ): void {

        abort_unless(
            $despesa->user_id
            === auth()->id(),
            403
        );
    }
}