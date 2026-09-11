<style>
.sga-assistente-botao{
    position:fixed;
    right:22px;
    bottom:22px;
    width:62px;
    height:62px;
    border-radius:50%;
    background:#fff;
    padding:0;
    overflow:hidden;
    cursor:pointer;
    z-index:1500;
    border:3px solid #0d6efd;
    box-shadow:0 10px 30px rgba(13,110,253,.30);
    animation:pulseAssistente 1.8s infinite;
    transition:transform .2s ease;
}
.sga-assistente-botao img{
    width:100%;
    height:100%;
    object-fit:cover;
    display:block;
}
.sga-assistente-botao:hover{transform:scale(1.08)}
.sga-assistente-identidade{
    display:flex;align-items:center;gap:10px;min-width:0
}
.sga-assistente-avatar{
    width:42px;height:42px;border-radius:50%;object-fit:cover;flex-shrink:0;
    background:#fff;border:2px solid rgba(255,255,255,.9)
}
.sga-assistente-textos{min-width:0}
@keyframes pulseAssistente{
    0%{transform:scale(1);box-shadow:0 0 0 0 rgba(13,110,253,.45)}
    50%{transform:scale(1.06);box-shadow:0 0 0 12px rgba(13,110,253,0)}
    100%{transform:scale(1);box-shadow:0 0 0 0 rgba(13,110,253,0)}
}
.sga-assistente-painel{
    position:fixed;right:22px;bottom:92px;width:min(390px,calc(100vw - 30px));
    height:560px;max-height:calc(100dvh - 125px);display:none;flex-direction:column;
    background:#fff;border:1px solid #e5e7eb;border-radius:18px;overflow:hidden;
    z-index:1500;box-shadow:0 20px 55px rgba(15,23,42,.22)
}
.sga-assistente-painel.aberto{display:flex}
.sga-assistente-topo{
    padding:15px 16px;background:linear-gradient(135deg,#07345c,#0d6efd);
    color:#fff;display:flex;align-items:center;justify-content:space-between;gap:12px;
    flex-shrink:0
}
.sga-assistente-titulo{font-weight:700;font-size:15px}
.sga-assistente-subtitulo{margin-top:2px;font-size:11px;opacity:.82}
.sga-assistente-fechar{
    border:0;background:rgba(255,255,255,.12);color:#fff;width:34px;height:34px;
    border-radius:8px;cursor:pointer;font-size:20px
}
.sga-assistente-mensagens{
    flex:1;overflow-y:auto;padding:14px;background:#f8fafc;
    -webkit-overflow-scrolling:touch
}
.sga-assistente-msg{
    max-width:86%;padding:10px 12px;margin-bottom:10px;border-radius:12px;
    font-size:13px;line-height:1.4;white-space:pre-wrap;word-break:break-word
}
.sga-assistente-msg.bot{
    background:#fff;border:1px solid #e5e7eb;color:#374151;border-bottom-left-radius:4px
}
.sga-assistente-msg.usuario{
    margin-left:auto;background:#0d6efd;color:#fff;border-bottom-right-radius:4px
}
.sga-assistente-manual-link{
    display:inline-flex;align-items:center;gap:6px;margin-top:9px;padding:7px 10px;
    border-radius:9px;background:#eff6ff;border:1px solid #bfdbfe;color:#0d6efd;
    font-weight:600;text-decoration:none;white-space:normal
}
.sga-assistente-manual-link:hover{background:#dbeafe}

.sga-assistente-acoes{
    display:flex;gap:8px;margin-top:10px;flex-wrap:wrap
}
.sga-assistente-acao{
    border:0;border-radius:9px;padding:8px 11px;font-size:12px;font-weight:700;
    cursor:pointer;transition:.15s ease
}
.sga-assistente-acao:disabled{
    opacity:.55;cursor:wait
}
.sga-assistente-acao.confirmar{
    background:#16a34a;color:#fff
}
.sga-assistente-acao.confirmar:hover{
    background:#15803d
}
.sga-assistente-acao.cancelar{
    background:#fff;color:#b91c1c;border:1px solid #fecaca
}
.sga-assistente-acao.cancelar:hover{
    background:#fef2f2
}

.sga-assistente-atalhos{
    display:flex;gap:7px;overflow-x:auto;padding:10px 12px;background:#fff;
    border-top:1px solid #eef0f3;scrollbar-width:none;flex-shrink:0
}
.sga-assistente-atalhos::-webkit-scrollbar{display:none}
.sga-assistente-atalho{
    white-space:nowrap;border:1px solid #dbe3ec;background:#fff;color:#334155;
    border-radius:999px;padding:7px 10px;font-size:11px;cursor:pointer
}
.sga-assistente-form{
    display:flex;gap:8px;padding:12px;background:#fff;border-top:1px solid #e5e7eb;
    flex-shrink:0
}
.sga-assistente-input{
    flex:1;min-width:0;min-height:42px;border:1px solid #d6dde6;border-radius:10px;
    padding:9px 11px;font:inherit;font-size:13px;outline:0
}
.sga-assistente-input:focus{border-color:#0d6efd}
.sga-assistente-microfone{
    width:42px;min-width:42px;height:42px;border:1px solid #d6dde6;
    border-radius:10px;background:#fff;color:#334155;cursor:pointer;
    font-size:18px;display:inline-flex;align-items:center;justify-content:center
}
.sga-assistente-microfone.ouvindo{
    background:#fee2e2;border-color:#fca5a5;color:#b91c1c
}
.sga-assistente-enviar{
    min-width:70px;border:0;border-radius:10px;padding:0 13px;background:#0d6efd;
    color:#fff;font-weight:600;cursor:pointer
}
.sga-assistente-enviar:disabled{opacity:.6;cursor:wait}

.sga-assistente-botao {
    max-width: 62px;
    max-height: 62px;
}

.sga-assistente-painel {
    box-sizing: border-box;
}

@media (max-width: 768px) {

    html,
    body {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
    }

    /* Botão sempre preso dentro da tela */
    .sga-assistente-botao {
        position: fixed !important;
        right: 14px !important;
        bottom: calc(14px + env(safe-area-inset-bottom)) !important;

        width: 58px;
        height: 58px;

        z-index: 99999 !important;

        margin: 0 !important;
        transform: none;
    }

    /*
     * No celular o assistente vira uma tela completa.
     */
    .sga-assistente-painel {
        position: fixed !important;

        top: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        left: 0 !important;

        width: 100vw !important;
        max-width: 100vw !important;

        height: 100vh;
        height: 100dvh;

        max-height: none !important;

        margin: 0 !important;

        border: 0;
        border-radius: 0;

        z-index: 100000 !important;

        background: #ffffff;
    }

    .sga-assistente-painel.aberto {
        display: flex;
    }

    .sga-assistente-topo {
        padding-top: max(
            15px,
            env(safe-area-inset-top)
        );

        min-height: 68px;
    }

    .sga-assistente-mensagens {
        flex: 1;
        min-height: 0;

        padding: 14px;

        overflow-y: auto;
        overflow-x: hidden;

        -webkit-overflow-scrolling: touch;
    }

    .sga-assistente-atalhos {
        flex-shrink: 0;
    }

    .sga-assistente-form {
        flex-shrink: 0;

        padding-bottom: max(
            12px,
            env(safe-area-inset-bottom)
        );
    }

    .sga-assistente-input {
        min-width: 0;
    }

    .sga-assistente-acoes {
        flex-direction: column;
    }

    .sga-assistente-acao {
        width: 100%;
    }
}
</style>

<button
    type="button"
    class="sga-assistente-botao"
    id="sgaAssistenteBotao"
    aria-label="Abrir assistente financeiro"
    title="Assistente SGA Finanças"
>
    <img
        src="{{ asset('images/assistente-financeiro.png') }}"
        alt="Assistente SGA Finanças"
    >
</button>

<section
    class="sga-assistente-painel"
    id="sgaAssistentePainel"
    aria-label="Assistente SGA Finanças"
>
    <div class="sga-assistente-topo">
        <div class="sga-assistente-identidade">
            <img
                src="{{ asset('images/assistente-financeiro.png') }}"
                alt="Assistente SGA Finanças"
                class="sga-assistente-avatar"
            >

            <div class="sga-assistente-textos">
                <div class="sga-assistente-titulo">Assistente SGA Finanças</div>
                <div class="sga-assistente-subtitulo">Seu assistente inteligente financeiro</div>
            </div>
        </div>

        <button
            type="button"
            class="sga-assistente-fechar"
            id="sgaAssistenteFechar"
            aria-label="Fechar assistente"
        >×</button>
    </div>

    <div class="sga-assistente-mensagens" id="sgaAssistenteMensagens">
        <div class="sga-assistente-msg bot">
            Olá! Posso responder sobre gastos do mês, pendências, atrasos,
            próximos vencimentos, categorias e também sobre o Manual do sistema.
        </div>
    </div>

    <div class="sga-assistente-atalhos">
        <button type="button" class="sga-assistente-atalho"
            data-pergunta="Quanto gastei este mês?">Gastos do mês</button>

        <button type="button" class="sga-assistente-atalho"
            data-pergunta="Quanto tenho pendente?">Pendentes</button>

        <button type="button" class="sga-assistente-atalho"
            data-pergunta="Tenho despesas atrasadas?">Atrasadas</button>

        <button type="button" class="sga-assistente-atalho"
            data-pergunta="Quais são os próximos vencimentos?">Próximos 7 dias</button>

        <button type="button" class="sga-assistente-atalho"
            data-pergunta="Qual categoria teve o maior gasto?">Maior categoria</button>

        <button type="button" class="sga-assistente-atalho"
            data-pergunta="Manual">Manual</button>
    </div>

    <form class="sga-assistente-form" id="sgaAssistenteForm">
        <input
            type="text"
            class="sga-assistente-input"
            id="sgaAssistenteInput"
            maxlength="500"
            autocomplete="off"
            placeholder="Digite ou fale sua pergunta..."
        >

        <button
            type="button"
            class="sga-assistente-microfone"
            id="sgaAssistenteMicrofone"
            aria-label="Falar pergunta"
            title="Falar pergunta"
        >🎤</button>

        <button
            type="submit"
            class="sga-assistente-enviar"
            id="sgaAssistenteEnviar"
        >Enviar</button>
    </form>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const botao = document.getElementById('sgaAssistenteBotao');
    const painel = document.getElementById('sgaAssistentePainel');
    const fechar = document.getElementById('sgaAssistenteFechar');
    const form = document.getElementById('sgaAssistenteForm');
    const input = document.getElementById('sgaAssistenteInput');
    const enviar = document.getElementById('sgaAssistenteEnviar');
    const microfone = document.getElementById('sgaAssistenteMicrofone');
    const mensagens = document.getElementById('sgaAssistenteMensagens');

    const token = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    function abrirPainel() {
        painel?.classList.add('aberto');

        setTimeout(function () {
            input?.focus();
        }, 100);
    }

    function fecharPainel() {
        painel?.classList.remove('aberto');
    }

    function limparInstrucoesConfirmacao(texto) {
        return texto
            .replace(
                /Dados localizados no seu cadastro\.\s*Para gravar, digite exatamente:\s*CONFIRMAR LANÇAMENTO\s*Para desistir, digite:\s*CANCELAR LANÇAMENTO\s*Ainda não foi gravado\./iu,
                'Dados localizados no seu cadastro.\nConfira os dados acima e escolha uma opção:'
            )
            .trim();
    }

    function adicionarMensagem(
        texto,
        tipo,
        manualUrl = null,
        mostrarAcoesLancamento = false
    ) {
        const elemento = document.createElement('div');

        elemento.className =
            'sga-assistente-msg ' + tipo;

        const conteudo =
            document.createElement('div');

        conteudo.textContent =
            texto;

        elemento.appendChild(
            conteudo
        );

        if (manualUrl) {
            const link =
                document.createElement('a');

            link.className =
                'sga-assistente-manual-link';

            link.href =
                manualUrl;

            link.target =
                '_blank';

            link.rel =
                'noopener';

            link.textContent =
                '📖 Abrir esta parte no Manual';

            elemento.appendChild(
                link
            );
        }

        if (
            mostrarAcoesLancamento
            && tipo === 'bot'
        ) {
            const acoes =
                document.createElement('div');

            acoes.className =
                'sga-assistente-acoes';

            const confirmar =
                document.createElement('button');

            confirmar.type =
                'button';

            confirmar.className =
                'sga-assistente-acao confirmar';

            confirmar.textContent =
                '✓ Confirmar lançamento';

            const cancelar =
                document.createElement('button');

            cancelar.type =
                'button';

            cancelar.className =
                'sga-assistente-acao cancelar';

            cancelar.textContent =
                'Cancelar';

            function bloquearAcoes() {
                confirmar.disabled =
                    true;

                cancelar.disabled =
                    true;
            }

            confirmar.addEventListener(
                'click',
                function () {

                    bloquearAcoes();

                    perguntar(
                        'CONFIRMAR LANÇAMENTO',
                        true
                    );
                }
            );

            cancelar.addEventListener(
                'click',
                function () {

                    bloquearAcoes();

                    perguntar(
                        'CANCELAR LANÇAMENTO',
                        true
                    );
                }
            );

            acoes.appendChild(
                confirmar
            );

            acoes.appendChild(
                cancelar
            );

            elemento.appendChild(
                acoes
            );
        }

        mensagens.appendChild(
            elemento
        );

        mensagens.scrollTop =
            mensagens.scrollHeight;
    }

    function extrairLinkManual(texto) {
        const regex =
            /\[\[manual:(.+?)\]\]/;

        const encontrado =
            texto.match(regex);

        if (!encontrado) {
            return {
                texto: texto,
                url: null
            };
        }

        return {
            texto:
                texto
                    .replace(
                        regex,
                        ''
                    )
                    .trim(),

            url:
                encontrado[1]
        };
    }

    async function perguntar(
        pergunta,
        comandoInterno = false
    ) {
        const texto =
            pergunta.trim();

        if (!texto) {
            return;
        }

        /*
         * Nos botões de confirmação/cancelamento,
         * exibimos uma mensagem amigável em vez do
         * comando técnico em caixa alta.
         */
        if (comandoInterno) {
            adicionarMensagem(
                texto === 'CONFIRMAR LANÇAMENTO'
                    ? 'Confirmar lançamento'
                    : 'Cancelar lançamento',
                'usuario'
            );
        } else {
            adicionarMensagem(
                texto,
                'usuario'
            );
        }

        input.value = '';

        enviar.disabled =
            true;

        enviar.textContent =
            '...';

        try {
            const resposta =
                await fetch(
                    @json(route('assistente-financeiro.perguntar')),
                    {
                        method: 'POST',

                        headers: {
                            'Content-Type':
                                'application/json',

                            'Accept':
                                'application/json',

                            'X-CSRF-TOKEN':
                                token
                        },

                        body:
                            JSON.stringify({
                                pergunta:
                                    texto
                            })
                    }
                );

            if (!resposta.ok) {
                throw new Error(
                    'Falha ao consultar o assistente.'
                );
            }

            const dados =
                await resposta.json();

            const respostaBot =
                dados.resposta
                ?? 'Não consegui responder agora.';

            const temConfirmacao =
                respostaBot.includes(
                    'CONFIRMAR LANÇAMENTO'
                )
                &&
                respostaBot.includes(
                    'CANCELAR LANÇAMENTO'
                );

            const manual =
                extrairLinkManual(
                    respostaBot
                );

            const textoExibido =
                temConfirmacao
                    ? limparInstrucoesConfirmacao(
                        manual.texto
                    )
                    : manual.texto;

            adicionarMensagem(
                textoExibido,
                'bot',
                manual.url,
                temConfirmacao
            );

        } catch (erro) {

            adicionarMensagem(
                'Não consegui consultar seus dados agora. Tente novamente.',
                'bot'
            );

        } finally {

            enviar.disabled =
                false;

            enviar.textContent =
                'Enviar';

            input.focus();
        }
    }

    const SpeechRecognition =
        window.SpeechRecognition
        || window.webkitSpeechRecognition;

    if (
        SpeechRecognition
        && microfone
    ) {
        const reconhecimento =
            new SpeechRecognition();

        reconhecimento.lang =
            'pt-BR';

        reconhecimento.interimResults =
            false;

        reconhecimento.continuous =
            false;

        microfone.addEventListener(
            'click',
            async function () {

                try {

                    /*
                    * Primeiro solicita permissão para usar
                    * o microfone do aparelho.
                    */
                    if (
                        navigator.mediaDevices
                        && navigator.mediaDevices.getUserMedia
                    ) {
                        const stream =
                            await navigator.mediaDevices.getUserMedia({
                                audio: true
                            });

                        /*
                        * A permissão já foi concedida.
                        * Não precisamos manter o áudio aberto.
                        */
                        stream.getTracks().forEach(
                            track => track.stop()
                        );
                    }

                    reconhecimento.start();

                    microfone.classList.add(
                        'ouvindo'
                    );

                    microfone.textContent =
                        '●';

                } catch (erro) {

                    console.error(
                        'Erro ao iniciar microfone:',
                        erro
                    );

                    alert(
                        'Não foi possível acessar o microfone. ' +
                        'Verifique se a permissão de microfone está liberada para este site.'
                    );
                }
            }
        );




        reconhecimento.addEventListener(
            'result',
            function (event) {

                const texto =
                    event
                        .results[0][0]
                        .transcript;

                input.value =
                    texto;

                input.focus();
            }
        );

        reconhecimento.addEventListener(
            'end',
            function () {

                microfone.classList.remove(
                    'ouvindo'
                );

                microfone.textContent =
                    '🎤';
            }
        );

        reconhecimento.addEventListener(
            'error',
            function (event) {

                microfone.classList.remove(
                    'ouvindo'
                );

                microfone.textContent =
                    '🎤';

                console.error(
                    'Erro reconhecimento de voz:',
                    event.error
                );

                if (
                    event.error === 'not-allowed'
                    || event.error === 'service-not-allowed'
                ) {
                    alert(
                        'A permissão para usar o microfone foi bloqueada. ' +
                        'Libere o microfone nas permissões do navegador.'
                    );
                }
            }
        );

    } else if (microfone) {

        microfone.style.display =
            'none';
    }

    botao?.addEventListener(
        'click',
        abrirPainel
    );

    fechar?.addEventListener(
        'click',
        fecharPainel
    );

    form?.addEventListener(
        'submit',
        function (event) {

            event.preventDefault();

            perguntar(
                input.value
            );
        }
    );

    document
        .querySelectorAll(
            '.sga-assistente-atalho'
        )
        .forEach(
            function (atalho) {

                atalho.addEventListener(
                    'click',
                    function () {

                        perguntar(
                            this.dataset.pergunta
                            ?? ''
                        );
                    }
                );
            }
        );
});
</script>
