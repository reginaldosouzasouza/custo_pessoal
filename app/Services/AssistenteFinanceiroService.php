<?php

namespace App\Services;

use App\Models\Categoria;
use App\Models\Cartao;
use App\Models\Conta;
use App\Models\Despesa;
use App\Models\Fatura;
use App\Models\FormaPagamento;
use App\Models\MovimentacaoConta;
use App\Models\Parcela;
use App\Models\Receita;
use App\Models\Recorrencia;
use Illuminate\Support\Str;

class AssistenteFinanceiroService
{
    public function __construct(
        private ManualFinanceiroService $manual,
        private LancamentoDespesaService $lancamentoDespesaService,
        private LancamentoCompraCartaoService $lancamentoCompraCartaoService
    ) {
    }

    private array $intencoes = [
        'agradecimento' => [
            'obrigado',
            'obrigada',
            'muito obrigado',
            'muito obrigada',
            'valeu',
            'agradecido',
            'agradecida',
            'obrigado pela ajuda',
            'obrigada pela ajuda',
        ],

        'saudacao' => [
            'bom dia',
            'boa tarde',
            'boa noite',
            'ola',
            'oi',
            'tudo bem',
        ],

        'saldo_atual' => [
            'qual e o meu saldo hoje',
            'qual meu saldo hoje',
            'qual e meu saldo hoje',
            'quanto tenho de saldo hoje',
            'quanto eu tenho de saldo hoje',
            'qual e o meu saldo atual',
            'qual meu saldo atual',
            'quanto tenho disponível',
            'quanto eu tenho disponível',
            'quanto tenho nas contas',
            'quanto tenho nas carteiras',
        ],

        'cartao_mes' => [
            'quanto tenho de cartao para pagar esse mes',
            'quanto tenho de cartao para pagar este mes',
            'quanto eu tenho de cartao para pagar esse mes',
            'quanto eu tenho de cartao para pagar este mes',
            'quanto tenho de fatura para pagar esse mes',
            'quanto tenho de fatura para pagar este mes',
            'quanto devo de cartao esse mes',
            'quanto devo de cartao este mes',
            'quanto vai dar meu cartao esse mes',
            'quanto vai dar meu cartao este mes',
            'fatura do cartao esse mes',
            'fatura do cartao este mes',
            'quanto de cartao vence esse mes',
            'quanto de cartao vence este mes',
        ],

        'vencimentos_hoje' => [
            'o que tenho que pagar hoje',
            'o que eu tenho que pagar hoje',
            'o que devo pagar hoje',
            'o que eu devo pagar hoje',
            'o que vence hoje',
            'quanto vence hoje',
            'contas de hoje',
            'vencimentos de hoje',
            'o que tenho para pagar hoje',
            'o que eu tenho para pagar hoje',
        ],

        'receitas_mes' => [
            'quanto tenho de receita',
            'quanto eu tenho de receita',
            'quanto tenho de receitas',
            'quanto recebi este mes',
            'quanto eu recebi este mes',
            'receitas do mes',
            'receita do mes',
            'total de receitas',
            'quanto entrou este mes',
            'quanto entrou no mes',
        ],

        'receitas_hoje' => [
            'quanto recebi hoje',
            'quanto eu recebi hoje',
            'receita de hoje',
            'receitas de hoje',
            'quanto entrou hoje',
        ],

        'gastos_mes' => [
            'quanto gastei este mes',
            'quanto eu gastei este mes',
            'gastos do mes',
            'gasto do mes',
            'meus gastos deste mes',
            'meus gastos desse mes',
            'quanto saiu este mes',
            'quanto saiu esse mes',
            'quanto paguei este mes',
            'quanto eu paguei este mes',
            'total gasto no mes',
        ],

        'gastos_hoje' => [
            'quanto paguei hoje',
            'quanto eu paguei hoje',
            'quanto gastei hoje',
            'quanto eu gastei hoje',
            'gastos de hoje',
            'gasto de hoje',
            'qual foi meu gasto de hoje',
            'quanto saiu hoje',
        ],

        'pendentes' => [
            'quanto tenho pendente',
            'quanto eu tenho pendente',
            'quanto tenho a pagar',
            'quanto eu tenho a pagar',
            'quanto devo',
            'quanto eu devo',
            'total pendente',
            'despesas pendentes',
            'contas pendentes',
        ],

        'atrasadas' => [
            'tenho despesas atrasadas',
            'tenho contas atrasadas',
            'despesas vencidas',
            'contas vencidas',
            'o que esta atrasado',
            'o que tenho atrasado',
            'quanto tenho atrasado',
        ],

        'proximos_vencimentos' => [
            'quais sao os proximos vencimentos',
            'proximos vencimentos',
            'proximas contas',
            'o que vence nos proximos 7 dias',
            'o que tenho para pagar nos proximos 7 dias',
            'quanto vence nos proximos 7 dias',
        ],

        'maior_categoria' => [
            'qual categoria teve o maior gasto',
            'qual categoria mais gastei',
            'onde estou gastando mais',
            'onde gastei mais',
            'maior categoria de gasto',
            'categoria com maior gasto',
        ],
    ];

    public function responder(int $userId, string $pergunta): string
    {
        $perguntaOriginal =
            trim($pergunta);

        $texto =
            $this->normalizar(
                $perguntaOriginal
            );

        /*
        |--------------------------------------------------------------------------
        | LIMPAR CONTEXTO TEMPORÁRIO DO ASSISTENTE
        |--------------------------------------------------------------------------
        |
        | Este comando limpa apenas os rascunhos e estados temporários
        | guardados na sessão do assistente. Nenhum lançamento já gravado
        | no banco é excluído.
        |
        | Exemplos:
        | limpar
        | limpar conversa
        | nova conversa
        | recomecar
        |
        */

        if (
            in_array(
                $texto,
                [
                    'limpar',
                    'limpar conversa',
                    'nova conversa',
                    'recomecar',
                ],
                true
            )
        ) {

            session()->forget([
                'assistente_lancamento_despesa',
                'assistente_compra_cartao',
                'assistente_compra_cartao_incompleta',
                'assistente_despesa_pendente_incompleta',
            ]);

            return
                'Conversa financeira limpa. '
                . 'Pode começar uma nova pergunta ou lançamento.';
        }

        /*
        |--------------------------------------------------------------------------
        | CONTINUAÇÃO DE COMPRA NO CARTÃO
        |--------------------------------------------------------------------------
        |
        | Quando o usuário informou uma compra no crédito sem dizer qual
        | cartão utilizou, guardamos o rascunho e aguardamos somente o nome
        | do cartão. A resposta pode ser simplesmente "Nubank", "Inter", etc.
        |
        */

        $compraCartaoIncompleta =
            session(
                'assistente_compra_cartao_incompleta'
            );

        if (
            is_array(
                $compraCartaoIncompleta
            )
            && (int) (
                $compraCartaoIncompleta['user_id']
                ?? 0
            ) === $userId
        ) {

            /*
             * Se o usuário desistiu do rascunho anterior e começou
             * um novo lançamento, descartamos a pergunta de cartão
             * pendente e processamos normalmente a nova frase.
             */
            $iniciouNovoLancamento =
                preg_match(
                    '/^(?:hoje\s+|ontem\s+)?(?:eu\s+)?'
                    . '(?:paguei|gastei|comprei|abasteci|abastecer|abastecimento|tenho\s+que\s+pagar|preciso\s+pagar|vou\s+pagar)\b/iu',
                    $perguntaOriginal
                ) === 1;

            if ($iniciouNovoLancamento) {

                session()->forget(
                    'assistente_compra_cartao_incompleta'
                );

                $compraCartaoIncompleta =
                    null;
            }
        }

        if (
            is_array(
                $compraCartaoIncompleta
            )
            && (int) (
                $compraCartaoIncompleta['user_id']
                ?? 0
            ) === $userId
        ) {

            $cartaoResposta =
                $this->resolverCartaoPorResposta(
                    $userId,
                    $perguntaOriginal
                );

            if ($cartaoResposta) {

                session()->forget(
                    'assistente_compra_cartao_incompleta'
                );

                $compraCartaoIncompleta['cartao_id'] =
                    $cartaoResposta->id;

                $compraCartaoIncompleta['cartao_nome'] =
                    $cartaoResposta->nome;

                return
                    $this->montarPreviaCompraCartao(
                        $compraCartaoIncompleta
                    );
            }

            /*
             * Enquanto o rascunho estiver aguardando cartão, uma resposta
             * que não corresponde a cartão não será interpretada como outra
             * intenção por engano.
             */
            if (
                !in_array(
                    $texto,
                    [
                        'cancelar lancamento',
                        'cancelar compra',
                    ],
                    true
                )
            ) {

                $cartoes =
                    $this->listarCartoesAtivos(
                        $userId
                    );

                return
                    "Não consegui identificar qual cartão você usou.\n\n"
                    . "Cartões ativos:\n"
                    . $cartoes
                    . "\n\n"
                    . "Responda apenas com o nome do cartão, por exemplo: Nubank.";
            }
        }

        /*
        |--------------------------------------------------------------------------
        | CONFIRMAÇÃO / CANCELAMENTO DE LANÇAMENTO
        |--------------------------------------------------------------------------
        |
        | A confirmação precisa ser explícita.
        | Não usamos apenas "sim" para evitar confirmação acidental.
        |
        */

        if (
            in_array(
                $texto,
                [
                    'confirmar lancamento',
                    'confirmar despesa',
                ],
                true
            )
        ) {
            return $this->confirmarLancamentoPendente(
                $userId
            );
        }

        if (
            in_array(
                $texto,
                [
                    'cancelar lancamento',
                    'cancelar despesa',
                    'cancelar compra',
                ],
                true
            )
        ) {

            session()->forget([
                'assistente_lancamento_despesa',
                'assistente_compra_cartao',
                'assistente_compra_cartao_incompleta',
            ]);

            return
                'Lançamento cancelado. Nenhuma despesa ou compra foi gravada.';
        }


        /*
        |--------------------------------------------------------------------------
        | MODO MANUAL
        |--------------------------------------------------------------------------
        |
        | Na V1 o usuário chama o manual explicitamente.
        | Exemplos:
        | Manual como cadastrar uma despesa
        | Manual: como funciona a previsão
        | Manual - como pagar uma fatura
        |
        | Sem o prefixo "Manual", a pergunta segue para o
        | assistente financeiro normalmente.
        |
        */

        if (
            preg_match(
                '/^manual\b/i',
                $perguntaOriginal
            )
        ) {

            $perguntaManual =
                trim(
                    preg_replace(
                        '/^manual\s*[:\-]?\s*/i',
                        '',
                        $perguntaOriginal
                    )
                );

            if ($perguntaManual === '') {
                return
                    $this->manual
                        ->ajudaInicial();
            }

            return
                $this->manual
                    ->responderDireto(
                        $perguntaManual
                    );
        }

        /*
        |--------------------------------------------------------------------------
        | PRÉVIA DE LANÇAMENTO AUTOMÁTICO
        |--------------------------------------------------------------------------
        |
        | Fase 2:
        | O Assistente interpreta a despesa, consulta os cadastros reais
        | do usuário e devolve uma prévia para conferência.
        |
        | IMPORTANTE:
        | Nenhum lançamento é gravado no banco nesta etapa.
        |
        */

        /*
        |--------------------------------------------------------------------------
        | DESPESA PENDENTE INCOMPLETA — FALTA VALOR
        |--------------------------------------------------------------------------
        |
        | Exemplo:
        | "tenho que pagar mercado dia 10/09"
        |
        | Guardamos descrição + vencimento na sessão e perguntamos
        | apenas o valor. A resposta pode ser simplesmente "250".
        |
        */

        $despesaPendenteSemValor =
            $this->extrairDespesaPendenteSemValor(
                $perguntaOriginal
            );

        if (
            $despesaPendenteSemValor
            !== null
        ) {

            session([
                'assistente_despesa_pendente_incompleta' =>
                    [
                        'user_id' =>
                            $userId,

                        'descricao' =>
                            $despesaPendenteSemValor['descricao'],

                        'data' =>
                            $despesaPendenteSemValor['data'],

                        'data_vencimento' =>
                            $despesaPendenteSemValor['data_vencimento'],

                        'data_descricao' =>
                            $despesaPendenteSemValor['data_descricao'],
                    ],
            ]);

            return
                "Entendi que você tem uma despesa pendente de "
                . $despesaPendenteSemValor['descricao']
                . " com vencimento em "
                . $despesaPendenteSemValor['data_descricao']
                . ".\n\n"
                . "Qual é o valor?";
        }

        /*
         * Se existe uma despesa pendente incompleta aguardando apenas
         * o valor, aceitamos uma resposta simples como "250" ou "R$ 250,00".
         */
        $despesaPendenteIncompleta =
            session(
                'assistente_despesa_pendente_incompleta'
            );

        if (
            is_array(
                $despesaPendenteIncompleta
            )
            && (int) (
                $despesaPendenteIncompleta['user_id']
                ?? 0
            ) === $userId
        ) {

            $valorComplemento =
                $this->extrairValorIsolado(
                    $perguntaOriginal
                );

            if (
                $valorComplemento
                !== null
            ) {

                session()->forget(
                    'assistente_despesa_pendente_incompleta'
                );

                $dadosComplementados = [
                    'tipo' =>
                        'despesa',

                    'descricao' =>
                        $despesaPendenteIncompleta['descricao'],

                    'valor' =>
                        $valorComplemento,

                    'data' =>
                        $despesaPendenteIncompleta['data'],

                    'data_vencimento' =>
                        $despesaPendenteIncompleta['data_vencimento'],

                    'data_descricao' =>
                        $despesaPendenteIncompleta['data_descricao'],

                    'categoria_sugerida' =>
                        $this->sugerirCategoriaLancamento(
                            $despesaPendenteIncompleta['descricao']
                        ),

                    'situacao' =>
                        'pendente',
                ];

                $dadosComplementados =
                    $this->prepararDespesaPendente(
                        $userId,
                        $dadosComplementados
                    );

                return
                    $this->montarPreviaDespesaPendente(
                        $dadosComplementados
                    );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | DESPESA PENDENTE POR LINGUAGEM NATURAL
        |--------------------------------------------------------------------------
        |
        | Exemplos:
        | Tenho que pagar 180 de internet dia 10
        | Preciso pagar 95 de água amanhã
        | Vou pagar 250 de aluguel dia 15
        |
        */

        $despesaPendente =
            $this->extrairDespesaPendente(
                $perguntaOriginal
            );

        if ($despesaPendente !== null) {

            session()->forget(
                'assistente_despesa_pendente_incompleta'
            );

            $despesaPendente =
                $this->prepararDespesaPendente(
                    $userId,
                    $despesaPendente
                );

            return
                $this->montarPreviaDespesaPendente(
                    $despesaPendente
                );
        }


        $lancamentoDespesa =
            $this->extrairLancamentoDespesa(
                $perguntaOriginal
            );

        if ($lancamentoDespesa !== null) {

            $lancamentoDespesa =
                $this->prepararLancamentoDespesa(
                    $userId,
                    $perguntaOriginal,
                    $lancamentoDespesa
                );

            return $this->montarPreviaLancamentoDespesa(
                $lancamentoDespesa
            );
        }


        $dataPerguntada =
            $this->extrairDataDaPergunta(
                $pergunta
            );

        if ($dataPerguntada !== null) {
            return $this->vencimentosNaData(
                $userId,
                $dataPerguntada
            );
        }

        $consultaEspecifica =
            $this->extrairConsultaEspecifica(
                $texto
            );

        if ($consultaEspecifica !== null) {
            return $this->consultarLancamentoEspecifico(
                $userId,
                $consultaEspecifica['termo'],
                $consultaEspecifica['tipo']
            );
        }

        $mesAno =
            $this->extrairMesAnoDaPergunta(
                $texto
            );

        if (
            $mesAno !== null
            && $this->perguntaSobrePagamentoOuGasto(
                $texto
            )
        ) {

            [$mes, $ano] =
                $mesAno;

            if (
                str_contains(
                    $texto,
                    'o que'
                )
                || str_contains(
                    $texto,
                    'quais'
                )
                || str_contains(
                    $texto,
                    'lista'
                )
                || str_contains(
                    $texto,
                    'listar'
                )
            ) {
                return $this->listarPagamentosMes(
                    $userId,
                    $mes,
                    $ano
                );
            }

            return $this->totalGastoMesEspecifico(
                $userId,
                $mes,
                $ano
            );
        }

        $intencao = $this->detectarIntencao($texto);

        if ($intencao !== null) {
            return $this->executarIntencao(
                $intencao,
                $userId
            );
        }

        $sugestoes = $this->sugerirFrases($texto);

        if ($sugestoes->isNotEmpty()) {
            $lista = $sugestoes
                ->map(function ($item) {
                    return '• ' . $item;
                })
                ->implode("\n");

            return
                "Não entendi exatamente sua pergunta.\n"
                . "Você quis dizer algo como:\n"
                . $lista;
        }

        return
            "Não consegui identificar sua pergunta ainda.\n"
            . "Você pode perguntar, por exemplo:\n"
            . "• Quanto gastei este mês?\n"
            . "• Quanto paguei hoje?\n"
            . "• Quanto tenho pendente?\n"
            . "• Tenho despesas atrasadas?\n"
            . "• Quais são os próximos vencimentos?\n"
            . "• Qual categoria teve o maior gasto?";
    }

    private function detectarIntencao(string $texto): ?string
    {
        /*
         * Primeiro tentamos correspondência exata/parcial.
         * Isso evita que uma frase sobre receita seja confundida
         * com uma intenção de despesa apenas por similaridade textual.
         */
        foreach ($this->intencoes as $intencao => $frases) {
            foreach ($frases as $frase) {
                if (
                    $texto === $frase
                    || str_contains($texto, $frase)
                    || str_contains($frase, $texto)
                ) {
                    return $intencao;
                }
            }
        }

        /*
         * Similaridade só é usada quando existe pelo menos
         * uma palavra relevante em comum.
         */
        $palavrasTexto = collect(
            explode(' ', $texto)
        )
            ->filter(fn ($palavra) => strlen($palavra) >= 4)
            ->values();

        $melhorIntencao = null;
        $melhorPercentual = 0;

        foreach ($this->intencoes as $intencao => $frases) {
            foreach ($frases as $frase) {

                $palavrasFrase = collect(
                    explode(' ', $frase)
                )
                    ->filter(fn ($palavra) => strlen($palavra) >= 4)
                    ->values();

                $temPalavraComum =
                    $palavrasTexto
                        ->intersect($palavrasFrase)
                        ->isNotEmpty();

                if (!$temPalavraComum) {
                    continue;
                }

                similar_text(
                    $texto,
                    $frase,
                    $percentual
                );

                if (
                    $percentual >= 78
                    && $percentual > $melhorPercentual
                ) {
                    $melhorPercentual = $percentual;
                    $melhorIntencao = $intencao;
                }
            }
        }

        return $melhorIntencao;
    }


    private function sugerirFrases(string $texto)
    {
        $resultados = collect();

        foreach ($this->intencoes as $intencao => $frases) {
            foreach ($frases as $frase) {
                similar_text(
                    $texto,
                    $frase,
                    $percentual
                );

                if ($percentual >= 38) {
                    $resultados->push([
                        'frase' => $frase,
                        'percentual' => $percentual,
                    ]);
                }
            }
        }

        return $resultados
            ->sortByDesc('percentual')
            ->unique('frase')
            ->take(3)
            ->pluck('frase')
            ->map(function ($frase) {
                return ucfirst($frase) . '?';
            })
            ->values();
    }

    private function executarIntencao(
        string $intencao,
        int $userId
    ): string {

        $nomeUsuario =
            trim(
                (string) (
                    auth()->user()->name
                    ?? auth()->user()->usuario
                    ?? 'Usuário'
                )
            );

        $primeiroNome =
            preg_split(
                '/\s+/',
                $nomeUsuario
            )[0]
            ?? 'Usuário';

        return match ($intencao) {
            'agradecimento' =>
                $this->responderAgradecimento(
                    $primeiroNome
                ),

            'saudacao' =>
                $this->responderSaudacao(
                    $primeiroNome
                ),

            'saldo_atual' =>
                $this->saldoAtual($userId),

            'cartao_mes' =>
                $this->totalCartaoMes($userId),

            'vencimentos_hoje' =>
                $this->vencimentosHoje($userId),

            'receitas_mes' =>
                $this->totalReceitasMes($userId),

            'receitas_hoje' =>
                $this->totalReceitasHoje($userId),

            'gastos_mes' =>
                $this->totalGastoMes($userId),

            'gastos_hoje' =>
                $this->totalGastoHoje($userId),

            'pendentes' =>
                $this->totalPendente($userId),

            'atrasadas' =>
                $this->despesasAtrasadas($userId),

            'proximos_vencimentos' =>
                $this->proximosVencimentos($userId),

            'maior_categoria' =>
                $this->maiorCategoriaMes($userId),

            default =>
                'Ainda não consigo responder essa pergunta.',
        };
    }

    private function responderAgradecimento(
        string $nome
    ): string {

        return
            'Disponha '
            . $nome
            . '! Estou à disposição para ajudar com suas finanças.';
    }

    private function responderSaudacao(
        string $nome
    ): string {$hora =
            (int) now()->format('H');

        if ($hora < 12) {

            return
                'Bom dia '
                . $nome
                . '! Como posso ajudar com suas finanças hoje?';
        }

        if ($hora < 18) {

            return
                'Boa tarde '
                . $nome
                . '! Como posso ajudar com suas finanças hoje?';
        }

        return
            'Boa noite '
            . $nome
            . '! Como posso ajudar com suas finanças hoje?';
    }


    private function saldoAtual(int $userId): string
    {
        $contas = Conta::query()
            ->where(
                'user_id',
                $userId
            )
            ->where(
                'ativa',
                true
            )
            ->orderBy('nome')
            ->get()
            ->map(
                function ($conta) use ($userId) {

                    $entradas =
                        (float) MovimentacaoConta::query()
                            ->where(
                                'user_id',
                                $userId
                            )
                            ->where(
                                'conta_id',
                                $conta->id
                            )
                            ->where(
                                'tipo',
                                'entrada'
                            )
                            ->where(
                                'estornada',
                                false
                            )
                            ->sum('valor');

                    $saidas =
                        (float) MovimentacaoConta::query()
                            ->where(
                                'user_id',
                                $userId
                            )
                            ->where(
                                'conta_id',
                                $conta->id
                            )
                            ->where(
                                'tipo',
                                'saida'
                            )
                            ->where(
                                'estornada',
                                false
                            )
                            ->sum('valor');

                    $conta->saldo_atual =
                        (float) $conta->saldo_inicial
                        + $entradas
                        - $saidas;

                    return $conta;
                }
            );

        if ($contas->isEmpty()) {
            return
                'Você não possui contas ou carteiras ativas cadastradas.';
        }

        $saldoAtual =
            (float) $contas
                ->sum('saldo_atual');

        $linhas =
            $contas
                ->map(
                    function ($conta) {

                        return
                            '• '
                            . $conta->nome
                            . ' — '
                            . $this->moeda(
                                (float) $conta->saldo_atual
                            );
                    }
                )
                ->implode("\n");

        return
            'Seu saldo atual é '
            . $this->moeda($saldoAtual)
            . ":\n"
            . $linhas;
    }


    private function extrairConsultaEspecifica(
        string $texto
    ): ?array {

        $padroes = [
            [
                'regex' =>
                    '/^(?:quando|que dia)\s+(?:vence|vencera)\s+(?:o|a|do|da)?\s*(.+)$/',

                'tipo' =>
                    'vencimento',
            ],

            [
                'regex' =>
                    '/^(?:quanto\s+(?:e|custa|fica)\s+(?:o|a)?\s*|qual\s+(?:e\s+)?(?:o\s+)?valor\s+(?:do|da|de)?\s*|quanto\s+devo\s+(?:do|da|de)\s+)(.+)$/',

                'tipo' =>
                    'valor',
            ],

            [
                'regex' =>
                    '/^(?:qual|quando e)\s+(?:a\s+)?proxima parcela\s+(?:do|da|de)?\s*(.+)$/',

                'tipo' =>
                    'proxima_parcela',
            ],
        ];

        foreach ($padroes as $padrao) {

            if (
                preg_match(
                    $padrao['regex'],
                    $texto,
                    $partes
                )
            ) {

                $termo =
                    trim(
                        $partes[1]
                    );

                $termosGenericos = [
                    'hoje',
                    'amanha',
                    'este mes',
                    'esse mes',
                    'mes',
                    'cartao',
                    'fatura',
                    'saldo',
                    'receita',
                    'receitas',
                    'pendente',
                    'pendentes',
                    'atrasado',
                    'atrasados',
                    'atrasada',
                    'atrasadas',
                ];

                if (
                    $termo === ''
                    || in_array(
                        $termo,
                        $termosGenericos,
                        true
                    )
                ) {
                    return null;
                }

                return [
                    'termo' =>
                        $termo,

                    'tipo' =>
                        $padrao['tipo'],
                ];
            }
        }

        return null;
    }

    private function consultarLancamentoEspecifico(
        int $userId,
        string $termo,
        string $tipo
    ): string {

        $itens =
            collect();

        $despesas =
            Despesa::query()
                ->where(
                    'user_id',
                    $userId
                )
                ->where(
                    'descricao',
                    'like',
                    '%' . $termo . '%'
                )
                ->orderBy(
                    'data_vencimento'
                )
                ->get();

        foreach ($despesas as $despesa) {

            $itens->push([
                'origem' =>
                    'despesa',

                'descricao' =>
                    $despesa->descricao,

                'valor' =>
                    (float) $despesa->valor,

                'vencimento' =>
                    $despesa->data_vencimento,

                'situacao' =>
                    $despesa->situacao,
            ]);
        }

        $parcelas =
            Parcela::query()
                ->with('parcelamento')
                ->where(
                    'user_id',
                    $userId
                )
                ->whereHas(
                    'parcelamento',
                    function ($query) use ($termo) {

                        $query->where(
                            'descricao',
                            'like',
                            '%' . $termo . '%'
                        );
                    }
                )
                ->orderBy(
                    'data_vencimento'
                )
                ->get();

        foreach ($parcelas as $parcela) {

            $descricao =
                $parcela
                    ->parcelamento
                    ?->descricao
                ?? 'Parcelamento';

            $descricao .=
                ' - '
                . $parcela->numero_parcela
                . '/'
                . $parcela->total_parcelas;

            $itens->push([
                'origem' =>
                    'parcela',

                'descricao' =>
                    $descricao,

                'valor' =>
                    (float) $parcela->valor,

                'vencimento' =>
                    $parcela->data_vencimento,

                'situacao' =>
                    $parcela->situacao,
            ]);
        }

        /*
         * CONTAS FIXAS / RECORRÊNCIAS
         *
         * A tela de Previsão de Despesas não usa apenas
         * despesas e parcelas. Itens como Prever e PUC Minas
         * podem existir somente em recorrencias e aparecer
         * como "Prevista".
         */
        $recorrencias =
            Recorrencia::query()
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
                ->where(
                    'descricao',
                    'like',
                    '%' . $termo . '%'
                )
                ->get();

        $inicioMes =
            now()
                ->copy()
                ->startOfMonth();

        $fimMes =
            now()
                ->copy()
                ->endOfMonth();

        foreach ($recorrencias as $recorrencia) {

            $vencimentos =
                $this->vencimentosRecorrenciaNoMes(
                    $recorrencia,
                    $inicioMes,
                    $fimMes
                );

            foreach ($vencimentos as $vencimento) {

                $itens->push([
                    'origem' =>
                        'recorrencia',

                    'descricao' =>
                        $recorrencia->descricao,

                    'valor' =>
                        (float) (
                            $recorrencia->valor_padrao
                            ?? 0
                        ),

                    'vencimento' =>
                        $vencimento,

                    'situacao' =>
                        'prevista',
                ]);
            }
        }


        if ($itens->isEmpty()) {

            return
                'Não encontrei nenhum lançamento relacionado a "'
                . $termo
                . '".';
        }

        /*
         * Para perguntas sobre próxima parcela ou vencimento,
         * priorizamos itens ainda pendentes e mais próximos.
         */
        if (
            in_array(
                $tipo,
                [
                    'vencimento',
                    'proxima_parcela',
                ],
                true
            )
        ) {

            $pendentes =
                $itens
                    ->filter(
                        function ($item) {

                            return
                                $item['situacao']
                                === 'pendente';
                        }
                    )
                    ->sortBy(
                        'vencimento'
                    )
                    ->values();

            if ($pendentes->isNotEmpty()) {
                $itens =
                    $pendentes;
            } else {
                $itens =
                    $itens
                        ->sortByDesc(
                            'vencimento'
                        )
                        ->values();
            }
        }

        if ($tipo === 'proxima_parcela') {

            $item =
                $itens->first();

            return
                'A próxima parcela relacionada a "'
                . $termo
                . '" é '
                . $item['descricao']
                . ', no valor de '
                . $this->moeda(
                    $item['valor']
                )
                . ', com vencimento em '
                . (
                    $item['vencimento']
                        ? $item['vencimento']
                            ->format('d/m/Y')
                        : '-'
                )
                . '.';
        }

        if ($tipo === 'vencimento') {

            if ($itens->count() === 1) {

                $item =
                    $itens->first();

                return
                    $item['descricao']
                    . ' vence em '
                    . (
                        $item['vencimento']
                            ? $item['vencimento']
                                ->format('d/m/Y')
                            : '-'
                    )
                    . ', no valor de '
                    . $this->moeda(
                        $item['valor']
                    )
                    . '.';
            }

            $linhas =
                $itens
                    ->take(6)
                    ->map(
                        function ($item) {

                            return
                                '• '
                                . $item['descricao']
                                . ' — '
                                . $this->moeda(
                                    $item['valor']
                                )
                                . ' — vence em '
                                . (
                                    $item['vencimento']
                                        ? $item['vencimento']
                                            ->format('d/m/Y')
                                        : '-'
                                );
                        }
                    )
                    ->implode("\n");

            $resposta =
                'Encontrei '
                . $itens->count()
                . ' lançamentos relacionados a "'
                . $termo
                . "\":\n"
                . $linhas;

            if ($itens->count() > 6) {

                $resposta .=
                    "\n• E mais ". ($itens->count() - 6)
                    . ' lançamento'
                    . (($itens->count() - 6) === 1 ? '' : 's')
                    . '.';
            }

            return $resposta;
        }

        /*
         * Pergunta de valor: somamos apenas pendentes quando houver,
         * pois normalmente "quanto é / quanto devo de X" significa
         * o valor ainda em aberto.
         */
        $pendentes =
            $itens
                ->filter(
                    function ($item) {

                        return
                            $item['situacao']
                            === 'pendente';
                    }
                )
                ->values();

        $base =
            $pendentes->isNotEmpty()
                ? $pendentes
                : $itens;

        $total =
            (float) $base
                ->sum('valor');

        if ($base->count() === 1) {

            $item =
                $base->first();

            return
                $item['descricao']
                . ' está no valor de '
                . $this->moeda(
                    $item['valor']
                )
                . ', com vencimento em '
                . (
                    $item['vencimento']
                        ? $item['vencimento']
                            ->format('d/m/Y')
                        : '-'
                )
                . '.';
        }

        return
            'Os lançamentos relacionados a "'
            . $termo
            . '" totalizam '
            . $this->moeda($total)
            . ' em '
            . $base->count()
            . ' lançamento'
            . ($base->count() === 1 ? '' : 's')
            . '.';
    }


    private function vencimentosRecorrenciaNoMes(
        Recorrencia $recorrencia,
        \Illuminate\Support\Carbon $inicioMes,
        \Illuminate\Support\Carbon $fimMes
    ) {
        $resultado =
            collect();

        $inicioRecorrencia =
            \Illuminate\Support\Carbon::parse(
                $recorrencia->data_inicio
            )->startOfDay();

        $fimRecorrencia =
            $recorrencia->data_fim
                ? \Illuminate\Support\Carbon::parse(
                    $recorrencia->data_fim
                )->endOfDay()
                : null;


        /*
         * Frequências baseadas em dias.
         * A contagem parte sempre da data_inicio e não
         * reinicia quando muda o mês.
         */
        $intervaloDias =
            match (
                $recorrencia->frequencia
            ) {
                'diaria' => 1,
                'cada_3_dias' => 3,
                'cada_5_dias' => 5,
                'semanal' => 7,
                default => null,
            };

        if ($intervaloDias !== null) {

            $data =
                $inicioRecorrencia
                    ->copy();

            if (
                $data->lt(
                    $inicioMes
                )
            ) {

                $diasDecorridos =
                    (int) $inicioRecorrencia
                        ->diffInDays(
                            $inicioMes
                        );

                $resto =
                    $diasDecorridos
                    % $intervaloDias;

                $data =
                    $inicioMes
                        ->copy();

                if ($resto !== 0) {

                    $data->addDays(
                        $intervaloDias
                        - $resto
                    );
                }
            }

            while (
                $data->lte(
                    $fimMes
                )
            ) {

                if (
                    !$fimRecorrencia
                    || $data->lte(
                        $fimRecorrencia
                    )
                ) {
                    $resultado->push(
                        $data->copy()
                    );
                }

                $data->addDays(
                    $intervaloDias
                );
            }

            return $resultado;
        }


        /*
         * Mensal / trimestral / semestral / anual.
         * Mesma regra usada na previsão/dashboard.
         */
        $intervaloMeses =
            match (
                $recorrencia->frequencia
            ) {
                'mensal' => 1,
                'trimestral' => 3,
                'semestral' => 6,
                'anual' => 12,
                default => null,
            };

        if (!$intervaloMeses) {
            return $resultado;
        }

        $mesInicio =
            $inicioRecorrencia
                ->copy()
                ->startOfMonth();

        $diferencaMeses =
            $mesInicio
                ->diffInMonths(
                    $inicioMes,
                    false
                );

        if ($diferencaMeses < 0) {
            return $resultado;
        }

        if (
            $diferencaMeses
            % $intervaloMeses
            !== 0
        ) {
            return $resultado;
        }

        $dia =
            (int) (
                $recorrencia->dia_vencimento
                ?: $inicioRecorrencia->day
            );

        $dia =
            min(
                $dia,
                $inicioMes->daysInMonth
            );

        $vencimento =
            $inicioMes
                ->copy()
                ->day($dia);

        if (
            $vencimento->lt(
                $inicioRecorrencia
            )
        ) {
            return $resultado;
        }

        if (
            $fimRecorrencia
            && $vencimento->gt(
                $fimRecorrencia
            )
        ) {
            return $resultado;
        }

        $resultado->push(
            $vencimento
        );

        return $resultado;
    }


    private function totalCartaoMes(int $userId): string
    {
        $competencia =
            now()->format('Y-m');

        $faturas = Fatura::query()
            ->with('cartao')
            ->where(
                'user_id',
                $userId
            )
            ->where(
                'competencia',
                $competencia
            )
            ->get();

        if ($faturas->isEmpty()) {
            return
                'Você não possui faturas de cartão para '
                . now()->format('m/Y')
                . '.';
        }

        $totalRestante = 0.0;
        $linhas = collect();

        foreach ($faturas as $fatura) {

            $restante = max(
                0,
                (float) $fatura->valor_total
                - (float) $fatura->valor_pago
            );

            if ($restante <= 0) {
                continue;
            }

            $totalRestante +=
                $restante;

            $nomeCartao =
                $fatura->cartao?->nome
                ?? 'Cartão';

            $vencimento =
                $fatura->data_vencimento
                    ? $fatura
                        ->data_vencimento
                        ->format('d/m/Y')
                    : '-';

            $linhas->push(
                '• '
                . $nomeCartao
                . ' — '
                . $this->moeda($restante)
                . ' — vence em '
                . $vencimento
            );
        }

        if ($linhas->isEmpty()) {
            return
                'As faturas de cartão de '
                . now()->format('m/Y')
                . ' já estão pagas.';
        }

        return
            'Neste mês, você tem '
            . $this->moeda($totalRestante)
            . " de cartão para pagar:\n"
            . $linhas->implode("\n");
    }


    private function vencimentosHoje(int $userId): string
    {
        return $this->vencimentosNaData(
            $userId,
            now()->startOfDay()
        );
    }

    private function vencimentosNaData(
        int $userId,
        \Illuminate\Support\Carbon $data
    ): string {

        $dataBanco =
            $data->toDateString();

        $itens = collect();

        $despesas = Despesa::query()
            ->with('categoria')
            ->where('user_id', $userId)
            ->where('situacao', 'pendente')
            ->whereDate(
                'data_vencimento',
                $dataBanco
            )
            ->get();

        foreach ($despesas as $despesa) {
            $itens->push([
                'descricao' =>
                    $despesa->descricao,

                'valor' =>
                    (float) $despesa->valor,
            ]);
        }

        $parcelas = Parcela::query()
            ->with('parcelamento')
            ->where('user_id', $userId)
            ->where('situacao', 'pendente')
            ->whereDate(
                'data_vencimento',
                $dataBanco
            )
            ->get();

        foreach ($parcelas as $parcela) {
            $descricao =
                $parcela->parcelamento?->descricao
                ?? 'Parcelamento';

            $descricao .=
                ' - '
                . $parcela->numero_parcela
                . '/'
                . $parcela->total_parcelas;

            $itens->push([
                'descricao' =>
                    $descricao,

                'valor' =>
                    (float) $parcela->valor,
            ]);
        }

        if ($itens->isEmpty()) {
            return
                'Você não possui pagamentos pendentes com vencimento em '
                . $data->format('d/m/Y')
                . '.';
        }

        $total =
            (float) $itens->sum('valor');

        $linhas =
            $itens
                ->take(6)
                ->map(
                    function ($item) {

                        return
                            '• '
                            . $item['descricao']
                            . ' — '
                            . $this->moeda(
                                $item['valor']
                            );
                    }
                )
                ->implode("\n");

        $resposta =
            'Em '
            . $data->format('d/m/Y')
            . ', você possui '
            . $itens->count()
            . ' pagamento'
            . ($itens->count() === 1 ? '' : 's')
            . ' pendente'
            . ($itens->count() === 1 ? '' : 's')
            . ', totalizando '
            . $this->moeda($total)
            . ":\n"
            . $linhas;

        if ($itens->count() > 6) {
            $resposta .=
                "\n• E mais "
                . ($itens->count() - 6)
                . ' lançamento'
                . (($itens->count() - 6) === 1 ? '' : 's')
                . '.';
        }

        return $resposta;
    }

    private function extrairDataDaPergunta(
        string $pergunta
    ): ?\Illuminate\Support\Carbon {

        if (
            !preg_match(
                '/\b(\d{1,2})\/(\d{1,2})(?:\/(\d{2,4}))?\b/',
                $pergunta,
                $partes
            )
        ) {
            return null;
        }

        $dia =
            (int) $partes[1];

        $mes =
            (int) $partes[2];

        $ano =
            !empty($partes[3])
                ? (int) $partes[3]
                : (int) now()->year;

        if ($ano < 100) {
            $ano += 2000;
        }

        if (
            !checkdate(
                $mes,
                $dia,
                $ano
            )
        ) {
            return null;
        }

        return \Illuminate\Support\Carbon::create(
            $ano,
            $mes,
            $dia
        )->startOfDay();
    }


    private function totalReceitasMes(int $userId): string
    {
        $inicio = now()
            ->startOfMonth()
            ->toDateString();

        $fim = now()
            ->endOfMonth()
            ->toDateString();

        $total = (float) Receita::query()
            ->where('user_id', $userId)
            ->where('situacao', 'recebida')
            ->whereBetween(
                'data_recebimento',
                [$inicio, $fim]
            )
            ->sum('valor');

        if ($total <= 0) {
            return
                'Neste mês, você ainda não possui receitas recebidas.';}

        return
            'Neste mês, você recebeu '
            . $this->moeda($total)
            . ' em receitas.';
    }

    private function totalReceitasHoje(int $userId): string
    {
        $hoje = now()->toDateString();

        $query = Receita::query()
            ->where('user_id', $userId)
            ->where('situacao', 'recebida')
            ->whereDate(
                'data_recebimento',
                $hoje
            );

        $quantidade =
            (clone $query)->count();

        $total =
            (float) (clone $query)
                ->sum('valor');

        if ($quantidade === 0) {
            return
                'Hoje você ainda não possui receitas recebidas.';
        }

        return
            'Hoje você recebeu '
            . $this->moeda($total)
            . ' em '
            . $quantidade
            . ' receita'
            . ($quantidade === 1 ? '' : 's')
            . '.';
    }


    private function totalGastoMesEspecifico(
        int $userId,
        int $mes,
        int $ano
    ): string {

        $inicio =
            \Illuminate\Support\Carbon::create(
                $ano,
                $mes,
                1
            )->startOfMonth();

        $fim =
            $inicio
                ->copy()
                ->endOfMonth();

        $despesas =
            (float) Despesa::query()
                ->where(
                    'user_id',
                    $userId
                )
                ->where(
                    'situacao',
                    'paga'
                )
                ->whereBetween(
                    'data_pagamento',
                    [
                        $inicio->toDateString(),
                        $fim->toDateString(),
                    ]
                )
                ->sum('valor');

        $parcelas =
            (float) Parcela::query()
                ->where(
                    'user_id',
                    $userId
                )
                ->where(
                    'situacao',
                    'paga'
                )
                ->whereBetween(
                    'data_pagamento',
                    [
                        $inicio->toDateString(),
                        $fim->toDateString(),
                    ]
                )
                ->sum('valor');

        $total =
            $despesas
            + $parcelas;

        if ($total <= 0) {
            return
                'Você não possui pagamentos registrados em '
                . str_pad(
                    (string) $mes,
                    2,
                    '0',
                    STR_PAD_LEFT
                )
                . '/'
                . $ano
                . '.';
        }

        return
            'Em '
            . str_pad(
                (string) $mes,
                2,
                '0',
                STR_PAD_LEFT
            )
            . '/'
            . $ano
            . ', você pagou '
            . $this->moeda($total)
            . ' em despesas e parcelas.';
    }

    private function listarPagamentosMes(
        int $userId,
        int $mes,
        int $ano
    ): string {

        $inicio =
            \Illuminate\Support\Carbon::create(
                $ano,
                $mes,
                1
            )->startOfMonth();

        $fim =
            $inicio
                ->copy()
                ->endOfMonth();

        $itens =
            collect();

        $despesas =
            Despesa::query()
                ->where(
                    'user_id',
                    $userId
                )
                ->where(
                    'situacao',
                    'paga'
                )
                ->whereBetween(
                    'data_pagamento',
                    [
                        $inicio->toDateString(),
                        $fim->toDateString(),
                    ]
                )
                ->orderBy(
                    'data_pagamento'
                )
                ->get();

        foreach ($despesas as $despesa) {

            $itens->push([
                'descricao' =>
                    $despesa->descricao,

                'valor' =>
                    (float) $despesa->valor,

                'data' =>
                    $despesa->data_pagamento,
            ]);
        }

        $parcelas =
            Parcela::query()
                ->with(
                    'parcelamento'
                )
                ->where(
                    'user_id',
                    $userId
                )
                ->where(
                    'situacao',
                    'paga'
                )
                ->whereBetween(
                    'data_pagamento',
                    [
                        $inicio->toDateString(),
                        $fim->toDateString(),
                    ]
                )
                ->orderBy(
                    'data_pagamento'
                )
                ->get();

        foreach ($parcelas as $parcela) {

            $descricao =
                $parcela
                    ->parcelamento
                    ?->descricao
                ?? 'Parcelamento';

            $descricao .=
                ' - '
                . $parcela->numero_parcela
                . '/'
                . $parcela->total_parcelas;

            $itens->push([
                'descricao' =>
                    $descricao,

                'valor' =>
                    (float) $parcela->valor,

                'data' =>
                    $parcela->data_pagamento,
            ]);
        }

        $itens =
            $itens
                ->sortBy('data')
                ->values();

        $mesFormatado =
            str_pad(
                (string) $mes,
                2,
                '0',
                STR_PAD_LEFT
            )
            . '/'
            . $ano;

        if ($itens->isEmpty()) {

            return
                'Você não possui pagamentos registrados em '
                . $mesFormatado
                . '.';
        }

        $total =
            (float) $itens
                ->sum('valor');

        $linhas =
            $itens
                ->take(10)
                ->map(
                    function ($item) {

                        $data =
                            $item['data']
                                ? $item['data']
                                    ->format('d/m/Y')
                                : '-';

                        return
                            '• '
                            . $item['descricao']
                            . ' — '
                            . $this->moeda(
                                $item['valor']
                            )
                            . ' — pago em '
                            . $data;
                    }
                )
                ->implode("\n");

        $resposta =
            'Em '
            . $mesFormatado
            . ', você possui '
            . $itens->count()
            . ' pagamento'
            . ($itens->count() === 1 ? '' : 's')
            . ', totalizando '
            . $this->moeda($total)
            . ":\n"
            . $linhas;

        if ($itens->count() > 10) {

            $resposta .=
                "\n• E mais "
                . ($itens->count() - 10)
                . ' lançamento'
                . (($itens->count() - 10) === 1 ? '' : 's')
                . '.';
        }

        return $resposta;
    }

    private function perguntaSobrePagamentoOuGasto(
        string $texto
    ): bool {

        $termos = [
            'paguei',
            'pago',
            'pagamentos',
            'pagamento',
            'gastei',
            'gasto',
            'gastos',
            'saiu',
        ];

        foreach ($termos as $termo) {

            if (
                str_contains(
                    $texto,
                    $termo
                )
            ) {
                return true;
            }
        }

        return false;
    }

    private function extrairMesAnoDaPergunta(
        string $texto
    ): ?array {

        $meses = [
            'janeiro' => 1,
            'fevereiro' => 2,
            'marco' => 3,
            'abril' => 4,
            'maio' => 5,
            'junho' => 6,
            'julho' => 7,
            'agosto' => 8,
            'setembro' => 9,
            'outubro' => 10,
            'novembro' => 11,
            'dezembro' => 12,
        ];

        $ano =
            (int) now()->year;

        if (
            preg_match(
                '/\b(20\d{2})\b/',
                $texto,
                $anoEncontrado
            )
        ) {
            $ano =
                (int) $anoEncontrado[1];
        }

        foreach ($meses as $nome => $numero) {

            if (
                preg_match(
                    '/\b'
                    . preg_quote(
                        $nome,
                        '/'
                    )
                    . '\b/',
                    $texto
                )
            ) {
                return [
                    $numero,
                    $ano,
                ];
            }
        }

        if (
            preg_match(
                '/\bmes\s+(?:de\s+)?(1[0-2]|0?[1-9])\b/',
                $texto,
                $mesEncontrado
            )
        ) {
            return [
                (int) $mesEncontrado[1],
                $ano,
            ];
        }

        if (
            preg_match(
                '/\b(0?[1-9]|1[0-2])\/(20\d{2})\b/',
                $texto,
                $mesAno
            )
        ) {
            return [
                (int) $mesAno[1],
                (int) $mesAno[2],
            ];
        }

        return null;
    }


    private function totalGastoMes(int $userId): string
    {
        $inicio = now()
            ->startOfMonth()
            ->toDateString();

        $fim = now()
            ->endOfMonth()
            ->toDateString();

        $despesas = (float) Despesa::query()
            ->where('user_id', $userId)
            ->where('situacao', 'paga')
            ->whereBetween(
                'data_pagamento',
                [$inicio, $fim]
            )
            ->sum('valor');

        $parcelas = (float) Parcela::query()
            ->where('user_id', $userId)
            ->where('situacao', 'paga')
            ->whereBetween(
                'data_pagamento',
                [$inicio, $fim]
            )
            ->sum('valor');

        return
            'Neste mês, você já pagou '
            . $this->moeda(
                $despesas + $parcelas
            )
            . ' em despesas e parcelas.';
    }

    private function totalGastoHoje(int $userId): string
    {
        $hoje = now()->toDateString();

        $despesas = Despesa::query()
            ->where('user_id', $userId)
            ->where('situacao', 'paga')
            ->whereDate(
                'data_pagamento',
                $hoje
            );

        $parcelas = Parcela::query()
            ->where('user_id', $userId)
            ->where('situacao', 'paga')
            ->whereDate(
                'data_pagamento',
                $hoje
            );

        $quantidade =
            (clone $despesas)->count()
            + (clone $parcelas)->count();

        $total =
            (float) (clone $despesas)
                ->sum('valor')
            + (float) (clone $parcelas)
                ->sum('valor');

        if ($quantidade === 0) {
            return
                'Você ainda não possui pagamentos registrados hoje.';
        }

        return
            'Hoje você pagou '
            . $this->moeda($total)
            . ' em '
            . $quantidade
            . ' lançamento'
            . ($quantidade === 1 ? '' : 's')
            . '.';
    }

    private function totalPendente(int $userId): string
    {
        $itens = collect();

        $despesas = Despesa::query()
            ->where('user_id', $userId)
            ->where('situacao', 'pendente')
            ->orderBy('data_vencimento')
            ->get();foreach ($despesas as $despesa) {
            $itens->push([
                'descricao' =>
                    $despesa->descricao,

                'valor' =>
                    (float) $despesa->valor,

                'vencimento' =>
                    $despesa->data_vencimento,
            ]);
        }

        $parcelas = Parcela::query()
            ->with('parcelamento')
            ->where('user_id', $userId)
            ->where('situacao', 'pendente')
            ->orderBy('data_vencimento')
            ->get();

        foreach ($parcelas as $parcela) {
            $descricao =
                $parcela->parcelamento?->descricao
                ?? 'Parcelamento';

            $descricao .=
                ' - '
                . $parcela->numero_parcela
                . '/'
                . $parcela->total_parcelas;

            $itens->push([
                'descricao' =>
                    $descricao,

                'valor' =>
                    (float) $parcela->valor,

                'vencimento' =>
                    $parcela->data_vencimento,
            ]);
        }

        $itens = $itens
            ->sortBy('vencimento')
            ->values();

        if ($itens->isEmpty()) {
            return
                'Você não possui despesas ou parcelas pendentes.';
        }

        $total =
            (float) $itens->sum('valor');

        $linhas =
            $itens
                ->take(8)
                ->map(
                    function ($item) {

                        $data =
                            $item['vencimento']
                                ? $item['vencimento']
                                    ->format('d/m/Y')
                                : '-';

                        return
                            '• '
                            . $item['descricao']
                            . ' — '
                            . $this->moeda(
                                $item['valor']
                            )
                            . ' — vence em '
                            . $data;
                    }
                )
                ->implode("\n");

        $resposta =
            'Você possui '
            . $itens->count()
            . ' lançamento'
            . ($itens->count() === 1 ? '' : 's')
            . ' pendente'
            . ($itens->count() === 1 ? '' : 's')
            . ', totalizando '
            . $this->moeda($total)
            . ":\n"
            . $linhas;

        if ($itens->count() > 8) {
            $resposta .=
                "\n• E mais "
                . ($itens->count() - 8)
                . ' lançamento'
                . (($itens->count() - 8) === 1 ? '' : 's')
                . '.';
        }

        return $resposta;
    }


    private function despesasAtrasadas(int $userId): string
    {
        $hoje =
            now()->toDateString();

        $itens = collect();

        $despesas = Despesa::query()
            ->where('user_id', $userId)
            ->where('situacao', 'pendente')
            ->whereDate(
                'data_vencimento',
                '<',
                $hoje
            )
            ->orderBy('data_vencimento')
            ->get();

        foreach ($despesas as $despesa) {
            $itens->push([
                'descricao' =>
                    $despesa->descricao,

                'valor' =>
                    (float) $despesa->valor,

                'vencimento' =>
                    $despesa->data_vencimento,
            ]);
        }

        $parcelas = Parcela::query()
            ->with('parcelamento')
            ->where('user_id', $userId)
            ->where('situacao', 'pendente')
            ->whereDate(
                'data_vencimento',
                '<',
                $hoje
            )
            ->orderBy('data_vencimento')
            ->get();

        foreach ($parcelas as $parcela) {
            $descricao =
                $parcela->parcelamento?->descricao
                ?? 'Parcelamento';

            $descricao .=
                ' - '
                . $parcela->numero_parcela
                . '/'
                . $parcela->total_parcelas;

            $itens->push([
                'descricao' =>
                    $descricao,

                'valor' =>
                    (float) $parcela->valor,

                'vencimento' =>
                    $parcela->data_vencimento,
            ]);
        }

        $itens = $itens
            ->sortBy('vencimento')
            ->values();

        if ($itens->isEmpty()) {
            return
                'Você não possui despesas atrasadas.';
        }

        $total =
            (float) $itens->sum('valor');

        $linhas =
            $itens
                ->take(8)
                ->map(
                    function ($item) {

                        $data =
                            $item['vencimento']
                                ? $item['vencimento']
                                    ->format('d/m/Y')
                                : '-';

                        return
                            '• '
                            . $item['descricao']
                            . ' — '
                            . $this->moeda(
                                $item['valor']
                            )
                            . ' — venceu em '
                            . $data;
                    }
                )
                ->implode("\n");

        $resposta =
            'Você possui '
            . $itens->count()
            . ' lançamento'
            . ($itens->count() === 1 ? '' : 's')
            . ' atrasado'
            . ($itens->count() === 1 ? '' : 's')
            . ', totalizando '
            . $this->moeda($total)
            . ":\n"
            . $linhas;

        if ($itens->count() > 8) {
            $resposta .=
                "\n• E mais "
                . ($itens->count() - 8)
                . ' lançamento'
                . (($itens->count() - 8) === 1 ? '' : 's')
                . '.';
        }

        return $resposta;
    }


    private function proximosVencimentos(int $userId): string
    {
        $inicio = now()
            ->toDateString();

        $fim = now()
            ->addDays(7)
            ->toDateString();

        $despesas = Despesa::query()
            ->where('user_id', $userId)
            ->where('situacao', 'pendente')
            ->whereBetween(
                'data_vencimento',
                [$inicio, $fim]
            );

        $parcelas = Parcela::query()
            ->where('user_id', $userId)
            ->where('situacao', 'pendente')
            ->whereBetween(
                'data_vencimento',
                [$inicio, $fim]
            );

        $quantidade =
            (clone $despesas)->count()
            + (clone $parcelas)->count();

        $total =
            (float) (clone $despesas)
                ->sum('valor')
            + (float) (clone $parcelas)
                ->sum('valor');

        if ($quantidade === 0) {
            return
                'Você não possui vencimentos pendentes nos próximos 7 dias.';
        }

        return
            'Nos próximos 7 dias vencem '
            . $quantidade
            . ' lançamento'
            . ($quantidade === 1 ? '' : 's')
            . ', totalizando '
            . $this->moeda($total)
            . '.';
    }

    private function maiorCategoriaMes(int $userId): string
    {
        $inicio = now()
            ->startOfMonth()
            ->toDateString();

        $fim = now()
            ->endOfMonth()
            ->toDateString();

        $totais = collect();

        $despesas = Despesa::query()
            ->with('categoria')
            ->where('user_id', $userId)
            ->where('situacao', 'paga')
            ->whereBetween(
                'data_pagamento',
                [$inicio, $fim]
            )
            ->get();

        foreach ($despesas as $despesa) {
            $categoria =
                $despesa->categoria?->nome
                ?? 'Sem categoria';

            $totais->put(
                $categoria,
                (float) $totais->get(
                    $categoria,
                    0
                )
                + (float) $despesa->valor
            );
        }

        $parcelas = Parcela::query()
            ->with(
                'parcelamento.categoria'
            )
            ->where('user_id', $userId)
            ->where('situacao', 'paga')
            ->whereBetween(
                'data_pagamento',
                [$inicio, $fim]
            )
            ->get();

        foreach ($parcelas as $parcela) {
            $categoria =
                $parcela
                    ->parcelamento
                    ?->categoria
                    ?->nome
                ?? 'Sem categoria';

            $totais->put(
                $categoria,
                (float) $totais->get(
                    $categoria,
                    0
                )
                + (float) $parcela->valor
            );
        }

        if ($totais->isEmpty()) {
            return
                'Ainda não há despesas pagas neste mês para comparar por categoria.';
        }

        $ordenado =
            $totais->sortDesc();

        $categoria =
            $ordenado
                ->keys()
                ->first();

        $valor =
            (float) $ordenado
                ->first();

        return
            'A categoria com maior gasto neste mês é '
            . $categoria
            . ', com '
            . $this->moeda($valor)
            . '.';
    }


    /*
    |--------------------------------------------------------------------------
    | CONFIRMA LANÇAMENTO PENDENTE
    |--------------------------------------------------------------------------
    */

    private function confirmarLancamentoPendente(
        int $userId
    ): string {

        /*
         * Compra no cartão tem prioridade, pois usa fluxo próprio
         * de CompraCartao/Fatura/ParcelaCartao.
         */
        $rascunhoCompraCartao =
            session(
                'assistente_compra_cartao'
            );

        if (
            is_array(
                $rascunhoCompraCartao
            )
        ) {

            if (
                (int) (
                    $rascunhoCompraCartao['user_id']
                    ?? 0
                )
                !== $userId
            ) {

                session()->forget(
                    'assistente_compra_cartao'
                );

                return
                    'Não foi possível confirmar esta compra. '
                    . 'Faça o lançamento novamente.';
            }

            try {

                $compra =
                    $this->lancamentoCompraCartaoService
                        ->criar(
                            $userId,
                            [
                                'cartao_id' =>
                                    $rascunhoCompraCartao['cartao_id'],

                                'categoria_id' =>
                                    $rascunhoCompraCartao['categoria_id'],

                                'descricao' =>
                                    $rascunhoCompraCartao['descricao'],

                                'valor_total' =>
                                    $rascunhoCompraCartao['valor_total'],

                                'data_compra' =>
                                    $rascunhoCompraCartao['data_compra'],

                                'quantidade_parcelas' =>
                                    $rascunhoCompraCartao['quantidade_parcelas'],

                                'observacao' =>
                                    null,
                            ]
                        );

                session()->forget(
                    'assistente_compra_cartao'
                );

                $valorParcelaMedio =
                    (float) $compra->valor_total
                    / max(
                        1,
                        (int) $compra->quantidade_parcelas
                    );

                return
                    "Compra no cartão registrada com sucesso!\n\n"
                    . "• Descrição: "
                    . $compra->descricao
                    . "\n"
                    . "• Cartão: "
                    . (
                        $compra->cartao?->nome
                        ?? 'Cartão'
                    )
                    . "\n"
                    . "• Valor total: "
                    . $this->moeda(
                        (float) $compra->valor_total
                    )
                    . "\n"
                    . "• Parcelas: "
                    . (int) $compra->quantidade_parcelas
                    . "x"
                    . (
                        (int) $compra->quantidade_parcelas > 1
                            ? " (aprox. "
                                . $this->moeda(
                                    $valorParcelaMedio
                                )
                                . " por parcela)"
                            : ''
                    )
                    . "\n"
                    . "• Faturas e parcelas geradas automaticamente.";

            } catch (\Throwable $e) {

                report($e);

                return
                    "Não consegui gravar a compra no cartão.\n"
                    . $e->getMessage();
            }
        }

        $rascunho =
            session(
                'assistente_lancamento_despesa'
            );

        if (!is_array($rascunho)) {
            return
                'Não há nenhum lançamento aguardando confirmação.';
        }

        if (
            (int) (
                $rascunho['user_id']
                ?? 0
            )
            !== $userId
        ) {

            session()->forget(
                'assistente_lancamento_despesa'
            );

            return
                'Não foi possível confirmar este lançamento. '
                . 'Faça o lançamento novamente.';
        }

        try {

            $situacaoRascunho =
                (string) (
                    $rascunho['situacao']
                    ?? 'paga'
                );

            $metodo =
                $situacaoRascunho === 'pendente'
                    ? 'criarDespesaPendente'
                    : 'criarDespesaPaga';

            $despesa =
                $this->lancamentoDespesaService
                    ->{$metodo}(
                        $userId,
                        [
                            'categoria_id' =>
                                $rascunho['categoria_id']
                                ?? null,

                            'conta_id' =>
                                $rascunho['conta_id']
                                ?? null,

                            'forma_pagamento_id' =>
                                $rascunho['forma_pagamento_id']
                                ?? null,

                            'descricao' =>
                                $rascunho['descricao']
                                ?? '',

                            'valor' =>
                                $rascunho['valor']
                                ?? 0,

                            'data' =>
                                $rascunho['data']
                                ?? now()->toDateString(),

                            'data_vencimento' =>
                                $rascunho['data_vencimento']
                                ?? (
                                    $rascunho['data']
                                    ?? now()->toDateString()
                                ),
                        ]
                    );

            /*
             * Consome o rascunho uma única vez.
             * Repetir a confirmação não duplica o lançamento.
             */
            session()->forget(
                'assistente_lancamento_despesa'
            );

            if (
                $situacaoRascunho
                === 'pendente'
            ) {
                return
                    "Despesa pendente lançada com sucesso!\n\n"
                    . '• Descrição: '
                    . $despesa->descricao
                    . "\n"
                    . '• Valor: '
                    . $this->moeda(
                        (float) $despesa->valor
                    )
                    . "\n"
                    . '• Vencimento: '
                    . (
                        $despesa->data_vencimento
                            ? $despesa
                                ->data_vencimento
                                ->format('d/m/Y')
                            : '-'
                    )
                    . "\n"
                    . '• Situação: Pendente';
            }

            return
                "Despesa lançada com sucesso!\n\n"
                . '• Descrição: '
                . $despesa->descricao
                . "\n"
                . '• Valor: '
                . $this->moeda(
                    (float) $despesa->valor
                )
                . "\n"
                . '• Data: '
                . (
                    $despesa->data_pagamento
                        ? $despesa
                            ->data_pagamento
                            ->format('d/m/Y')
                        : '-'
                )
                . "\n"
                . '• Situação: Paga';

        } catch (\Throwable $e) {

            report($e);

            return
                'Não consegui gravar a despesa. '
                . 'Nenhum lançamento foi concluído. '
                . 'Confira os dados e tente novamente.';
        }
    }


    private function normalizar(string $texto): string
    {
        $texto = Str::ascii(
            Str::lower(
                trim($texto)
            )
        );

        $texto = preg_replace(
            '/[^a-z0-9\s]/',
            ' ',
            $texto
        );

        $texto = preg_replace(
            '/\s+/',
            ' ',
            $texto
        );

        return trim($texto);
    }

    private function moeda(float $valor): string
    {
        return
            'R$ '
            . number_format(
                $valor,
                2,
                ',',
                '.'
            );
    }



    /*
    |--------------------------------------------------------------------------
    | DESPESA PENDENTE SEM VALOR
    |--------------------------------------------------------------------------
    */

    private function extrairDespesaPendenteSemValor(
        string $pergunta
    ): ?array {

        $original =
            trim($pergunta);

        if (
            !preg_match(
                '/^(?:tenho\s+que\s+pagar|preciso\s+pagar|vou\s+pagar)\b/iu',
                $original
            )
        ) {
            return null;
        }

        /*
         * Se já existe um valor logo após o verbo, deixa o parser completo
         * tratar a frase.
         */
        if (
            preg_match(
                '/^(?:tenho\s+que\s+pagar|preciso\s+pagar|vou\s+pagar)\s+'
                . '(?:r\$\s*)?\d/iu',
                $original
            )
        ) {
            return null;
        }

        $restante =
            preg_replace(
                '/^(?:tenho\s+que\s+pagar|preciso\s+pagar|vou\s+pagar)\s+/iu',
                '',
                $original
            );

        $restante =
            trim(
                (string) $restante
            );

        $vencimento =
            null;

        $vencimentoDescricao =
            null;

        if (
            preg_match(
                '/\bamanh[aã]\b/iu',
                $restante
            )
        ) {

            $vencimento =
                now()
                    ->copy()
                    ->addDay()
                    ->startOfDay();

            $vencimentoDescricao =
                'amanhã';

            $restante =
                preg_replace(
                    '/\bamanh[aã]\b/iu',
                    '',
                    $restante
                );

        } elseif (
            preg_match(
                '/\b(\d{1,2})\/(\d{1,2})(?:\/(\d{2,4}))?\b/',
                $restante,
                $dataPartes
            )
        ) {

            $dia =
                (int) $dataPartes[1];

            $mes =
                (int) $dataPartes[2];

            $ano =
                !empty(
                    $dataPartes[3]
                )
                    ? (int) $dataPartes[3]
                    : (int) now()->year;

            if ($ano < 100) {
                $ano += 2000;
            }

            if (
                checkdate(
                    $mes,
                    $dia,
                    $ano
                )
            ) {

                $vencimento =
                    \Illuminate\Support\Carbon::create(
                        $ano,
                        $mes,
                        $dia
                    )->startOfDay();

                $vencimentoDescricao =
                    $vencimento
                        ->format('d/m/Y');

                $restante =
                    str_replace(
                        $dataPartes[0],
                        '',
                        $restante
                    );
            }

        } elseif (
            preg_match(
                '/\bdia\s+(\d{1,2})\b/iu',
                $restante,
                $diaPartes
            )
        ) {

            $dia =
                (int) $diaPartes[1];

            $base =
                now()
                    ->copy()
                    ->startOfDay();

            if (
                $dia >= 1
                && $dia <= 31
            ) {

                $mes =
                    (int) $base->month;

                $ano =
                    (int) $base->year;

                if (
                    $dia
                    < (int) $base->day
                ) {

                    $proximo =
                        $base
                            ->copy()
                            ->addMonthNoOverflow();

                    $mes =
                        (int) $proximo->month;

                    $ano =
                        (int) $proximo->year;
                }

                if (
                    checkdate(
                        $mes,
                        $dia,
                        $ano
                    )
                ) {

                    $vencimento =
                        \Illuminate\Support\Carbon::create(
                            $ano,
                            $mes,
                            $dia
                        )->startOfDay();

                    $vencimentoDescricao =
                        $vencimento
                            ->format('d/m/Y');

                    $restante =
                        preg_replace(
                            '/\bdia\s+'
                                . preg_quote(
                                    $diaPartes[1],
                                    '/'
                                )
                                . '\b/iu',
                            '',
                            $restante
                        );
                }
            }
        }

        if (!$vencimento) {
            return null;
        }

        $descricao =
            trim(
                preg_replace(
                    '/\s+/',
                    ' ',
                    $restante
                )
            );

        $descricao =
            preg_replace(
                '/^(?:de|do|da|em|no|na)\s+/iu',
                '',
                $descricao
            );

        $descricao =
            trim(
                (string) $descricao
            );

        if ($descricao === '') {
            return null;
        }

        return [
            'descricao' =>
                ucfirst($descricao),

            'data' =>
                now()
                    ->toDateString(),

            'data_vencimento' =>
                $vencimento
                    ->toDateString(),

            'data_descricao' =>
                $vencimentoDescricao,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | VALOR ISOLADO
    |--------------------------------------------------------------------------
    */

    private function extrairValorIsolado(
        string $texto
    ): ?float {

        $texto =
            trim($texto);

        if (
            !preg_match(
                '/^(?:r\$\s*)?'
                . '(\d{1,3}(?:\.\d{3})*(?:,\d{1,2})?|\d+(?:[.,]\d{1,2})?)'
                . '\s*(?:reais?|real)?$/iu',
                $texto,
                $partes
            )
        ) {
            return null;
        }

        $valorTexto =
            trim(
                $partes[1]
            );

        if (
            str_contains(
                $valorTexto,
                ','
            )
        ) {

            $valorTexto =
                str_replace(
                    '.',
                    '',
                    $valorTexto
                );

            $valorTexto =
                str_replace(
                    ',',
                    '.',
                    $valorTexto
                );
        }

        $valor =
            (float) $valorTexto;

        return
            $valor > 0
                ? $valor
                : null;
    }


    /*
    |--------------------------------------------------------------------------
    | INTERPRETAÇÃO DE DESPESA PENDENTE
    |--------------------------------------------------------------------------
    */

    private function extrairDespesaPendente(
        string $pergunta
    ): ?array {

        $original =
            trim($pergunta);

        if (
            !preg_match(
                '/^(?:tenho\s+que\s+pagar|preciso\s+pagar|vou\s+pagar)\b/iu',
                $original
            )
        ) {
            return null;
        }

        if (
            !preg_match(
                '/^(?:tenho\s+que\s+pagar|preciso\s+pagar|vou\s+pagar)\s+'
                . '(?:r\$\s*)?'
                . '(\d{1,3}(?:\.\d{3})*(?:,\d{1,2})?|\d+(?:[.,]\d{1,2})?)'
                . '\s*(?:reais?|real)?'
                . '\s+(.+)$/iu',
                $original,
                $partes
            )
        ) {
            return null;
        }

        $valorTexto =
            trim($partes[1]);

        if (
            str_contains(
                $valorTexto,
                ','
            )
        ) {
            $valorTexto =
                str_replace(
                    '.',
                    '',
                    $valorTexto
                );

            $valorTexto =
                str_replace(
                    ',',
                    '.',
                    $valorTexto
                );
        }

        $valor =
            (float) $valorTexto;

        if ($valor <= 0) {
            return null;
        }

        $restante =
            trim($partes[2]);

        $vencimento =
            null;

        $vencimentoDescricao =
            null;

        /*
         * Amanhã.
         */
        if (
            preg_match(
                '/\bamanh[aã]\b/iu',
                $restante
            )
        ) {
            $vencimento =
                now()
                    ->copy()
                    ->addDay()
                    ->startOfDay();

            $vencimentoDescricao =
                'amanhã';

            $restante =
                preg_replace(
                    '/\bamanh[aã]\b/iu',
                    '',
                    $restante
                );

        /*
         * Data completa: 10/09 ou 10/09/2026.
         */
        } elseif (
            preg_match(
                '/\b(\d{1,2})\/(\d{1,2})(?:\/(\d{2,4}))?\b/',
                $restante,
                $dataPartes
            )
        ) {

            $dia =
                (int) $dataPartes[1];

            $mes =
                (int) $dataPartes[2];

            $ano =
                !empty($dataPartes[3])
                    ? (int) $dataPartes[3]
                    : (int) now()->year;

            if ($ano < 100) {
                $ano += 2000;
            }

            if (
                checkdate(
                    $mes,
                    $dia,
                    $ano
                )
            ) {
                $vencimento =
                    \Illuminate\Support\Carbon::create(
                        $ano,
                        $mes,
                        $dia
                    )->startOfDay();

                $vencimentoDescricao =
                    $vencimento
                        ->format('d/m/Y');

                $restante =
                    str_replace(
                        $dataPartes[0],
                        '',
                        $restante
                    );
            }

        /*
         * Apenas dia do mês: "dia 10".
         * Se o dia já passou no mês atual, assume o mês seguinte.
         */
        } elseif (
            preg_match(
                '/\bdia\s+(\d{1,2})\b/iu',
                $restante,
                $diaPartes
            )
        ) {

            $dia =
                (int) $diaPartes[1];

            $base =
                now()
                    ->copy()
                    ->startOfDay();

            if (
                $dia >= 1
                && $dia <= 31
            ) {

                $mes =
                    (int) $base->month;

                $ano =
                    (int) $base->year;

                if (
                    $dia
                    < (int) $base->day
                ) {
                    $proximo =
                        $base
                            ->copy()
                            ->addMonthNoOverflow();

                    $mes =
                        (int) $proximo->month;

                    $ano =
                        (int) $proximo->year;
                }

                if (
                    checkdate(
                        $mes,
                        $dia,
                        $ano
                    )
                ) {
                    $vencimento =
                        \Illuminate\Support\Carbon::create(
                            $ano,
                            $mes,
                            $dia
                        )->startOfDay();

                    $vencimentoDescricao =
                        $vencimento
                            ->format('d/m/Y');

                    $restante =
                        preg_replace(
                            '/\bdia\s+'
                                . preg_quote(
                                    $diaPartes[1],
                                    '/'
                                )
                                . '\b/iu',
                            '',
                            $restante
                        );
                }
            }
        }

        if (!$vencimento) {
            return null;
        }

        $descricao =
            trim($restante);

        $descricao =
            preg_replace(
                '/^(?:de|do|da|em|no|na)\s+/iu',
                '',
                $descricao
            );

        $descricao =
            trim(
                preg_replace(
                    '/\s+/',
                    ' ',
                    $descricao
                )
            );

        if ($descricao === '') {
            $descricao =
                'Despesa';
        }

        return [
            'tipo' =>
                'despesa',

            'descricao' =>
                ucfirst($descricao),

            'valor' =>
                $valor,

            'data' =>
                now()
                    ->toDateString(),

            'data_vencimento' =>
                $vencimento
                    ->toDateString(),

            'data_descricao' =>
                $vencimentoDescricao,

            'categoria_sugerida' =>
                $this->sugerirCategoriaLancamento(
                    $descricao
                ),

            'situacao' =>
                'pendente',
        ];
    }


    private function prepararDespesaPendente(
        int $userId,
        array $dados
    ): array {

        $categoria =
            $this->resolverCategoriaLancamento(
                $userId,
                $dados['descricao'],
                $dados['categoria_sugerida']
                    ?? null
            );

        $dados['categoria_id'] =
            $categoria?->id;

        $dados['categoria_nome'] =
            $categoria?->nome;

        return $dados;
    }


    private function montarPreviaDespesaPendente(
        array $dados
    ): string {

        $categoria =
            $dados['categoria_nome']
            ?? (
                $dados['categoria_sugerida']
                    ? $dados['categoria_sugerida']
                        . ' (não encontrada no cadastro)'
                    : 'A confirmar'
            );

        $resposta =
            "Entendi que você quer registrar uma despesa pendente:\n\n"
            . "• Descrição: "
            . $dados['descricao']
            . "\n"
            . "• Valor: "
            . $this->moeda(
                (float) $dados['valor']
            )
            . "\n"
            . "• Vencimento: "
            . $dados['data_descricao']
            . "\n"
            . "• Categoria: "
            . $categoria
            . "\n"
            . "• Situação: Pendente";

        if (
            empty(
                $dados['categoria_id']
            )
        ) {
            return
                $resposta
                . "\n\n"
                . "Antes de confirmar, preciso identificar uma "
                . "categoria já cadastrada para esta despesa."
                . "\n\n"
                . "Nenhum lançamento foi gravado.";
        }

        session([
            'assistente_lancamento_despesa' => [
                'user_id' =>
                    auth()->id(),

                'situacao' =>
                    'pendente',

                'categoria_id' =>
                    $dados['categoria_id'],

                'conta_id' =>
                    null,

                'forma_pagamento_id' =>
                    null,

                'descricao' =>
                    $dados['descricao'],

                'valor' =>
                    (float) $dados['valor'],

                'data' =>
                    $dados['data'],

                'data_vencimento' =>
                    $dados['data_vencimento'],
            ],
        ]);

        return
            $resposta
            . "\n\n"
            . "Dados localizados no seu cadastro."
            . "\n"
            . "Para gravar, digite exatamente:"
            . "\n"
            . "CONFIRMAR LANÇAMENTO"
            . "\n\n"
            . "Para desistir, digite:"
            . "\n"
            . "CANCELAR LANÇAMENTO"
            . "\n\n"
            . "Ainda não foi gravado.";
    }


    /*
    |--------------------------------------------------------------------------
    | INTERPRETAÇÃO DE DESPESA POR LINGUAGEM NATURAL
    |--------------------------------------------------------------------------
    |
    | Exemplos aceitos nesta primeira etapa:
    |
    | Paguei 63 reais de gasolina hoje no Pix
    | Gastei 49,90 na farmácia
    | Paguei 120 de mercado ontem em dinheiro
    | Paguei R$ 80 de almoço no pix
    |
    | Nesta fase o método apenas interpreta a frase.
    | Nenhuma despesa ou movimentação é criada.
    |
    */

    private function extrairLancamentoDespesa(
        string $pergunta
    ): ?array {

        $original =
            trim($pergunta);

        $textoLancamento =
            $original;

        $data =
            now()
                ->copy()
                ->startOfDay();

        $dataDescricao =
            'hoje';

        if (
            preg_match(
                '/^ontem\b/iu',
                $textoLancamento
            )
        ) {

            $data =
                now()
                    ->copy()
                    ->subDay()
                    ->startOfDay();

            $dataDescricao =
                'ontem';

            $textoLancamento =
                preg_replace(
                    '/^ontem\s+/iu',
                    '',
                    $textoLancamento
                );

        } elseif (
            preg_match(
                '/^hoje\b/iu',
                $textoLancamento
            )
        ) {

            $textoLancamento =
                preg_replace(
                    '/^hoje\s+/iu',
                    '',
                    $textoLancamento
                );
        }

        $textoLancamento =
            preg_replace(
                '/^eu\s+/iu',
                '',
                $textoLancamento
            );

        $textoLancamento =
            trim(
                (string) $textoLancamento
            );

        $ehAbastecimento =
            preg_match(
                '/^(?:abasteci|abastecer|abastecimento)\b/iu',
                $textoLancamento
            ) === 1;

        if (
            !preg_match(
                '/^(?:paguei|gastei|comprei|abasteci|abastecer|abastecimento)\b/iu',
                $textoLancamento
            )
        ) {
            return null;
        }

        /*
         * Abastecimento aceita o veículo entre a ação e o valor:
         *
         * abasteci o carro R$ 100 ...
         * abasteci a moto 80 ...
         * abasteci minha moto 70 ...
         * abastecimento do carro 120 ...
         */
        if ($ehAbastecimento) {

            if (
                !preg_match(
                    '/^(?:abasteci|abastecer|abastecimento)\s+'
                    . '(?:(?:o|a|meu|minha|do|da)\s+)?'
                    . '(?:(?:carro|moto|motocicleta|strada|estrada|veiculo|veículo)\s+)?'
                    . '(?:com\s+)?'
                    . '(?:r\$\s*)?'
                    . '(\d{1,3}(?:\.\d{3})*(?:,\d{1,2})?|\d+(?:[.,]\d{1,2})?)'
                    . '\s*(?:reais?|real)?'
                    . '(?:\s+(.+))?$/iu',
                    $textoLancamento,
                    $partes
                )
            ) {
                return null;
            }

        } else {

            if (
                !preg_match(
                    '/^(?:paguei|gastei|comprei)\s+'
                    . '(?:r\$\s*)?'
                    . '(\d{1,3}(?:\.\d{3})*(?:,\d{1,2})?|\d+(?:[.,]\d{1,2})?)'
                    . '\s*(?:reais?|real)?'
                    . '\s+(.+)$/iu',
                    $textoLancamento,
                    $partes
                )
            ) {
                return null;
            }
        }

        $valorTexto =
            trim($partes[1]);

        if (
            str_contains(
                $valorTexto,
                ','
            )
        ) {

            $valorTexto =
                str_replace(
                    '.',
                    '',
                    $valorTexto
                );

            $valorTexto =
                str_replace(
                    ',',
                    '.',
                    $valorTexto
                );
        }

        $valor =
            (float) $valorTexto;

        if ($valor <= 0) {
            return null;
        }

        $restante =
            trim(
                (string) (
                    $partes[2]
                    ?? ''
                )
            );

        $parcelas =
            1;

        $parcelasPorExtenso = [
            'um' => 1,
            'uma' => 1,
            'dois' => 2,
            'duas' => 2,
            'tres' => 3,
            'três' => 3,
            'quatro' => 4,
            'cinco' => 5,
            'seis' => 6,
            'sete' => 7,
            'oito' => 8,
            'nove' => 9,
            'dez' => 10,
            'onze' => 11,
            'doze' => 12,
        ];

        if (
            preg_match(
                '/\bem\s+(\d{1,2})\s*(?:x|vez|vezes)\b/iu',
                $restante,
                $parcelasPartes
            )
        ) {

            $parcelas =
                max(
                    1,
                    (int) $parcelasPartes[1]
                );

            $restante =
                preg_replace(
                    '/\bem\s+'
                    . preg_quote(
                        $parcelasPartes[1],
                        '/'
                    )
                    . '\s*(?:x|vez|vezes)\b/iu',
                    '',
                    $restante
                );

        } elseif (
            preg_match(
                '/\b(?:em\s+)?(um|uma|dois|duas|tr[eê]s|quatro|cinco|seis|sete|oito|nove|dez|onze|doze)\s+vez(?:es)?\b/iu',
                $restante,
                $parcelasPartes
            )
        ) {

            $parcelaTexto =
                mb_strtolower(
                    $parcelasPartes[1],
                    'UTF-8'
                );

            $parcelas =
                $parcelasPorExtenso[
                    $parcelaTexto
                ]
                ?? 1;

            $restante =
                preg_replace(
                    '/\b(?:em\s+)?'
                    . preg_quote(
                        $parcelasPartes[1],
                        '/'
                    )
                    . '\s+vez(?:es)?\b/iu',
                    '',
                    $restante
                );

        } elseif (
            preg_match(
                '/\b(\d{1,2})x\b/iu',
                $restante,
                $parcelasPartes
            )
        ) {

            $parcelas =
                max(
                    1,
                    (int) $parcelasPartes[1]
                );

            $restante =
                preg_replace(
                    '/\b'
                    . preg_quote(
                        $parcelasPartes[1],
                        '/'
                    )
                    . 'x\b/iu',
                    '',
                    $restante
                );
        }

        if (
            preg_match(
                '/\bontem\b/iu',
                $restante
            )
        ) {

            $data =
                now()
                    ->copy()
                    ->subDay()
                    ->startOfDay();

            $dataDescricao =
                'ontem';

            $restante =
                preg_replace(
                    '/\bontem\b/iu',
                    '',
                    $restante
                );

        } elseif (
            preg_match(
                '/\bhoje\b/iu',
                $restante
            )
        ) {

            $data =
                now()
                    ->copy()
                    ->startOfDay();

            $dataDescricao =
                'hoje';

            $restante =
                preg_replace(
                    '/\bhoje\b/iu',
                    '',
                    $restante
                );

        } elseif (
            preg_match(
                '/\b(\d{1,2})\/(\d{1,2})(?:\/(\d{2,4}))?\b/',
                $restante,
                $dataPartes
            )
        ) {

            $dia =
                (int) $dataPartes[1];

            $mes =
                (int) $dataPartes[2];

            $ano =
                !empty($dataPartes[3])
                    ? (int) $dataPartes[3]
                    : (int) now()->year;

            if ($ano < 100) {
                $ano += 2000;
            }

            if (
                checkdate(
                    $mes,
                    $dia,
                    $ano
                )
            ) {

                $data =
                    \Illuminate\Support\Carbon::create(
                        $ano,
                        $mes,
                        $dia
                    )->startOfDay();

                $dataDescricao =
                    $data->format('d/m/Y');

                $restante =
                    str_replace(
                        $dataPartes[0],
                        '',
                        $restante
                    );
            }
        }

        $formaPagamento =
            null;

        $formas = [
            'pix' => [
                '/\b(?:no|na|em|com)?\s*pix\b/iu',
            ],

            'dinheiro' => [
                '/\b(?:no|na|em|com)?\s*dinheiro\b/iu',
            ],

            'debito' => [
                '/\b(?:no|na|em|com)?\s*(?:cart[aã]o\s+de\s+)?d[eé]bito\b/iu',
            ],

            'credito' => [
                '/\b(?:no|na|em|com)?\s*(?:cart[aã]o\s+de\s+)?cr[eé]dito\b/iu',
            ],
        ];

        foreach (
            $formas as $forma => $padroes
        ) {

            foreach (
                $padroes as $padrao
            ) {

                if (
                    preg_match(
                        $padrao,
                        $restante
                    )
                ) {

                    $formaPagamento =
                        $forma;

                    $restante =
                        preg_replace(
                            $padrao,
                            '',
                            $restante
                        );

                    break 2;
                }
            }
        }

        $descricao =
            trim($restante);

        $descricao =
            preg_replace(
                '/^(?:de|do|da|em|no|na)\s+/iu',
                '',
                $descricao
            );

        $descricao =
            preg_replace(
                '/\s+(?:de|do|da|em|no|na|com)\s*$/iu',
                '',
                $descricao
            );

        $descricao =
            trim(
                preg_replace(
                    '/\s+/',
                    ' ',
                    $descricao
                )
            );

        /*
         * Carro/moto são contexto. O lançamento financeiro fica
         * padronizado como Abastecimento.
         */
        if ($ehAbastecimento) {
            $descricao =
                'Abastecimento';
        }

        if ($descricao === '') {
            $descricao =
                'Despesa';
        }

        $categoriaSugerida =
            $this->sugerirCategoriaLancamento(
                $descricao
            );

        return [
            'tipo' =>
                'despesa',

            'descricao' =>
                ucfirst($descricao),

            'valor' =>
                $valor,

            'data' =>
                $data->toDateString(),

            'data_descricao' =>
                $dataDescricao,

            'forma_pagamento' =>
                $formaPagamento,

            'categoria_sugerida' =>
                $categoriaSugerida,

            'situacao' =>
                'paga',

            'parcelas' =>
                $parcelas,
        ];
    }

    private function sugerirCategoriaLancamento(
        string $descricao
    ): ?string {

        $descricao =
            $this->normalizar(
                $descricao
            );

        $mapa = [

            'Abastecimento' => [
                'abastecimento',
                'abasteci',
                'abastecer',
                'posto',
                'gasolina',
                'etanol',
                'diesel',
                'combustivel',
            ],

            /*
             * Carro e moto são contexto do abastecimento.
             * Não sugerimos uma categoria genérica "Veículo" aqui.
             */

            'Alimentação' => [
                'almoco',
                'jantar',
                'lanche',
                'restaurante',
                'padaria',
                'ifood',
            ],

            'Mercado' => [
                'mercado',
                'supermercado',
                'compras mercado',
            ],

            'Saúde' => [
                'farmacia',
                'remedio',
                'medicamento',
                'consulta',
            ],

            'Transporte' => [
                'uber',
                'taxi',
                'onibus',
            ],
        ];

        foreach (
            $mapa as $categoria => $termos
        ) {

            foreach (
                $termos as $termo
            ) {

                if (
                    str_contains(
                        $descricao,
                        $termo
                    )
                ) {
                    return $categoria;
                }
            }
        }

        return null;
    }



    /*
    |--------------------------------------------------------------------------
    | PREPARA O LANÇAMENTO COM DADOS REAIS DO USUÁRIO
    |--------------------------------------------------------------------------
    |
    | Nesta Fase 2 o Assistente:
    |
    | - procura uma categoria REAL do usuário;
    | - procura uma conta REAL do usuário;
    | - procura a forma de pagamento REAL cadastrada;
    | - NÃO grava nada no banco.
    |
    */

    private function prepararLancamentoDespesa(
        int $userId,
        string $perguntaOriginal,
        array $dados
    ): array {

        $categoria =
            $this->resolverCategoriaLancamento(
                $userId,
                $dados['descricao'],
                $dados['categoria_sugerida']
                    ?? null
            );

        $conta =
            $this->resolverContaLancamento(
                $userId,
                $perguntaOriginal
            );

        /*
         * REGRA AUTOMÁTICA PARA DINHEIRO
         *
         * Se o usuário informou pagamento em dinheiro e NÃO informou
         * explicitamente uma conta, tentamos usar automaticamente a
         * conta "Carteira", desde que ela exista e esteja ativa.
         *
         * Exemplo:
         * "Hoje eu gastei 10 em dinheiro na padaria"
         * -> Conta: Carteira
         *
         * Se o usuário informar uma conta explicitamente, ela continua
         * tendo prioridade sobre esta regra automática.
         */
        if (
            !$conta
            && (
                $dados['forma_pagamento']
                ?? null
            ) === 'dinheiro'
        ) {

            $conta =
                Conta::query()
                    ->where(
                        'user_id',
                        $userId
                    )
                    ->where(
                        'ativa',
                        true
                    )
                    ->get()
                    ->first(
                        function ($item) {
                            return
                                $this->normalizar(
                                    (string) $item->nome
                                )
                                === 'carteira';
                        }
                    );
        }

        /*
         * Se a conta foi identificada na frase, removemos esse trecho
         * da descrição para não gravar algo como:
         *
         * "Gasolina pelo Itaú"
         *
         * A descrição correta deve ficar apenas:
         *
         * "Gasolina"
         */
        if ($conta) {

            $dados['descricao'] =
                $this->removerContaDaDescricao(
                    $dados['descricao'],
                    (string) $conta->nome
                );
        }

        $formaPagamento =
            $this->resolverFormaPagamentoLancamento(
                $dados['forma_pagamento']
                    ?? null
            );

        $cartao =
            null;

        if (
            (
                $dados['forma_pagamento']
                ?? null
            ) === 'credito'
        ) {

            $cartao =
                $this->resolverCartaoLancamento(
                    $userId,
                    $perguntaOriginal
                );
        }

        $dados['cartao_id'] =
            $cartao?->id;

        $dados['cartao_nome'] =
            $cartao?->nome;

        $contasAtivas =
            Conta::query()
                ->where(
                    'user_id',
                    $userId
                )
                ->where(
                    'ativa',
                    true
                )
                ->orderBy('nome')
                ->get([
                    'id',
                    'nome',
                ]);

        $dados['categoria_id'] =
            $categoria?->id;

        $dados['categoria_nome'] =
            $categoria?->nome;

        $dados['conta_id'] =
            $conta?->id;

        $dados['conta_nome'] =
            $conta?->nome;

        $dados['forma_pagamento_id'] =
            $formaPagamento?->id;

        $dados['forma_pagamento_nome'] =
            $formaPagamento?->nome;

        $dados['contas_disponiveis'] =
            $contasAtivas
                ->pluck('nome')
                ->values()
                ->all();

        /*
         * Só estará pronto para confirmação quando houver
         * categoria e conta reais.
         *
         * A forma de pagamento continua opcional, pois a própria
         * tela de Despesas atualmente aceita forma_pagamento_id nula.
         */
        $dados['pronto_para_confirmar'] =
            !empty(
                $dados['categoria_id']
            )
            && !empty(
                $dados['conta_id']
            );

        return $dados;
    }


    /*
    |--------------------------------------------------------------------------
    | RESOLVE CATEGORIA REAL
    |--------------------------------------------------------------------------
    */

    private function resolverCategoriaLancamento(
        int $userId,
        string $descricao,
        ?string $categoriaSugerida
    ): ?Categoria {

        $categorias =
            Categoria::query()
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
                ->orderBy('nome')
                ->get();

        if ($categorias->isEmpty()) {
            return null;
        }

        /*
         * 1. Primeiro tentamos a categoria sugerida pelo mapa.
         */
        if ($categoriaSugerida) {

            $categoriaSugeridaNormalizada =
                $this->normalizar(
                    $categoriaSugerida
                );

            $encontrada =
                $categorias->first(
                    function ($categoria) use (
                        $categoriaSugeridaNormalizada
                    ) {

                        return
                            $this->normalizar(
                                (string) $categoria->nome
                            )
                            ===
                            $categoriaSugeridaNormalizada;
                    }
                );

            if ($encontrada) {
                return $encontrada;
            }
        }

        /*
         * 2. Depois tentamos relacionar o nome da categoria
         * diretamente com a descrição informada.
         *
         * Exemplo:
         * descrição = "farmácia"
         * categoria = "Farmácia"
         */
        $descricaoNormalizada =
            $this->normalizar(
                $descricao
            );

        $melhor =
            null;

        $melhorTamanho =
            0;

        foreach ($categorias as $categoria) {

            $nomeNormalizado =
                $this->normalizar(
                    (string) $categoria->nome
                );

            if (
                $nomeNormalizado === ''
            ) {
                continue;
            }

            if (
                str_contains(
                    $descricaoNormalizada,
                    $nomeNormalizado
                )
                ||
                str_contains(
                    $nomeNormalizado,
                    $descricaoNormalizada
                )
            ) {

                $tamanho =
                    strlen(
                        $nomeNormalizado
                    );

                if (
                    $tamanho
                    >
                    $melhorTamanho
                ) {

                    $melhor =
                        $categoria;

                    $melhorTamanho =
                        $tamanho;
                }
            }
        }

        return $melhor;
    }


    /*
    |--------------------------------------------------------------------------
    | RESOLVE CONTA REAL
    |--------------------------------------------------------------------------
    |
    | Regras:
    |
    | 1. Se o usuário escrever o nome de uma conta na frase,
    |    ela será usada.
    |
    | 2. Se existir somente UMA conta ativa, ela será sugerida
    |    automaticamente.
    |
    | 3. Se houver duas ou mais contas e nenhuma estiver na frase,
    |    a conta ficará "A confirmar".
    |
    */

    private function resolverContaLancamento(
        int $userId,
        string $perguntaOriginal
    ): ?Conta {

        $contas =
            Conta::query()
                ->where(
                    'user_id',
                    $userId
                )
                ->where(
                    'ativa',
                    true
                )
                ->orderBy('nome')
                ->get();

        if ($contas->isEmpty()) {
            return null;
        }

        $perguntaNormalizada =
            $this->normalizar(
                $perguntaOriginal
            );

        /*
         * Procura primeiro pelo nome explícito da conta.
         * Nomes maiores têm prioridade para evitar coincidências
         * em nomes muito curtos.
         */
        $ordenadas =
            $contas
                ->sortByDesc(
                    function ($conta) {
                        return strlen(
                            $this->normalizar(
                                (string) $conta->nome
                            )
                        );
                    }
                );

        foreach ($ordenadas as $conta) {

            $nome =
                $this->normalizar(
                    (string) $conta->nome
                );

            if (
                $nome !== ''
                &&
                str_contains(
                    $perguntaNormalizada,
                    $nome
                )
            ) {
                return $conta;
            }
        }

        if ($contas->count() === 1) {
            return $contas->first();
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | REMOVE A CONTA DA DESCRIÇÃO
    |--------------------------------------------------------------------------
    |
    | Exemplos:
    |
    | "Gasolina pelo Itaú"  -> "Gasolina"
    | "Mercado pela Nubank" -> "Mercado"
    | "Almoço no Itaú"      -> "Almoço"
    |
    */

    private function removerContaDaDescricao(
        string $descricao,
        string $nomeConta
    ): string {

        $descricao =
            trim($descricao);

        $nomeConta =
            trim($nomeConta);

        if (
            $descricao === ''
            || $nomeConta === ''
        ) {
            return $descricao;
        }

        $contaRegex =
            preg_quote(
                $nomeConta,
                '/'
            );

        /*
         * Remove a conta quando vier no final da descrição
         * precedida por formas comuns de ligação.
         */
        $padroes = [
            '/\s+(?:pelo|pela|por|via)\s+'
                . $contaRegex
                . '\s*$/iu',

            '/\s+(?:no|na|do|da|em)\s+'
                . $contaRegex
                . '\s*$/iu',

            '/\s+'
                . $contaRegex
                . '\s*$/iu',
        ];

        foreach ($padroes as $padrao) {

            $descricaoLimpa =
                preg_replace(
                    $padrao,
                    '',
                    $descricao
                );

            if (
                $descricaoLimpa !== null
                && trim($descricaoLimpa)
                    !== trim($descricao)
            ) {

                $descricao =
                    trim(
                        $descricaoLimpa
                    );

                break;
            }
        }

        $descricao =
            trim(
                preg_replace(
                    '/\s+/',
                    ' ',
                    $descricao
                )
            );

        return
            $descricao !== ''
                ? ucfirst($descricao)
                : 'Despesa';
    }


    /*
    |--------------------------------------------------------------------------
    | CARTÕES DO USUÁRIO
    |--------------------------------------------------------------------------
    */

    private function resolverCartaoLancamento(
        int $userId,
        string $perguntaOriginal
    ): ?Cartao {

        $cartoes =
            Cartao::query()
                ->where(
                    'user_id',
                    $userId
                )
                ->where(
                    'ativo',
                    true
                )
                ->orderBy('nome')
                ->get();

        if ($cartoes->isEmpty()) {
            return null;
        }

        $texto =
            $this->normalizar(
                $perguntaOriginal
            );

        $ordenados =
            $cartoes
                ->sortByDesc(
                    fn ($cartao) =>
                        strlen(
                            $this->normalizar(
                                (string) $cartao->nome
                            )
                        )
                );

        foreach ($ordenados as $cartao) {

            $nome =
                $this->normalizar(
                    (string) $cartao->nome
                );

            if (
                $nome !== ''
                && str_contains(
                    $texto,
                    $nome
                )
            ) {
                return $cartao;
            }
        }

        /*
         * Se existir somente um cartão ativo, ele pode ser assumido
         * automaticamente. Com dois ou mais, o assistente pergunta.
         */
        if ($cartoes->count() === 1) {
            return $cartoes->first();
        }

        return null;
    }


    private function resolverCartaoPorResposta(
        int $userId,
        string $resposta
    ): ?Cartao {

        $texto =
            $this->normalizar(
                $resposta
            );

        if ($texto === '') {
            return null;
        }

        $cartoes =
            Cartao::query()
                ->where(
                    'user_id',
                    $userId
                )
                ->where(
                    'ativo',
                    true
                )
                ->orderBy('nome')
                ->get();

        /*
         * Aceita tanto o nome completo quanto uma parte distintiva.
         *
         * Exemplos:
         * "Banco Nubank" -> aceita "nubank"
         * "Banco Inter"  -> aceita "inter"
         */
        foreach ($cartoes as $cartao) {

            $nome =
                $this->normalizar(
                    (string) $cartao->nome
                );

            if (
                $texto === $nome
                || str_contains(
                    $texto,
                    $nome
                )
                || str_contains(
                    $nome,
                    $texto
                )
            ) {
                return $cartao;
            }

            $palavrasNome =
                collect(
                    explode(
                        ' ',
                        $nome
                    )
                )
                    ->filter(
                        fn ($palavra) =>
                            strlen(
                                $palavra
                            ) >= 3
                            && !in_array(
                                $palavra,
                                [
                                    'banco',
                                    'cartao',
                                    'credito',
                                ],
                                true
                            )
                    );

            foreach ($palavrasNome as $palavra) {

                if (
                    $texto === $palavra
                ) {
                    return $cartao;
                }
            }
        }

        return null;
    }


    private function listarCartoesAtivos(
        int $userId
    ): string {

        $cartoes =
            Cartao::query()
                ->where(
                    'user_id',
                    $userId
                )
                ->where(
                    'ativo',
                    true
                )
                ->orderBy('nome')
                ->pluck('nome');

        if ($cartoes->isEmpty()) {
            return
                '• Nenhum cartão ativo cadastrado';
        }

        return
            $cartoes
                ->map(
                    fn ($nome) =>
                        '• ' . $nome
                )
                ->implode("\n");
    }


    private function montarPreviaCompraCartao(
        array $dados
    ): string {

        /*
         * A categoria continua obrigatória porque CompraCartao exige
         * categoria válida. Quando o parser não conseguir identificá-la,
         * não devemos lançar usando uma categoria inventada.
         */
        if (
            empty(
                $dados['categoria_id']
            )
        ) {

            return
                "Identifiquei o cartão, mas ainda falta uma categoria "
                . "válida para esta compra.\n\n"
                . "Informe o que você comprou com mais detalhes para "
                . "que eu consiga classificar corretamente.\n\n"
                . "Nenhuma compra foi gravada.";
        }

        $quantidadeParcelas =
            max(
                1,
                (int) (
                    $dados['quantidade_parcelas']
                    ?? 1
                )
            );

        $valorTotal =
            (float) $dados['valor_total'];

        $valorParcela =
            $valorTotal
            / $quantidadeParcelas;

        session([
            'assistente_compra_cartao' => [
                'user_id' =>
                    auth()->id(),

                'cartao_id' =>
                    $dados['cartao_id'],

                'categoria_id' =>
                    $dados['categoria_id'],

                'descricao' =>
                    $dados['descricao'],

                'valor_total' =>
                    $valorTotal,

                'data_compra' =>
                    $dados['data_compra'],

                'quantidade_parcelas' =>
                    $quantidadeParcelas,
            ],
        ]);

        return
            "Confirme a compra no cartão:\n\n"
            . "• Descrição: "
            . $dados['descricao']
            . "\n"
            . "• Categoria: "
            . (
                $dados['categoria_nome']
                ?? 'Categoria identificada'
            )
            . "\n"
            . "• Cartão: "
            . $dados['cartao_nome']
            . "\n"
            . "• Valor total: "
            . $this->moeda(
                $valorTotal
            )
            . "\n"
            . "• Parcelas: "
            . $quantidadeParcelas
            . "x"
            . (
                $quantidadeParcelas > 1
                    ? " de aproximadamente "
                        . $this->moeda(
                            $valorParcela
                        )
                    : ''
            )
            . "\n"
            . "• Data da compra: "
            . (
                $dados['data_descricao']
                ?? $dados['data_compra']
            )
            . "\n\n"
            . "O sistema usará o fechamento e o vencimento cadastrados "
            . "no cartão para gerar as faturas e parcelas.\n\n"
            . "Para gravar, digite exatamente:\n"
            . "CONFIRMAR LANÇAMENTO\n\n"
            . "Para desistir:\n"
            . "CANCELAR LANÇAMENTO\n\n"
            . "Ainda não foi gravado.";
    }


    /*
    |--------------------------------------------------------------------------
    | RESOLVE FORMA DE PAGAMENTO REAL
    |--------------------------------------------------------------------------
    */

    private function resolverFormaPagamentoLancamento(
        ?string $formaIdentificada
    ): ?FormaPagamento {

        if (!$formaIdentificada) {
            return null;
        }

        $formas =
            FormaPagamento::query()
                ->where(
                    'ativa',
                    true
                )
                ->orderBy('nome')
                ->get();

        if ($formas->isEmpty()) {
            return null;
        }

        $aliases =
            match ($formaIdentificada) {

                'pix' => [
                    'pix',
                ],

                'dinheiro' => [
                    'dinheiro',
                ],

                'debito' => [
                    'debito',
                    'cartao de debito',
                ],

                'credito' => [
                    'credito',
                    'cartao de credito',
                ],

                default => [
                    $formaIdentificada,
                ],
            };

        foreach ($formas as $forma) {

            $nome =
                $this->normalizar(
                    (string) $forma->nome
                );

            foreach ($aliases as $alias) {

                $alias =
                    $this->normalizar(
                        $alias
                    );

                if (
                    $nome === $alias
                    ||
                    str_contains(
                        $nome,
                        $alias
                    )
                    ||
                    str_contains(
                        $alias,
                        $nome
                    )
                ) {
                    return $forma;
                }
            }
        }

        return null;
    }


    private function montarPreviaLancamentoDespesa(
        array $dados
    ): string {

        $forma =
            $dados['forma_pagamento_nome']
            ?? match (
                $dados['forma_pagamento']
                    ?? null
            ) {
                'pix' =>
                    'Pix',

                'dinheiro' =>
                    'Dinheiro',

                'debito' =>
                    'Cartão de débito',

                'credito' =>
                    'Cartão de crédito',

                default =>
                    'Não informada',
            };

        $categoria =
            $dados['categoria_nome']
            ?? (
                $dados['categoria_sugerida']
                ? $dados['categoria_sugerida']
                    . ' (não encontrada no cadastro)'
                : 'A confirmar'
            );

        $conta =
            $dados['conta_nome']
            ?? 'A confirmar';

        $resposta =
            "Entendi que você quer registrar uma despesa:

"
            . "• Descrição: "
            . $dados['descricao']
            . "
"
            . "• Valor: "
            . $this->moeda(
                $dados['valor']
            )
            . "
"
            . "• Data: "
            . $dados['data_descricao']
            . "
"
            . "• Forma de pagamento: "
            . $forma
            . "
"
            . (
                ($dados['forma_pagamento'] ?? null) === 'credito'
                    ? "• Parcelas: "
                        . (int) ($dados['parcelas'] ?? 1)
                        . "x
"
                    : ''
            )
            . "• Categoria: "
            . $categoria
            . "
"
            . "• Conta: "
            . $conta
            . "
"
            . "• Situação: Paga";

        /*
         * Compra no cartão de crédito segue por CompraCartao/Fatura.
         * Nunca será gravada como Despesa comum.
         */
        if (
            ($dados['forma_pagamento']
                ?? null)
            === 'credito'
        ) {

            /*
             * Sem cartão informado: guardamos o restante do rascunho
             * e perguntamos apenas qual cartão foi utilizado.
             */
            if (
                empty(
                    $dados['cartao_id']
                )
            ) {

                session([
                    'assistente_compra_cartao_incompleta' => [
                        'user_id' =>
                            auth()->id(),

                        'categoria_id' =>
                            $dados['categoria_id']
                            ?? null,

                        'categoria_nome' =>
                            $dados['categoria_nome']
                            ?? null,

                        'descricao' =>
                            $dados['descricao'],

                        'valor_total' =>
                            (float) $dados['valor'],

                        'data_compra' =>
                            $dados['data'],

                        'data_descricao' =>
                            $dados['data_descricao'],

                        'quantidade_parcelas' =>
                            (int) (
                                $dados['parcelas']
                                ?? 1
                            ),
                    ],
                ]);

                $cartoes =
                    $this->listarCartoesAtivos(
                        auth()->id()
                    );

                return
                    "Entendi a compra no cartão de crédito.\n\n"
                    . "• Descrição: "
                    . $dados['descricao']
                    . "\n"
                    . "• Valor: "
                    . $this->moeda(
                        (float) $dados['valor']
                    )
                    . "\n"
                    . "• Parcelas: "
                    . (int) (
                        $dados['parcelas']
                        ?? 1
                    )
                    . "x\n"
                    . "• Data: "
                    . $dados['data_descricao']
                    . "\n\n"
                    . "Qual cartão você utilizou?\n"
                    . $cartoes
                    . "\n\n"
                    . "Responda apenas com o nome do cartão.";
            }

            return
                $this->montarPreviaCompraCartao([
                    'user_id' =>
                        auth()->id(),

                    'cartao_id' =>
                        $dados['cartao_id'],

                    'cartao_nome' =>
                        $dados['cartao_nome'],

                    'categoria_id' =>
                        $dados['categoria_id']
                        ?? null,

                    'categoria_nome' =>
                        $dados['categoria_nome']
                        ?? null,

                    'descricao' =>
                        $dados['descricao'],

                    'valor_total' =>
                        (float) $dados['valor'],

                    'data_compra' =>
                        $dados['data'],

                    'data_descricao' =>
                        $dados['data_descricao'],

                    'quantidade_parcelas' =>
                        (int) (
                            $dados['parcelas']
                            ?? 1
                        ),
                ]);
        }

        /*
         * Categoria ainda não existe entre as categorias ativas
         * deste usuário.
         */
        if (
            empty(
                $dados['categoria_id']
            )
        ) {

            return
                $resposta
                . "

"
                . "Antes de confirmar, preciso identificar uma "
                . "categoria já cadastrada para esta despesa."
                . "

"
                . "PRÉVIA DE TESTE — nenhum lançamento foi gravado.";
        }

        /*
         * Mais de uma conta ativa e nenhuma foi informada.
         */
        if (
            empty(
                $dados['conta_id']
            )
        ) {

            $contas =
                collect(
                    $dados['contas_disponiveis']
                    ?? []
                );

            $listaContas =
                $contas->isNotEmpty()
                    ? $contas
                        ->map(
                            fn ($nome) =>
                                '• ' . $nome
                        )
                        ->implode("
")
                    : '• Nenhuma conta ativa cadastrada';

            return
                $resposta
                . "

"
                . "Antes de confirmar, informe de qual conta "
                . "saiu o dinheiro. Suas contas ativas são:
"
                . $listaContas
                . "

"
                . "Exemplo: \"Paguei 63 de gasolina no Pix pela Nubank\"."
                . "

"
                . "PRÉVIA DE TESTE — nenhum lançamento foi gravado.";
        }

        /*
         * Guarda uma cópia mínima do rascunho na sessão.
         * Na confirmação, categoria, conta e forma de pagamento
         * serão validadas novamente antes de gravar.
         */
        session([
            'assistente_lancamento_despesa' => [
                'user_id' =>
                    auth()->id(),

                'situacao' =>
                    'paga',

                'categoria_id' =>
                    $dados['categoria_id'],

                'conta_id' =>
                    $dados['conta_id'],

                'forma_pagamento_id' =>
                    $dados['forma_pagamento_id']
                    ?? null,

                'descricao' =>
                    $dados['descricao'],

                'valor' =>
                    (float) $dados['valor'],

                'data' =>
                    $dados['data'],
            ],
        ]);

        return
            $resposta
            . "

"
            . "Dados localizados no seu cadastro."
            . "
"
            . "Para gravar, digite exatamente:"
            . "
"
            . "CONFIRMAR LANÇAMENTO"
            . "

"
            . "Para desistir, digite:"
            . "
"
            . "CANCELAR LANÇAMENTO"
            . "

"
            . "Ainda não foi gravado.";
    }

}