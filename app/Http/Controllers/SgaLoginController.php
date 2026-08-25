<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Categoria;
use App\Models\FormaPagamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SgaLoginController extends Controller
{
    public function entrar(Request $request)
    {
        $dados = $request->validate([
            'user_id' => [
                'required',
                'integer',
            ],

            'nome' => [
                'required',
                'string',
                'max:255',
            ],

            'usuario' => [
                'nullable',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
            ],

            'expires' => [
                'required',
                'integer',
            ],

            'nonce' => [
                'required',
                'string',
                'max:100',
            ],

            'signature' => [
                'required',
                'string',
                'size:64',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | VALIDADE DO LINK
        |--------------------------------------------------------------------------
        */

        if ((int) $dados['expires'] < now()->timestamp) {
            abort(
                403,
                'O acesso ao SGA Finanças expirou.'
            );
        }

        if (
            (int) $dados['expires']
            > now()->addMinutes(2)->timestamp
        ) {
            abort(
                403,
                'Prazo de acesso inválido.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | CHAVE SSO
        |--------------------------------------------------------------------------
        */

        $secret = config(
            'services.custo_pessoal_sso.secret'
        );

        if (!$secret) {
            abort(
                500,
                'A chave de integração do SGA Finanças não foi configurada.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | MONTA O MESMO PAYLOAD DO SGA
        |--------------------------------------------------------------------------
        */

        $payload = implode('|', [
            (string) $dados['user_id'],
            (string) $dados['nome'],
            (string) ($dados['usuario'] ?? ''),
            (string) $dados['email'],
            (string) $dados['expires'],
            (string) $dados['nonce'],
        ]);

        $assinaturaEsperada = hash_hmac(
            'sha256',
            $payload,
            $secret
        );

        if (
            !hash_equals(
                $assinaturaEsperada,
                $dados['signature']
            )
        ) {
            abort(
                403,
                'Assinatura de acesso inválida.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | IMPEDE REUTILIZAÇÃO DO MESMO LINK
        |--------------------------------------------------------------------------
        */

        $chaveNonce = 'custo_pessoal_sso_nonce_'
            . hash(
                'sha256',
                $dados['nonce']
            );

        if (
            !Cache::add(
                $chaveNonce,
                true,
                now()->addMinutes(5)
            )
        ) {
            abort(
                403,
                'Este acesso já foi utilizado.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | LOCALIZA OU CRIA O USUÁRIO LOCAL
        |--------------------------------------------------------------------------
        */

        $usuario = User::firstOrNew([
            'sga_user_id' => (int) $dados['user_id'],
        ]);

        /*
         * O usuário não terá uma senha conhecida,
         * pois o acesso normal será realizado pelo SGA.
         */
        if (!$usuario->exists) {
            $usuario->password = Hash::make(
                Str::random(64)
            );

            $usuario->ativo = true;
        }

        /*
        |--------------------------------------------------------------------------
        | SINCRONIZA DADOS DO SGA
        |--------------------------------------------------------------------------
        */

        $usuario->name = trim(
            (string) $dados['nome']
        );

        $usuario->usuario = isset($dados['usuario'])
            ? trim((string) $dados['usuario'])
            : null;

        $usuario->email = strtolower(
            trim((string) $dados['email'])
        );

        $usuario->ultimo_acesso_sga = now();
        $usuario->ultimo_acesso_em = now();

        $usuario->save();

        /*
        |--------------------------------------------------------------------------
        | CADASTROS INICIAIS DO USUÁRIO
        |--------------------------------------------------------------------------
        */

        $this->prepararCadastrosIniciais($usuario);

        /*
        |--------------------------------------------------------------------------
        | BLOQUEIO LOCAL DO USUÁRIO
        |--------------------------------------------------------------------------
        */

        if (!$usuario->ativo) {
            abort(
                403,
                'Este usuário está bloqueado no SGA Finanças.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | LOGIN LOCAL
        |--------------------------------------------------------------------------
        */

        Auth::login($usuario);

        /*
         * Regenera a sessão para evitar fixação de sessão.
         */
        $request->session()->regenerate();

        /*
        |--------------------------------------------------------------------------
        | REDIRECIONAMENTO
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('dashboard')
            ->with(
                'success',
                'Acesso ao SGA Finanças realizado com sucesso.'
            );
    }

    private function prepararCadastrosIniciais(User $usuario): void
    {
        /*
        |--------------------------------------------------------------------------
        | CATEGORIAS PADRÃO POR USUÁRIO
        |--------------------------------------------------------------------------
        */

        $categorias = [
            [
                'nome' => 'Salário',
                'tipo' => 'receita',
                'classificacao' => null,
            ],
            [
                'nome' => 'Renda Extra',
                'tipo' => 'receita',
                'classificacao' => null,
            ],
            [
                'nome' => 'Investimentos',
                'tipo' => 'receita',
                'classificacao' => null,
            ],
            [
                'nome' => 'Outros',
                'tipo' => 'receita',
                'classificacao' => null,
            ],

            [
                'nome' => 'Alimentação',
                'tipo' => 'despesa',
                'classificacao' => 'essencial',
            ],
            [
                'nome' => 'Moradia',
                'tipo' => 'despesa',
                'classificacao' => 'essencial',
            ],
            [
                'nome' => 'Água',
                'tipo' => 'despesa',
                'classificacao' => 'essencial',
            ],
            [
                'nome' => 'Energia',
                'tipo' => 'despesa',
                'classificacao' => 'essencial',
            ],
            [
                'nome' => 'Internet',
                'tipo' => 'despesa',
                'classificacao' => 'essencial',
            ],
            [
                'nome' => 'Saúde',
                'tipo' => 'despesa',
                'classificacao' => 'essencial',
            ],
            [
                'nome' => 'Transporte',
                'tipo' => 'despesa',
                'classificacao' => 'essencial',
            ],
            [
                'nome' => 'Educação',
                'tipo' => 'despesa',
                'classificacao' => 'essencial',
            ],

            [
                'nome' => 'Lazer',
                'tipo' => 'despesa',
                'classificacao' => 'nao_essencial',
            ],
            [
                'nome' => 'Compras',
                'tipo' => 'despesa',
                'classificacao' => 'nao_essencial',
            ],
            [
                'nome' => 'Assinaturas',
                'tipo' => 'despesa',
                'classificacao' => 'nao_essencial',
            ],
            [
                'nome' => 'Outros',
                'tipo' => 'despesa',
                'classificacao' => 'nao_essencial',
            ],
        ];

        foreach ($categorias as $categoria) {
            Categoria::firstOrCreate(
                [
                    'user_id' => $usuario->id,
                    'nome' => $categoria['nome'],
                    'tipo' => $categoria['tipo'],
                ],
                [
                    'classificacao' => $categoria['classificacao'],
                    'ativa' => true,
                    'padrao_sistema' => true,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FORMAS DE PAGAMENTO GLOBAIS
        |--------------------------------------------------------------------------
        */

        $formasPagamento = [
            [
                'nome' => 'Dinheiro',
                'codigo' => 'dinheiro',
                'tipo' => 'ambos',
            ],
            [
                'nome' => 'PIX',
                'codigo' => 'pix',
                'tipo' => 'ambos',
            ],
            [
                'nome' => 'Cartão de Débito',
                'codigo' => 'debito',
                'tipo' => 'ambos',
            ],
            [
                'nome' => 'Cartão de Crédito',
                'codigo' => 'credito',
                'tipo' => 'ambos',
            ],
            [
                'nome' => 'Boleto',
                'codigo' => 'boleto',
                'tipo' => 'ambos',
            ],
            [
                'nome' => 'Transferência',
                'codigo' => 'transferencia',
                'tipo' => 'ambos',
            ],
        ];

        foreach ($formasPagamento as $forma) {
            FormaPagamento::firstOrCreate(
                [
                    'codigo' => $forma['codigo'],
                ],
                [
                    'nome' => $forma['nome'],
                    'tipo' => $forma['tipo'],
                    'ativa' => true,
                ]
            );
        }
    }
}