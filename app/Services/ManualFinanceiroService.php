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
                'a vencer',
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
                "Para cadastrar uma conta fixa:\n"
                . "1. Acesse Contas Fixas e clique em + Nova recorrência.\n"
                . "2. Informe a descrição da conta, categoria, forma de pagamento e o valor padrão.\n"
                . "3. Escolha a frequência: semanal, mensal, trimestral, semestral ou anual.\n"
                . "4. É essa frequência que informa ao sistema com que periodicidade a conta deve voltar a aparecer nas previsões.\n"
                . "5. Informe o dia de vencimento e a data de início.\n"
                . "6. Se a conta tiver prazo para terminar, informe também a data final.\n"
                . "7. Se a data final ficar em branco, a recorrência continuará sendo considerada por tempo indeterminado, enquanto estiver ativa.\n"
                . "8. Salve.\n\n"
                . "Exemplo: uma conta de luz mensal, com vencimento todo dia 15, iniciada em setembro e sem data final, aparecerá na Previsão de Despesas de setembro, outubro, novembro, dezembro e nos meses seguintes.\n\n"
                . "Se em algum momento essa conta deixar de existir, o recomendado é desativar a recorrência. Assim ela deixa de aparecer nas previsões futuras sem apagar o histórico.",

            'fatura' =>
                "Para pagar uma fatura:\n"
                . "1. Acesse Faturas.\n"
                . "2. Use o filtro para localizar a fatura que deseja pagar.\n"
                . "3. Clique em Pagar.\n"
                . "4. Selecione a conta/carteira de pagamento.\n"
                . "5. Informe ou confira a data do pagamento.\n"
                . "6. Confirme o pagamento.\n\n"
                . "Depois da confirmação, o valor da fatura passa a representar uma saída real de dinheiro e afeta o saldo da conta.",

            'previsao' =>
                "Para usar a Previsão de Despesas:\n"
                . "1. Acesse Previsão de Despesas.\n"
                . "2. Escolha o mês que deseja consultar.\n"
                . "3. Confira os compromissos previstos para o período.\n"
                . "4. A tela reúne despesas lançadas, contas recorrentes previstas e faturas com vencimento no mês.\n"
                . "5. Use essa visão antes de assumir novos compromissos.\n\n"
                . "Importante: Previsão de Despesas é compromisso do período. Despesas do mês mostra o que já foi efetivamente pago.",

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
                . "Use-o para acompanhar Receitas do mês, Despesas do mês, Previsão de Despesas, Saldo atual, A vencer, Cartão em aberto e Próximo mês.",

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
