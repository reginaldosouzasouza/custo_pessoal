<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Conta;
use App\Models\FormaPagamento;
use App\Models\MovimentacaoConta;
use App\Models\Receita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReceitaController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();

        $query = Receita::query()
            ->with([
                'categoria',
                'conta',
                'formaPagamento',
            ])
            ->where('user_id', $userId);

        if ($request->filled('situacao')) {
            $query->where(
                'situacao',
                $request->situacao
            );
        }

        if ($request->filled('mes')) {
            $query->whereMonth(
                'data_prevista',
                substr($request->mes, 5, 2)
            )
            ->whereYear(
                'data_prevista',
                substr($request->mes, 0, 4)
            );
        }

        $receitas = $query
            ->orderByDesc('data_prevista')
            ->orderByDesc('id')
            ->get();

            

        return view(
            'receitas.index',
            compact('receitas')
        );
    }

    public function create()
    {
        $userId = auth()->id();

        $categorias = Categoria::query()
            ->where('user_id', $userId)
            ->where('tipo', 'receita')
            ->where('ativa', true)
            ->orderBy('nome')
            ->get();

        $contas = Conta::query()
            ->where('user_id', $userId)
            ->where('ativa', true)
            ->orderBy('nome')
            ->get();

        $formasPagamento =
            FormaPagamento::query()
                ->where('ativa', true)
                ->whereIn('tipo', [
                    'recebimento',
                    'ambos',
                ])
                ->orderBy('nome')
                ->get();

        return view(
            'receitas.create',
            compact(
                'categorias',
                'contas',
                'formasPagamento'
            )
        );
    }

    public function store(Request $request)
    {
        $userId = auth()->id();

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

            'data_prevista' => [
                'required',
                'date',
            ],

            'data_recebimento' => [
                'nullable',
                'date',
            ],

            'situacao' => [
                'required',
                'in:pendente,recebida',
            ],

            'observacao' => [
                'nullable',
                'string',
            ],
        ]);

        $categoria = Categoria::query()
            ->where('id', $dados['categoria_id'])
            ->where('user_id', $userId)
            ->where('tipo', 'receita')
            ->firstOrFail();

        if (!empty($dados['conta_id'])) {
            Conta::query()
                ->where('id', $dados['conta_id'])
                ->where('user_id', $userId)
                ->where('ativa', true)
                ->firstOrFail();
        }

        if ($dados['situacao'] === 'recebida') {
            if (empty($dados['conta_id'])) {
                return back()
                    ->withErrors([
                        'conta_id' =>
                            'Informe a conta que recebeu o valor.',
                    ])
                    ->withInput();
            }

            $dados['data_recebimento'] =
                $dados['data_recebimento']
                ?? now()->toDateString();

            $dados['recebida_em'] = now();
        } else {
            $dados['data_recebimento'] = null;
            $dados['recebida_em'] = null;
        }

        $dados['user_id'] = $userId;

        DB::transaction(function () use ($dados) {

            $receita = Receita::create($dados);

            if ($receita->situacao === 'recebida') {

                MovimentacaoConta::create([
                    'user_id' => $receita->user_id,
                    'conta_id' => $receita->conta_id,
                    'tipo' => 'entrada',
                    'origem_tipo' => 'receita',
                    'origem_id' => $receita->id,
                    'valor' => $receita->valor,
                    'data_movimentacao' =>
                        $receita->data_recebimento,
                    'descricao' =>
                        'Receita: '
                        . $receita->descricao,
                    'estornada' => false,
                ]);
            }
        });

        return redirect()
            ->route('receitas.index')
            ->with(
                'success',
                'Receita cadastrada com sucesso.'
            );
    }

    public function edit(Receita $receita)
    {
        $this->validarReceitaUsuario($receita);

        if ($receita->situacao === 'recebida') {
            return redirect()
                ->route('receitas.index')
                ->with(
                    'error',
                    'Receitas recebidas não podem ser editadas diretamente.'
                );
        }

        $userId = auth()->id();

        $categorias = Categoria::query()
            ->where('user_id', $userId)
            ->where('tipo', 'receita')
            ->where('ativa', true)
            ->orderBy('nome')
            ->get();

        $contas = Conta::query()
            ->where('user_id', $userId)
            ->where('ativa', true)
            ->orderBy('nome')
            ->get();

        $formasPagamento =
            FormaPagamento::query()
                ->where('ativa', true)
                ->whereIn('tipo', [
                    'recebimento',
                    'ambos',
                ])
                ->orderBy('nome')
                ->get();

        return view(
            'receitas.edit',
            compact(
                'receita',
                'categorias',
                'contas',
                'formasPagamento'
            )
        );
    }

    public function update(
        Request $request,
        Receita $receita
    ) {
        $this->validarReceitaUsuario($receita);

        if ($receita->situacao !== 'pendente') {
            abort(403);
        }

        $userId = auth()->id();

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

            'data_prevista' => [
                'required',
                'date',
            ],

            'observacao' => [
                'nullable',
                'string',
            ],
        ]);

        Categoria::query()
            ->where('id', $dados['categoria_id'])
            ->where('user_id', $userId)
            ->where('tipo', 'receita')
            ->firstOrFail();

        if (!empty($dados['conta_id'])) {
            Conta::query()
                ->where('id', $dados['conta_id'])
                ->where('user_id', $userId)
                ->firstOrFail();
        }

        $receita->update($dados);

        return redirect()
            ->route('receitas.index')
            ->with(
                'success',
                'Receita atualizada com sucesso.'
            );
    }

    public function receber(
        Request $request,
        Receita $receita
    ) {
        $this->validarReceitaUsuario($receita);

        if ($receita->situacao !== 'pendente') {
            return back()->with(
                'error',
                'Somente receitas pendentes podem ser recebidas.'
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

            'data_recebimento' => [
                'required',
                'date',
            ],
        ]);

        Conta::query()
            ->where('id', $dados['conta_id'])
            ->where('user_id', auth()->id())
            ->where('ativa', true)
            ->firstOrFail();

        DB::transaction(function () use (
            $receita,
            $dados
        ) {

            $receita->update([
                'conta_id' =>
                    $dados['conta_id'],

                'forma_pagamento_id' =>
                    $dados['forma_pagamento_id']
                    ?? null,

                'data_recebimento' =>
                    $dados['data_recebimento'],

                'situacao' =>
                    'recebida',

                'recebida_em' =>
                    now(),
            ]);

            MovimentacaoConta::create([
                'user_id' =>
                    $receita->user_id,

                'conta_id' =>
                    $dados['conta_id'],

                'tipo' =>
                    'entrada',

                'origem_tipo' =>
                    'receita',

                'origem_id' =>
                    $receita->id,

                'valor' =>
                    $receita->valor,

                'data_movimentacao' =>
                    $dados['data_recebimento'],

                'descricao' =>
                    'Receita: '
                    . $receita->descricao,

                'estornada' =>
                    false,
            ]);
        });

        return back()->with(
            'success',
            'Receita recebida com sucesso.'
        );
    }

    private function validarReceitaUsuario(
        Receita $receita
    ): void {
        abort_unless(
            $receita->user_id === auth()->id(),
            403
        );
    }
}
