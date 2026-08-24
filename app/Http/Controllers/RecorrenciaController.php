<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Conta;
use App\Models\FormaPagamento;
use App\Models\Recorrencia;
use Illuminate\Http\Request;

class RecorrenciaController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $recorrencias = Recorrencia::query()
            ->with([
                'categoria',
                'contaPadrao',
                'formaPagamento',
            ])
            ->where('user_id', $userId)
            ->orderByDesc('ativa')
            ->orderBy('dia_vencimento')
            ->orderBy('descricao')
            ->get();

        $totalMensalFixas = Recorrencia::query()
            ->where('user_id', $userId)
            ->where('ativa', true)
            ->where('tipo', 'despesa')
            ->sum('valor_padrao');    

        return view(
            'recorrencias.index',
            compact('recorrencias',
                    'totalMensalFixas')
        );
    }


    public function create()
    {
        $userId = auth()->id();

        $categorias = Categoria::query()
            ->where('user_id', $userId)
            ->where('ativa', true)
            ->orderBy('tipo')
            ->orderBy('nome')
            ->get();

        $contas = Conta::query()
            ->where('user_id', $userId)
            ->where('ativa', true)
            ->orderBy('nome')
            ->get();

        $formasPagamento = FormaPagamento::query()
            ->where('ativa', true)
            ->orderBy('nome')
            ->get();

        return view(
            'recorrencias.create',
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
            'tipo' => [
                'required',
                'in:receita,despesa',
            ],

            'categoria_id' => [
                'required',
                'integer',
            ],

            'conta_padrao_id' => [
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

            'tipo_valor' => [
                'required',
                'in:fixo,variavel',
            ],

            'valor_padrao' => [
                'nullable',
                'numeric',
                'gte:0',
            ],

            'frequencia' => [
                'required',
                'in:semanal,mensal,trimestral,semestral,anual',
            ],

            'dia_vencimento' => [
                'nullable',
                'integer',
                'between:1,31',
            ],

            'data_inicio' => [
                'required',
                'date',
            ],

            'data_fim' => [
                'nullable',
                'date',
                'after_or_equal:data_inicio',
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
            ->where('id', $dados['categoria_id'])
            ->where('user_id', $userId)
            ->where('tipo', $dados['tipo'])
            ->where('ativa', true)
            ->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | CONTA PADRÃO
        |--------------------------------------------------------------------------
        */

        if (!empty($dados['conta_padrao_id'])) {

            Conta::query()
                ->where(
                    'id',
                    $dados['conta_padrao_id']
                )
                ->where('user_id', $userId)
                ->where('ativa', true)
                ->firstOrFail();
        }


        /*
        |--------------------------------------------------------------------------
        | VALOR FIXO
        |--------------------------------------------------------------------------
        */

        if (
            $dados['tipo_valor'] === 'fixo'
            && (
                !isset($dados['valor_padrao'])
                || (float) $dados['valor_padrao'] <= 0
            )
        ) {
            return back()
                ->withErrors([
                    'valor_padrao' =>
                        'Informe o valor da conta fixa.',
                ])
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | ESSENCIAL
        |--------------------------------------------------------------------------
        */

        $essencial =
            $dados['tipo'] === 'despesa'
            && $categoria->classificacao === 'essencial';


        Recorrencia::create([
            'user_id' =>
                $userId,

            'categoria_id' =>
                $categoria->id,

            'forma_pagamento_id' =>
                $dados['forma_pagamento_id'] ?? null,

            'conta_padrao_id' =>
                $dados['conta_padrao_id'] ?? null,

            'tipo' =>
                $dados['tipo'],

            'descricao' =>
                $dados['descricao'],

            'tipo_valor' =>
                $dados['tipo_valor'],

            'valor_padrao' =>
                $dados['valor_padrao'] ?? null,

            'frequencia' =>
                $dados['frequencia'],

            'dia_vencimento' =>
                $dados['dia_vencimento'] ?? null,

            'data_inicio' =>
                $dados['data_inicio'],

            'data_fim' =>
                $dados['data_fim'] ?? null,

            'essencial' =>
                $essencial,

            'ativa' =>
                true,

            'observacao' =>
                $dados['observacao'] ?? null,
        ]);


        return redirect()
            ->route('recorrencias.index')
            ->with(
                'success',
                'Conta fixa cadastrada com sucesso.'
            );
    }


    public function edit(Recorrencia $recorrencia)
    {
        $this->validarUsuario($recorrencia);

        $userId = auth()->id();

        $categorias = Categoria::query()
            ->where('user_id', $userId)
            ->where('ativa', true)
            ->orderBy('tipo')
            ->orderBy('nome')
            ->get();

        $contas = Conta::query()
            ->where('user_id', $userId)
            ->where('ativa', true)
            ->orderBy('nome')
            ->get();

        $formasPagamento = FormaPagamento::query()
            ->where('ativa', true)
            ->orderBy('nome')
            ->get();

        return view(
            'recorrencias.edit',
            compact(
                'recorrencia',
                'categorias',
                'contas',
                'formasPagamento'
            )
        );
    }


    public function update(
        Request $request,
        Recorrencia $recorrencia
    ) {
        $this->validarUsuario($recorrencia);

        $userId = auth()->id();

        $dados = $request->validate([
            'tipo' => [
                'required',
                'in:receita,despesa',
            ],

            'categoria_id' => [
                'required',
                'integer',
            ],

            'conta_padrao_id' => [
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

            'tipo_valor' => [
                'required',
                'in:fixo,variavel',
            ],

            'valor_padrao' => [
                'nullable',
                'numeric',
                'gte:0',
            ],

            'frequencia' => [
                'required',
                'in:semanal,mensal,trimestral,semestral,anual',
            ],

            'dia_vencimento' => [
                'nullable',
                'integer',
                'between:1,31',
            ],

            'data_inicio' => [
                'required',
                'date',
            ],

            'data_fim' => [
                'nullable',
                'date',
                'after_or_equal:data_inicio',
            ],

            'observacao' => [
                'nullable',
                'string',
            ],
        ]);


        $categoria = Categoria::query()
            ->where('id', $dados['categoria_id'])
            ->where('user_id', $userId)
            ->where('tipo', $dados['tipo'])
            ->where('ativa', true)
            ->firstOrFail();


        if (!empty($dados['conta_padrao_id'])) {

            Conta::query()
                ->where(
                    'id',
                    $dados['conta_padrao_id']
                )
                ->where('user_id', $userId)
                ->where('ativa', true)
                ->firstOrFail();
        }


        if (
            $dados['tipo_valor'] === 'fixo'
            && (
                !isset($dados['valor_padrao'])
                || (float) $dados['valor_padrao'] <= 0
            )
        ) {
            return back()
                ->withErrors([
                    'valor_padrao' =>
                        'Informe o valor da conta fixa.',
                ])
                ->withInput();
        }


        $dados['essencial'] =
            $dados['tipo'] === 'despesa'
            && $categoria->classificacao === 'essencial';


        $recorrencia->update($dados);


        return redirect()
            ->route('recorrencias.index')
            ->with(
                'success',
                'Conta fixa atualizada com sucesso.'
            );
    }


    public function alternarStatus(
        Recorrencia $recorrencia
    ) {
        $this->validarUsuario($recorrencia);

        $recorrencia->ativa =
            !$recorrencia->ativa;

        $recorrencia->save();


        return redirect()
            ->route('recorrencias.index')
            ->with(
                'success',
                $recorrencia->ativa
                    ? 'Conta fixa ativada.'
                    : 'Conta fixa desativada.'
            );
    }


    private function validarUsuario(
        Recorrencia $recorrencia
    ): void {
        abort_unless(
            $recorrencia->user_id === auth()->id(),
            403
        );
    }
}