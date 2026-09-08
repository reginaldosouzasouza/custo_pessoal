<?php

namespace App\Services;

use App\Models\Categoria;
use App\Models\Conta;
use App\Models\Despesa;
use App\Models\FormaPagamento;
use App\Models\MovimentacaoConta;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LancamentoDespesaService
{
    public function criarDespesaPaga(
        int $userId,
        array $dados
    ): Despesa {

        $categoriaId =
            (int) (
                $dados['categoria_id']
                ?? 0
            );

        $contaId =
            (int) (
                $dados['conta_id']
                ?? 0
            );

        $formaPagamentoId =
            !empty(
                $dados['forma_pagamento_id']
            )
                ? (int) $dados['forma_pagamento_id']
                : null;

        $descricao =
            trim(
                (string) (
                    $dados['descricao']
                    ?? ''
                )
            );

        $valor =
            (float) (
                $dados['valor']
                ?? 0
            );

        $data =
            (string) (
                $dados['data']
                ?? ''
            );

        if (
            $descricao === ''
            || mb_strlen(
                $descricao
            ) > 180
        ) {
            throw ValidationException::withMessages([
                'descricao' =>
                    'Descrição inválida para a despesa.',
            ]);
        }

        if ($valor <= 0) {
            throw ValidationException::withMessages([
                'valor' =>
                    'O valor da despesa deve ser maior que zero.',
            ]);
        }

        if (
            !$data
            || strtotime($data) === false
        ) {
            throw ValidationException::withMessages([
                'data' =>
                    'Data inválida para a despesa.',
            ]);
        }

        $categoria =
            Categoria::query()
                ->where('id', $categoriaId)
                ->where('user_id', $userId)
                ->where('tipo', 'despesa')
                ->where('ativa', true)
                ->first();

        if (!$categoria) {
            throw ValidationException::withMessages([
                'categoria_id' =>
                    'Categoria inválida ou inativa.',
            ]);
        }

        $conta =
            Conta::query()
                ->where('id', $contaId)
                ->where('user_id', $userId)
                ->where('ativa', true)
                ->first();

        if (!$conta) {
            throw ValidationException::withMessages([
                'conta_id' =>
                    'Conta inválida ou inativa.',
            ]);
        }

        if ($formaPagamentoId) {

            $formaPagamento =
                FormaPagamento::query()
                    ->where(
                        'id',
                        $formaPagamentoId
                    )
                    ->where(
                        'ativa',
                        true
                    )
                    ->first();

            if (!$formaPagamento) {
                throw ValidationException::withMessages([
                    'forma_pagamento_id' =>
                        'Forma de pagamento inválida ou inativa.',
                ]);
            }
        }

        return DB::transaction(
            function () use (
                $userId,
                $categoria,
                $conta,
                $formaPagamentoId,
                $descricao,
                $valor,
                $data
            ) {

                $despesa =
                    Despesa::create([
                        'user_id' =>
                            $userId,

                        'categoria_id' =>
                            $categoria->id,

                        'conta_id' =>
                            $conta->id,

                        'forma_pagamento_id' =>
                            $formaPagamentoId,

                        'descricao' =>
                            $descricao,

                        'valor' =>
                            $valor,

                        'data_despesa' =>
                            $data,

                        'data_vencimento' =>
                            $data,

                        'data_pagamento' =>
                            $data,

                        'situacao' =>
                            'paga',

                        'essencial' =>
                            $categoria->classificacao
                            === 'essencial',

                        'valor_estimado' =>
                            false,

                        'paga_em' =>
                            now(),

                        'observacao' =>
                            'Lançado pelo Assistente SGA Finanças',
                    ]);

                MovimentacaoConta::create([
                    'user_id' =>
                        $userId,

                    'conta_id' =>
                        $conta->id,

                    'tipo' =>
                        'saida',

                    'origem_tipo' =>
                        'despesa',

                    'origem_id' =>
                        $despesa->id,

                    'valor' =>
                        $valor,

                    'data_movimentacao' =>
                        $data,

                    'descricao' =>
                        'Despesa: '
                        . $descricao,

                    'estornada' =>
                        false,
                ]);

                return $despesa->fresh();
            }
        );
    }

    public function criarDespesaPendente(
        int $userId,
        array $dados
    ): Despesa {

        $categoriaId =
            (int) (
                $dados['categoria_id']
                ?? 0
            );

        $descricao =
            trim(
                (string) (
                    $dados['descricao']
                    ?? ''
                )
            );

        $valor =
            (float) (
                $dados['valor']
                ?? 0
            );

        $dataDespesa =
            (string) (
                $dados['data']
                ?? now()->toDateString()
            );

        $dataVencimento =
            (string) (
                $dados['data_vencimento']
                ?? ''
            );

        if (
            $descricao === ''
            || mb_strlen(
                $descricao
            ) > 180
        ) {
            throw ValidationException::withMessages([
                'descricao' =>
                    'Descrição inválida para a despesa.',
            ]);
        }

        if ($valor <= 0) {
            throw ValidationException::withMessages([
                'valor' =>
                    'O valor da despesa deve ser maior que zero.',
            ]);
        }

        if (
            !$dataVencimento
            || strtotime(
                $dataVencimento
            ) === false
        ) {
            throw ValidationException::withMessages([
                'data_vencimento' =>
                    'Data de vencimento inválida.',
            ]);
        }

        $categoria =
            Categoria::query()
                ->where(
                    'id',
                    $categoriaId
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
                ->first();

        if (!$categoria) {
            throw ValidationException::withMessages([
                'categoria_id' =>
                    'Categoria inválida ou inativa.',
            ]);
        }

        return DB::transaction(
            function () use (
                $userId,
                $categoria,
                $descricao,
                $valor,
                $dataDespesa,
                $dataVencimento
            ) {

                return Despesa::create([
                    'user_id' =>
                        $userId,

                    'categoria_id' =>
                        $categoria->id,

                    'conta_id' =>
                        null,

                    'forma_pagamento_id' =>
                        null,

                    'descricao' =>
                        $descricao,

                    'valor' =>
                        $valor,

                    'data_despesa' =>
                        $dataDespesa,

                    'data_vencimento' =>
                        $dataVencimento,

                    'data_pagamento' =>
                        null,

                    'situacao' =>
                        'pendente',

                    'essencial' =>
                        $categoria->classificacao
                        === 'essencial',

                    'valor_estimado' =>
                        false,

                    'paga_em' =>
                        null,

                    'observacao' =>
                        'Lançado pelo Assistente SGA Finanças',
                ]);
            }
        );
    }

}
