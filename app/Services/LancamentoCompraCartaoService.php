<?php

namespace App\Services;

use App\Models\Cartao;
use App\Models\Categoria;
use App\Models\CompraCartao;
use App\Models\Fatura;
use App\Models\ParcelaCartao;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LancamentoCompraCartaoService
{
    public function criar(
        int $userId,
        array $dados
    ): CompraCartao {

        $cartao =
            Cartao::query()
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

        $categoria =
            Categoria::query()
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

        $valorTotal =
            (float) (
                $dados['valor_total']
                ?? 0
            );

        $quantidadeParcelas =
            (int) (
                $dados['quantidade_parcelas']
                ?? 1
            );

        if ($valorTotal <= 0) {
            throw new RuntimeException(
                'O valor da compra deve ser maior que zero.'
            );
        }

        if (
            $quantidadeParcelas < 1
            || $quantidadeParcelas > 60
        ) {
            throw new RuntimeException(
                'A quantidade de parcelas deve estar entre 1 e 60.'
            );
        }

        $descricao =
            trim(
                (string) (
                    $dados['descricao']
                    ?? ''
                )
            );

        if ($descricao === '') {
            throw new RuntimeException(
                'A descrição da compra é obrigatória.'
            );
        }

        $dataCompra =
            Carbon::parse(
                $dados['data_compra']
                ?? now()->toDateString()
            )->startOfDay();

        $essencial =
            $categoria->classificacao === 'essencial';

        /*
        |--------------------------------------------------------------------------
        | PRIMEIRA COMPETÊNCIA
        |--------------------------------------------------------------------------
        |
        | Compra no dia do fechamento ainda entra na competência atual.
        | Compra depois do fechamento entra na próxima competência.
        |
        */

        $primeiraCompetencia =
            $dataCompra
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
        | BLOQUEIA FATURA JÁ PAGA
        |--------------------------------------------------------------------------
        */

        for (
            $numero = 1;
            $numero <= $quantidadeParcelas;
            $numero++
        ) {

            $competencia =
                $primeiraCompetencia
                    ->copy()
                    ->addMonthsNoOverflow(
                        $numero - 1
                    )
                    ->format('Y-m');

            $faturaPaga =
                Fatura::query()
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
                        $competencia
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
                        $competencia
                    )->format('m/Y');

                throw new RuntimeException(
                    'Não é possível cadastrar esta compra. '
                    . 'A fatura de '
                    . $competenciaFormatada
                    . ' já está paga.'
                );
            }
        }

        return DB::transaction(
            function () use (
                $userId,
                $cartao,
                $categoria,
                $descricao,
                $valorTotal,
                $dataCompra,
                $quantidadeParcelas,
                $essencial,
                $dados,
                $primeiraCompetencia
            ) {

                $compra =
                    CompraCartao::create([
                        'user_id' =>
                            $userId,

                        'cartao_id' =>
                            $cartao->id,

                        'categoria_id' =>
                            $categoria->id,

                        'descricao' =>
                            $descricao,

                        'valor_total' =>
                            $valorTotal,

                        'data_compra' =>
                            $dataCompra->toDateString(),

                        'quantidade_parcelas' =>
                            $quantidadeParcelas,

                        'essencial' =>
                            $essencial,

                        'observacao' =>
                            $dados['observacao']
                            ?? null,
                    ]);

                /*
                |--------------------------------------------------------------------------
                | DIVISÃO EXATA EM CENTAVOS
                |--------------------------------------------------------------------------
                */

                $valorCentavos =
                    (int) round(
                        $valorTotal * 100
                    );

                $valorBaseCentavos =
                    intdiv(
                        $valorCentavos,
                        $quantidadeParcelas
                    );

                $restoCentavos =
                    $valorCentavos
                    % $quantidadeParcelas;

                for (
                    $numero = 1;
                    $numero <= $quantidadeParcelas;
                    $numero++
                ) {

                    $competencia =
                        $primeiraCompetencia
                            ->copy()
                            ->addMonthsNoOverflow(
                                $numero - 1
                            );

                    $parcelaCentavos =
                        $valorBaseCentavos;

                    if (
                        $numero
                        <= $restoCentavos
                    ) {
                        $parcelaCentavos++;
                    }

                    $valorParcela =
                        $parcelaCentavos / 100;

                    $dataFechamento =
                        $this->montarDataNoMes(
                            $competencia,
                            (int) $cartao->dia_fechamento
                        );

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
                            (int) $cartao->dia_vencimento
                        );

                    $competenciaTexto =
                        $competencia
                            ->format('Y-m');

                    $fatura =
                        Fatura::query()
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

                        $fatura =
                            Fatura::create([
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

                    $fatura->valor_total =
                        (float) $fatura->valor_total
                        + $valorParcela;

                    $fatura->save();
                }

                return $compra->fresh([
                    'cartao',
                    'categoria',
                    'parcelas.fatura',
                ]);
            }
        );
    }

    private function montarDataNoMes(
        Carbon $mes,
        int $dia
    ): Carbon {

        $data =
            $mes
                ->copy()
                ->startOfMonth();

        $diaSeguro =
            min(
                $dia,
                $data->daysInMonth
            );

        return
            $data->day(
                $diaSeguro
            );
    }
}
