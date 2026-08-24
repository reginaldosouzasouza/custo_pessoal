<?php

namespace App\Http\Controllers;

use App\Models\Cartao;
use App\Models\Conta;
use Illuminate\Http\Request;

class CartaoController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $cartoes = Cartao::query()
            ->with('contaPagamento')
            ->where('user_id', $userId)
            ->orderByDesc('ativo')
            ->orderBy('nome')
            ->get()
            ->map(function ($cartao) use ($userId) {

                /*
                |--------------------------------------------------------------------------
                | LIMITE UTILIZADO
                |--------------------------------------------------------------------------
                |
                | Soma somente faturas ainda abertas/fechadas.
                | Faturas pagas já liberaram o limite.
                |
                */

                $limiteUtilizado = \App\Models\Fatura::query()
                    ->where('user_id', $userId)
                    ->where('cartao_id', $cartao->id)
                    ->whereIn(
                        'situacao',
                        [
                            'aberta',
                            'fechada',
                        ]
                    )
                    ->sum('valor_total');


                $cartao->limite_utilizado =
                    (float) $limiteUtilizado;


                $cartao->limite_disponivel =
                    (float) $cartao->limite_total
                    - (float) $limiteUtilizado;


                return $cartao;
            });


        return view(
            'cartoes.index',
            compact('cartoes')
        );
    }

    public function create()
    {
        $contas = Conta::query()
            ->where('user_id', auth()->id())
            ->where('ativa', true)
            ->orderBy('nome')
            ->get();

        return view(
            'cartoes.create',
            compact('contas')
        );
    }

    public function store(Request $request)
    {
        $userId = auth()->id();

        $dados = $request->validate([
            'nome' => [
                'required',
                'string',
                'max:120',
            ],

            'instituicao' => [
                'nullable',
                'string',
                'max:150',
            ],

            'ultimos_digitos' => [
                'nullable',
                'string',
                'max:4',
            ],

            'limite_total' => [
                'required',
                'numeric',
                'gte:0',
            ],

            'dia_fechamento' => [
                'required',
                'integer',
                'between:1,31',
            ],

            'dia_vencimento' => [
                'required',
                'integer',
                'between:1,31',
            ],

            'conta_pagamento_id' => [
                'nullable',
                'integer',
            ],

            'observacao' => [
                'nullable',
                'string',
            ],
        ]);

        if (!empty($dados['conta_pagamento_id'])) {
            Conta::query()
                ->where(
                    'id',
                    $dados['conta_pagamento_id']
                )
                ->where('user_id', $userId)
                ->firstOrFail();
        }

        $dados['user_id'] = $userId;
        $dados['ativo'] = true;

        Cartao::create($dados);

        return redirect()
            ->route('cartoes.index')
            ->with(
                'success',
                'Cartão cadastrado com sucesso.'
            );
    }

    public function edit(Cartao $cartao)
    {
        $this->validarCartaoUsuario($cartao);

        $contas = Conta::query()
            ->where('user_id', auth()->id())
            ->where('ativa', true)
            ->orderBy('nome')
            ->get();

        return view(
            'cartoes.edit',
            compact(
                'cartao',
                'contas'
            )
        );
    }

    public function update(
        Request $request,
        Cartao $cartao
    ) {
        $this->validarCartaoUsuario($cartao);

        $userId = auth()->id();

        $dados = $request->validate([
            'nome' => [
                'required',
                'string',
                'max:120',
            ],

            'instituicao' => [
                'nullable',
                'string',
                'max:150',
            ],

            'ultimos_digitos' => [
                'nullable',
                'string',
                'max:4',
            ],

            'limite_total' => [
                'required',
                'numeric',
                'gte:0',
            ],

            'dia_fechamento' => [
                'required',
                'integer',
                'between:1,31',
            ],

            'dia_vencimento' => [
                'required',
                'integer',
                'between:1,31',
            ],

            'conta_pagamento_id' => [
                'nullable',
                'integer',
            ],

            'observacao' => [
                'nullable',
                'string',
            ],
        ]);

        if (!empty($dados['conta_pagamento_id'])) {
            Conta::query()
                ->where(
                    'id',
                    $dados['conta_pagamento_id']
                )
                ->where('user_id', $userId)
                ->firstOrFail();
        }

        $cartao->update($dados);

        return redirect()
            ->route('cartoes.index')
            ->with(
                'success',
                'Cartão atualizado com sucesso.'
            );
    }

    public function alternarStatus(Cartao $cartao)
    {
        $this->validarCartaoUsuario($cartao);

        $cartao->ativo = !$cartao->ativo;
        $cartao->save();

        return redirect()
            ->route('cartoes.index')
            ->with(
                'success',
                $cartao->ativo
                    ? 'Cartão ativado com sucesso.'
                    : 'Cartão desativado com sucesso.'
            );
    }

    private function validarCartaoUsuario(
        Cartao $cartao
    ): void {
        abort_unless(
            $cartao->user_id === auth()->id(),
            403
        );
    }
}