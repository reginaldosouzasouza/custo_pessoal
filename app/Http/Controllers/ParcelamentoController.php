<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Conta;
use App\Models\FormaPagamento;
use App\Models\Parcela;
use App\Models\Parcelamento;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MovimentacaoConta;


class ParcelamentoController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | CADASTRAR PARCELAMENTO
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $userId = auth()->id();

        /*
        |--------------------------------------------------------------------------
        | VALIDAÇÃO
        |--------------------------------------------------------------------------
        */

        $dados = $request->validate([
            'categoria_id' => [
                'required',
                'integer',
            ],

            'forma_pagamento_id' => [
                'nullable',
                'integer',
            ],

            'conta_padrao_id' => [
                'nullable',
                'integer',
            ],

            'descricao' => [
                'required',
                'string',
                'max:180',
            ],

            'valor_total' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'quantidade_parcelas' => [
                'required',
                'integer',
                'min:2',
                'max:120',
            ],

            'primeiro_vencimento' => [
                'required',
                'date',
            ],

            'observacao' => [
                'nullable',
                'string',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | VALIDA CATEGORIA
        |--------------------------------------------------------------------------
        */

        $categoria = Categoria::query()
            ->where('id', $dados['categoria_id'])
            ->where('user_id', $userId)
            ->where('tipo', 'despesa')
            ->where('ativa', true)
            ->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | VALIDA CONTA PADRÃO
        |--------------------------------------------------------------------------
        */

        if (!empty($dados['conta_padrao_id'])) {

            Conta::query()
                ->where('id', $dados['conta_padrao_id'])
                ->where('user_id', $userId)
                ->where('ativa', true)
                ->firstOrFail();
        }


        /*
        |--------------------------------------------------------------------------
        | VALIDA FORMA DE PAGAMENTO
        |--------------------------------------------------------------------------
        */

        if (!empty($dados['forma_pagamento_id'])) {

            FormaPagamento::query()
                ->where('id', $dados['forma_pagamento_id'])
                ->where('ativa', true)
                ->firstOrFail();
        }


        /*
        |--------------------------------------------------------------------------
        | PREPARA OS VALORES
        |--------------------------------------------------------------------------
        |
        | Trabalhamos em centavos para não ocorrer diferença
        | de ponto flutuante.
        |
        | Exemplo:
        |
        | R$ 1.000,00 / 3
        |
        | 1/3 = 333,33
        | 2/3 = 333,33
        | 3/3 = 333,34
        |
        */

        $valorTotalCentavos = (int) round(
            ((float) $dados['valor_total']) * 100
        );

        $quantidadeParcelas =
            (int) $dados['quantidade_parcelas'];

        $valorBaseCentavos =
            intdiv(
                $valorTotalCentavos,
                $quantidadeParcelas
            );

        $restanteCentavos =
            $valorTotalCentavos
            -
            (
                $valorBaseCentavos
                *
                $quantidadeParcelas
            );

        $valorParcela =
            $valorBaseCentavos / 100;


        /*
        |--------------------------------------------------------------------------
        | CRIA PARCELAMENTO E PARCELAS
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $dados,
            $userId,
            $categoria,
            $quantidadeParcelas,
            $valorTotalCentavos,
            $valorBaseCentavos,
            $restanteCentavos,
            $valorParcela
        ) {

            /*
            |--------------------------------------------------------------------------
            | CABEÇALHO DO PARCELAMENTO
            |--------------------------------------------------------------------------
            */

            $parcelamento = Parcelamento::create([
                'user_id' =>
                    $userId,

                'categoria_id' =>
                    $categoria->id,

                'forma_pagamento_id' =>
                    $dados['forma_pagamento_id']
                    ?? null,

                'conta_padrao_id' =>
                    $dados['conta_padrao_id']
                    ?? null,

                'descricao' =>
                    $dados['descricao'],

                'valor_total' =>
                    $valorTotalCentavos / 100,

                'quantidade_parcelas' =>
                    $quantidadeParcelas,

                /*
                 * Guarda o valor base.
                 *
                 * Se houver diferença de centavos,
                 * ela será aplicada na última parcela.
                 */
                'valor_parcela' =>
                    $valorParcela,

                'primeiro_vencimento' =>
                    $dados['primeiro_vencimento'],

                'situacao' =>
                    'ativo',

                'observacao' =>
                    $dados['observacao']
                    ?? null,
            ]);


            /*
            |--------------------------------------------------------------------------
            | GERA AS PARCELAS
            |--------------------------------------------------------------------------
            */

            $primeiroVencimento =
                Carbon::parse(
                    $dados['primeiro_vencimento']
                );


            for (
                $numero = 1;
                $numero <= $quantidadeParcelas;
                $numero++
            ) {

                /*
                 * Todas recebem o valor base.
                 */
                $valorCentavos =
                    $valorBaseCentavos;


                /*
                 * A diferença de centavos fica
                 * na última parcela.
                 */
                if (
                    $numero === $quantidadeParcelas
                ) {
                    $valorCentavos +=
                        $restanteCentavos;
                }


                /*
                 * Calcula vencimento mensalmente.
                 *
                 * addMonthsNoOverflow evita datas
                 * inválidas, como 31/02.
                 */
                $vencimento =
                    $primeiroVencimento
                        ->copy()
                        ->addMonthsNoOverflow(
                            $numero - 1
                        );


                Parcela::create([
                    'user_id' =>
                        $userId,

                    'parcelamento_id' =>
                        $parcelamento->id,

                    'numero_parcela' =>
                        $numero,

                    'total_parcelas' =>
                        $quantidadeParcelas,

                    'valor' =>
                        $valorCentavos / 100,

                    'data_vencimento' =>
                        $vencimento
                            ->format('Y-m-d'),

                    'data_pagamento' =>
                        null,

                    'situacao' =>
                        'pendente',

                    'conta_id' =>
                        $dados['conta_padrao_id']
                        ?? null,

                    'forma_pagamento_id' =>
                        $dados['forma_pagamento_id']
                        ?? null,

                    'paga_em' =>
                        null,

                    'observacao' =>
                        $dados['observacao']
                        ?? null,
                ]);
            }
        });


        /*
        |--------------------------------------------------------------------------
        | RETORNO
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('despesas.index')
            ->with(
                'success',
                'Despesa parcelada cadastrada com sucesso.'
            );
    }

    public function pagarParcela(
    Request $request,
    Parcela $parcela
    ) {
        /*
        |--------------------------------------------------------------------------
        | SEGURANÇA
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $parcela->user_id === auth()->id(),
            403
        );


        /*
        |--------------------------------------------------------------------------
        | SOMENTE PARCELA PENDENTE
        |--------------------------------------------------------------------------
        */

        if ($parcela->situacao !== 'pendente') {

            return back()->with(
                'error',
                'Somente parcelas pendentes podem ser pagas.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | VALIDAÇÃO
        |--------------------------------------------------------------------------
        */

        $dados = $request->validate([
            'conta_id' => [
                'required',
                'integer',
            ],

            'data_pagamento' => [
                'required',
                'date',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | VALIDA CONTA
        |--------------------------------------------------------------------------
        */

        Conta::query()
            ->where('id', $dados['conta_id'])
            ->where('user_id', auth()->id())
            ->where('ativa', true)
            ->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | PAGAMENTO
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $parcela,
            $dados
        ) {

            /*
            * Carrega o parcelamento principal
            * para termos descrição e situação.
            */
            $parcelamento =
                $parcela->parcelamento;


            /*
            |--------------------------------------------------------------------------
            | ATUALIZA PARCELA
            |--------------------------------------------------------------------------
            */

            $parcela->update([
                'conta_id' =>
                    $dados['conta_id'],

                'data_pagamento' =>
                    $dados['data_pagamento'],

                'situacao' =>
                    'paga',

                'paga_em' =>
                    now(),
            ]);


            /*
            |--------------------------------------------------------------------------
            | MOVIMENTA CONTA
            |--------------------------------------------------------------------------
            */

            MovimentacaoConta::create([
                'user_id' =>
                    $parcela->user_id,

                'conta_id' =>
                    $dados['conta_id'],

                'tipo' =>
                    'saida',

                'origem_tipo' =>
                    'parcela',

                'origem_id' =>
                    $parcela->id,

                'valor' =>
                    $parcela->valor,

                'data_movimentacao' =>
                    $dados['data_pagamento'],

                'descricao' =>
                    'Parcela: '
                    . ($parcelamento?->descricao ?? 'Parcelamento')
                    . ' - '
                    . $parcela->numero_parcela
                    . '/'
                    . $parcela->total_parcelas,

                'estornada' =>
                    false,
            ]);


            /*
            |--------------------------------------------------------------------------
            | VERIFICA SE TODO O PARCELAMENTO FOI PAGO
            |--------------------------------------------------------------------------
            */

            if ($parcelamento) {

                $temParcelaNaoPaga =
                    $parcelamento
                        ->parcelas()
                        ->where(
                            'situacao',
                            '!=',
                            'paga'
                        )
                        ->exists();


                if (!$temParcelaNaoPaga) {

                    $parcelamento->update([
                        'situacao' =>
                            'quitado',
                    ]);
                }
            }
        });


        return back()->with(
            'success',
            'Parcela paga com sucesso.'
        );
    }

    public function cancelarParcela(
    Parcela $parcela
    )   {
            /*
            |--------------------------------------------------------------------------
            | SEGURANÇA
            |--------------------------------------------------------------------------
            */

            abort_unless(
                $parcela->user_id === auth()->id(),
                403
            );


            /*
            |--------------------------------------------------------------------------
            | SOMENTE PARCELA PENDENTE
            |--------------------------------------------------------------------------
            */

            if ($parcela->situacao !== 'pendente') {

                return back()->with(
                    'error',
                    'Somente parcelas pendentes podem ser canceladas.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | CANCELAMENTO
            |--------------------------------------------------------------------------
            */

            DB::transaction(function () use ($parcela) {

                $parcelamento =
                    $parcela->parcelamento;


                $parcela->update([
                    'situacao' =>
                        'cancelada',
                ]);


                /*
                |--------------------------------------------------------------------------
                | VERIFICA SE AINDA EXISTE PARCELA ATIVA
                |--------------------------------------------------------------------------
                |
                | Se todas as parcelas estiverem canceladas,
                | marcamos o parcelamento como cancelado.
                |
                */

                if ($parcelamento) {

                    $temParcelaAtiva =
                        $parcelamento
                            ->parcelas()
                            ->whereIn(
                                'situacao',
                                [
                                    'pendente',
                                    'paga',
                                ]
                            )
                            ->exists();


                    if (!$temParcelaAtiva) {

                        $parcelamento->update([
                            'situacao' =>
                                'cancelado',
                        ]);
                    }
                }
            });


            return back()->with(
                'success',
                'Parcela cancelada com sucesso.'
            );
        }

}
