<?php

namespace App\Console\Commands;

use App\Models\Despesa;
use App\Models\Receita;
use App\Models\Recorrencia;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GerarRecorrencias extends Command
{
    protected $signature = 'recorrencias:gerar
                            {--data= : Data de referência no formato Y-m-d}';

    protected $description =
        'Gera lançamentos de receitas e despesas recorrentes sem duplicidade';

    public function handle(): int
    {
        $dataReferencia = $this->option('data')
            ? Carbon::parse($this->option('data'))->startOfDay()
            : Carbon::today();

        $this->info(
            'Gerando recorrências para '
            . $dataReferencia->format('d/m/Y')
        );


        $recorrencias = Recorrencia::query()
            ->where('ativa', true)
            ->get();


        $geradas = 0;
        $ignoradas = 0;


        foreach ($recorrencias as $recorrencia) {

            if (
                !$this->recorrenciaValidaNaData(
                    $recorrencia,
                    $dataReferencia
                )
            ) {
                $ignoradas++;
                continue;
            }


            $competencia = $this->obterCompetencia(
                $recorrencia,
                $dataReferencia
            );


            if (!$competencia) {
                $ignoradas++;
                continue;
            }


            $dataVencimento =
                $this->obterDataVencimento(
                    $recorrencia,
                    $competencia
                );


            DB::transaction(function () use (
                $recorrencia,
                $competencia,
                $dataVencimento,
                &$geradas,
                &$ignoradas
            ) {

                if ($recorrencia->tipo === 'despesa') {

                    $jaExiste = Despesa::query()
                        ->where(
                            'user_id',
                            $recorrencia->user_id
                        )
                        ->where(
                            'recorrencia_id',
                            $recorrencia->id
                        )
                        ->whereYear(
                            'data_vencimento',
                            $competencia->year
                        )
                        ->whereMonth(
                            'data_vencimento',
                            $competencia->month
                        )
                        ->exists();


                    if ($jaExiste) {
                        $ignoradas++;
                        return;
                    }


                    Despesa::create([
                        'user_id' =>
                            $recorrencia->user_id,

                        'categoria_id' =>
                            $recorrencia->categoria_id,

                        'conta_id' =>
                            $recorrencia->conta_padrao_id,

                        'forma_pagamento_id' =>
                            $recorrencia->forma_pagamento_id,

                        'recorrencia_id' =>
                            $recorrencia->id,

                        'descricao' =>
                            $recorrencia->descricao,

                        'valor' =>
                            $recorrencia->valor_padrao ?? 0,

                        'data_despesa' =>
                            $competencia
                                ->copy()
                                ->startOfMonth(),

                        'data_vencimento' =>
                            $dataVencimento,

                        'data_pagamento' =>
                            null,

                        'situacao' =>
                            'pendente',

                        'essencial' =>
                            (bool) $recorrencia->essencial,

                        'valor_estimado' =>
                            $recorrencia->tipo_valor
                            === 'variavel',

                        'observacao' =>
                            $recorrencia->observacao,
                    ]);

                    $geradas++;

                    return;
                }


                if ($recorrencia->tipo === 'receita') {

                    $jaExiste = Receita::query()
                        ->where(
                            'user_id',
                            $recorrencia->user_id
                        )
                        ->where(
                            'recorrencia_id',
                            $recorrencia->id
                        )
                        ->whereYear(
                            'data_prevista',
                            $competencia->year
                        )
                        ->whereMonth(
                            'data_prevista',
                            $competencia->month
                        )
                        ->exists();


                    if ($jaExiste) {
                        $ignoradas++;
                        return;
                    }


                    Receita::create([
                        'user_id' =>
                            $recorrencia->user_id,

                        'categoria_id' =>
                            $recorrencia->categoria_id,

                        'conta_id' =>
                            $recorrencia->conta_padrao_id,

                        'forma_pagamento_id' =>
                            $recorrencia->forma_pagamento_id,

                        'recorrencia_id' =>
                            $recorrencia->id,

                        'descricao' =>
                            $recorrencia->descricao,

                        'valor' =>
                            $recorrencia->valor_padrao ?? 0,

                        'data_prevista' =>
                            $dataVencimento,

                        'data_recebimento' =>
                            null,

                        'situacao' =>
                            'pendente',

                        'observacao' =>
                            $recorrencia->observacao,
                    ]);

                    $geradas++;
                }
            });
        }


        $this->newLine();

        $this->info(
            'Geradas: ' . $geradas
        );

        $this->info(
            'Ignoradas/duplicadas: ' . $ignoradas
        );


        return self::SUCCESS;
    }


    private function recorrenciaValidaNaData(
        Recorrencia $recorrencia,
        Carbon $dataReferencia
    ): bool {
        $inicio = Carbon::parse(
            $recorrencia->data_inicio
        )->startOfDay();


        if ($dataReferencia->lt($inicio)) {
            return false;
        }


        if ($recorrencia->data_fim) {

            $fim = Carbon::parse(
                $recorrencia->data_fim
            )->endOfDay();

            if ($dataReferencia->gt($fim)) {
                return false;
            }
        }


        return true;
    }


    private function obterCompetencia(
        Recorrencia $recorrencia,
        Carbon $dataReferencia
    ): ?Carbon {
        $inicio = Carbon::parse(
            $recorrencia->data_inicio
        )->startOfMonth();

        $referencia = $dataReferencia
            ->copy()
            ->startOfMonth();


        $meses =
            $inicio->diffInMonths(
                $referencia,
                false
            );


        if ($meses < 0) {
            return null;
        }


        $intervalo = match (
            $recorrencia->frequencia
        ) {
            'mensal' =>
                1,

            'trimestral' =>
                3,

            'semestral' =>
                6,

            'anual' =>
                12,

            default =>
                null,
        };


        /*
         * Semanal será tratado separadamente
         * mais abaixo.
         */

        if ($recorrencia->frequencia === 'semanal') {

            $inicioReal = Carbon::parse(
                $recorrencia->data_inicio
            )->startOfDay();

            $dias =
                $inicioReal->diffInDays(
                    $dataReferencia,
                    false
                );

            if ($dias < 0 || $dias % 7 !== 0) {
                return null;
            }

            return $dataReferencia->copy();
        }


        if (!$intervalo) {
            return null;
        }


        if ($meses % $intervalo !== 0) {
            return null;
        }


        return $referencia;
    }


    private function obterDataVencimento(
        Recorrencia $recorrencia,
        Carbon $competencia
    ): Carbon {
        if ($recorrencia->frequencia === 'semanal') {
            return $competencia->copy();
        }


        $dia =
            $recorrencia->dia_vencimento
            ?: Carbon::parse(
                $recorrencia->data_inicio
            )->day;


        $base = $competencia
            ->copy()
            ->startOfMonth();


        $diaSeguro = min(
            $dia,
            $base->daysInMonth
        );


        return $base->day(
            $diaSeguro
        );
    }
}