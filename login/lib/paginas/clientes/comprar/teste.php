<style>
            * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #007BFF;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            position: relative;
        }

        header {
            background-color: #007BFF;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 20px;
        }

        header h1 {
            flex-grow: 1;
            text-align: center;
            font-size: 30px;
            line-height: 100px;
            margin: 0;
        }

        header .logo img {
            height: 150px;
            width: 150px;
            border-radius: 50%;
        }

        .menu-superior-direito {
            font-size: 20px;
            display: flex;
            align-items: flex-start;
            /* Alinha o conteúdo no topo */
            margin-top: -10px;
            /* Ajuste para alinhar ao topo */
        }

        .menu-superior-direito span {
            margin-right: 15px;
            /* Espaçamento entre o nome do usuário e os ícones */
            transition: color 0.3s ease;
            /* Transição suave para a cor */
        }

        .menu-superior-direito i {
            font-size: 20px;
            /* Aumenta o tamanho dos ícones */
            margin-left: 15px;
            transition: transform 0.3s ease, color 0.3s ease;
            /* Transição para o movimento e cor */
            cursor: pointer;
            /* Cursor de ponteiro ao passar o mouse */
        }

        /* Efeito ao passar o mouse */
        .menu-superior-direito span:hover {
            color: #f0a309;
            /* Muda a cor do texto ao passar o mouse */
        }

        .menu-superior-direito i:hover {
            transform: translateY(-5px);
            /* Move o ícone para cima ao passar o mouse */
            color: #ff9d00;
            /* Muda a cor do ícone ao passar o mouse */
        }

        /* Efeito ao clicar */
        .menu-superior-direito i:active {
            transform: scale(0.9);
            /* Diminui o tamanho do ícone ao clicar */
            color: #ff9d09;
            /* Muda a cor do ícone ao passar o mouse */
        }

        aside#menu-lateral {
            display: none;
            position: fixed;
            top: 40px;
            /* Ajuste conforme a altura do cabeçalho */
            right: 20px;
            /* Posiciona o menu à direita */
            width: 200px;
            height: auto;
            background-color: white;
            border: 2px solid #ffb300;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(20, 5, 232, 0.31);
            z-index: 1000;
            padding: 10px;
            color: rgb(24, 8, 235);
            width: 210px;
            /* Largura fixa da barra lateral */
            position: absolute;
            /* Mantém a barra lateral fixa */
            transition: all 0.3s ease;
            /* Transição suave */
        }

        aside#menu-lateral ul {
            list-style: none;
            padding: 0;
        }

        aside#menu-lateral ul li {
            margin: 0;
            /* Margem entre os itens */
            font-size: 16px;
            /* Tamanho da fonte */
            display: flex;
            /* Flexbox para alinhar ícone e texto */
            align-items: center;
            /* Alinha verticalmente */
            transition: background-color 0.3s ease;
            /* Transição suave para a cor de fundo */
            border-radius: 5px;
            /* Bordas arredondadas */
            padding: 5px;
            /* Espaçamento interno */
            font-weight: bold;
            /* Aplica negrito ao texto */
        }

        /* Remove o sublinhado do link "Sair" */
        #menu-lateral a {
            text-decoration: none;
            /* Remove o sublinhado */
            color: inherit;
            /* Mantém a cor do texto herdada */
            transition: color 0.3s ease;
            /* Suave transição de cor */
        }

        /* Efeito ao passar o mouse sobre o link */
        #menu-lateral a:hover {
            cursor: pointer;
            color: #007BFF;
            /* Muda a cor ao passar o mouse */
        }

        /* Efeito ao passar o mouse sobre o item do menu */
        aside#menu-lateral ul li:hover {
            cursor: pointer;
            background-color: rgba(0, 123, 255, 0.1);
            /* Cor de fundo ao passar o mouse */
        }

        /* Estilo para ícones */
        aside#menu-lateral ul li i {
            margin-right: 5px;
            /* Espaçamento entre ícone e texto */
            font-size: 20px;
            /* Tamanho dos ícones */
            transition: transform 0.3s ease, color 0.3s ease;
            /* Transição para movimento e cor */
        }

        /* Efeito ao passar o mouse sobre o ícone */
        aside#menu-lateral ul li:hover i {
            cursor: pointer;
            transform: translateY(-3px);
            /* Move o ícone para cima ao passar o mouse */
            color: #ffbb09;
            /* Muda a cor do ícone ao passar o mouse */
        }

        /* Efeito ao clicar em um ícone */
        aside#menu-lateral ul li i:active {
            transform: scale(0.9);
            /* Diminui o tamanho do ícone ao clicar */
            color: #ffbb09;
            /* Muda a cor do ícone ao passar o mouse */
        }

        /* Efeitos para os spans */
        aside#menu-lateral ul li span {
            transition: transform 0.3s ease, color 0.3s ease;
            /* Transição para movimento e cor */
        }

        /* Efeito ao passar o mouse sobre o span */
        aside#menu-lateral ul li:hover span {
            cursor: pointer;
            transform: translateY(-3px);
            /* Move o ícone para cima ao passar o mouse */
            color: #bf9c44;
            /* Muda a cor do texto ao passar o mouse */
            /*text-decoration: underline; /* Adiciona sublinhado ao passar o mouse */
        }

        /* Estilo para o ícone de notificações com o número de notificações */
        .notificacoes {
            position: relative;
            display: inline-block;
        }

        /* Efeito de movimento no ícone de notificação e no número de notificações ao passar o mouse */
        .notificacoes:hover i,
        .notificacoes:hover .notificacao-count {
            animation: moverNotificacao 0.5s ease-in-out forwards;
        }

        /* Definição da animação de movimento */
        @keyframes moverNotificacao {
            0% {
                transform: translateY(0);
                /* Posição inicial */
            }

            50% {
                transform: translateY(-10px);
                /* Movimento para cima */
            }

            100% {
                transform: translateY(0);
                /* Volta à posição original */
            }
        }

        .notificacao-count {
            position: absolute;
            top: -8px;
            right: -1px;
            background-color: red;
            color: white;
            padding: 5px;
            border-radius: 50%;
            font-size: 12px;
            font-weight: bold;
        }

        .carrinho-count {
            position: absolute;
            top: 0px;
            right: 33px;
            background-color: chocolate;
            color: white;
            padding: 3px;
            border-radius: 50%;
            font-size: 10px;
            font-weight: bold;
        }

        /* Painel de notificações estilo semelhante ao menu lateral */
        #painel-notificacoes {
            display: none;
            position: fixed;
            top: 40px;
            /* Ajuste conforme a altura do cabeçalho */
            right: 20px;
            /* Posiciona o menu à direita */
            width: 250px;
            height: auto;
            background-color: white;
            border: 2px solid #ffb300;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            padding: 10px;
        }

        #painel-notificacoes h2 {
            margin: 0 0 10px 0;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
        }

        #painel-notificacoes ul {
            list-style: none;
            padding: 0;
        }

        #painel-notificacoes li {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #ddd;
        }

        #painel-notificacoes li:hover {
            background-color: #f0f0f0;
        }

        /* Faixa de Navegação */
        .sub-nav {
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f8f8f8;
            /* Cor de fundo suave */
            padding: 10px 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            /* Sombras sutis para destacar */
        }

        .sub-nav div {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
            /* Cor do texto */
            margin: 0 20px;
            /* Espaçamento entre os itens */
            cursor: pointer;
            transition: all 0.3s ease;
            /* Suavização do efeito de hover */
        }

        .sub-nav div:hover {
            color: #007bff;
            /* Cor de destaque quando o item é hover */
            text-decoration: underline;
            /* Adiciona um sublinhado no hover */
        }

        .section {
            margin: 40px auto;
            width: 70%;
            max-width: 1200px;
            text-align: center;
        }

        main {
            display: flex;
            flex-direction: column;
            /*height: 100vh; /* O contêiner principal ocupa a altura total da tela */
            box-sizing: border-box;
            align-items: center;
            /* Centraliza horizontalmente */
            justify-content: center;
            /* Centraliza verticalmente */
            text-align: center;
        }

        /* Estilos para as abas */
        main .opcoes {
            background-color: #007bff;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 0px;
            padding: auto;
        }

        main .tab {
            padding: 10px;
            border-radius: 8px 8px 0 0;
            /* Bordas arredondadas só no topo, estilo de aba */
            background-color: #27ae60;
            cursor: pointer;
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            transition: background-color 0.3s ease, transform 0.3s ease;
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

        /* Estilos para o conteúdo das abas */
        .conteudo-aba {
            flex-grow: 1;
            /* Faz o conteúdo ocupar todo o espaço restante */
            margin-left: 2px;
            margin-right: 2px;
            margin-top: 0px;
            padding: 10px;
            border: 2px solid #ffb300;
            border-radius: 8px;
            display: none;
            /* Por padrão, todos os conteúdos estão escondidos */
            padding-top: 5px;
            box-sizing: border-box;
            /* Garante que o padding seja incluído no tamanho */
            /*overflow: auto; /* Para que o conteúdo role se for maior que a tela */
            background-color: #d3d0ce;
            width: 100%;
            text-align: center;
            /* Centraliza o texto */
            display: flex;
            /* Define um layout flexível */
            flex-direction: column;
            /* Coloca os elementos verticalmente */
            align-items: center;
            /* Centraliza horizontalmente os itens */
            justify-content: center;
            /* Centraliza verticalmente os itens */
            height: auto;
            /*min-height: 200px; /* Define uma altura mínima para centralização adequada */
            /*padding: 20px; /* Adiciona espaçamento interno */
            /* padding-bottom: 50px; /* Ajuste conforme o tamanho do seu menu */
        }

        .container {
            display: flex;
            /*flex-direction: column;*/
            align-items: center;
            /* Centraliza horizontalmente */
            justify-content: center;
            text-align: center;
        }

        .parceiros-carousel {
            width: 100%;
            /* Ocupar toda a largura */
            margin: 0 auto;
            /* Centralizar o carrossel */
            display: flex;
            /* Flexbox para alinhar elementos */
            justify-content: center;
            /* Centraliza o conteúdo dentro */
        }

        .parceiros-carousel .parceiro-card {
            text-align: center;
            padding: 10px;
            border-radius: 60px;
            margin: 10px auto;
            max-width: 200px;
            background-color: transparent;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .parceiros-carousel .parceiro-card:hover {
            transform: translateY(-10px);
            /* Move o cartão para cima */
        }

        .input {
            font-size: 15px;
            /* Tamanho padrão do título */
            margin-top: 5px;
            width: 40%;
            padding: 3px;
            padding-left: 5px;
            border-radius: 5px;
            height: 30px;
            border: 1px solid #ffb300;
        }

        .parceiros-carousel .parceiro-card img {
            max-width: 120px;
            /* Ajuste o tamanho da logo */
            height: 120px;
            /* Para mantê-la circular */
            margin: auto;
            /* Centraliza horizontalmente e adiciona espaço abaixo */
            border-radius: 50%;
            /* Torna a imagem redonda */
            display: block;
            /* Garante que o elemento seja tratado como bloco */
            border: 2px solid #ddd;
            /* Borda ao redor da imagem */
        }

        .parceiros-carousel .parceiro-card h3 {
            font-size: 1.2em;
            font-weight: bold;
            margin: 5px 0;
            color: #333;
            /* Cor do texto */
        }

        .parceiros-carousel .parceiro-card p {
            font-size: 0.9em;
            color: #666;
            /* Cor da categoria */
            margin: 5px 0 0;
        }

        /* Contêiner da seção de produtos */
        .products {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            /* Espaçamento entre os cartões */
            justify-content: center;
            /* Centraliza os produtos */
            margin: 10px 0;

        }

        /* Cartão do produto */
        .product-card {
            background: #ffffff;
            border: 1px solid #ddd;
            border-radius: 10px;
            width: 200px;
            /* Largura do cartão */
            height: 380px;
            /* Define a altura fixa */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-align: center;
            padding: 3px;
        }

        /* Efeito ao passar o mouse */
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
        }

        /* Imagem do produto */
        .product-card img {
            width: 300px;
            max-width: 100%;
            max-height: 250px;
            height: 200px;
            border-radius: 5px;
            margin-bottom: 2px;
        }

        /* Nome do produto */
        .product-card h3 {
            font-size: 1.2em;
            color: #333;
            margin-top: 5px;
            margin-bottom: 5px;
            font-weight: 500;
        }

        /* Descrição do produto */
        .product-card p {
            font-size: 0.9em;
            color: #333;
            margin-top: 5px;
            margin-bottom: 5px;
            line-height: 1.4;
        }

        .moeda {
            font-size: 1.2em;
            color: #007BFF;
        }

        /* Preço do produto */
        .product-card p:last-child {
            font-size: 1em;
            color: #27ae60;
            /* Verde para o preço */
            font-weight: bold;
        }

        /* Botões */
        .product-card .btn {
            display: inline-block;
            background: #27ae60;
            /* Cor do botão */
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 5px;
            transition: background-color 0.3s ease;
            font-size: 0.9em;
            width: 100%;
        }

        /* Efeito ao passar o mouse no botão */
        .product-card .btn:hover {
            background: darkorange;
        }

        .descricao {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            /* Limita a 2 linhas */
            -webkit-box-orient: vertical;
            overflow: hidden;
            /* Oculta o texto excedente */
            text-overflow: ellipsis;
            /* Adiciona "..." ao final do texto cortado */
            max-width: 100%;
            /* Define uma largura máxima para o texto */
        }

        .conteudo-aba h2 {
            border-radius: 3px;
            background-color: #fff;
            text-align: left;
            /* Alinha o texto à esquerda */
            /*margin-left: 0;   /* Garante que não há margem que afaste do lado esquerdo */
            padding-left: 5px;
            /* Garante que não há espaçamento interno */
        }

        /* Efeito hover */
        .nome-fantasia:hover {
            color: #aaff00;
            /* Muda a cor ao passar o mouse */
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
            /* Adiciona uma leve sombra no texto */
        }

        /* Footer */
        footer {
            text-align: center;
            padding: 20px 0;
            background-color: #333;
            color: white;
            margin-top: 20px;
            /*border-radius: 5px;*/
            display: none;
        }

        .menu-mobile {
            background-color: #343a40;
            color: white;
            padding: 10px;
            position: fixed;
            bottom: 0;
            width: 100%;
            display: none;
            justify-content: space-around;
        }

        #menu-mobile i {
            text-decoration: none;
            /* Remove o sublinhado dos links */
            color: inherit;
            /* Herda a cor do item pai */
        }

        #menu-mobile i:hover {
            background-color: #f0f0f0;
            /* Efeito de hover */
            color: #007BFF;
            /* Cor ao passar o mouse */
        }

        .menu-mobile ul {
            list-style: none;
            display: flex;
            justify-content: space-around;
            width: 100%;
            /* Garantir que o menu ocupe toda a largura */
        }

        /* Efeitos para os itens do menu mobile */
        .menu-mobile ul li {
            transition: transform 0.3s ease, color 0.3s ease;
            /* Transição suave para movimento e cor */
        }

        /* Efeito ao passar o mouse sobre o item do menu */
        .menu-mobile ul li:hover {
            cursor: pointer;
            transform: translateY(-3px);
            /* Move o item para cima ao passar o mouse */
            color: #ffbb09;
            /* Muda a cor do ícone ao passar o mouse */
        }

        .menu-mobile ul li i {
            font-size: 24px;
            /* Aumente o tamanho dos ícones aqui */
            margin: 0;
            /* Remova a margem, se necessário */
            display: block;
            /* Garante que o ícone seja exibido como um bloco */
            text-align: center;
            /* Centraliza o ícone dentro do item do menu */
            transform: scale(0.9);
            /* Diminui o tamanho do ícone ao clicar */
            /*color: #afa791; /* Muda a cor do ícone ao passar o mouse */
        }

        /* Efeito ao passar o mouse sobre o ícone */
        .menu-mobile ul li:hover i {
            cursor: pointer;
            transition: transform 0.3s ease, color 0.3s ease;
            /* Transição suave para movimento e cor */
            color: #ffbb09;
            /* Muda a cor do ícone ao passar o mouse */
        }

        #lista-notificacoes a {
            text-decoration: none;
            /* Remove o sublinhado */
            color: inherit;
            /* Mantém a cor do texto herdada */
            display: block;
            /* Faz o link ocupar toda a área do <li> */
            padding: 5px;
            /* Adiciona espaçamento interno para melhor interação */
        }

        #lista-notificacoes a:hover {
            background-color: #f0f0f0;
            /* Cor de fundo ao passar o mouse */
            border-radius: 4px;
            /* Bordas arredondadas */
        }

        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            width: 280px;
            height: 355px;
            text-align: center;
        }

        .popup #info {
            margin: 12px 12px 8px 12px;
            border: 1px solid black;
            /* Adiciona uma borda */
            border-radius: 5px;
            /* Arredonda os cantos */
        }


        .popup h2 {
            margin-top: 0;
            margin-bottom: 15px;
        }

        .popup aside {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 5px;
            padding-bottom: 10px;
        }

        .popup p {
            text-align: left;
            padding-left: 5px;
        }

        .popup input {
            flex: 1;
            border: none;
            text-align: left;
            margin: 5px;
            width: 80px;
            font-size: 16px;
        }

        .popup input:focus {
            outline: none;
        }

        .popup input[type="number"] {
            border: 1px solid #000;
            /* Cor da borda */
            padding: 5px;
            /* Espaçamento interno */
            border-radius: 4px;
            /* Bordas arredondadas */
            outline: none;
            /* Remove o contorno ao focar */
        }

        .popup #produtoNome {
            font-weight: bold;
            /* Deixa o texto em negrito */
            text-align: center;
            width: 95%;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .close-btn,
        .confirm-btn {
            width: 90%;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin: 3px;
            transition: transform 0.2s ease-in-out;
        }

        .close-btn {
            background: red;
        }

        .confirm-btn {
            background: #28a745;
        }

        .close-btn:hover,
        .confirm-btn:hover {
            transform: translateY(-3px);
        }

        #resposra-carrinho {
            position: fixed;
            /* Fixa a posição na tela */
            top: 50%;
            /* Coloca no centro vertical */
            left: 50%;
            /* Coloca no centro horizontal */
            transform: translate(-50%, -50%);
            /* Ajusta para centralizar exatamente */
            background-color: rgba(0, 0, 0, 0.7);
            /* Fundo semitransparente */
            color: white;
            /* Cor do texto */
            padding: 20px;
            /* Espaçamento interno */
            border-radius: 10px;
            /* Bordas arredondadas */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            /* Sombra para dar destaque */
            font-size: 16px;
            /* Tamanho da fonte */
            z-index: 9999;
            /* Garante que o popup fique acima de outros elementos */
            display: none;
            /* Inicialmente escondido */
        }

        .conteudo-aba p {
            margin-top: 50px;
            margin-bottom: 50px;
        }

        .products p {
            margin-top: 5px;
            margin-bottom: 5px;
        }
</style>