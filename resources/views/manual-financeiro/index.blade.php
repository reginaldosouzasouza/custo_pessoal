@extends('layouts.custo-pessoal')

@section('content')
<style>
.manual-wrap{max-width:1050px;margin:0 auto;padding:18px}
.manual-hero{
    background:linear-gradient(135deg,#07345c,#0d6efd);color:#fff;
    border-radius:18px;padding:24px;margin-bottom:18px
}
.manual-hero h1{margin:0 0 6px;font-size:28px}
.manual-hero p{margin:0;opacity:.9}
.manual-grid{display:grid;grid-template-columns:260px 1fr;gap:18px;align-items:start}
.manual-indice{
    position:sticky;top:18px;background:#fff;border:1px solid #e5e7eb;
    border-radius:14px;padding:14px
}
.manual-indice strong{display:block;margin-bottom:9px;color:#0f172a}
.manual-indice a{
    display:block;padding:7px 9px;border-radius:8px;text-decoration:none;
    color:#334155;font-size:14px
}
.manual-indice a:hover{background:#eff6ff;color:#0d6efd}
.manual-conteudo{min-width:0}
.manual-secao{
    scroll-margin-top:18px;background:#fff;border:1px solid #e5e7eb;
    border-radius:14px;padding:20px;margin-bottom:16px
}
.manual-secao h2{margin:0 0 12px;color:#07345c;font-size:22px}
.manual-secao h3{margin:18px 0 8px;color:#0f172a;font-size:17px}
.manual-secao p{color:#334155;line-height:1.65}
.manual-secao ul{padding-left:20px;color:#334155;line-height:1.65}
.manual-alerta{
    background:#eff6ff;border-left:4px solid #0d6efd;padding:12px 14px;
    border-radius:8px;margin-top:12px;color:#1e3a5f
}
.manual-table-wrap{overflow-x:auto}
.manual-table{width:100%;border-collapse:collapse;min-width:700px}
.manual-table th,.manual-table td{
    border:1px solid #cbd5e1;padding:10px;text-align:left;vertical-align:top
}
.manual-table th{background:#07345c;color:#fff}
.manual-passos{counter-reset:passo;list-style:none;padding:0;margin:0}
.manual-passos li{
    counter-increment:passo;position:relative;padding:10px 10px 10px 42px;
    border-bottom:1px solid #eef2f7;color:#334155
}
.manual-passos li:before{
    content:counter(passo);position:absolute;left:4px;top:8px;width:27px;height:27px;
    border-radius:50%;display:flex;align-items:center;justify-content:center;
    background:#0d6efd;color:#fff;font-weight:700;font-size:12px
}
.manual-voltar{
    display:inline-block;margin-top:12px;text-decoration:none;color:#0d6efd;font-weight:600
}
@media(max-width:850px){
    .manual-wrap{padding:10px}
    .manual-grid{grid-template-columns:1fr}
    .manual-indice{position:static}
    .manual-indice .links{display:flex;gap:6px;overflow-x:auto;padding-bottom:3px}
    .manual-indice a{white-space:nowrap;border:1px solid #e2e8f0}
    .manual-hero{padding:18px;border-radius:14px}
    .manual-hero h1{font-size:23px}
    .manual-secao{padding:16px}
}
</style>

<div class="manual-wrap" id="inicio">
    <div class="manual-hero">
        <h1>Manual SGA Finanças</h1>
        <p>Guia de uso para parceiros — versão Setembro/2026.</p>
    </div>

    <div class="manual-grid">
        <aside class="manual-indice">
            <strong>Ir para</strong>
            <div class="links">
                <a href="#dashboard">Dashboard</a>
                <a href="#receitas">Receitas</a>
                <a href="#despesas">Despesas</a>
                <a href="#contas">Contas e Carteiras</a>
                <a href="#transferencias">Transferências</a>
                <a href="#recorrencias">Contas Recorrentes</a>
                <a href="#cartoes">Cartões</a>
                <a href="#compras-cartao">Compras no Cartão</a>
                <a href="#faturas">Faturas</a>
                <a href="#previsao">Previsão de Despesas</a>
                <a href="#contas-pagar">Contas a Pagar</a>
                <a href="#movimentacoes">Movimentações</a>
                <a href="#fluxo">Fluxo recomendado</a>
                <a href="#testes">Roteiro de testes</a>
                <a href="#duvidas">Dúvidas comuns</a>
            </div>
        </aside>

        <main class="manual-conteudo">
            <section class="manual-secao">
                <h2>Roteiro rápido de 10 minutos</h2>
                <ol class="manual-passos">
                    <li>Acesse o SGA Finanças com o usuário fornecido.</li>
                    <li>Confira o Dashboard e os principais indicadores do mês.</li>
                    <li>Cadastre ou confira uma Conta/Carteira.</li>
                    <li>Cadastre uma Receita e marque como recebida quando houver entrada real.</li>
                    <li>Cadastre uma Despesa e deixe-a pendente até o pagamento.</li>
                    <li>Cadastre uma conta recorrente, como internet, aluguel, mercado ou energia.</li>
                    <li>Cadastre um cartão e uma compra parcelada.</li>
                    <li>Abra Previsão de Despesas e confira os compromissos do mês.</li>
                    <li>Abra Contas a Pagar e faça a baixa das obrigações que forem pagas.</li>
                    <li>Confira Movimentações e envie feedback sobre dúvidas ou comportamentos inesperados.</li>
                </ol>
                <div class="manual-alerta">
                    Para testes, prefira dados fictícios ou valores aproximados.
                </div>
            </section>

            <section class="manual-secao" id="dashboard">
                <h2>1. Entendendo o Dashboard</h2>
                <p>O Dashboard é o resumo financeiro. Ele mostra quanto entrou e saiu, compromissos futuros e valores de cartão.</p>

                <div class="manual-table-wrap">
                    <table class="manual-table">
                        <thead>
                            <tr>
                                <th>Card</th>
                                <th>O que mostra</th>
                                <th>Regra principal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Receitas do mês</td><td>Total de receitas efetivamente recebidas no mês.</td><td>Receita pendente não entra até ser recebida.</td></tr>
                            <tr><td>Despesas Pagas do mês</td><td>Total que realmente saiu das contas no mês.</td><td>Só entra pagamento real de despesa, parcela ou fatura.</td></tr>
                            <tr><td>Previsão de despesas</td><td>Compromissos ainda não pagos com vencimento no mês atual.</td><td>Considera despesas pendentes, parcelas pendentes, recorrências previstas e faturas abertas/fechadas do mês.</td></tr>
                            <tr><td>Saldo atual</td><td>Soma do dinheiro disponível nas contas bancárias, digitais e carteiras ativas.</td><td>Saldo inicial + entradas - saídas.</td></tr>
                            <tr><td>Cartão em aberto</td><td>Saldo restante das faturas que vencem no mês atual e ainda não foram pagas.</td><td>Faturas futuras não entram neste card.</td></tr>
                            <tr><td>Próximo mês</td><td>Compromissos com vencimento no próximo mês.</td><td>Ajuda a antecipar despesas, parcelas, recorrências e faturas futuras.</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="manual-alerta">
                    <strong>Leitura correta:</strong> “Despesas do mês” é realizado. “Previsão de despesas” é compromisso do período. Por isso os dois valores podem ser diferentes.
                </div>
            </section>

            <section class="manual-secao" id="receitas">
                <h2>2. Receitas</h2>
                <p>Use esta área para registrar dinheiro que entra, como salário, rendas extras, reembolsos ou outros recebimentos.</p>
                <ul>
                    <li>Informe descrição, categoria, valor e datas conforme a tela.</li>
                    <li>Quando a receita for realmente recebida, marque-a como recebida.</li>
                    <li>O Dashboard só considera em “Receitas do mês” as receitas efetivamente recebidas.</li>
                </ul>
            </section>

            <section class="manual-secao" id="despesas">
                <h2>3. Despesas</h2>
                <p>Use esta área para registrar contas e gastos que não são compras de cartão.</p>
                <ul>
                    <li>Cadastre descrição, categoria, valor e vencimento.</li>
                    <li>Uma despesa pendente representa compromisso, mas ainda não é saída realizada.</li>
                    <li>Quando pagar, registre o pagamento na conta/carteira correta.</li>
                    <li>Depois do pagamento, a saída passa a aparecer em “Despesas do mês” e afeta o Saldo atual.</li>
                </ul>
                <div class="manual-alerta">
                    <strong>Evite duplicidade:</strong> se uma compra foi feita no cartão de crédito, registre-a no módulo de cartão. Não lance a mesma compra como despesa comum e também no cartão.
                </div>
            </section>

            <section class="manual-secao" id="contas">
                <h2>4. Contas e Carteiras</h2>
                <p>Aqui ficam os locais onde o dinheiro realmente existe: conta bancária, carteira em dinheiro ou outra conta que você queira controlar.</p>
                <ul>
                    <li>Use o saldo inicial apenas para representar o dinheiro existente no momento em que começar o controle.</li>
                    <li>Entradas e saídas posteriores devem ser registradas por receitas, pagamentos, transferências ou outros movimentos do sistema.</li>
                    <li>O Saldo atual do Dashboard é calculado a partir das contas ativas.</li>
                </ul>
            </section>

            <section class="manual-secao" id="transferencias">
                <h2>5. Transferências</h2>
                <p>Use Transferências quando o dinheiro apenas muda de lugar, por exemplo: Conta Corrente → Carteira ou Banco A → Banco B.</p>
                <div class="manual-alerta">
                    Uma transferência não é receita nem despesa. Ela apenas reduz uma conta e aumenta outra.
                </div>
            </section>

            <section class="manual-secao" id="recorrencias">
                <h2>6. Contas Fixas / Recorrências</h2>
                <p>
                    Use esta área para compromissos que se repetem, como internet, energia,
                    mensalidades, seguros e outros pagamentos periódicos.
                </p>
                <p>
                    No SGA Finanças, uma <strong>Conta Fixa</strong> é cadastrada como uma
                    <strong>recorrência</strong>. É a frequência escolhida que informa ao sistema
                    com que periodicidade essa conta deve voltar a aparecer nas previsões.
                </p>

                <ul>
                    <li>Frequências disponíveis incluem diária, a cada 3 dias, a cada 5 dias, semanal, mensal, trimestral, semestral e anual.</li>
                    <li>O valor pode ser fixo ou variável.</li>
                    <li>Defina o dia de vencimento e a data de início.</li>
                    <li>Se a conta tiver prazo para terminar, informe a data final.</li>
                    <li>Se a data final ficar em branco, a recorrência continuará sendo considerada por tempo indeterminado, enquanto estiver ativa.</li>
                    <li>Uma recorrência pode ser desativada sem ser apagada.</li>
                    <li>A Previsão de Despesas calcula as ocorrências conforme a frequência cadastrada, inclusive frequências baseadas em dias.</li>
                    <li>O pagamento de uma ocorrência recorrente é feito em <strong>Contas a Pagar</strong>, sem alterar a regra principal da recorrência.</li>
                </ul>

                <div class="manual-alerta">
                    <strong>Exemplo:</strong> uma conta de luz mensal, com vencimento todo dia 15,
                    iniciada em setembro e sem data final, aparecerá na Previsão de Despesas de
                    setembro, outubro, novembro, dezembro e nos meses seguintes.
                </div>

                <p>
                    Se em algum momento essa conta deixar de existir, o recomendado é
                    <strong>desativar a recorrência</strong>. Assim ela deixa de aparecer nas
                    previsões futuras sem apagar o histórico.
                </p>
            </section>

            <section class="manual-secao" id="cartoes">
                <h2>7. Cartões, Compras e Faturas</h2>

                <h3>7.1 Cadastro do cartão</h3>
                <ul>
                    <li>Informe nome do cartão, limite, dia de fechamento e dia de vencimento.</li>
                    <li>O cartão pode permanecer ativo enquanto estiver em uso.</li>
                </ul>

                <h3 id="compras-cartao">7.2 Compra no cartão</h3>
                <ul>
                    <li>Informe descrição da compra, categoria, valor total, número de parcelas e data da compra.</li>
                    <li>O sistema distribui as parcelas nas competências/faturas correspondentes.</li>
                    <li>Uma compra parcelada gera compromissos futuros, mas não reduz o saldo da conta naquele momento.</li>
                </ul>

                <h3 id="faturas">7.3 Faturas</h3>
                <ul>
                    <li>A fatura consolida as parcelas que pertencem a uma competência.</li>
                    <li>O card “Cartão em aberto” do Dashboard mostra somente o saldo restante das faturas que vencem no mês atual.</li>
                    <li>A tela Faturas apresenta vencimentos atuais e futuros e permite filtrar por ano, mês, cartão e situação.</li>
                    <li>Quando a fatura for paga, registre o pagamento usando a conta correta.</li>
                    <li>O pagamento da fatura é a saída real de dinheiro e passa a compor “Despesas do mês”.</li>
                </ul>

                <div class="manual-alerta">
                    <strong>Regra-chave:</strong> não some a compra do cartão e a fatura como duas despesas. O compromisso financeiro é representado pela fatura; a saída real acontece no pagamento da fatura.
                </div>
            </section>

            <section class="manual-secao" id="previsao">
                <h2>8. Previsão de Despesas</h2>
                <p>Esta tela responde à pergunta: “Quanto ainda tenho comprometido neste mês?”</p>
                <ul>
                    <li>Despesas pendentes com vencimento no mês.</li>
                    <li>Parcelas pendentes com vencimento no mês.</li>
                    <li>Contas recorrentes previstas para o mês.</li>
                    <li>Faturas abertas ou fechadas com vencimento no mês.</li>
                    <li>Faturas já pagas não entram mais na previsão.</li>
                    <li>Quando houver pagamento parcial de fatura, o sistema considera apenas o saldo restante.</li>
                    <li>O sistema evita contar novamente uma recorrência que já tenha sido materializada em despesa para a mesma data.</li>
                </ul>
                <div class="manual-alerta">
                    Consulte a Previsão de Despesas antes de assumir novos compromissos. Ela mostra o que ainda está previsto para o período, não o que já foi pago.
                </div>
            </section>

            <section class="manual-secao" id="contas-pagar">
                <h2>9. Contas a Pagar</h2>
                <p>
                    Esta é a tela central para consultar e pagar obrigações financeiras,
                    independentemente da origem do compromisso.
                </p>

                <h3>O que aparece nesta tela</h3>
                <ul>
                    <li>Despesas pendentes.</li>
                    <li>Parcelas pendentes.</li>
                    <li>Ocorrências previstas de contas recorrentes.</li>
                    <li>Faturas abertas ou fechadas ainda não pagas.</li>
                </ul>

                <h3>Filtros disponíveis</h3>
                <ul>
                    <li>Mês.</li>
                    <li>Data de vencimento.</li>
                    <li>Categoria.</li>
                    <li>Origem: Despesa, Recorrente, Parcela ou Cartão.</li>
                    <li>Descrição.</li>
                    <li>Situação.</li>
                </ul>

                <p>
                    O campo <strong>Mês</strong> pode ficar em branco. Nesse caso, despesas,
                    parcelas e faturas pendentes podem ser consultadas sem limitar a um único mês.
                    Para recorrências sem data final, o sistema trabalha com um horizonte futuro
                    controlado para evitar uma lista infinita.
                </p>

                <h3>Como pagar</h3>
                <ol>
                    <li>Localize o compromisso.</li>
                    <li>Clique em <strong>Pagar</strong>.</li>
                    <li>Confira o valor.</li>
                    <li>Selecione a conta utilizada.</li>
                    <li>Informe a forma de pagamento quando disponível.</li>
                    <li>Confira a data do pagamento.</li>
                    <li>Confirme.</li>
                </ol>

                <div class="manual-alerta">
                    Para recorrências, o pagamento gera uma despesa real referente somente àquela ocorrência.
                    A regra de recorrência continua ativa para os próximos vencimentos.
                </div>
            </section>

            <section class="manual-secao" id="movimentacoes">
                <h2>10. Movimentações</h2>
                <p>A tela de Movimentações é o histórico financeiro das contas e carteiras.</p>
                <ul>
                    <li>Use-a para auditoria e conferência.</li>
                    <li>Compare uma movimentação com a receita, despesa, parcela ou fatura que a originou.</li>
                    <li>Movimentações estornadas não devem compor os saldos atuais.</li>
                </ul>
            </section>

            <section class="manual-secao" id="fluxo">
                <h2>11. Fluxo recomendado de uso</h2>
                <ol class="manual-passos">
                    <li>Cadastre/valide as contas e carteiras — defina onde o dinheiro está.</li>
                    <li>Cadastre receitas e despesas — registre compromissos e entradas.</li>
                    <li>Cadastre recorrências — automatize compromissos que se repetem.</li>
                    <li>Cadastre cartão e compras — distribua corretamente as parcelas nas faturas.</li>
                    <li>Acompanhe a Previsão de Despesas — veja o que ainda compromete o mês.</li>
                    <li>Use Contas a Pagar — centralize e efetue as baixas das obrigações.</li>
                    <li>Confira Movimentações — valide a saída ou entrada registrada.</li>
                    <li>Confira o Dashboard — compare realizado, saldo e compromissos futuros.</li>
                </ol>
            </section>

            <section class="manual-secao" id="testes">
                <h2>12. Roteiro de Teste para Parceiros</h2>
                <ul>
                    <li><strong>Teste A - Receita:</strong> cadastre uma receita de R$ 1.000,00, marque como recebida e confirme o card.</li>
                    <li><strong>Teste B - Despesa:</strong> cadastre R$ 200,00, confira como compromisso e depois pague para validar saída e saldo.</li>
                    <li><strong>Teste C - Recorrência:</strong> cadastre Internet mensal, vencimento dia 10, e confira na Previsão.</li>
                    <li><strong>Teste D - Cartão:</strong> cadastre uma compra de R$ 600,00 em 3 parcelas e confira faturas futuras.</li>
                    <li><strong>Teste E - Transferência:</strong> transfira valor entre duas contas e confirme que só muda a distribuição do saldo.</li>
                    <li><strong>Teste F - Contas a Pagar:</strong> filtre por origem, categoria e situação; depois efetue um pagamento e confira a movimentação.</li>
                    <li><strong>Teste G - Próximo mês:</strong> confira se parcelas, recorrências e faturas futuras aparecem no mês correto.</li>
                </ul>

                <h3>Como enviar um bom feedback</h3>
                <ul>
                    <li>Informe a tela/módulo usado.</li>
                    <li>O que tentou fazer.</li>
                    <li>Qual resultado esperava.</li>
                    <li>O que aconteceu de fato.</li>
                    <li>Valor, data e situação usados no teste.</li>
                    <li>Print da tela, quando possível.</li>
                    <li>Se o problema ocorreu uma vez ou sempre.</li>
                </ul>
            </section>

            <section class="manual-secao" id="duvidas">
                <h2>13. Dúvidas comuns</h2>

                <h3 id="duvida-receita">Como cadastrar uma receita?</h3>
                <ol>
                    <li>Acesse <strong>Receitas</strong>.</li>
                    <li>Clique em <strong>+ Nova receita</strong>.</li>
                    <li>Informe descrição, categoria, valor e as datas solicitadas.</li>
                    <li>Salve a receita.</li>
                    <li>Quando o dinheiro realmente entrar, localize a receita e registre o recebimento.</li>
                    <li>Confirme a conta/carteira que recebeu o valor.</li>
                </ol>
                <div class="manual-alerta">
                    Enquanto a receita estiver pendente, ela ainda não entra em “Receitas do mês”.
                </div>

                <h3 id="duvida-previsao">Como funciona a Previsão de Despesas?</h3>
                <ol>
                    <li>Acesse <strong>Previsão de Despesas</strong>.</li>
                    <li>Escolha o mês que deseja consultar.</li>
                    <li>Confira os compromissos previstos para o período.</li>
                    <li>A tela reúne despesas pendentes, parcelas pendentes, contas recorrentes previstas e faturas abertas/fechadas com vencimento no mês.</li>
                    <li>Faturas pagas não entram na previsão.</li>
                    <li>Use essa visão antes de assumir novos compromissos.</li>
                </ol>
                <div class="manual-alerta">
                    “Previsão de Despesas” é compromisso do período. “Despesas do mês” mostra o que já foi efetivamente pago.
                </div>

                <h3 id="duvida-transferencia">Como fazer uma transferência?</h3>
                <ol>
                    <li>Acesse <strong>Transferências</strong>.</li>
                    <li>Clique em <strong>+ Nova transferência</strong>.</li>
                    <li>Selecione a conta de origem.</li>
                    <li>Selecione a conta de destino.</li>
                    <li>Informe o valor e a data.</li>
                    <li>Confirme a transferência.</li>
                </ol>
                <div class="manual-alerta">
                    Transferência não é receita nem despesa. O valor apenas sai de uma conta e entra em outra.
                </div>

                <h3 id="duvida-fatura">Como pagar uma fatura?</h3>
                <ol>
                    <li>Acesse <strong>Faturas</strong>.</li>
                    <li>Use o filtro para localizar a fatura que deseja pagar.</li>
                    <li>Clique em <strong>Pagar</strong>.</li>
                    <li>Selecione a conta/carteira de pagamento.</li>
                    <li>Informe ou confira a data do pagamento.</li>
                    <li>Confirme o pagamento.</li>
                </ol>
                <div class="manual-alerta">
                    Depois da confirmação, o valor da fatura passa a representar uma saída real de dinheiro e afeta o saldo da conta.
                </div>

                <h3 id="duvida-conta-fixa">Como cadastrar uma conta fixa?</h3>
                <ol>
                    <li>Acesse <strong>Contas Fixas</strong> e clique em <strong>+ Nova recorrência</strong>.</li>
                    <li>Informe a descrição da conta, categoria, forma de pagamento e o valor padrão.</li>
                    <li>Escolha a frequência disponível, como diária, a cada 3 dias, a cada 5 dias, semanal, mensal, trimestral, semestral ou anual.</li>
                    <li>É essa frequência que informa ao sistema com que periodicidade a conta deve voltar a aparecer nas previsões.</li>
                    <li>Informe o dia de vencimento e a data de início.</li>
                    <li>Se a conta tiver prazo para terminar, informe também a data final.</li>
                    <li>Se a data final ficar em branco, a recorrência continuará sendo considerada por tempo indeterminado, enquanto estiver ativa.</li>
                    <li>Salve.</li>
                </ol>

                <div class="manual-alerta">
                    <strong>Exemplo:</strong> uma conta de luz mensal, com vencimento todo dia 15,
                    iniciada em setembro e sem data final, aparecerá na Previsão de Despesas de
                    setembro, outubro, novembro, dezembro e nos meses seguintes.
                </div>

                <p>
                    Se em algum momento essa conta deixar de existir, o recomendado é
                    <strong>desativar a recorrência</strong>. Assim ela deixa de aparecer nas
                    previsões futuras sem apagar o histórico.
                </p>

                <h3 id="duvida-compra-cartao">Como funciona uma compra no cartão?</h3>
                <ol>
                    <li>Acesse <strong>Compras no Cartão</strong>.</li>
                    <li>Clique em <strong>+ Nova compra</strong>.</li>
                    <li>Selecione o cartão.</li>
                    <li>Informe descrição, categoria, valor total e data da compra.</li>
                    <li>Informe o número de parcelas.</li>
                    <li>Salve a compra.</li>
                </ol>
                <div class="manual-alerta">
                    O sistema distribui as parcelas nas faturas correspondentes. A saída real de dinheiro acontece quando a fatura é paga.
                </div>

                <h3 id="duvida-contas-pagar">Para que serve Contas a Pagar?</h3>
                <p>
                    A tela <strong>Contas a Pagar</strong> reúne em um único lugar despesas,
                    parcelas, recorrências e faturas que ainda precisam ser pagas.
                </p>
                <ol>
                    <li>Acesse <strong>Contas a Pagar</strong>.</li>
                    <li>Use os filtros para localizar o compromisso.</li>
                    <li>Clique em <strong>Pagar</strong>.</li>
                    <li>Selecione a conta utilizada e informe a data do pagamento.</li>
                    <li>Confirme e depois confira a saída em <strong>Movimentações</strong>.</li>
                </ol>
                <div class="manual-alerta">
                    Para uma conta recorrente, o pagamento vale somente para aquela ocorrência e não encerra a recorrência inteira.
                </div>

                <h3 id="duvida-movimentacoes">Para que serve Movimentações?</h3>
                <p>
                    A tela <strong>Movimentações</strong> serve para conferir o histórico financeiro das contas e carteiras.
                    Nela você pode verificar entradas, saídas, origem da movimentação e a conta envolvida.
                </p>
                <p>
                    Use-a principalmente para conferência e auditoria. Se houver dúvida em um saldo,
                    compare a movimentação com a receita, despesa, parcela, transferência ou fatura que a originou.
                </p>

                <h3 id="duvida-conta">Como cadastrar uma conta ou carteira?</h3>
                <ol>
                    <li>Acesse <strong>Contas e Carteiras</strong>.</li>
                    <li>Clique em <strong>+ Nova conta</strong>.</li>
                    <li>Informe o nome e os demais dados solicitados.</li>
                    <li>Informe saldo inicial somente se já existir dinheiro nessa conta no início do controle.</li>
                    <li>Salve.</li>
                </ol>

                <h3 id="duvida-duplicidade-cartao">Posso lançar uma compra do cartão como despesa?</h3>
                <p><strong>Não.</strong> Se a compra foi feita no cartão de crédito, não lance a mesma compra também como despesa comum.</p>
                <ol>
                    <li>Registre a compra em <strong>Compras no Cartão</strong>.</li>
                    <li>O sistema leva a parcela para a fatura correspondente.</li>
                    <li>Quando a fatura for paga, registre o pagamento da fatura.</li>
                </ol>
                <div class="manual-alerta">
                    Se lançar a compra no cartão e também em Despesas, o mesmo gasto ficará duplicado.
                </div>

                <h3>Por que uma despesa pendente não aparece em “Despesas do mês”?</h3>
                <p>Porque esse card representa dinheiro que realmente saiu. A pendência aparece como compromisso/previsão até ser paga.</p>

                <h3>Por que uma compra no cartão não reduz meu saldo imediatamente?</h3>
                <p>Porque o dinheiro sai da conta no pagamento da fatura, e não na data da compra.</p>

                <h3>Previsão de despesas e Cartão em aberto podem mostrar valores diferentes?</h3>
                <p>Sim. A Previsão reúne todos os compromissos ainda não pagos do mês consultado. Já o card Cartão em aberto mostra somente o saldo restante das faturas que vencem no mês atual.</p>

                <h3>Posso apagar uma recorrência que não uso mais?</h3>
                <p>O caminho mais seguro é desativá-la, preservando o histórico e evitando projeções futuras.</p>

                <h3>Transferência entre minhas contas é despesa?</h3>
                <p>Não. É apenas movimentação interna entre contas.</p>

                <a class="manual-voltar" href="#inicio">↑ Voltar ao início</a>
            </section>
        </main>
    </div>
</div>
@endsection
