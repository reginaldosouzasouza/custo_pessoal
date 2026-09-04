<?php

namespace App\Http\Controllers;

use App\Models\Conta;
use App\Models\Fatura;
use App\Models\MovimentacaoConta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FaturaController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $userId = auth()->id();

        $cartaoId = $request->input('cartao_id');
        $situacao = $request->input('situacao');

        $ano = $request->input(
            'ano',
            now()->format('Y')
        );

        $mes = $request->input('mes');

        $query = Fatura::query()
            ->with([
                'cartao',
                'contaPagamento',
                'parcelas.compra.categoria',
            ])
            ->where('user_id', $userId);

        if ($cartaoId) {
            $query->where('cartao_id', $cartaoId);
        }

        if (
            in_array(
                $situacao,
                ['aberta', 'fechada', 'paga'],
                true
            )
        ) {
            $query->where('situacao', $situacao);
        }

        /*
        |--------------------------------------------------------------------------
        | FILTRO PELO ANO DO VENCIMENTO
        |--------------------------------------------------------------------------
        */
        if ($ano) {
            $query->whereYear(
                'data_vencimento',
                $ano
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTRO PELO MÊS DO VENCIMENTO
        |--------------------------------------------------------------------------
        */
        if (
            $mes
            && is_numeric($mes)
            && $mes >= 1
            && $mes <= 12
        ) {
            $query->whereMonth(
                'data_vencimento',
                $mes
            );
        }

        /*
        |--------------------------------------------------------------------------
        | ORDENAÇÃO
        |--------------------------------------------------------------------------
        |
        | 1. Faturas abertas primeiro
        | 2. Depois fechadas
        | 3. Depois pagas
        | 4. Dentro de cada situação:
        |    vencimento mais recente primeiro
        |
        */
        $faturas = $query
            ->orderByRaw("
                CASE situacao
                    WHEN 'aberta' THEN 1
                    WHEN 'fechada' THEN 2
                    WHEN 'paga' THEN 3
                    ELSE 4
                END
            ")
            ->orderByDesc('data_vencimento')
            ->orderByDesc('id')
            ->get();

        $cartoes = \App\Models\Cartao::query()
            ->where('user_id', $userId)
            ->orderBy('nome')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | ANOS DISPONÍVEIS PELO VENCIMENTO
        |--------------------------------------------------------------------------
        */
        $anos = Fatura::query()
            ->where('user_id', $userId)
            ->whereNotNull('data_vencimento')
            ->selectRaw(
                'DISTINCT YEAR(data_vencimento) as ano'
            )
            ->orderByDesc('ano')
            ->pluck('ano');

        return view(
            'faturas.index',
            compact(
                'faturas',
                'cartoes',
                'cartaoId',
                'situacao',
                'ano',
                'mes',
                'anos'
            )
        );
    }


    public function pagar(
        Request $request,
        Fatura $fatura
    ) {
        $this->validarFaturaUsuario($fatura);

        if ($fatura->situacao === 'paga') {
            return back()->with(
                'warning',
                'Esta fatura já está paga.'
            );
        }

        $dados = $request->validate([
            'conta_pagamento_id' => [
                'required',
                'integer',
            ],

            'data_pagamento' => [
                'required',
                'date',
            ],
        ]);


        $conta = Conta::query()
            ->where(
                'id',
                $dados['conta_pagamento_id']
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


        DB::transaction(function () use (
            $fatura,
            $conta,
            $dados
        ) {

            /*
            |--------------------------------------------------------------------------
            | VALOR A PAGAR
            |--------------------------------------------------------------------------
            */

            $valorRestante =
                (float) $fatura->valor_total
                - (float) $fatura->valor_pago;


            if ($valorRestante <= 0) {
                return;
            }


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
                    'pagamento_fatura',

                'origem_id' =>
                    $fatura->id,

                'valor' =>
                    $valorRestante,

                'data_movimentacao' =>
                    $dados['data_pagamento'],

                'descricao' =>
                    'Pagamento fatura '
                    . (
                        $fatura->cartao?->nome
                        ?? 'Cartão'
                    ),

                'estornada' =>
                    false,
            ]);


            /*
            |--------------------------------------------------------------------------
            | ATUALIZA FATURA
            |--------------------------------------------------------------------------
            */

            $fatura->valor_pago =
                (float) $fatura->valor_pago
                + $valorRestante;

            $fatura->situacao =
                'paga';

            $fatura->data_pagamento =
                $dados['data_pagamento'];

            $fatura->conta_pagamento_id =
                $conta->id;

            $fatura->paga_em =
                now();

            $fatura->save();


            /*
            |--------------------------------------------------------------------------
            | MARCA PARCELAS DA FATURA COMO PAGAS
            |--------------------------------------------------------------------------
            */

            $fatura
                ->parcelas()
                ->where(
                    'situacao',
                    'pendente'
                )
                ->update([
                    'situacao' => 'paga',
                ]);
        });


        return redirect()
            ->route('faturas.index')
            ->with(
                'success',
                'Fatura paga com sucesso.'
            );
    }


    private function validarFaturaUsuario(
        Fatura $fatura
    ): void {
        abort_unless(
            $fatura->user_id === auth()->id(),
            403
        );
    }
}
