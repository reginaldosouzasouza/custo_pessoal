<?php

namespace App\Services;

use App\Models\Conta;
use App\Models\Despesa;
use App\Models\Fatura;
use App\Models\MovimentacaoConta;
use App\Models\Parcela;
use App\Models\Receita;
use App\Models\Recorrencia;
use Illuminate\Support\Str;

class AssistenteFinanceiroService
{
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
        $texto = $this->normalizar($pergunta);

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
    ): string {

        $hora =
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
                    "\n• E mais "
                    . ($itens->count() - 6)
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
         * Semanal: repete de 7 em 7 dias
         * usando data_inicio como referência.
         */
        if (
            $recorrencia->frequencia
            === 'semanal'
        ) {

            $data =
                $inicioRecorrencia
                    ->copy();

            while (
                $data->lt(
                    $inicioMes
                )
            ) {
                $data->addWeek();
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

                $data->addWeek();
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
                'Neste mês, você ainda não possui receitas recebidas.';
        }

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
}
