<style>
        /* Para telas menores que 768px */
        @media (max-width: 768px) {
            header h1 {
                font-size: 10px;
                /* Diminui o tamanho do título em telas menores */
                margin-left: 10%;
                /* Ajusta o espaçamento */
            }

            .logo {
                width: 80px;
                /* Diminui o tamanho da logo */
                height: 80px;
            }

            .logo-img {
                width: 100%;
                height: 100%;
            }

            aside#menu-lateral {
                display: none;
                /* Oculta a barra lateral em telas pequenas */
            }

            .menu-superior-direito .fa-shopping-cart {
                display: none;
                /* Oculta o ícone do carrinho em telas pequenas */
            }

            .carrinho-count {
                display: none !important;
            }

            /* Adicionando esta linha para esconder o ícone do menu */
            .menu-superior-direito .fa-bars {
                display: none;
                /* Oculta o ícone do menu em telas pequenas */
            }

            .menu-mobile {
                display: flex;
                /* Exibe o menu mobile em telas pequenas */
            }

            /* Botão "Inclua seu primeiro produto" */
            .button {
                font-weight: bold;
                /* Deixa o texto em negrito */
                padding: 10px 10px;
                font-size: 12px;
            }

            main .opcoes {
                /*flex-direction: column;*/
                gap: 10px;
                background-color: #007BFF;
                display: flex;
                justify-content: center;
                align-items: center;
                margin-top: 0px;
                padding: auto;
            }

            /* Diminui o tamanho das letras em telas menores */
            main .tab span {
                font-size: 15px;
                /* Ajuste conforme o necessário */
            }

            main {
                display: flex;
                flex-direction: column;
                height: 100vh;
                /* O contêiner principal ocupa a altura total da tela */
                box-sizing: border-box;
            }

            main .tab {
                max-width: 10px;
                border-radius: 8px 8px 0 0;
                background-color: #007BFF;
                cursor: pointer;
                font-size: 20px;
                font-weight: bold;
                text-align: center;
                transition: background-color 0.3s ease, transform 0.3s ease;
                display: flex;
                padding: 10px 50px;
                width: auto;
                justify-content: center;
                align-items: center;
            }

            main .tab:hover {
                background-color: #afa791;
                color: white;
                transform: scale(1.05);
            }

            main .tab.active {
                background-color: #ffb300;
                /* Aba ativa com cor diferente */
                color: white;
                transform: scale(1.05);
            }

            .produto-nome {
                font-size: 1.1em;
            }

            .carrinho-count {
                display: none;
            }

            .icone-carrinho-wrapper {
                position: relative;
                display: inline-block;
            }

            .carrinho-count-rodape {
                position: absolute;
                top: -11px;
                /* sobe um pouco acima do ícone */
                right: -8px;
                /* desloca para a direita do ícone */
                background-color: green;
                color: white;
                padding: 5px;
                border-radius: 50%;
                font-size: 13px;
                font-weight: bold;
                z-index: 10;
            }

            .voltar {
                font-size: 1.2rem;
                /* Reduz o tamanho do botão "voltar" */
            }

            .categoria-item {
                width: 50px;
                /* Reduz o tamanho das categorias */
                height: 50px;
            }

            .categoria-item .categoria-imagem {
                width: 50px;
                /* Ajusta o tamanho da imagem da categoria */
                height: 50px;
            }

            .categoria-item p {
                font-size: 12px;
                /* Reduz o tamanho do texto das categorias */
            }

            .categoria-item:hover .categoria-imagem {
                width: 55px;
                /* Reduz o tamanho no hover */
                height: 55px;
                transform: translateY(-3px);
                /* Reduz o movimento no hover */
            }

            .categoria-item:hover p {
                font-size: 13px;
                /* Reduz o tamanho do texto no hover */
                transform: translateY(-3px);
                /* Reduz o movimento no hover */
            }

            .categoria-item.selected .categoria-imagem {
                width: 55px;
                /* Ajusta o tamanho da imagem selecionada */
                height: 55px;
                transform: translateY(-3px);
                /* Reduz o movimento */
            }

            .categoria-item.selected p {
                font-size: 13px;
                /* Ajusta o tamanho do texto selecionado */
                transform: translateY(-3px);
                /* Reduz o movimento */
            }
        }

        /* Para telas menores que 480px */
        @media (max-width: 480px) {
            header #logo-header h1 {
                font-size: 18px;
                /* Reduz ainda mais o tamanho do título */
                margin-left: 5%;
                /* Ajusta o espaçamento */
            }

            .logo {
                width: 60px;
                /* Reduz ainda mais o tamanho da logo */
                height: 60px;
            }

            .logo-img {
                width: 60px;
                height: 60px;
            }

            .logo-img {
                width: 60px;
            }

            .logo-text {
                font-size: 16px;
            }

            .products {
                grid-template-columns: 1fr;
            }

            .menu-mobile {
                display: flex;
                /* Exibe o menu mobile em telas pequenas */
            }

            main {
                display: flex;
                flex-direction: column;
                min-height: 100vh;
                /* Garante que o main ocupe no mínimo a altura da tela */
                overflow: auto;
                /* Permite que o conteúdo do main role se for maior que a tela */
            }
            .opcoes {
                width: 100%;
                flex-direction: column;
                align-items: stretch;
                background-color: #007BFF;
                display: flex;
                justify-content: center;
                align-items: center;
                gap: 10px;
                margin-top: 0px;
                padding: auto;
            }

            .opcoes .tab {
                width: 100%;
                justify-content: center;
                flex-direction: column;
                align-items: stretch;
            }

            main .tab:hover {
                background-color: #afa791;
                color: white;
                transform: scale(1.05);
            }

            main .tab.active {
                background-color: #ffb300;
                /* Aba ativa com cor diferente */
                color: white;
                transform: scale(1.05);
                word-spacing: -10px;
                /* Junta as palavras mais próximas */
                justify-content: center;
                /* Centraliza o texto dentro da aba */
                align-items: center;
            }
            .conteudo-aba {
                flex-grow: 1;
                overflow-y: auto;
                /* Permite que o conteúdo dentro das abas role */
                max-height: calc(100vh - 100px);
                /* Ajuste para que o conteúdo role corretamente */
            }

            /* Estilos para telas maiores (desktops) */
            .lista-produtos {
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
                justify-content: center;
                padding-bottom: 50px;
            }

            .lista-promocoes {
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
                justify-content: center;
                padding-bottom: 50px;
            }

            .lista-freteGgratis {
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
                justify-content: center;
                padding-bottom: 50px;
            }

            .lista-novidades {
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
                justify-content: center;
                padding-bottom: 50px;
            }

            .carrinho-count {
                display: none;
            }

            .voltar {
                font-size: 1rem;
                /* Reduz ainda mais o tamanho do botão "voltar" */
            }

            .categoria-item {
                width: 40px;
                /* Reduz ainda mais o tamanho das categorias */
                height: 40px;
            }

            .categoria-item .categoria-imagem {
                width: 40px;
                /* Ajusta ainda mais o tamanho da imagem da categoria */
                height: 40px;
            }

            .categoria-item p {
                font-size: 10px;
                /* Reduz ainda mais o tamanho do texto das categorias */
            }

            .categoria-item:hover .categoria-imagem {
                width: 45px;
                /* Reduz ainda mais o tamanho no hover */
                height: 45px;
                transform: translateY(-2px);
                /* Reduz ainda mais o movimento no hover */
            }

            .categoria-item:hover p {
                font-size: 11px;
                /* Reduz ainda mais o tamanho do texto no hover */
                transform: translateY(-2px);
                /* Reduz ainda mais o movimento no hover */
            }

            .categoria-item.selected .categoria-imagem {
                width: 45px;
                /* Ajusta ainda mais o tamanho da imagem selecionada */
                height: 45px;
                transform: translateY(-2px);
                /* Reduz ainda mais o movimento */
            }

            .categoria-item.selected p {
                font-size: 11px;
                /* Ajusta ainda mais o tamanho do texto selecionado */
                transform: translateY(-2px);
                /* Reduz ainda mais o movimento */
            }
        }




</style>