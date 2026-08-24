<?php

namespace App\Http\Controllers;

use App\Models\Conta;
use App\Models\MovimentacaoConta;
use Illuminate\Http\Request;


class ContaController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $contas = Conta::query()
            ->where('user_id', $userId)
            ->orderByDesc('ativa')
            ->orderBy('nome')
            ->get()
            ->map(function ($conta) use ($userId) {

                $entradas = MovimentacaoConta::query()
                    ->where('user_id', $userId)
                    ->where('conta_id', $conta->id)
                    ->where('tipo', 'entrada')
                    ->sum('valor');

                $saidas = MovimentacaoConta::query()
                    ->where('user_id', $userId)
                    ->where('conta_id', $conta->id)
                    ->where('tipo', 'saida')
                    ->sum('valor');

                $conta->saldo_atual =
                    (float) $conta->saldo_inicial
                    + (float) $entradas
                    - (float) $saidas;

                return $conta;
            });

        return view(
            'contas.index',
            compact('contas')
        );
    }

    public function create()
    {
        return view('contas.create');
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'nome' => [
                'required',
                'string',
                'max:120',
            ],

            'tipo' => [
                'required',
                'in:conta_corrente,conta_digital,poupanca,dinheiro,carteira,outro',
            ],

            'instituicao' => [
                'nullable',
                'string',
                'max:150',
            ],

            'saldo_inicial' => [
                'nullable',
                'numeric',
            ],

            'data_saldo_inicial' => [
                'nullable',
                'date',
            ],

            'observacao' => [
                'nullable',
                'string',
            ],
        ]);

        $dados['user_id'] = auth()->id();
        $dados['saldo_inicial'] =
            $dados['saldo_inicial'] ?? 0;

        $dados['ativa'] = true;

        Conta::create($dados);

        return redirect()
            ->route('contas.index')
            ->with(
                'success',
                'Conta cadastrada com sucesso.'
            );
    }

    public function edit(Conta $conta)
    {
        $this->validarContaUsuario($conta);

        return view(
            'contas.edit',
            compact('conta')
        );
    }

    public function update(
        Request $request,
        Conta $conta
    ) {
        $this->validarContaUsuario($conta);

        $dados = $request->validate([
            'nome' => [
                'required',
                'string',
                'max:120',
            ],

            'tipo' => [
                'required',
                'in:conta_corrente,conta_digital,poupanca,dinheiro,carteira,outro',
            ],

            'instituicao' => [
                'nullable',
                'string',
                'max:150',
            ],

            'observacao' => [
                'nullable',
                'string',
            ],
        ]);

        /*
         * O saldo inicial não é alterado aqui.
         * Depois que a conta existe, correção de saldo
         * será feita por Ajuste de Saldo.
         */
        $conta->update($dados);

        return redirect()
            ->route('contas.index')
            ->with(
                'success',
                'Conta atualizada com sucesso.'
            );
    }

    public function alternarStatus(Conta $conta)
    {
        $this->validarContaUsuario($conta);

        $conta->ativa = !$conta->ativa;
        $conta->save();

        return redirect()
            ->route('contas.index')
            ->with(
                'success',
                $conta->ativa
                    ? 'Conta ativada com sucesso.'
                    : 'Conta desativada com sucesso.'
            );
    }

    private function validarContaUsuario(
        Conta $conta
    ): void {
        abort_unless(
            $conta->user_id === auth()->id(),
            403
        );
    }
}