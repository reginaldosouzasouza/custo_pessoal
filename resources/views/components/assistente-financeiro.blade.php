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
    animation: pulseAssistente 1.8s infinite;
    transition: transform .2s ease;
}

.sga-assistente-botao img{
    width:100%;
    height:100%;
    object-fit:cover;
    display:block;
}

.sga-assistente-botao:hover{
    transform: scale(1.08);
}

.sga-assistente-identidade{
    display:flex;
    align-items:center;
    gap:10px;
    min-width:0;
}

.sga-assistente-avatar{
    width:42px;
    height:42px;
    border-radius:50%;
    object-fit:cover;
    flex-shrink:0;
    background:#fff;
    border:2px solid rgba(255,255,255,.9);
}

.sga-assistente-textos{
    min-width:0;
}
.sga-assistente-painel{
    position:fixed;right:22px;bottom:92px;width:min(390px,calc(100vw - 30px));
    height:560px;max-height:calc(100dvh - 125px);display:none;flex-direction:column;
    background:#fff;border:1px solid #e5e7eb;border-radius:18px;overflow:hidden;
    z-index:1500;box-shadow:0 20px 55px rgba(15,23,42,.22)
}
.sga-assistente-painel.aberto{display:flex}
.sga-assistente-painel *{box-sizing:border-box}
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


@keyframes pulseAssistente {
    0% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.45);
    }
    50% {
        transform: scale(1.06);
        box-shadow: 0 0 0 12px rgba(13, 110, 253, 0);
    }
    100% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(13, 110, 253, 0);
    }
}

.sga-assistente-enviar:disabled{opacity:.6;cursor:wait}

@media(max-width:768px){
    .sga-assistente-botao{right:15px;bottom:15px}
    .sga-assistente-painel{
        inset:0;width:100%;height:100dvh;max-height:none;border:0;border-radius:0
    }
    .sga-assistente-topo{padding-top:max(15px,env(safe-area-inset-top))}
    .sga-assistente-form{padding-bottom:max(12px,env(safe-area-inset-bottom))}
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

            <div class="sga-assistente-titulo">
                Assistente SGA Finanças
            </div>

            <div class="sga-assistente-subtitulo">
                Seu assistente inteligente financeiro
            </div>

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

            Olá! Eu sou o Assistente SGA Finanças. Posso ajudar com gastos do mês,
            pendências, atrasos, próximos vencimentos, categorias e também com o Manual do sistema.
            Pode perguntar com áudio, ou digitar se preferir, ou escolher uma das opções abaixo.
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

    function adicionarMensagem(texto, tipo, manualUrl = null) {
        const elemento = document.createElement('div');
        elemento.className = 'sga-assistente-msg ' + tipo;

        const conteudo = document.createElement('div');
        conteudo.textContent = texto;
        elemento.appendChild(conteudo);

        if (manualUrl) {
            const link = document.createElement('a');
            link.className = 'sga-assistente-manual-link';
            link.href = manualUrl;
            link.target = '_blank';
            link.rel = 'noopener';
            link.textContent = '📖 Abrir esta parte no Manual';
            elemento.appendChild(link);
        }

        mensagens.appendChild(elemento);
        mensagens.scrollTop = mensagens.scrollHeight;
    }

    function extrairLinkManual(texto) {
        const regex = /\[\[manual:(.+?)\]\]/;
        const encontrado = texto.match(regex);

        if (!encontrado) {
            return {
                texto: texto,
                url: null
            };
        }

        return {
            texto: texto.replace(regex, '').trim(),
            url: encontrado[1]
        };
    }

    async function perguntar(pergunta) {
        const texto = pergunta.trim();

        if (!texto) {
            return;
        }

        adicionarMensagem(texto, 'usuario');
        input.value = '';
        enviar.disabled = true;
        enviar.textContent = '...';

        try {
            const resposta = await fetch(
                @json(route('assistente-financeiro.perguntar')),
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({
                        pergunta: texto
                    })
                }
            );

            if (!resposta.ok) {
                throw new Error('Falha ao consultar o assistente.');
            }

            const dados = await resposta.json();
            const respostaBot =
                dados.resposta ?? 'Não consegui responder agora.';

            const manual =
                extrairLinkManual(respostaBot);

            adicionarMensagem(
                manual.texto,
                'bot',
                manual.url
            );
        } catch (erro) {
            adicionarMensagem(
                'Não consegui consultar seus dados agora. Tente novamente.',
                'bot'
            );
        } finally {
            enviar.disabled = false;
            enviar.textContent = 'Enviar';
            input.focus();
        }
    }

    const SpeechRecognition =
        window.SpeechRecognition
        || window.webkitSpeechRecognition;

    if (SpeechRecognition && microfone) {
        const reconhecimento = new SpeechRecognition();

        reconhecimento.lang = 'pt-BR';
        reconhecimento.interimResults = false;
        reconhecimento.continuous = false;

        microfone.addEventListener('click', function () {
            try {
                reconhecimento.start();
                microfone.classList.add('ouvindo');
                microfone.textContent = '●';
            } catch (erro) {
            }
        });

        reconhecimento.addEventListener('result', function (event) {
            const texto = event.results[0][0].transcript;
            input.value = texto;
            input.focus();
        });

        reconhecimento.addEventListener('end', function () {
            microfone.classList.remove('ouvindo');
            microfone.textContent = '🎤';
        });

        reconhecimento.addEventListener('error', function () {
            microfone.classList.remove('ouvindo');
            microfone.textContent = '🎤';
        });
    } else if (microfone) {
        microfone.style.display = 'none';
    }

    botao?.addEventListener('click', abrirPainel);
    fechar?.addEventListener('click', fecharPainel);

    form?.addEventListener('submit', function (event) {
        event.preventDefault();
        perguntar(input.value);
    });

    document
        .querySelectorAll('.sga-assistente-atalho')
        .forEach(function (atalho) {
            atalho.addEventListener('click', function () {
                perguntar(this.dataset.pergunta ?? '');
            });
        });
});
</script>
