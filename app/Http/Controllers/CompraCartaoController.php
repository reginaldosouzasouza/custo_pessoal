<?php

namespace App\Http\Controllers;

use App\Models\Cartao;
use App\Models\Categoria;
use App\Models\CompraCartao;
use App\Models\Fatura;
use App\Models\ParcelaCartao;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompraCartaoController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $compras = CompraCartao::query()
            ->with([
                'cartao',
                'categoria',
                'parcelas.fatura',
            ])
            ->where('user_id', $userId)
            ->orderByDesc('data_compra')
            ->orderByDesc('id')
            ->paginate(20);

        return view(
            'compras-cartao.index',
            compact('compras')
        );
    }   


    public function create()
    {
        $userId = auth()->id();

        $cartoes = Cartao::query()
            ->where('user_id', $userId)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

        $categorias = Categoria::query()
            ->where('user_id', $userId)
            ->where('tipo', 'despesa')
            ->where('ativa', true)
            ->orderBy('nome')
            ->get();

        return view(
            'compras-cartao.create',
            compact(
                'cartoes',
                'categorias'
            )
        );
    }


    public function store(Request $request)
    {
        $userId = auth()->id();

        $dados = $request->validate([
            'cartao_id' => [
                'required',
                'integer',
            ],

            'categoria_id' => [
                'required',
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

            'data_compra' => [
                'required',
                'date',
            ],

            'quantidade_parcelas' => [
                'required',
                'integer',
                'between:1,60',
            ],

            'observacao' => [
                'nullable',
                'string',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | VALIDA CARTÃO
        |--------------------------------------------------------------------------
        */

        $cartao = Cartao::query()
            ->where(
                'id',
                $dados['cartao_id']
            )
            ->where(
                'user_id',
                $userId
            )
            ->where(
                'ativo',
                true
            )
            ->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | VALIDA CATEGORIA
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


        /*
        |--------------------------------------------------------------------------
        | ESSENCIAL
        |--------------------------------------------------------------------------
        |
        | Guardamos uma fotografia da classificação atual.
        |
        | Mesmo que a categoria seja alterada futuramente,
        | a compra mantém como estava no momento do lançamento.
        |
        */

        $essencial =
            $categoria->classificacao === 'essencial';


        /*
        |--------------------------------------------------------------------------
        | BLOQUEIA COMPRA EM FATURA JÁ PAGA
        |--------------------------------------------------------------------------
        |
        | Antes de criar a compra, verificamos TODAS as competências que
        | serão atingidas pelas parcelas.
        |
        | Se qualquer uma delas já possuir uma fatura paga, o lançamento
        | inteiro é bloqueado. Assim evitamos criar a compra parcialmente
        | ou alterar uma fatura já encerrada.
        |
        */

        $dataCompraValidacao = Carbon::parse(
            $dados['data_compra']
        );

        $primeiraCompetenciaValidacao =
            $dataCompraValidacao
                ->copy()
                ->startOfMonth();

        if (
            $dataCompraValidacao->day
            > $cartao->dia_fechamento
        ) {
            $primeiraCompetenciaValidacao
                ->addMonthNoOverflow();
        }

        $quantidadeParcelasValidacao =
            (int) $dados['quantidade_parcelas'];

        for (
            $numero = 1;
            $numero <= $quantidadeParcelasValidacao;
            $numero++
        ) {
            $competenciaValidacao =
                $primeiraCompetenciaValidacao
                    ->copy()
                    ->addMonthsNoOverflow(
                        $numero - 1
                    )
                    ->format('Y-m');

            $faturaPaga = Fatura::query()
                ->where(
                    'user_id',
                    $userId
                )
                ->where(
                    'cartao_id',
                    $cartao->id
                )
                ->where(
                    'competencia',
                    $competenciaValidacao
                )
                ->where(
                    'situacao',
                    'paga'
                )
                ->exists();

            if ($faturaPaga) {
                $competenciaFormatada =
                    Carbon::createFromFormat(
                        'Y-m',
                        $competenciaValidacao
                    )->format('m/Y');

                return back()
                    ->withInput()
                    ->withErrors([
                        'data_compra' =>
                            'Não é possível cadastrar esta compra. '
                            . 'A fatura de '
                            . $competenciaFormatada
                            . ' já está paga.',
                    ]);
            }
        }


        DB::transaction(function () use (
            $dados,
            $userId,
            $cartao,
            $essencial
        ) {

            /*
            |--------------------------------------------------------------------------
            | CRIA A COMPRA
            |--------------------------------------------------------------------------
            */

            $compra = CompraCartao::create([
                'user_id' =>
                    $userId,

                'cartao_id' =>
                    $cartao->id,

                'categoria_id' =>
                    $dados['categoria_id'],

                'descricao' =>
                    $dados['descricao'],

                'valor_total' =>
                    $dados['valor_total'],

                'data_compra' =>
                    $dados['data_compra'],

                'quantidade_parcelas' =>
                    $dados['quantidade_parcelas'],

                'essencial' =>
                    $essencial,

                'observacao' =>
                    $dados['observacao'] ?? null,
            ]);


            /*
            |--------------------------------------------------------------------------
            | PRIMEIRA COMPETÊNCIA
            |--------------------------------------------------------------------------
            |
            | Exemplo:
            |
            | fechamento dia 20
            |
            | compra dia 18/08 -> competência 08/2026
            | compra dia 20/08 -> competência 08/2026
            | compra dia 21/08 -> competência 09/2026
            |
            */

            $dataCompra = Carbon::parse(
                $dados['data_compra']
            );

            $primeiraCompetencia = $dataCompra
                ->copy()
                ->startOfMonth();


            if (
                $dataCompra->day
                > $cartao->dia_fechamento
            ) {
                $primeiraCompetencia
                    ->addMonthNoOverflow();
            }


            /*
            |--------------------------------------------------------------------------
            | DIVISÃO EXATA DAS PARCELAS
            |--------------------------------------------------------------------------
            |
            | Trabalhamos em centavos para não perder dinheiro
            | por arredondamento.
            |
            | Exemplo:
            |
            | R$ 100,00 em 3x:
            |
            | 33,34
            | 33,33
            | 33,33
            |
            */

            $valorCentavos = (int) round(
                ((float) $dados['valor_total'])
                * 100
            );

            $quantidadeParcelas =
                (int) $dados['quantidade_parcelas'];

            $valorBaseCentavos =
                intdiv(
                    $valorCentavos,
                    $quantidadeParcelas
                );

            $restoCentavos =
                $valorCentavos
                % $quantidadeParcelas;


            /*
            |--------------------------------------------------------------------------
            | GERA PARCELAS E FATURAS
            |--------------------------------------------------------------------------
            */

            for (
                $numero = 1;
                $numero <= $quantidadeParcelas;
                $numero++
            ) {

                $competencia = $primeiraCompetencia
                    ->copy()
                    ->addMonthsNoOverflow(
                        $numero - 1
                    );


                /*
                 * Distribui centavos excedentes
                 * nas primeiras parcelas.
                 */

                $parcelaCentavos =
                    $valorBaseCentavos;

                if ($numero <= $restoCentavos) {
                    $parcelaCentavos++;
                }

                $valorParcela =
                    $parcelaCentavos / 100;


                /*
                |--------------------------------------------------------------------------
                | DATAS DA FATURA
                |--------------------------------------------------------------------------
                */

                $dataFechamento =
                    $this->montarDataNoMes(
                        $competencia,
                        $cartao->dia_fechamento
                    );


                /*
                 * Se o vencimento é depois do fechamento,
                 * vence no próprio mês.
                 *
                 * Exemplo:
                 * fecha 20, vence 27.
                 *
                 * Se o vencimento é antes ou igual ao fechamento,
                 * vence no mês seguinte.
                 *
                 * Exemplo:
                 * fecha 28, vence 05.
                 */

                if (
                    $cartao->dia_vencimento
                    > $cartao->dia_fechamento
                ) {

                    $mesVencimento =
                        $competencia->copy();

                } else {

                    $mesVencimento =
                        $competencia
                            ->copy()
                            ->addMonthNoOverflow();
                }


                $dataVencimento =
                    $this->montarDataNoMes(
                        $mesVencimento,
                        $cartao->dia_vencimento
                    );


                /*
                |--------------------------------------------------------------------------
                | FATURA
                |--------------------------------------------------------------------------
                */

                $competenciaTexto =
                    $competencia->format('Y-m');


                $fatura = Fatura::query()
                    ->where(
                        'user_id',
                        $userId
                    )
                    ->where(
                        'cartao_id',
                        $cartao->id
                    )
                    ->where(
                        'competencia',
                        $competenciaTexto
                    )
                    ->first();


                if (!$fatura) {

                    $fatura = Fatura::create([
                        'user_id' =>
                            $userId,

                        'cartao_id' =>
                            $cartao->id,

                        'competencia' =>
                            $competenciaTexto,

                        'data_fechamento' =>
                            $dataFechamento,

                        'data_vencimento' =>
                            $dataVencimento,

                        'valor_total' =>
                            0,

                        'valor_pago' =>
                            0,

                        'situacao' =>
                            'aberta',
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | PARCELA DO CARTÃO
                |--------------------------------------------------------------------------
                */

                ParcelaCartao::create([
                    'user_id' =>
                        $userId,

                    'compra_cartao_id' =>
                        $compra->id,

                    'fatura_id' =>
                        $fatura->id,

                    'numero_parcela' =>
                        $numero,

                    'total_parcelas' =>
                        $quantidadeParcelas,

                    'valor' =>
                        $valorParcela,

                    'competencia' =>
                        $competenciaTexto,

                    'data_vencimento' =>
                        $dataVencimento,

                    'situacao' =>
                        'pendente',
                ]);


                /*
                |--------------------------------------------------------------------------
                | ATUALIZA TOTAL DA FATURA
                |--------------------------------------------------------------------------
                */

                $fatura->valor_total =
                    (float) $fatura->valor_total
                    + $valorParcela;

                $fatura->save();
            }
        });


        return redirect()
            ->route('compras-cartao.index')
            ->with(
                'success',
                'Compra registrada e faturas geradas com sucesso.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | MONTA DATA SEGURA
    |--------------------------------------------------------------------------
    |
    | Evita erros com:
    |
    | dia 31 em fevereiro;
    | dia 31 em abril;
    | etc.
    |
    | Se o cartão vence dia 31 e o mês tem 30 dias,
    | usamos o último dia daquele mês.
    |
    */

    private function montarDataNoMes(
        Carbon $mes,
        int $dia
    ): Carbon {
        $data = $mes
            ->copy()
            ->startOfMonth();

        $diaSeguro = min(
            $dia,
            $data->daysInMonth
        );

        return $data->day(
            $diaSeguro
        );
    }
}