<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <meta name="csrf-token"
          content="{{ csrf_token() }}">

    <title>
        @yield('title', 'SGA Finanças')
    </title>

    <link rel="preconnect"
          href="https://fonts.bunny.net">

    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap"
          rel="stylesheet" />

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            min-height: 100%;
        }

        body {
            font-family: 'Figtree', Arial, sans-serif;
            background: #f5f7fb;
            color: #1f2937;
        }

        /* =========================================================
           ESTRUTURA GERAL
        ========================================================= */

        .cp-app {
            min-height: 100vh;
            display: flex;
        }

        /* =========================================================
           SIDEBAR
        ========================================================= */

        .cp-sidebar {
            width: 250px;

            height: 100vh;
            height: 100dvh;

            background:
                linear-gradient(
                    180deg,
                    #07345c 0%,
                    #062b4b 55%,
                    #05243f 100%
                );

            color: #ffffff;

            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;

            z-index: 1000;

            display: flex;
            flex-direction: column;

            transition: transform .25s ease;

            /*
             * A sidebar inteira não rola.
             * Quem rola é somente o menu.
             */
            overflow: hidden;
        }

        .cp-brand {
            min-height: 86px;

            display: flex;
            align-items: center;

            gap: 12px;

            padding: 12px 22px;

            border-bottom:
                1px solid rgba(255,255,255,.08);

            /*
             * Não deixa o logo encolher.
             */
            flex-shrink: 0;
        }

        .cp-brand-text {
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-width: 0;
        }

        .cp-brand-title {
            font-size: 22px;
            font-weight: 700;
            line-height: 1.1;
            color: #ffffff;
        }

        .cp-brand-subtitle {
            margin-top: 4px;
            font-size: 12px;
            font-weight: 400;
            line-height: 1.25;
            color: rgba(255,255,255,.72);
            white-space: nowrap;
        }

        .cp-brand-icon {
            display: flex;
            align-items: flex-end;

            height: 32px;
            gap: 3px;
        }

        .cp-brand-icon span {
            display: block;
            width: 5px;
            border-radius: 5px;
            background: #16c7b7;
        }

        .cp-brand-icon span:nth-child(1) {
            height: 15px;
        }

        .cp-brand-icon span:nth-child(2) {
            height: 23px;
        }

        .cp-brand-icon span:nth-child(3) {
            height: 31px;
        }

        /*
        |--------------------------------------------------------------------------
        | ÁREA ROLÁVEL DO MENU
        |--------------------------------------------------------------------------
        */

        .cp-menu {
            padding: 20px 12px 30px;

            flex: 1;

            /*
             * Fundamental para funcionar corretamente
             * dentro de um container flex.
             */
            min-height: 0;

            overflow-y: auto;
            overflow-x: hidden;

            -webkit-overflow-scrolling: touch;

            overscroll-behavior: contain;
        }

        /*
         * Barra discreta no desktop.
         * No celular o navegador pode ocultá-la visualmente,
         * mas o gesto de rolagem continuará funcionando.
         */
        .cp-menu::-webkit-scrollbar {
            width: 6px;
        }

        .cp-menu::-webkit-scrollbar-track {
            background: transparent;
        }

        .cp-menu::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,.20);
            border-radius: 10px;
        }

        .cp-menu::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,.30);
        }

        .cp-menu-link {
            display: flex;
            align-items: center;

            gap: 13px;

            min-height: 50px;

            padding: 0 15px;

            margin-bottom: 5px;

            border-radius: 9px;

            text-decoration: none;

            color: #e8edf4;

            font-size: 15px;
            font-weight: 500;

            transition: .18s ease;
        }

        .cp-menu-link:hover {
            background: rgba(255,255,255,.08);
            color: #ffffff;
        }

        .cp-menu-link.active {
            background:
                linear-gradient(
                    135deg,
                    #147be6,
                    #0767cd
                );

            color: #ffffff;

            box-shadow:
                0 5px 15px rgba(0,91,192,.22);
        }

        .cp-menu-icon {
            width: 25px;

            display: flex;
            justify-content: center;

            font-size: 19px;

            flex-shrink: 0;
        }

        .cp-sidebar-footer {
            padding: 17px 20px;

            border-top:
                1px solid rgba(255,255,255,.10);

            font-size: 12px;
            color: #d7e1ec;

            /*
             * Rodapé não entra na rolagem do menu.
             */
            flex-shrink: 0;
        }

        .cp-sidebar-footer strong {
            display: block;
            color: #ffffff;
            font-size: 13px;
            margin-bottom: 4px;
        }

        .cp-assinatura-status {
            display: block;
            font-size: 12px;
            font-weight: 600;
            line-height: 1.35;
        }

        .cp-assinatura-detalhe {
            display: block;
            margin-top: 3px;
            font-size: 11px;
            line-height: 1.35;
            color: rgba(255,255,255,.72);
        }

        .cp-assinatura-teste {
            color: #7dd3fc;
        }

        .cp-assinatura-ativa {
            color: #86efac;
        }

        .cp-assinatura-vencida,
        .cp-assinatura-suspensa,
        .cp-assinatura-cancelada {
            color: #fca5a5;
        }

        .cp-assinatura-admin {
            color: #fde68a;
        }

        .cp-assinatura-sem {
            color: #cbd5e1;
        }

        /* =========================================================
           ÁREA PRINCIPAL
        ========================================================= */

        .cp-main {
            width: calc(100% - 250px);
            margin-left: 250px;

            min-height: 100vh;
        }

        /* =========================================================
           TOPBAR
        ========================================================= */

        .cp-topbar {
            height: 74px;

            background: #ffffff;

            display: flex;
            align-items: center;
            justify-content: space-between;

            padding: 0 30px;

            border-bottom: 1px solid #e5e7eb;

            position: sticky;
            top: 0;

            z-index: 900;
        }

        .cp-topbar-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .cp-menu-toggle {
            width: 40px;
            height: 40px;

            border: none;
            border-radius: 8px;

            background: transparent;

            font-size: 25px;

            cursor: pointer;
            color: #263238;
        }

        .cp-topbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .cp-date {
            color: #4b5563;
            font-size: 14px;
            white-space: nowrap;
        }

        .cp-user {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cp-avatar {
            width: 38px;
            height: 38px;

            border-radius: 50%;

            background: #0ea5a8;
            color: white;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 13px;
            font-weight: 700;
        }

        .cp-user-name {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
        }

        .cp-logout {
            border: none;
            background: transparent;

            color: #6b7280;

            cursor: pointer;
            font-size: 13px;
        }

        .cp-logout:hover {
            color: #dc2626;
        }

        /* =========================================================
           CONTEÚDO
        ========================================================= */

        .cp-content {
            padding: 28px 30px 40px;
        }

        .cp-page-title {
            margin: 0;

            font-size: 30px;
            font-weight: 700;

            color: #111827;
        }

        .cp-page-subtitle {
            margin-top: 5px;
            color: #6b7280;
            font-size: 14px;
        }

        /* =========================================================
           ALERTAS
        ========================================================= */

        .cp-alert {
            padding: 14px 18px;
            border-radius: 10px;

            margin-bottom: 20px;

            font-size: 14px;
        }

        .cp-alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .cp-alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .cp-alert-warning {
            background: #fff7ed;
            color: #9a3412;
            border: 1px solid #fed7aa;
        }

        /* =========================================================
           CARDS PADRÃO
        ========================================================= */

        .cp-card {
            background: #ffffff;

            border: 1px solid #e8ebef;

            border-radius: 13px;

            box-shadow:
                0 3px 12px rgba(15,23,42,.05);
        }

        /* =========================================================
           FUNDO MOBILE
        ========================================================= */

        .cp-overlay {
            display: none;

            position: fixed;
            inset: 0;

            background: rgba(15,23,42,.42);

            z-index: 950;
        }

        /* =========================================================
           RESPONSIVO
        ========================================================= */

        @media (max-width: 1000px) {

            .cp-sidebar {
                transform: translateX(-100%);
            }

            .cp-sidebar.open {
                transform: translateX(0);
            }

            .cp-overlay.show {
                display: block;
            }

            .cp-main {
                width: 100%;
                margin-left: 0;
            }

            /*
             * No celular, a rolagem continua restrita
             * apenas à área dos itens.
             */
            .cp-menu {
                overflow-y: auto;
                overflow-x: hidden;
                -webkit-overflow-scrolling: touch;
            }
        }

        @media (max-width: 768px) {

            .cp-topbar {
                padding: 0 15px;
            }

            .cp-content {
                padding: 20px 15px 30px;
            }

            .cp-date {
                display: none;
            }

            .cp-user-name {
                display: none;
            }

            .cp-page-title {
                font-size: 25px;
            }

            .cp-topbar-right {
                gap: 10px;
            }
        }

        @media (max-width: 420px) {

            .cp-sidebar {
                width: 85%;
                max-width: 290px;
            }

            .cp-brand {
                font-size: 20px;
            }
        }
    </style>

    @stack('styles')
</head>

<body>

<div class="cp-app">

    {{-- =========================================================
         SIDEBAR
    ========================================================== --}}

    <aside class="cp-sidebar"
           id="cpSidebar">

        <div class="cp-brand">

            <div class="cp-brand-icon">
                <span></span>
                <span></span>
                <span></span>
            </div>

            <div class="cp-brand-text">

                <span class="cp-brand-title">
                    SGA Finanças
                </span>

                <span class="cp-brand-subtitle">
                    Sua vida financeira em um só lugar.
                </span>

            </div>

        </div>


        <nav class="cp-menu">

            <a href="{{ url('/dashboard') }}"
               class="cp-menu-link
               {{ request()->is('dashboard') ? 'active' : '' }}">

                <span class="cp-menu-icon">⌂</span>
                <span>Dashboard</span>

            </a>


            <a href="{{ url('/receitas') }}"
               class="cp-menu-link
               {{ request()->is('receitas*') ? 'active' : '' }}">

                <span class="cp-menu-icon">↗</span>
                <span>Receitas</span>

            </a>


            <a href="{{ url('/despesas') }}"
               class="cp-menu-link
               {{ request()->is('despesas*') ? 'active' : '' }}">

                <span class="cp-menu-icon">▣</span>
                <span>Despesas</span>

            </a>


            <a href="{{ route('previsao-despesas.index') }}"
               class="cp-menu-link
               {{ request()->is('previsao-despesas*') ? 'active' : '' }}">

                <span class="cp-menu-icon">◫</span>
                <span>Previsão de Despesas</span>

            </a>


            <a href="{{ route('contas-a-pagar.index') }}"
               class="cp-menu-link
               {{ request()->routeIs('contas-a-pagar.*') ? 'active' : '' }}">

                <span class="cp-menu-icon">▤</span>
                <span>Contas a Pagar</span>

            </a>


            <a href="{{ route('contas.index') }}"
               class="cp-menu-link
               {{ request()->routeIs('contas.*') ? 'active' : '' }}">

                <span class="cp-menu-icon">▥</span>
                <span>Contas e Carteiras</span>

            </a>


            <a href="{{ route('transferencias.index') }}"
               class="cp-menu-link
               {{ request()->is('transferencias*') ? 'active' : '' }}">

                <span class="cp-menu-icon">⇄</span>
                <span>Transferências</span>

            </a>


            <a href="{{ route('movimentacoes.index') }}"
               class="cp-menu-link
               {{ request()->is('movimentacoes*') ? 'active' : '' }}">

                <span class="cp-menu-icon">⇄</span>
                <span>Movimentações</span>

            </a>


            <a href="{{ route('cartoes.index') }}"
               class="cp-menu-link
               {{ request()->is('cartoes*') ? 'active' : '' }}">

                <span class="cp-menu-icon">▤</span>
                <span>Cartões</span>

            </a>


            <a href="{{ route('compras-cartao.index') }}"
               class="cp-menu-link
               {{ request()->is('compras-cartao*') ? 'active' : '' }}">

                <span class="cp-menu-icon">🛒</span>
                <span>Compras no Cartão</span>

            </a>


            <a href="{{ route('faturas.index') }}"
               class="cp-menu-link
               {{ request()->is('faturas*') ? 'active' : '' }}">

                <span class="cp-menu-icon">▧</span>
                <span>Faturas</span>

            </a>


            <a href="{{ url('/recorrencias') }}"
               class="cp-menu-link
               {{ request()->is('recorrencias*') ? 'active' : '' }}">

                <span class="cp-menu-icon">▦</span>
                <span>Contas Fixas</span>

            </a>


            <a href="{{ url('/categorias') }}"
               class="cp-menu-link
               {{ request()->is('categorias*') ? 'active' : '' }}">

                <span class="cp-menu-icon">◔</span>
                <span>Categorias</span>

            </a>


            <a href="{{ route('relatorios.index') }}"
               class="cp-menu-link
               {{ request()->routeIs('relatorios.index') ? 'active' : '' }}">

                <span class="cp-menu-icon">▥</span>
                <span>Relatórios</span>

            </a>


            <a href="{{ route('relatorios.despesas') }}"
               class="cp-menu-link
               {{ request()->routeIs('relatorios.despesas*') ? 'active' : '' }}">

                <span class="cp-menu-icon">▦</span>
                <span>Relatório de Despesas</span>

            </a>

            @if(auth()->user()?->is_admin)

                <div
                    style="
                        margin:18px 12px 8px;
                        padding-top:14px;
                        border-top:1px solid rgba(255,255,255,.12);
                        font-size:10px;
                        font-weight:700;
                        letter-spacing:.08em;
                        text-transform:uppercase;
                        color:rgba(255,255,255,.55);
                    "
                >
                    Administração
                </div>


                <a
                    href="{{ route('admin.assinaturas.index') }}"
                    class="cp-menu-link
                    {{ request()->routeIs('admin.assinaturas.*')
                        ? 'active'
                        : '' }}"
                >
                    <span class="cp-menu-icon">
                        ◫
                    </span>

                    <span>
                        Assinaturas
                    </span>
                </a>


                <a
                    href="{{ route('admin.planos.index') }}"
                    class="cp-menu-link
                    {{ request()->routeIs('admin.planos.*')
                        ? 'active'
                        : '' }}"
                >
                    <span class="cp-menu-icon">
                        ▤
                    </span>

                    <span>
                        Planos
                    </span>
                </a>

            @endif





        </nav>


        @php

            $usuarioAssinatura =
                auth()->user();

            $assinaturaAtual =
                $usuarioAssinatura
                    ?->assinaturaAtual;

            if ($assinaturaAtual) {
                $assinaturaAtual
                    ->loadMissing('plano');
            }

            $statusAssinatura =
                $assinaturaAtual
                    ?->situacao;

            $vencimentoAssinatura =
                $assinaturaAtual
                    ?->data_vencimento;

            /*
             * Mesmo que a situação ainda esteja "teste" ou "ativa"
             * no banco, visualmente consideramos vencida se a data
             * de vencimento já passou.
             */
            if (
                $assinaturaAtual
                &&
                in_array(
                    $statusAssinatura,
                    ['teste', 'ativa'],
                    true
                )
                &&
                $vencimentoAssinatura
                &&
                $vencimentoAssinatura
                    ->lt(today())
            ) {
                $statusAssinatura =
                    'vencida';
            }

            $diasRestantesAssinatura =
                null;

            if (
                $vencimentoAssinatura
                &&
                !$vencimentoAssinatura
                    ->lt(today())
            ) {
                $diasRestantesAssinatura =
                    today()->diffInDays(
                        $vencimentoAssinatura,
                        false
                    );
            }

            /*
             * Duração ORIGINAL do teste.
             *
             * Exemplo:
             * teste liberado por 60 dias -> continuará exibindo
             * "Teste gratuito por 60 dias" durante o período normal.
             *
             * A contagem regressiva só será mostrada quando faltarem
             * 5 dias ou menos para o vencimento.
             */
            $duracaoOriginalTeste =
                null;

            if (
                $assinaturaAtual
                &&
                $statusAssinatura === 'teste'
                &&
                $assinaturaAtual->data_inicio
                &&
                $vencimentoAssinatura
            ) {
                $duracaoOriginalTeste =
                    $assinaturaAtual
                        ->data_inicio
                        ->copy()
                        ->startOfDay()
                        ->diffInDays(
                            $vencimentoAssinatura
                                ->copy()
                                ->startOfDay()
                        );
            }

        @endphp


        <div class="cp-sidebar-footer">

            <strong>
                Assinatura
            </strong>


            @if($usuarioAssinatura?->is_admin)

                <span class="
                    cp-assinatura-status
                    cp-assinatura-admin
                ">
                    Administrador
                </span>

                <span class="cp-assinatura-detalhe">
                    Acesso administrativo liberado
                </span>


            @elseif(!$assinaturaAtual)

                <span class="
                    cp-assinatura-status
                    cp-assinatura-sem
                ">
                    Sem assinatura
                </span>

                <span class="cp-assinatura-detalhe">
                    Aguardando liberação
                </span>


            @elseif($statusAssinatura === 'teste')

                @if(
                    $diasRestantesAssinatura !== null
                    && $diasRestantesAssinatura <= 5
                )

                    <span class="
                        cp-assinatura-status
                        cp-assinatura-teste
                    ">
                        Teste gratuito
                    </span>

                    <span class="cp-assinatura-detalhe">

                        @if($diasRestantesAssinatura === 0)

                            Teste termina hoje

                        @elseif($diasRestantesAssinatura === 1)

                            1 dia restante

                        @else

                            {{ $diasRestantesAssinatura }}
                            dias restantes

                        @endif

                    </span>

                @else

                    <span class="
                        cp-assinatura-status
                        cp-assinatura-teste
                    ">
                        Teste gratuito
                        @if($duracaoOriginalTeste)
                            por {{ $duracaoOriginalTeste }}
                            {{ $duracaoOriginalTeste === 1 ? 'dia' : 'dias' }}
                        @endif
                    </span>

                @endif


            @elseif($statusAssinatura === 'ativa')

                <span class="
                    cp-assinatura-status
                    cp-assinatura-ativa
                ">
                    {{ $assinaturaAtual
                        ->plano
                        ?->nome
                        ?? 'Assinatura ativa' }}
                </span>

                <span class="cp-assinatura-detalhe">
                    Vence em
                    {{ $vencimentoAssinatura
                        ?->format('d/m/Y') }}
                </span>


            @elseif($statusAssinatura === 'vencida')

                <span class="
                    cp-assinatura-status
                    cp-assinatura-vencida
                ">
                    Vencida
                </span>

                <span class="cp-assinatura-detalhe">
                    Venceu em
                    {{ $vencimentoAssinatura
                        ?->format('d/m/Y') }}
                </span>


            @elseif($statusAssinatura === 'suspensa')

                <span class="
                    cp-assinatura-status
                    cp-assinatura-suspensa
                ">
                    Suspensa
                </span>

                <span class="cp-assinatura-detalhe">
                    Acesso bloqueado
                </span>


            @elseif($statusAssinatura === 'cancelada')

                <span class="
                    cp-assinatura-status
                    cp-assinatura-cancelada
                ">
                    Cancelada
                </span>

                <span class="cp-assinatura-detalhe">
                    Acesso bloqueado
                </span>


            @else

                <span class="
                    cp-assinatura-status
                    cp-assinatura-sem
                ">
                    {{ ucfirst(
                        $statusAssinatura
                        ?? 'Indefinida'
                    ) }}
                </span>

            @endif

        </div>

    </aside>


    <div class="cp-overlay"
         id="cpOverlay"></div>


    {{-- =========================================================
         ÁREA PRINCIPAL
    ========================================================== --}}

    <div class="cp-main">

        <header class="cp-topbar">

            <div class="cp-topbar-left">

                <button
                    type="button"
                    class="cp-menu-toggle"
                    id="cpMenuToggle"
                >
                    ☰
                </button>

            </div>


            <div class="cp-topbar-right">

                <div
                    class="cp-date"
                    id="cpCurrentDate"
                >
                </div>


                @php

                    $nomeUsuario =
                        auth()->user()->name
                        ?? auth()->user()->usuario
                        ?? 'Usuário';


                    $partesNome =
                        preg_split(
                            '/\s+/',
                            trim($nomeUsuario)
                        );


                    $iniciais = '';


                    if (!empty($partesNome[0])) {

                        $iniciais .= strtoupper(
                            substr(
                                $partesNome[0],
                                0,
                                1
                            )
                        );
                    }


                    if (count($partesNome) > 1) {

                        $iniciais .= strtoupper(
                            substr(
                                end($partesNome),
                                0,
                                1
                            )
                        );
                    }

                @endphp


                <div class="cp-user">

                    <div class="cp-avatar">
                        {{ $iniciais ?: 'CP' }}
                    </div>

                    <span class="cp-user-name">
                        {{ $nomeUsuario }}
                    </span>

                </div>


                <form
                    method="POST"
                    action="{{ route('logout') }}"
                    style="margin:0;"
                >

                    @csrf

                    <button
                        type="submit"
                        class="cp-logout"
                    >
                        Sair
                    </button>

                </form>

            </div>

        </header>


        <main class="cp-content">

            @if(session('success'))

                <div class="cp-alert cp-alert-success">
                    {{ session('success') }}
                </div>

            @endif


            @if(session('error'))

                <div class="cp-alert cp-alert-error">
                    {{ session('error') }}
                </div>

            @endif


            @if(session('warning'))

                <div class="cp-alert cp-alert-warning">
                    {{ session('warning') }}
                </div>

            @endif


            @yield('content')

        </main>

    </div>

</div>

@auth
    @include('components.assistente-financeiro')
@endauth


<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {

        const sidebar =
            document.getElementById(
                'cpSidebar'
            );

        const overlay =
            document.getElementById(
                'cpOverlay'
            );

        const toggle =
            document.getElementById(
                'cpMenuToggle'
            );


        function abrirMenu() {

            sidebar?.classList.add(
                'open'
            );

            overlay?.classList.add(
                'show'
            );
        }


        function fecharMenu() {

            sidebar?.classList.remove(
                'open'
            );

            overlay?.classList.remove(
                'show'
            );
        }


        toggle?.addEventListener(
            'click',
            function () {

                if (
                    sidebar?.classList.contains(
                        'open'
                    )
                ) {

                    fecharMenu();

                } else {

                    abrirMenu();
                }
            }
        );


        overlay?.addEventListener(
            'click',
            fecharMenu
        );


        /*
        |--------------------------------------------------------------------------
        | DATA ATUAL
        |--------------------------------------------------------------------------
        */

        const elementoData =
            document.getElementById(
                'cpCurrentDate'
            );


        if (elementoData) {

            const hoje =
                new Date();


            elementoData.textContent =
                hoje.toLocaleDateString(
                    'pt-BR',
                    {
                        day: '2-digit',
                        month: 'long',
                        year: 'numeric'
                    }
                );
        }
    }
);
</script>

@stack('scripts')

</body>
</html>