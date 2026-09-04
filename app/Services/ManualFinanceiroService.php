<?php

namespace App\Services;

use Illuminate\Support\Str;

class ManualFinanceiroService
{
    public function ajudaInicial(): string
    {
        return
            "Você entrou no Manual do SGA Finanças.\n"
            . "Pergunte, por exemplo:\n"
            . "• Manual como cadastrar uma receita?\n"
            . "• Manual como funciona a Previsão de Despesas?\n"
            . "• Manual como fazer uma transferência?\n"
            . "• Manual como pagar uma fatura?\n"
            . "• Manual como cadastrar uma conta fixa?\n"
            . "• Manual como funciona uma compra no cartão?\n"
            . "• Manual para que serve Movimentações?\n"
            . "• Manual como cadastrar uma conta ou carteira?\n"
            . "• Manual posso lançar compra do cartão como despesa?\n"
            . "[[manual:"
            . route('manual-financeiro.index')
            . "#duvidas]]";
    }

    public function responderDireto(string $pergunta): string
    {
        $texto = $this->normalizar($pergunta);
        $topico = $this->detectarTopico($texto);

        if ($topico === null) {
            return
                "Não encontrei essa orientação no manual ainda.\n"
                . "Tente informar o assunto com mais detalhes, por exemplo:\n"
                . "• Manual como cadastrar uma receita?\n"
                . "• Manual como pagar uma fatura?\n"
                . "• Manual como cadastrar uma conta fixa?\n"
                . "[[manual:"
                . route('manual-financeiro.index')
                . "#duvidas]]";
        }

        return
            $this->respostaDoTopico($topico)
            . "\n[[manual:"
            . route('manual-financeiro.index')
            . '#'
            . $this->ancoraDoTopico($topico)
            . "]]";
    }

    private function detectarTopico(string $texto): ?string
    {
        $topicos = [
            'contas_a_pagar' => [
                'para que serve contas a pagar',
                'como funciona contas a pagar',
                'contas a pagar',
                'pagar conta',
                'pagar contas',
                'onde pagar contas',
                'como pagar uma recorrencia',
                'como pagar recorrencia',
            ],

            'duplicidade_cartao' => [
                'posso lancar compra do cartao como despesa',
                'lancar compra do cartao como despesa',
                'registrar cartao como despesa',
                'duplicidade',
                'compra no cartao reduz saldo',
                'cartao reduz saldo',
            ],

            'previsao' => [
                'como funciona a previsao de despesas',
                'previsao de despesas',
                'previsao',
                'comprometido no mes',
                'compromissos previstos',
            ],

            'transferencias' => [
                'como fazer uma transferencia',
                'transferencia',
                'transferencias',
                'transferir',
                'mudar dinheiro de conta',
            ],

            'recorrencias' => [
                'como cadastrar uma conta fixa',
                'conta fixa',
                'contas fixas',
                'recorrencia',
                'recorrencias',
                'despesa recorrente',
                'mensalidade',
            ],

            'compras_cartao' => [
                'como funciona uma compra no cartao',
                'compra no cartao',
                'compras no cartao',
                'compra parcelada',
                'parcelas do cartao',
            ],

            'fatura' => [
                'como pagar uma fatura',
                'pagar fatura',
                'pagamento da fatura',
                'fatura',
                'faturas',
            ],

            'movimentacoes' => [
                'para que serve movimentacoes',
                'para que serve movimentacao',
                'movimentacao',
                'movimentacoes',
                'historico financeiro',
                'auditoria',
                'estornada',
                'estorno',
            ],

            'contas' => [
                'como cadastrar uma conta ou carteira',
                'como cadastrar uma conta',
                'como cadastrar uma carteira',
                'conta e carteira',
                'contas e carteiras',
                'carteira',
                'saldo inicial',
                'conta bancaria',
            ],

            'receitas' => [
                'como cadastrar uma receita',
                'receita',
                'receitas',
                'dinheiro que entra',
                'recebimento',
            ],

            'despesas' => [
                'como cadastrar uma despesa',
                'despesa',
                'despesas',
                'gasto avulso',
                'conta para pagar',
            ],

            'cartao' => [
                'como cadastrar um cartao',
                'cartao',
                'cartoes',
                'limite do cartao',
                'fechamento do cartao',
            ],

            'dashboard' => [
                'dashboard',
                'resumo financeiro',
                'saldo atual',
                'despesas pagas do mes',
                'previsao de despesas',
                'cartao em aberto',
                'proximo mes',
            ],
        ];

        $melhorTopico = null;
        $melhorPontuacao = 0;

        foreach ($topicos as $topico => $termos) {
            $pontuacao = 0;

            foreach ($termos as $termo) {
                if (str_contains($texto, $termo)) {
                    $pontuacao += strlen($termo);
                }
            }

            if ($pontuacao > $melhorPontuacao) {
                $melhorPontuacao = $pontuacao;
                $melhorTopico = $topico;
            }
        }

        return $melhorTopico;
    }

    private function ancoraDoTopico(string $topico): string
    {
        return match ($topico) {
            'dashboard' => 'dashboard',
            'receitas' => 'duvida-receita',
            'despesas' => 'duvida-despesa',
            'contas' => 'duvida-conta',
            'transferencias' => 'duvida-transferencia',
            'recorrencias' => 'duvida-conta-fixa',
            'cartao' => 'cartoes',
            'compras_cartao' => 'duvida-compra-cartao',
            'fatura' => 'duvida-fatura',
            'previsao' => 'duvida-previsao',
            'contas_a_pagar' => 'duvida-contas-pagar',
            'movimentacoes' => 'duvida-movimentacoes',
            'duplicidade_cartao' => 'duvida-duplicidade-cartao',
            default => 'duvidas',
        };
    }

    private function respostaDoTopico(string $topico): string
    {
        return match ($topico) {
            'receitas' =>
                "Para cadastrar uma receita:\n"
                . "1. Acesse Receitas.\n"
                . "2. Clique em + Nova receita.\n"
                . "3. Informe descrição, categoria, valor e as datas solicitadas.\n"
                . "4. Salve a receita.\n"
                . "5. Quando o dinheiro realmente entrar, localize a receita e registre o recebimento.\n"
                . "6. Confirme a conta/carteira que recebeu o valor.\n\n"
                . "Atenção: enquanto a receita estiver pendente, ela ainda não entra em Receitas do mês.",

            'despesas' =>
                "Para cadastrar uma despesa:\n"
                . "1. Acesse Despesas.\n"
                . "2. Clique em + Nova despesa.\n"
                . "3. Informe categoria, descrição, valor, data da despesa, vencimento e situação.\n"
                . "4. Salve a despesa.\n"
                . "5. Quando pagar, localize a despesa e clique em Pagar.\n"
                . "6. Selecione a conta/carteira usada no pagamento, informe a data e confirme.\n\n"
                . "Enquanto estiver pendente, a despesa representa um compromisso. Depois do pagamento, passa a afetar o saldo e as Despesas do mês.",

            'contas' =>
                "Para cadastrar uma conta ou carteira:\n"
                . "1. Acesse Contas e Carteiras.\n"
                . "2. Clique em + Nova conta.\n"
                . "3. Informe o nome da conta/carteira e os demais dados solicitados.\n"
                . "4. Informe o saldo inicial somente se já existir dinheiro nessa conta no início do controle.\n"
                . "5. Salve.\n\n"
                . "Depois disso, o saldo deve ser alterado pelas movimentações do sistema, como recebimentos, pagamentos e transferências.",

            'transferencias' =>
                "Para fazer uma transferência:\n"
                . "1. Acesse Transferências.\n"
                . "2. Clique em + Nova transferência.\n"
                . "3. Selecione a conta de origem.\n"
                . "4. Selecione a conta de destino.\n"
                . "5. Informe o valor e a data.\n"
                . "6. Confirme a transferência.\n\n"
                . "A transferência não é receita nem despesa. O valor apenas sai de uma conta e entra em outra.",

            'recorrencias' =>
                "Para cadastrar uma conta fixa/recorrente:\n"
                . "1. Acesse Contas Fixas e clique em + Nova recorrência.\n"
                . "2. Informe a descrição, categoria, forma de pagamento e o valor padrão.\n"
                . "3. Escolha a frequência disponível: diária, a cada 3 dias, a cada 5 dias, semanal, mensal, trimestral, semestral ou anual.\n"
                . "4. Informe a data de início e, quando aplicável, o dia de vencimento.\n"
                . "5. Se a conta tiver prazo para terminar, informe a data final.\n"
                . "6. Se a data final ficar em branco, a recorrência continuará ativa por tempo indeterminado.\n"
                . "7. Salve.\n\n"
                . "A recorrência é a regra de repetição. Quando uma ocorrência for realmente paga, faça a baixa em Contas a Pagar. O sistema registra aquela ocorrência como despesa real sem encerrar a recorrência.\n\n"
                . "Se a conta deixar de existir, o recomendado é desativar a recorrência para preservar o histórico.",

            'fatura' =>
                "Para pagar uma fatura:\n"
                . "1. Acesse Faturas ou Contas a Pagar.\n"
                . "2. Localize a fatura desejada.\n"
                . "3. Clique em Pagar.\n"
                . "4. Selecione a conta/carteira de pagamento.\n"
                . "5. Informe ou confira a data do pagamento.\n"
                . "6. Confirme.\n\n"
                . "A tela Faturas permite consultar compromissos atuais e futuros. No Dashboard, o card Cartão em aberto mostra somente o saldo das faturas ainda não pagas que vencem no mês atual.",

            'previsao' =>
                "Para usar a Previsão de Despesas:\n"
                . "1. Acesse Previsão de Despesas.\n"
                . "2. Escolha o mês que deseja consultar.\n"
                . "3. Confira os compromissos ainda não pagos do período.\n"
                . "4. A tela reúne despesas pendentes, parcelas pendentes, recorrências previstas e faturas abertas/fechadas com vencimento no mês.\n"
                . "5. Faturas já pagas não entram na previsão.\n"
                . "6. Quando houver valor já pago em uma fatura, considera-se apenas o saldo restante.\n\n"
                . "Importante: Previsão de Despesas mostra o que ainda está comprometido.",

            'contas_a_pagar' =>
                "A tela Contas a Pagar centraliza as obrigações que ainda precisam ser quitadas.\n"
                . "Ela reúne despesas pendentes, parcelas pendentes, ocorrências de recorrências e faturas ainda não pagas.\n\n"
                . "Para usar:\n"
                . "1. Acesse Contas a Pagar.\n"
                . "2. Use os filtros de mês, vencimento, categoria, origem, descrição ou situação.\n"
                . "3. O campo Mês pode ficar em branco para uma consulta mais ampla.\n"
                . "4. Localize o compromisso e clique em Pagar.\n"
                . "5. Confira o valor, selecione a conta e informe a data do pagamento.\n"
                . "6. Confirme.\n\n"
                . "Nas recorrências, o pagamento quita somente aquela ocorrência. A regra recorrente continua ativa para os próximos vencimentos.",

            'compras_cartao' =>
                "Para registrar uma compra no cartão:\n"
                . "1. Acesse Compras no Cartão.\n"
                . "2. Clique em + Nova compra.\n"
                . "3. Selecione o cartão.\n"
                . "4. Informe descrição, categoria, valor total e data da compra.\n"
                . "5. Informe o número de parcelas.\n"
                . "6. Salve a compra.\n\n"
                . "O sistema distribui as parcelas nas competências/faturas correspondentes. A compra não reduz o saldo da conta naquele momento; a saída acontece quando a fatura for paga.",

            'movimentacoes' =>
                "A tela Movimentações serve para conferir o histórico financeiro das contas e carteiras.\n"
                . "Nela você pode verificar entradas, saídas, origem da movimentação e a conta envolvida.\n\n"
                . "Use Movimentações principalmente para conferência e auditoria. Se houver dúvida em um saldo, compare a movimentação com a receita, despesa, parcela, transferência ou fatura que a originou.",

            'duplicidade_cartao' =>
                "Não. Se a compra foi feita no cartão de crédito, não lance a mesma compra também como despesa comum.\n\n"
                . "Faça assim:\n"
                . "1. Registre a compra em Compras no Cartão.\n"
                . "2. O sistema leva a parcela para a fatura correspondente.\n"
                . "3. Quando a fatura for paga, registre o pagamento da fatura.\n\n"
                . "Se lançar a compra no cartão e também em Despesas, o mesmo gasto ficará duplicado.",

            'cartao' =>
                "Para cadastrar um cartão:\n"
                . "1. Acesse Cartões.\n"
                . "2. Clique em + Novo cartão.\n"
                . "3. Informe nome, limite, dia de fechamento e dia de vencimento.\n"
                . "4. Salve o cartão.\n\n"
                . "Depois disso, ele poderá ser usado nas compras e faturas.",

            'dashboard' =>
                "O Dashboard é o resumo financeiro do SGA Finanças. "
                . "Ele apresenta o realizado, o saldo disponível e os compromissos futuros. "
                . "Use-o para acompanhar Receitas do mês, Despesas Pagas do mês, Previsão de Despesas, Saldo atual, Cartão em aberto e Próximo mês. "
                . "O card Cartão em aberto considera somente faturas ainda não pagas com vencimento no mês atual.",

            default =>
                "Ainda não encontrei essa orientação no manual do SGA Finanças.",
        };
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
}
