<?php

namespace App\Http\Controllers;

use App\Models\Conta;
use App\Models\MovimentacaoConta;
use Illuminate\Http\Request;

class MovimentacaoContaController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();

        $mes = $request->input(
            'mes',
            now()->format('Y-m')
        );

        $tipo = $request->input('tipo');

        $contaId = $request->input('conta_id');


        $query = MovimentacaoConta::query()
            ->with('conta')
            ->where('user_id', $userId)
            ->where('estornada', false);


        /*
        |--------------------------------------------------------------------------
        | FILTRO POR MÊS
        |--------------------------------------------------------------------------
        */

        if ($mes) {

            [$ano, $numeroMes] =
                explode('-', $mes);

            $query
                ->whereYear(
                    'data_movimentacao',
                    $ano
                )
                ->whereMonth(
                    'data_movimentacao',
                    $numeroMes
                );
        }


        /*
        |--------------------------------------------------------------------------
        | TIPO
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $tipo,
                ['entrada', 'saida'],
                true
            )
        ) {
            $query->where('tipo', $tipo);
        }


        /*
        |--------------------------------------------------------------------------
        | CONTA
        |--------------------------------------------------------------------------
        */

        if ($contaId) {

            $contaValida = Conta::query()
                ->where('id', $contaId)
                ->where('user_id', $userId)
                ->exists();

            if ($contaValida) {
                $query->where(
                    'conta_id',
                    $contaId
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | MOVIMENTAÇÕES
        |--------------------------------------------------------------------------
        */

        $movimentacoes = $query
            ->orderByDesc(
                'data_movimentacao'
            )
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | TOTAIS DO FILTRO
        |--------------------------------------------------------------------------
        */

        $queryTotais = MovimentacaoConta::query()
            ->where('user_id', $userId)
            ->where('estornada', false);


        if ($mes) {

            [$ano, $numeroMes] =
                explode('-', $mes);

            $queryTotais
                ->whereYear(
                    'data_movimentacao',
                    $ano
                )
                ->whereMonth(
                    'data_movimentacao',
                    $numeroMes
                );
        }


        if ($contaId) {

            $queryTotais->where(
                'conta_id',
                $contaId
            );
        }


        $totalEntradas = (clone $queryTotais)
            ->where('tipo', 'entrada')
            ->sum('valor');


        $totalSaidas = (clone $queryTotais)
            ->where('tipo', 'saida')
            ->sum('valor');


        $saldoPeriodo =
            (float) $totalEntradas
            - (float) $totalSaidas;


        $contas = Conta::query()
            ->where('user_id', $userId)
            ->orderByDesc('ativa')
            ->orderBy('nome')
            ->get();


        return view(
            'movimentacoes.index',
            compact(
                'movimentacoes',
                'contas',
                'mes',
                'tipo',
                'contaId',
                'totalEntradas',
                'totalSaidas',
                'saldoPeriodo'
            )
        );
    }
}
