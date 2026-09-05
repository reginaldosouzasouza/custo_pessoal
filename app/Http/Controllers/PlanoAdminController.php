<?php

namespace App\Http\Controllers;

use App\Models\Plano;
use Illuminate\Http\Request;

class PlanoAdminController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTAGEM
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $planos = Plano::query()
            ->orderByDesc('ativo')
            ->orderBy('duracao_meses')
            ->orderBy('nome')
            ->get();

        return view(
            'admin.planos.index',
            compact('planos')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CADASTRAR
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $dados = $request->validate([

            'nome' => [
                'required',
                'string',
                'max:100',
            ],

            'duracao_meses' => [
                'required',
                'integer',
                'min:1',
                'max:120',
            ],

            'valor' => [
                'required',
                'numeric',
                'min:0',
            ],

            'descricao' => [
                'nullable',
                'string',
                'max:500',
            ],
        ]);


        Plano::create([

            'nome' =>
                $dados['nome'],

            'duracao_meses' =>
                $dados['duracao_meses'],

            'valor' =>
                $dados['valor'],

            'descricao' =>
                $dados['descricao']
                ?? null,

            'ativo' =>
                true,
        ]);


        return redirect()
            ->route('admin.planos.index')
            ->with(
                'success',
                'Plano cadastrado com sucesso.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | ATUALIZAR
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Plano $plano
    ) {
        $dados = $request->validate([

            'nome' => [
                'required',
                'string',
                'max:100',
            ],

            'duracao_meses' => [
                'required',
                'integer',
                'min:1',
                'max:120',
            ],

            'valor' => [
                'required',
                'numeric',
                'min:0',
            ],

            'descricao' => [
                'nullable',
                'string',
                'max:500',
            ],
        ]);


        $plano->update([

            'nome' =>
                $dados['nome'],

            'duracao_meses' =>
                $dados['duracao_meses'],

            'valor' =>
                $dados['valor'],

            'descricao' =>
                $dados['descricao']
                ?? null,
        ]);


        return redirect()
            ->route('admin.planos.index')
            ->with(
                'success',
                'Plano atualizado com sucesso.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | ATIVAR / DESATIVAR
    |--------------------------------------------------------------------------
    */

    public function alternarStatus(
        Plano $plano
    ) {
        $plano->update([
            'ativo' =>
                !$plano->ativo,
        ]);


        return back()->with(
            'success',
            $plano->ativo
                ? 'Plano ativado com sucesso.'
                : 'Plano desativado com sucesso.'
        );
    }
}