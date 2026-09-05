<?php

namespace App\Http\Controllers;

use App\Models\Assinatura;
use App\Models\Plano;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssinaturaAdminController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTAGEM
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $usuarios = User::query()
            ->with([
                'assinaturaAtual.plano',
            ])
            ->where('ativo', true)
            ->orderBy('name')
            ->get();


        $planos = Plano::query()
            ->where('ativo', true)
            ->orderBy('duracao_meses')
            ->orderBy('nome')
            ->get();


        return view(
            'admin.assinaturas.index',
            compact(
                'usuarios',
                'planos'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | LIBERAR PERÍODO DE TESTE
    |--------------------------------------------------------------------------
    */

    public function liberarTeste(
        Request $request
    ) {
        $dados = $request->validate([

            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],

            'dias' => [
                'nullable',
                'integer',
                'in:7,15,30',
            ],

            'data_vencimento' => [
                'nullable',
                'date',
                'after_or_equal:today',
            ],
        ]);


        /*
         * O administrador pode escolher:
         *
         * 7 dias
         * 15 dias
         * 30 dias
         * ou uma data personalizada.
         */

        if (
            empty($dados['dias'])
            &&
            empty($dados['data_vencimento'])
        ) {
            return back()
                ->withErrors([
                    'dias' =>
                        'Informe o período de teste ou uma data de vencimento.',
                ])
                ->withInput();
        }


        $inicio = Carbon::today();


        if (
            !empty(
                $dados['data_vencimento']
            )
        ) {

            $vencimento =
                Carbon::parse(
                    $dados['data_vencimento']
                )->startOfDay();

        } else {

            $vencimento =
                $inicio
                    ->copy()
                    ->addDays(
                        (int) $dados['dias']
                    );
        }


        DB::transaction(
            function () use (
                $dados,
                $inicio,
                $vencimento
            ) {

                /*
                 * Encerra qualquer assinatura atual
                 * que ainda esteja liberada.
                 */

                Assinatura::query()
                    ->where(
                        'user_id',
                        $dados['user_id']
                    )
                    ->whereIn(
                        'situacao',
                        [
                            'teste',
                            'ativa',
                        ]
                    )
                    ->update([
                        'situacao' =>
                            'cancelada',
                    ]);


                /*
                 * Cria o novo período de teste.
                 */

                Assinatura::create([

                    'user_id' =>
                        $dados['user_id'],

                    'plano_id' =>
                        null,

                    'data_inicio' =>
                        $inicio,

                    'data_vencimento' =>
                        $vencimento,

                    'situacao' =>
                        'teste',

                    'renovacao_automatica' =>
                        false,

                    'gateway' =>
                        null,

                    'referencia_externa' =>
                        null,
                ]);
            }
        );


        return redirect()
            ->route(
                'admin.assinaturas.index'
            )
            ->with(
                'success',
                'Período de teste liberado com sucesso.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | ATIVAR PLANO PAGO
    |--------------------------------------------------------------------------
    */

    public function ativarPlano(
        Request $request
    ) {
        $dados = $request->validate([

            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],

            'plano_id' => [
                'required',
                'integer',
                'exists:planos,id',
            ],

            'data_inicio' => [
                'nullable',
                'date',
            ],
        ]);


        $plano = Plano::query()
            ->where(
                'id',
                $dados['plano_id']
            )
            ->where(
                'ativo',
                true
            )
            ->firstOrFail();


        $inicio =
            !empty(
                $dados['data_inicio']
            )
                ? Carbon::parse(
                    $dados['data_inicio']
                )->startOfDay()
                : Carbon::today();


        $vencimento =
            $inicio
                ->copy()
                ->addMonths(
                    $plano->duracao_meses
                );


        DB::transaction(
            function () use (
                $dados,
                $plano,
                $inicio,
                $vencimento
            ) {

                /*
                 * Encerra assinatura anterior.
                 */

                Assinatura::query()
                    ->where(
                        'user_id',
                        $dados['user_id']
                    )
                    ->whereIn(
                        'situacao',
                        [
                            'teste',
                            'ativa',
                        ]
                    )
                    ->update([
                        'situacao' =>
                            'cancelada',
                    ]);


                /*
                 * Cria nova assinatura paga.
                 */

                Assinatura::create([

                    'user_id' =>
                        $dados['user_id'],

                    'plano_id' =>
                        $plano->id,

                    'data_inicio' =>
                        $inicio,

                    'data_vencimento' =>
                        $vencimento,

                    'situacao' =>
                        'ativa',

                    'renovacao_automatica' =>
                        false,

                    'gateway' =>
                        null,

                    'referencia_externa' =>
                        null,
                ]);
            }
        );


        return redirect()
            ->route(
                'admin.assinaturas.index'
            )
            ->with(
                'success',
                'Plano ativado com sucesso.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | RENOVAR
    |--------------------------------------------------------------------------
    */

    public function renovar(
        Request $request,
        Assinatura $assinatura
    ) {
        $dados = $request->validate([

            'plano_id' => [
                'nullable',
                'integer',
                'exists:planos,id',
            ],

            'dias' => [
                'nullable',
                'integer',
                'min:1',
                'max:3650',
            ],

            'data_vencimento' => [
                'nullable',
                'date',
                'after_or_equal:today',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | RENOVAÇÃO DE TESTE
        |--------------------------------------------------------------------------
        */

        if (
            $assinatura->situacao === 'teste'
            &&
            !empty($dados['dias'])
        ) {

            $base =
                $assinatura->data_vencimento
                && $assinatura
                    ->data_vencimento
                    ->isFuture()
                    ? $assinatura
                        ->data_vencimento
                        ->copy()
                    : Carbon::today();


            $assinatura->update([

                'data_vencimento' =>
                    $base->addDays(
                        (int) $dados['dias']
                    ),

                'situacao' =>
                    'teste',
            ]);


            return back()->with(
                'success',
                'Período de teste renovado com sucesso.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | DATA PERSONALIZADA
        |--------------------------------------------------------------------------
        */

        if (
            !empty(
                $dados['data_vencimento']
            )
        ) {

            $assinatura->update([

                'data_vencimento' =>
                    Carbon::parse(
                        $dados['data_vencimento']
                    ),

                'situacao' =>
                    $assinatura->plano_id
                        ? 'ativa'
                        : 'teste',
            ]);


            return back()->with(
                'success',
                'Assinatura renovada com sucesso.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | RENOVAÇÃO POR PLANO
        |--------------------------------------------------------------------------
        */

        $planoId =
            $dados['plano_id']
            ?? $assinatura->plano_id;


        if (!$planoId) {

            return back()->withErrors([
                'plano_id' =>
                    'Selecione um plano ou informe o período da renovação.',
            ]);
        }


        $plano = Plano::query()
            ->where(
                'id',
                $planoId
            )
            ->where(
                'ativo',
                true
            )
            ->firstOrFail();


        $base =
            $assinatura->data_vencimento
            && $assinatura
                ->data_vencimento
                ->isFuture()
                ? $assinatura
                    ->data_vencimento
                    ->copy()
                : Carbon::today();


        $assinatura->update([

            'plano_id' =>
                $plano->id,

            'data_vencimento' =>
                $base->addMonths(
                    $plano->duracao_meses
                ),

            'situacao' =>
                'ativa',
        ]);


        return back()->with(
            'success',
            'Assinatura renovada com sucesso.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SUSPENDER
    |--------------------------------------------------------------------------
    */

    public function suspender(
        Assinatura $assinatura
    ) {
        if (
            in_array(
                $assinatura->situacao,
                [
                    'cancelada',
                    'suspensa',
                ],
                true
            )
        ) {
            return back()->with(
                'warning',
                'Essa assinatura já está bloqueada.'
            );
        }


        $assinatura->update([
            'situacao' =>
                'suspensa',
        ]);


        return back()->with(
            'success',
            'Assinatura suspensa com sucesso.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CANCELAR
    |--------------------------------------------------------------------------
    */

    public function cancelar(
        Assinatura $assinatura
    ) {
        if (
            $assinatura->situacao
            === 'cancelada'
        ) {
            return back()->with(
                'warning',
                'Essa assinatura já está cancelada.'
            );
        }


        $assinatura->update([
            'situacao' =>
                'cancelada',
        ]);


        return back()->with(
            'success',
            'Assinatura cancelada com sucesso.'
        );
    }
}