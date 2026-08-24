<?php

namespace App\Http\Controllers;

use App\Models\Conta;
use App\Models\MovimentacaoConta;
use App\Models\Transferencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferenciaController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $transferencias = Transferencia::query()
            ->with([
                'contaOrigem',
                'contaDestino',
            ])
            ->where('user_id', $userId)
            ->orderByDesc('data_transferencia')
            ->orderByDesc('id')
            ->paginate(30);

        return view(
            'transferencias.index',
            compact('transferencias')
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
            'transferencias.create',
            compact('contas')
        );
    }


    public function store(Request $request)
    {
        $userId = auth()->id();

        $dados = $request->validate([
            'conta_origem_id' => [
                'required',
                'integer',
            ],

            'conta_destino_id' => [
                'required',
                'integer',
                'different:conta_origem_id',
            ],

            'valor' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'data_transferencia' => [
                'required',
                'date',
            ],

            'descricao' => [
                'nullable',
                'string',
                'max:180',
            ],

            'observacao' => [
                'nullable',
                'string',
            ],
        ]);


        $contaOrigem = Conta::query()
            ->where('id', $dados['conta_origem_id'])
            ->where('user_id', $userId)
            ->where('ativa', true)
            ->firstOrFail();


        $contaDestino = Conta::query()
            ->where('id', $dados['conta_destino_id'])
            ->where('user_id', $userId)
            ->where('ativa', true)
            ->firstOrFail();


        DB::transaction(function () use (
            $dados,
            $userId,
            $contaOrigem,
            $contaDestino
        ) {

            $descricao =
                $dados['descricao']
                ?? (
                    'Transferência de '
                    . $contaOrigem->nome
                    . ' para '
                    . $contaDestino->nome
                );


            $transferencia = Transferencia::create([
                'user_id' =>
                    $userId,

                'conta_origem_id' =>
                    $contaOrigem->id,

                'conta_destino_id' =>
                    $contaDestino->id,

                'valor' =>
                    $dados['valor'],

                'data_transferencia' =>
                    $dados['data_transferencia'],

                'descricao' =>
                    $descricao,

                'observacao' =>
                    $dados['observacao'] ?? null,
            ]);


            MovimentacaoConta::create([
                'user_id' =>
                    $userId,

                'conta_id' =>
                    $contaOrigem->id,

                'tipo' =>
                    'saida',

                'origem_tipo' =>
                    'transferencia',

                'origem_id' =>
                    $transferencia->id,

                'valor' =>
                    $dados['valor'],

                'data_movimentacao' =>
                    $dados['data_transferencia'],

                'descricao' =>
                    'Transferência para '
                    . $contaDestino->nome,

                'estornada' =>
                    false,
            ]);


            MovimentacaoConta::create([
                'user_id' =>
                    $userId,

                'conta_id' =>
                    $contaDestino->id,

                'tipo' =>
                    'entrada',

                'origem_tipo' =>
                    'transferencia',

                'origem_id' =>
                    $transferencia->id,

                'valor' =>
                    $dados['valor'],

                'data_movimentacao' =>
                    $dados['data_transferencia'],

                'descricao' =>
                    'Transferência de '
                    . $contaOrigem->nome,

                'estornada' =>
                    false,
            ]);
        });


        return redirect()
            ->route('transferencias.index')
            ->with(
                'success',
                'Transferência realizada com sucesso.'
            );
    }
}
