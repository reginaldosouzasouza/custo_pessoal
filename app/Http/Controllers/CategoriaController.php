<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoriaController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $categoriasReceita = Categoria::query()
            ->where('user_id', $userId)
            ->where('tipo', 'receita')
            ->orderByDesc('ativa')
            ->orderBy('nome')
            ->get();

        $categoriasDespesa = Categoria::query()
            ->where('user_id', $userId)
            ->where('tipo', 'despesa')
            ->orderByDesc('ativa')
            ->orderBy('nome')
            ->get();

        return view(
            'categorias.index',
            compact(
                'categoriasReceita',
                'categoriasDespesa'
            )
        );
    }

    public function create()
    {
        return view('categorias.create');
    }

    public function store(Request $request)
    {
        $userId = auth()->id();

        $dados = $request->validate([
            'nome' => [
                'required',
                'string',
                'max:120',

                Rule::unique('categorias')
                    ->where(
                        fn ($query) =>
                            $query
                                ->where('user_id', $userId)
                                ->where(
                                    'tipo',
                                    $request->input('tipo')
                                )
                    ),
            ],

            'tipo' => [
                'required',
                'in:receita,despesa',
            ],

            'classificacao' => [
                'nullable',
                'in:essencial,nao_essencial',
            ],

            'icone' => [
                'nullable',
                'string',
                'max:100',
            ],
        ]);

        /*
         * Receita não possui classificação
         * essencial / não essencial.
         */
        if ($dados['tipo'] === 'receita') {
            $dados['classificacao'] = null;
        }

        $dados['user_id'] = $userId;
        $dados['ativa'] = true;
        $dados['padrao_sistema'] = false;

        Categoria::create($dados);

        return redirect()
            ->route('categorias.index')
            ->with(
                'success',
                'Categoria cadastrada com sucesso.'
            );
    }

    public function edit(Categoria $categoria)
    {
        $this->validarCategoriaUsuario($categoria);

        return view(
            'categorias.edit',
            compact('categoria')
        );
    }

    public function update(
        Request $request,
        Categoria $categoria
    ) {
        $this->validarCategoriaUsuario($categoria);

        $userId = auth()->id();

        $dados = $request->validate([
            'nome' => [
                'required',
                'string',
                'max:120',

                Rule::unique('categorias')
                    ->ignore($categoria->id)
                    ->where(
                        fn ($query) =>
                            $query
                                ->where('user_id', $userId)
                                ->where(
                                    'tipo',
                                    $request->input('tipo')
                                )
                    ),
            ],

            'tipo' => [
                'required',
                'in:receita,despesa',
            ],

            'classificacao' => [
                'nullable',
                'in:essencial,nao_essencial',
            ],

            'icone' => [
                'nullable',
                'string',
                'max:100',
            ],
        ]);

        if ($dados['tipo'] === 'receita') {
            $dados['classificacao'] = null;
        }

        $categoria->update($dados);

        return redirect()
            ->route('categorias.index')
            ->with(
                'success',
                'Categoria atualizada com sucesso.'
            );
    }

    public function alternarStatus(Categoria $categoria)
    {
        $this->validarCategoriaUsuario($categoria);

        $categoria->ativa = !$categoria->ativa;
        $categoria->save();

        return redirect()
            ->route('categorias.index')
            ->with(
                'success',
                $categoria->ativa
                    ? 'Categoria ativada com sucesso.'
                    : 'Categoria desativada com sucesso.'
            );
    }

    private function validarCategoriaUsuario(
        Categoria $categoria
    ): void {
        abort_unless(
            $categoria->user_id === auth()->id(),
            403
        );
    }
}
