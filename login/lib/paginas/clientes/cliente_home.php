<?php

include('../../conexao.php');

// Inicia a sessão
session_start();

if (isset($_SESSION['id'])) {
    // Se a sessão do usuário estiver ativa
    $id = $_SESSION['id'];

    // Consulta para buscar os dados do cliente
    $sql_query = $mysqli->prepare("SELECT * FROM meus_clientes WHERE id = ?");
    $sql_query->bind_param("i", $id); // Bind para evitar injeção de SQL
    $sql_query->execute();
    $usuario = $sql_query->get_result()->fetch_assoc();
    //echo 'oii1'; // Para verificar que está no bloco de sessão

} else {
    // Se não houver ID na sessão ou na URL
    //echo 'oii3';
    // Redirecionamento opcional para a página de login
    session_unset();
    session_destroy();
    header("Location: ../../../../index.php");
    exit(); // Importante parar a execução do código aqui
}


$usuarioLogado = $id;

$dados = $mysqli->query("SELECT * FROM config_admin WHERE logo != '' ORDER BY data_alteracao DESC LIMIT 1") or die($mysqli->error);
$dadosEscolhido = $dados->fetch_assoc();
$nomeFantasia = $dadosEscolhido['nomeFantasia'];

// Carrega a logo
if (isset($dadosEscolhido['logo'])) {
    $logo = $dadosEscolhido['logo'];
    if ($logo == '') {
        $logo = '../arquivos_fixos/imagem_credgas.jpg';
    } else {
        $logo = '../administrativo/arquivos/' . $logo;
    }
}

$taxa_padrao = $mysqli->query("SELECT * FROM config_admin WHERE taxa_padrao != '' ORDER BY data_alteracao DESC LIMIT 1") or die($mysqli->error);
$taxa = $taxa_padrao->fetch_assoc();

// Consulta para somar todas as notificações de um cliente específico
$sql_query = "SELECT COUNT(*) AS total_notificacoes FROM contador_notificacoes_cliente WHERE id_cliente = ? AND lida = 1";
$stmt = $mysqli->prepare($sql_query);
$stmt->bind_param("i", $id); // Substituir $id pelo ID do cliente
$stmt->execute();
$stmt->bind_result($total_notificacoes);
$stmt->fetch();
$stmt->close();

// Obtém a data de hoje menos 1 dias
$data_limite = date('Y-m-d', strtotime('-1 days'));

// Exclui produtos do carrinho do cliente adicionados há mais de 1 dias
$sql_delete = "DELETE FROM carrinho WHERE id_cliente = ? AND DATE(data) < ?";
$stmt_delete = $mysqli->prepare($sql_delete);
$stmt_delete->bind_param("is", $id, $data_limite); // Corrigir $id_cliente para $id
$stmt_delete->execute();
$stmt_delete->close();


// Consulta para somar todas as quantidades de produtos no carrinho de um cliente específico
$sql_query = "SELECT SUM(qt) AS total_carrinho FROM carrinho WHERE id_cliente = ?";
$stmt = $mysqli->prepare($sql_query);
$stmt->bind_param("i", $id); // Substituir $id_cliente pelo ID do cliente
$stmt->execute();
$stmt->bind_result($total_carrinho);
$stmt->fetch();
$stmt->close();

// Se não houver produtos no carrinho, definir como 0 para evitar retorno null
$total_carrinho = $total_carrinho ?? 0;

// Verificar se o cliente tem status_crediario = 1
$status_crediario = $usuario['status_crediario'] ?? 0;

//echo "Total de produtos no carrinho: " . $total_carrinho;
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $nomeFantasia; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
    <script src="cliente_home.js?v=<?php echo time(); ?>"></script><!--força a tualização-->
    <!--<script src="cadastro_inicial/localizador.js" defer></script>-->
    <!--<link rel="stylesheet" href="cliente_home.css">-->
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
            /* Alinha itens ao topo */
            padding: 20px;
        }

        header h1 {
            flex-grow: 1;
            /* Faz o título ocupar o espaço disponível */
            text-align: center;
            /* Centraliza o título horizontalmente */
            font-size: 30px;
            /* Tamanho padrão do título */
            line-height: 100px;
            /* Alinha verticalmente o título com a altura do cabeçalho */
            margin: 0;
            /* Remove margens padrão */
        }

        header .logo img {
            height: 150px;
            /* Aumenta o tamanho do logo */
            width: 150px;
            /* Ajuste proporcional ao tamanho */
            border-radius: 50%;
            /* Mantém o logo redondo */
        }

        .menu-superior-direito {
            font-size: 20px;
            display: flex;
            align-items: center; /* Alinha verticalmente os itens */
            justify-content: flex-end; /* Alinha os itens no canto superior direito */
            gap: 10px; /* Espaçamento entre os itens */
        }

        .menu-superior-direito span {
            margin: 0; /* Remove margens extras */
            transition: color 0.3s ease;
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

        /*.profile-dropdown {
position: relative;
}

.profile-dropdown #dropdownMenu {
display: none;
position: absolute;
top: 100%;
right: 0;
background: #fff;
box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
border-radius: 4px;
list-style: none;
padding: 10px;
}

.profile-dropdown:hover #dropdownMenu {
display: block;
}*/
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
            /* Centraliza verticalmente */
            /*left: 50vh;
height: 40vh; /* Altura total da tela */
            text-align: center;
            /*width: 95%;
/*padding: 10px;
margin-left: 10px;*/
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
            /*background: #f9f9f9;
border: 1px solid #ddd;*/
            border-radius: 60px;
            /*box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);*/
            margin: 10px auto;
            /* Centraliza e ajusta margens vertical e horizontal */
            max-width: 200px;
            /* Define o comprimento máximo do cartão */
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

        @media (max-width: 768px) {

            /*.sub-nav {
    flex-direction: column; /* Coloca os itens em coluna em telas menores */
            /*align-items: flex-start; /* Alinha os itens à esquerda */
            /*padding: 15px; /* Aumenta o padding em telas menores */
            /*}*/
            .menu-superior-direito .fa-shopping-cart {
                display: none;
            }

            /* Adicionando esta linha para esconder o ícone do menu */
            .menu-superior-direito .fa-bars {
                display: none;
                /* Oculta o ícone do menu em telas pequenas */
            }

            .carrinho-count {
                display: none !important;
            }

            .menu-superior-direito span {
                /*position: absolute; /* Mantém a barra lateral fixa */
                font-size: 0.7rem;
                /* Tamanho reduzido para o nome fantasia */
            }

            .sub-nav div {
                margin: 10px 0;
                /* Reduz o espaçamento entre os itens em telas menores */
                text-align: left;
                /* Alinha os itens à esquerda */
            }

            .nome-fantasia {
                font-size: 1.8rem;
                /* Tamanho reduzido para o nome fantasia */
                font-weight: bold;
                color: #ffffff;
                /* Cor mais suave para o texto */
                text-align: left;
                /* Alinha à esquerda para ficar mais natural ao lado da logo */
                margin: 0;
                line-height: 1.2;
                display: flex;
                justify-content: flex-start;
                /* Garante que o texto fique alinhado à esquerda */
                align-items: center;
                flex-grow: 1;
                /* Permite que o nome ocupe o máximo de espaço disponível ao lado da logo */
                padding-left: 15px;
                /* Espaço entre a logo e o nome */
                margin: 15px 0;
                /* Ajusta o espaçamento para telas pequenas */
            }

            /* Cartão do produto */
            .product-card {
                background: #ffffff;
                border: 1px solid #ddd;
                border-radius: 10px;
                width: 180px;
                /* Largura do cartão */
                height: 450px;
                /* Define a altura fixa */
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                overflow: hidden;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                text-align: center;
                padding: 3px;
            }

            .menu-mobile {
                display: flex;
                /* Exibe o menu mobile em telas pequenas */
            }

            .notificacao-count {
                font-size: 5px;
            }

            .carrinho-count {
                display: none;
            }

            .carrinho-count-rodape {
                position: absolute;
                top: 0px;
                /*right: 260px;*/
                background-color: chocolate;
                color: white;
                padding: 3px;
                border-radius: 50%;
                font-size: 10px;
                font-weight: bold;
            }
        }

        @media (max-width: 480px) {
            .nome-fantasia {
                font-size: 1.2rem;
                /* Ainda menor para dispositivos móveis */
                letter-spacing: 1px;
                /* Reduz o espaçamento entre as letras */
            }

            .logo-img {
                width: 130px;
                height: 130px;
                border-radius: 50%;
                margin-right: 10px;
            }

            .btn-login {
                padding: 3px 5px;
                border-radius: 5px;
            }

            .menu-mobile {
                display: flex;
                /* Exibe o menu mobile em telas pequenas */
            }

            /* Adicionando esta linha para esconder o ícone do menu */
            .menu-superior-direito .fa-bars {
                display: none;
                /* Oculta o ícone do menu em telas pequenas */
            }

            .menu-superior-direito .fa-shopping-cart {
                display: none;
            }

            .carrinho-count {
                display: none;
            }

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

        /* Responsividade geral */
        @media (max-width: 768px) {
            .logo img {
                max-width: 100px;
                /* Reduz o tamanho da logo */
                height: auto;
            }

            .nome-fantasia {
                font-size: 18px;
                /* Ajusta o tamanho da fonte */
                text-align: center;
            }

            .menu-superior-direito {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 10px;
            }

            .menu-superior-direito span {
                font-size: 14px;
            }

            .menu-superior-direito i {
                font-size: 18px;
            }

            .opcoes {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 10px;
            }

            .tab {
                flex: 1 1 calc(50% - 10px);
                /* Ajusta o tamanho das abas */
                text-align: center;
                padding: 10px;
                font-size: 14px;
            }

            .conteudo-aba h2 {
                font-size: 18px;
                text-align: center;
            }

            .parceiros-carousel .parceiro-card img {
                max-width: 80px;
                /* Ajusta o tamanho das imagens dos parceiros */
                height: auto;
            }

            .parceiros-carousel .parceiro-card h3 {
                font-size: 14px;
            }

            .products .product-card img {
                max-width: 100px;
                /* Ajusta o tamanho das imagens dos produtos */
                height: auto;
            }

            .products .product-card h3 {
                font-size: 14px;
            }

            .products .product-card p {
                font-size: 12px;
            }

            .input {
                width: 90%;
                /* Ajusta a largura dos inputs de pesquisa */
                margin: 10px auto;
                font-size: 14px;
            }

            .popup {
                width: 90%;
                /* Ajusta a largura do popup */
                height: auto;
            }

            .popup input {
                font-size: 14px;
            }

            .popup p {
                font-size: 14px;
            }

            footer.menu-mobile ul {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-around;
            }

            footer.menu-mobile ul li {
                flex: 1 1 20%;
                text-align: center;
            }

            footer.menu-mobile ul li i {
                font-size: 18px;
            }
        }

        @media (max-width: 480px) {
            .logo img {
                max-width: 80px;
            }

            .nome-fantasia {
                font-size: 16px;
            }

            .tab {
                flex: 1 1 100%;
                font-size: 12px;
            }

            .products .product-card img {
                max-width: 80px;
            }

            .products .product-card h3 {
                font-size: 12px;
            }

            .products .product-card p {
                font-size: 10px;
            }

            .input {
                font-size: 12px;
            }

            .popup p {
                font-size: 12px;
            }

            footer.menu-mobile ul li i {
                font-size: 16px;
            }
        }
    </style>
    <script>

        function exibirCampoPesquisaCatalogo(exibir) {
            var inputPesquisaParceiro = document.getElementById("inputPesquisaParceiroCatalogo");
            var inputPesquisaProduto = document.getElementById("inputPesquisaCatalogo");
            if (inputPesquisaParceiro) {
                inputPesquisaParceiro.style.display = exibir ? "block" : "none";
                inputPesquisaProduto.style.display = exibir ? "block" : "none";
                //console.log("Exibir campo de pesquisa: " + exibir);
            }
        }

        function exibirCampoPesquisaPromocao(exibir) {
            var inputPesquisaParceiro = document.getElementById("inputPesquisaParceiroPromocao");
            var inputPesquisaProduto = document.getElementById("inputPesquisaPromocao");
            if (inputPesquisaParceiro) {
                inputPesquisaParceiro.style.display = exibir ? "block" : "none";
                inputPesquisaProduto.style.display = exibir ? "block" : "none";
                //console.log("Exibir campo de pesquisa: " + exibir);
            }
        }

        function exibirCampoPesquisaFreteGratis(exibir) {
            var inputPesquisaParceiro = document.getElementById("inputPesquisaParceiroFrete_gratis");
            var inputPesquisaProduto = document.getElementById("inputPesquisaFrete_gratis");
            if (inputPesquisaParceiro) {
                inputPesquisaParceiro.style.display = exibir ? "block" : "none";
                inputPesquisaProduto.style.display = exibir ? "block" : "none";
                //console.log("Exibir campo de pesquisa: " + exibir);
            }
        }

        function exibirCampoPesquisaNovidades(exibir) {
            var inputPesquisaParceiro = document.getElementById("inputPesquisaParceiroNovidades");
            var inputPesquisaProduto = document.getElementById("inputPesquisaNovidades");
            if (inputPesquisaParceiro) {
                inputPesquisaParceiro.style.display = exibir ? "block" : "none";
                inputPesquisaProduto.style.display = exibir ? "block" : "none";
                //console.log("Exibir campo de pesquisa: " + exibir);
            }
        }

        function exibirCampoPesquisaCrediario(exibir) {
            var inputPesquisaParceiro = document.getElementById("inputPesquisaParceiroCrediario");
            var inputPesquisaProduto = document.getElementById("inputPesquisaCrediario");
            if (inputPesquisaParceiro) {
                inputPesquisaParceiro.style.display = exibir ? "block" : "none";
            }
            if (inputPesquisaProduto) {
                inputPesquisaProduto.style.display = exibir ? "block" : "none";
            }
        }

    </script>
</head>

<body>

    <!-- Header -->
    <header>
        <div class="logo">
            <img src="<?php if (isset($logo))
                echo $logo; ?>" alt="Logo" class="logo-img">
        </div>

        <h1 class="nome-fantasia">
            <?php
            if (!empty($nomeFantasia)) {
                echo htmlspecialchars($nomeFantasia);
            } else {
                echo "Nome Fantasia Indisponível";
            }
            ?>
        </h1>

        <div class="menu-superior-direito">
            <?php if ($usuarioLogado): ?>
                <span>Bem-vindo,
                    <strong><?php echo htmlspecialchars(explode(' ', $usuario['nome_completo'])[0]); ?></strong></span>
                <!-- Ícone de notificações com contagem -->
                <div class="notificacoes">
                    <i class="fas fa-bell" title="Notificações" onclick="toggleNotificacoes()"></i>
                    <!-- Exibir a contagem de notificações -->
                    <?php if ($total_notificacoes > 0): ?>
                        <span id="notificacao-count"
                            class="notificacao-count"><?php echo htmlspecialchars($total_notificacoes); ?></span>
                    <?php else: ?>
                        <span id="notificacao-count" class="notificacao-count" style="display: none;"></span>
                    <?php endif; ?>
                </div>
                <a href="comprar/meu_carrinho.php?id_cliente=<?php echo urlencode($id); ?>" style="color:#f0f0f0;"><i
                        class="fas fa-shopping-cart" title="Meu Carrinho" onclick=""></i></a>
                <!-- Exibir a contagem de notificações -->
                <?php if ($total_carrinho > 0): ?>
                    <span id="carrinho-count" class="carrinho-count"><?php echo htmlspecialchars($total_carrinho); ?></span>
                <?php else: ?>
                    <span id="carrinho-count" class="carrinho-count" style="display: none;"></span>
                <?php endif; ?>
                <i class="fas fa-bars" title="Menu" onclick="toggleMenu()"></i>
            <?php else: ?>
                <span>Seja bem-vindo!</span>
                <a href="login/lib/login.php" class="btn-login">Entrar</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Painel de notificações que aparece ao clicar no ícone de notificações -->
    <aside id="painel-notificacoes">
        <h2>Notificações: <?php echo htmlspecialchars(string: $total_notificacoes); ?></h2>
        <ul id="lista-notificacoes">
            <?php
            // Consulta para obter notificações do cliente onde lida = 1
            $sql_query_notificacoes = "SELECT * FROM contador_notificacoes_cliente WHERE id_cliente = ? AND lida = 1 ORDER BY data DESC";
            $stmt = $mysqli->prepare($sql_query_notificacoes);
            $stmt->bind_param("i", $id); // Substituir $id pelo ID do cliente
            $stmt->execute();
            $result = $stmt->get_result();

            // Verificar se há notificações
            if ($result->num_rows > 0) {
                // Iterar pelas notificações e renderizar no painel
                while ($notificacao = $result->fetch_assoc()) {
                    $idNotificacao = htmlspecialchars($notificacao['id']);
                    $dataOriginal = $notificacao['data']; // Substituir pela sua coluna de data
                    $dataFormatada = (new DateTime($dataOriginal))->format('d/m/Y H:i:s');
                    $mensagem = htmlspecialchars($notificacao['msg']);

                    echo "<li>";
                    echo "<a href='mensagem.php?id_cliente=" . htmlspecialchars($id) . "&id_not=" . $idNotificacao . "'>";
                    echo "<strong>$dataFormatada</strong><br>";
                    echo $mensagem;
                    echo "</a>";
                    echo "</li>";
                }
            } else {
                echo "<li>Sem notificações no momento.</li>";
            }

            $stmt->close();
            ?>
        </ul>

    </aside>

    <!-- Menu lateral que aparece abaixo do ícone de menu -->
    <aside id="menu-lateral">
        <ul>
            <!-- Item Perfil da Loja -->
            <li>
                <a href="perfil_cliente.php?id=<?php echo urlencode($id); ?>" title="Meu Perfil">
                    <i class="fas fa-user"></i>
                    <span>Perfil</span>
                </a>
            </li>

            <!-- Item crediario-->
            <li>
                <a href="perfil_crediario.php?id=<?php echo urlencode($id); ?>" title="Perfil no Crediário">
                    <i class="fas fa-user"></i>
                    <span>Perfil Crediario</span>
                </a>
            </li>

            <!-- Item crediario-->
            <li>
                <a href="meu_crediario.php?id=<?php echo urlencode($id); ?>" title="Crediário">
                    <i class="fas fa-handshake"></i>
                    <span>Meu Crediario</span>
                </a>
            </li>

            <!-- Item Meus pedidos-->
            <li>
                <a href="comprar/meus_pedidos.php?id=<?php echo urlencode($id); ?>" title="Meus Pedidos">
                    <i class="fas fa-box"></i> <!-- Ícone de pedido -->
                    <span>Meus Pedidos</span>
                </a>
            </li>

            <!-- Item de Mensagens -->
            <li>
                <a href="caixa_msg.php?id_cliente=<?php echo urlencode($id); ?>" title="Mensagens">
                    <i class="fas fa-envelope"></i>
                    <span>Mensagens</span>
                </a>
            </li>

            <!-- Item Configurações -->
            <li>
                <a href="configuracoes.php?id_admin=<?php echo urlencode($id); ?>" title="Configurações">
                    <i class="fas fa-cog"></i>
                    <span>Configurações</span>
                </a>
            </li>

            <!-- Item Sair -->
            <li>
                <a href="cliente_logout.php" title="Sair">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Sair</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- Conteúdo principal -->
    <main id="main-content">
        <!-- Conteúdo -->
        <div class="opcoes">
            <!-- Conteúdo -->
            <div class="tab active" onclick="mostrarConteudo('catalogo',this)">
                <span>Catálogo</span>
            </div>

            <div class="tab" onclick="mostrarConteudo('promocoes',this)">
                <span class="icone-promocao" title="Produto em promoção">🔥</span><span>Promoções</span>
            </div>

            <div class="tab" onclick="mostrarConteudo('frete_gratis',this)">
                <span class="icone-frete-gratis" title="Frete grátis">🚚</span><span>Frete Grátis</span>
            </div>

            <div class="tab" onclick="mostrarConteudo('novidades',this)">
                <span class="icone-novidades" title="Novidades">🆕</span><span>Novidades</span>
            </div>

            <?php if ($status_crediario == 1): ?>
                <div class="tab" onclick="mostrarConteudo('crediario', this)">
                    <span class="icone-crediario" title="Produtos no Crediário">🤝</span><span>Crediário</span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Conteúdos correspondentes às abas -->
        <div id="conteudo-catalogo" class="conteudo-aba" style="display: none;">
            <h2>Nossos Parceiros</h2>
            <?php
            // Consulta para buscar parceiros ativos
            $sql_parceiros = "SELECT * FROM meus_parceiros WHERE status = '1'";
            $result_parceiros = $mysqli->query($sql_parceiros) or die($mysqli->error);

            // Consulta para buscar produtos visíveis e aprovados que não estão ocultos
            $sql_produtos = "SELECT * FROM produtos WHERE oculto != '1' AND produto_aprovado = '1'";
            $result_produtos = $mysqli->query($sql_produtos) or die($mysqli->error);

            if ($result_parceiros->num_rows > 0 && $result_produtos->num_rows > 0): ?>
                <!-- Pesquisa de Parceiros -->
                <input id="inputPesquisaParceiroCatalogo" class="input" type="text" placeholder="Pesquisar Parceiro.">
                <!-- Carrossel de Parceiros -->
                <div class="parceiros-carousel owl-carousel">
                    <?php while ($parceiro = $result_parceiros->fetch_assoc()):
                        $logoParceiro = !empty($parceiro['logo']) ? $parceiro['logo'] : 'placeholder.jpg'; ?>
                        <div class="parceiro-card"
                            onclick="window.location.href='loja_parceiro.php?id=<?php echo $parceiro['id']; ?>'">
                            <img src="../parceiros/arquivos/<?php echo htmlspecialchars($logoParceiro); ?>"
                                alt="Loja não encontrada">
                            <h3><?php echo mb_strimwidth(htmlspecialchars($parceiro['nomeFantasia'] ?? ''), 0, 18, '...'); ?>
                            </h3>
                            <p><?php echo htmlspecialchars($parceiro['categoria']); ?></p>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>Nenhum parceiro encontrado.</p>
            <?php endif; ?>
            <!-- Mensagem de Parceiro Não Encontrado -->
            <p id="mensagemParNaoEncontradoCatalogo" style="display: none;">Parceiro não encontrado.</p>

            <!-- Produtos -->
            <h2>Produtos</h2>
            <?php
            if ($result_parceiros->num_rows > 0 && $result_produtos->num_rows > 0): ?>
                <!-- Pesquisa de Produtos -->
                <input id="inputPesquisaCatalogo" class="input" type="text" placeholder="Pesquisar Produto.">
                <div class="container">
                    <div class="products">
                        <?php while ($produto = $result_produtos->fetch_assoc()):
                            // Determinar o valor do produto
                            $valorProduto = ($produto['promocao'] === '1')
                                ? $produto['valor_promocao'] + ($produto['valor_promocao'] * ($taxa['taxa_padrao'] / 100))
                                : $produto['valor_venda_vista'];

                            // Determinar o valor do frete
                            $valorFrete = ($produto['promocao'] === '1' && $produto['frete_gratis_promocao'] === '1')
                                ? 0
                                : $produto['valor_frete'];

                            $imagens = !empty($produto['imagens']) ? explode(',', $produto['imagens']) : [];
                            $primeira_imagem = $imagens[0] ?? 'placeholder.jpg'; ?>
                            <div class="product-card">
                                <img src="../parceiros/produtos/img_produtos/<?php echo htmlspecialchars($primeira_imagem); ?>"
                                    alt="<?php echo htmlspecialchars($produto['nome_produto']); ?>">
                                <?php if ($produto['frete_gratis'] == '1' || ($produto['promocao'] == '1' && $produto['frete_gratis_promocao'] == '1')): ?>
                                    <span class="icone-frete-gratis" title="Frete grátis">🚚</span>
                                <?php endif; ?>
                                <?php if ($produto['promocao'] == '1'): ?>
                                    <span class="icone-promocao" title="Produto em promoção">🔥</span>
                                <?php endif; ?>
                                <?php
                                $dataCadastro = new DateTime($produto['data']);
                                $dataAtual = new DateTime();
                                $diasDesdeCadastro = $dataCadastro->diff($dataAtual)->days;
                                if ($diasDesdeCadastro <= 30): ?>
                                    <span class="icone-novidades" title="Novidades">🆕</span>
                                <?php endif; ?>
                                <?php if ($produto['vende_crediario'] == '1' && $status_crediario == 1): ?>
                                    <span class="icone-crediario" title="Disponível no crediário">🤝</span>
                                <?php endif; ?>
                                <h3><?php echo htmlspecialchars($produto['nome_produto']); ?></h3>
                                <p class="moeda">R$ <?php echo number_format($valorProduto, 2, ',', '.'); ?></p>
                                <a href="detalhes_produto.php?id_cliente=<?php echo $id; ?>&id_produto=<?php echo $produto['id_produto']; ?>"
                                    class="btn">Detalhes</a>

                                <?php if (isset($usuarioLogado) && $usuarioLogado): ?>
                                    <a href="#" class="btn"
                                        onclick="abrirPopup('<?php echo $produto['id_produto']; ?>', '<?php echo $produto['nome_produto']; ?>', '<?php echo $valorProduto; ?>', '<?php echo $valorFrete; ?>')">Adicionar
                                        ao Carrinho</a>
                                <?php else: ?>
                                    <a href="login/lib/login.php" class="btn">Faça login para comprar</a>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            <?php else: ?>
                <p>Não há produtos no momento.</p>
            <?php endif; ?>
            <!-- Mensagem de produto não encontrado -->
            <p id="mensagemNaoEncontradoCatalogo" style="display: none;">Produto não encontrado.</p>
        </div>

        <!-- Conteúdos correspondentes às abas -->
        <div id="conteudo-promocoes" class="conteudo-aba" style="display: none;">
            <h2>Nossos Parceiros</h2>
            <div class="container">
                <!-- Pesquisa de Parceiros -->
                <input id="inputPesquisaParceiroPromocao" style="display: none;" class="input" type="text"
                    placeholder="Pesquisar Parceiro.">
            </div>

            <?php
            // Consulta para buscar parceiros com produtos em promoção
            $sql_parceiros = "
                SELECT DISTINCT mp.* 
                FROM meus_parceiros mp
                JOIN produtos p ON mp.id = p.id_parceiro
                WHERE mp.status = '1'
                AND p.oculto != '1' 
                AND p.produto_aprovado = '1'
                AND p.promocao = '1'
            ";
            $result_parceiros = $mysqli->query($sql_parceiros) or die($mysqli->error);
            ?>

            <!-- Carrossel de Parceiros -->
            <div class="parceiros-carousel owl-carousel">
                <?php if ($result_parceiros->num_rows > 0):
                    while ($parceiro = $result_parceiros->fetch_assoc()):
                        $logoParceiro = !empty($parceiro['logo']) ? $parceiro['logo'] : 'placeholder.jpg';
                        ?>
                        <div class="parceiro-card"
                            onclick="window.location.href='loja_parceiro.php?id=<?php echo $parceiro['id']; ?>&id_cliente=<?php echo $usuario['id']; ?>'">
                            <img src="../parceiros/arquivos/<?php echo htmlspecialchars($logoParceiro); ?>"
                                alt="Loja não encontrada">
                            <h3><?php echo mb_strimwidth(htmlspecialchars($parceiro['nomeFantasia'] ?? ''), 0, 18, '...'); ?>
                            </h3>
                            <p><?php echo htmlspecialchars($parceiro['categoria']); ?></p>
                        </div>
                    <?php endwhile; else: ?>
                    <p>Nenhum parceiro com promoção no momento.</p>
                <?php endif; ?>
            </div>

            <!-- Mensagem de Parceiro Não Encontrado -->
            <p id="mensagemParNaoEncontradoPromocao" style="display: none;">Parceiro não encontrado.</p>

            <!-- Produtos -->
            <h2>Produtos</h2>
            <div class="container">
                <!-- Pesquisa de Produtos -->
                <input id="inputPesquisaPromocao" style="display: none;" class="input" type="text"
                    placeholder="Pesquisar Produto.">
            </div>

            <div class="products">
                <?php
                // Consulta para buscar produtos em promoção
                $sql_produtos = "
                    SELECT * 
                    FROM produtos 
                    WHERE promocao = '1' 
                    AND oculto != '1' 
                    AND produto_aprovado = '1'
                ";
                $result_produtos = $mysqli->query($sql_produtos) or die($mysqli->error);
                ?>

                <?php if ($result_produtos->num_rows > 0): ?>
                    <script>exibirCampoPesquisaPromocao(true);</script>
                    <?php while ($produto = $result_produtos->fetch_assoc()): ?>
                        <div class="product-card">
                            <?php
                            $imagens = !empty($produto['imagens']) ? explode(',', $produto['imagens']) : [];
                            $primeira_imagem = $imagens[0] ?? 'placeholder.jpg';

                            // Determinar o valor do produto
                            $valorProduto = $produto['valor_promocao'] + ($produto['valor_promocao'] * ($taxa['taxa_padrao'] / 100));

                            // Determinar o valor do frete
                            if ($produto['promocao'] === '1' && $produto['frete_gratis_promocao'] === '1') {
                                $valorFrete = 0;
                            } else if ($produto['promocao'] === '1' && $produto['frete_gratis_promocao'] === '0') {
                                $valorFrete = $produto['valor_frete_promocao'];
                            } else if ($produto['frete_gratis'] === '1') {
                                $valorFrete = 0;
                            } else {
                                $valorFrete = $produto['valor_frete'];
                            }
                            ?>
                            <img src="../parceiros/produtos/img_produtos/<?php echo htmlspecialchars($primeira_imagem); ?>"
                                alt="<?php echo htmlspecialchars($produto['nome_produto']); ?>">

                            <?php
                            // Exibir ícone de frete grátis
                            if ($produto['frete_gratis'] === '1' || ($produto['promocao'] === '1' && $produto['frete_gratis_promocao'] === '1')): ?>
                                <span class="icone-frete-gratis" title="Frete grátis">🚚</span>
                            <?php endif; ?>

                            <?php
                            // Exibir ícone de promoção
                            if ($produto['promocao'] === '1'): ?>
                                <span class="icone-promocao" title="Produto em promoção">🔥</span>
                            <?php endif; ?>

                            <?php
                            // Exibir ícone de novidades
                            $dataCadastro = new DateTime($produto['data']);
                            $dataAtual = new DateTime();
                            $diasDesdeCadastro = $dataCadastro->diff($dataAtual)->days;

                            if ($diasDesdeCadastro <= 30): ?>
                                <span class="icone-novidades" title="Novidades">🆕</span>
                            <?php endif; ?>

                            <?php if ($produto['vende_crediario'] == '1' && $status_crediario == 1): ?>
                                <span class="icone-crediario" title="Disponível no crediário">🤝</span>
                            <?php endif; ?>

                            <h3><?php echo htmlspecialchars($produto['nome_produto']); ?></h3>
                            <p class="moeda">R$ <?php echo number_format($valorProduto, 2, ',', '.'); ?></p>
                            <a href="detalhes_produto.php?id_cliente=<?php echo $id; ?>&id_produto=<?php echo $produto['id_produto']; ?>"
                                class="btn">Detalhes</a>

                            <?php if (isset($usuarioLogado) && $usuarioLogado): ?>
                                <a href="#" class="btn"
                                    onclick="abrirPopup('<?php echo $produto['id_produto']; ?>', '<?php echo $produto['nome_produto']; ?>', '<?php echo $valorProduto; ?>', '<?php echo $valorFrete; ?>')">Adicionar
                                    ao Carrinho</a>
                            <?php else: ?>
                                <a href="login/lib/login.php" class="btn">Faça login para comprar</a>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <script>exibirCampoPesquisaPromocao(false);</script>
                    <p>Não há produtos na promoção no momento.</p>
                <?php endif; ?>
                <p id="mensagemNaoEncontradoPromocao" style="display: none;">Produto não encontrado.</p>
            </div>
        </div>

        <!-- Conteúdos correspondentes às abas -->
        <div id="conteudo-frete_gratis" class="conteudo-aba" style="display: none;">
            <h2>Nossos Parceiros</h2>
            <div class="container">
                <!-- Pesquisa de Parceiros -->
                <input id="inputPesquisaParceiroFrete_gratis" style="display: none;" class="input" type="text"
                    placeholder="Pesquisar Parceiro.">
            </div>

            <?php
            // Consulta para buscar parceiros com produtos com frete grátis
            $sql_parceiros = "
                SELECT DISTINCT mp.* 
                FROM meus_parceiros mp
                JOIN produtos p ON mp.id = p.id_parceiro
                WHERE mp.status = '1'
                AND (
                    p.frete_gratis = '1' 
                    OR (p.promocao = '1' AND p.frete_gratis_promocao = '1')
                )
            ";
            $result_parceiros = $mysqli->query($sql_parceiros) or die($mysqli->error);
            ?>

            <!-- Carrossel de Parceiros -->
            <div class="parceiros-carousel owl-carousel">
                <?php if ($result_parceiros->num_rows > 0):
                    while ($parceiro = $result_parceiros->fetch_assoc()):
                        $logoParceiro = !empty($parceiro['logo']) ? $parceiro['logo'] : 'placeholder.jpg';
                        ?>
                        <div class="parceiro-card"
                            onclick="window.location.href='loja_parceiro.php?id=<?php echo $parceiro['id']; ?>&id_cliente=<?php echo $usuario['id']; ?>'">
                            <img src="../parceiros/arquivos/<?php echo htmlspecialchars($logoParceiro); ?>"
                                alt="Loja não encontrada">
                            <h3><?php echo mb_strimwidth(htmlspecialchars($parceiro['nomeFantasia'] ?? ''), 0, 18, '...'); ?>
                            </h3>
                            <p><?php echo htmlspecialchars($parceiro['categoria']); ?></p>
                        </div>
                    <?php endwhile; else: ?>
                    <p>Nenhum parceiro com frete grátis no momento.</p>
                <?php endif; ?>
            </div>

            <!-- Mensagem de Parceiro Não Encontrado -->
            <p id="mensagemParNaoEncontradoFrete_gratis" style="display: none;">Parceiro não encontrado.</p>

            <!-- Produtos -->
            <h2>Produtos</h2>
            <div class="container">
                <!-- Pesquisa de Produtos -->
                <input id="inputPesquisaFrete_gratis" style="display: none;" class="input" type="text"
                    placeholder="Pesquisar Produto.">
            </div>

            <div class="products">
                <?php
                // Consulta para buscar produtos com frete grátis
                $sql_produtos = "
                    SELECT * 
                    FROM produtos 
                    WHERE oculto != '1' 
                    AND produto_aprovado = '1'
                    AND (
                        frete_gratis = '1' 
                        OR (promocao = '1' AND frete_gratis_promocao = '1')
                    )
                ";
                $result_produtos = $mysqli->query($sql_produtos) or die($mysqli->error);
                ?>

                <?php if ($result_produtos->num_rows > 0): ?>
                    <script>exibirCampoPesquisaFreteGratis(true);</script>
                    <?php while ($produto = $result_produtos->fetch_assoc()): ?>
                        <div class="product-card">
                            <?php
                            $imagens = !empty($produto['imagens']) ? explode(',', $produto['imagens']) : [];
                            $primeira_imagem = $imagens[0] ?? 'placeholder.jpg';

                            // Determinar o valor do frete
                            if ($produto['promocao'] === '1' && $produto['frete_gratis_promocao'] === '1') {
                                $valorFrete = 0;
                            } else if ($produto['promocao'] === '1' && $produto['frete_gratis_promocao'] === '0') {
                                $valorFrete = $produto['valor_frete_promocao'];
                            } else if ($produto['frete_gratis'] === '1') {
                                $valorFrete = 0;
                            } else {
                                $valorFrete = $produto['valor_frete'];
                            }

                            // Determinar o valor do frete
                            $valorFrete = ($produto['frete_gratis'] === '1' || ($produto['promocao'] === '1' && $produto['frete_gratis_promocao'] === '1')) ? 0 : $produto['valor_frete'];
                            ?>
                            <img src="../parceiros/produtos/img_produtos/<?php echo htmlspecialchars($primeira_imagem); ?>"
                                alt="<?php echo htmlspecialchars($produto['nome_produto']); ?>">

                            <?php
                            // Exibir ícone de frete grátis
                            if ($produto['frete_gratis'] === '1' || ($produto['promocao'] === '1' && $produto['frete_gratis_promocao'] === '1')): ?>
                                <span class="icone-frete-gratis" title="Frete grátis">🚚</span>
                            <?php endif; ?>

                            <?php
                            // Exibir ícone de promoção
                            if ($produto['promocao'] === '1'): ?>
                                <span class="icone-promocao" title="Produto em promoção">🔥</span>
                            <?php endif; ?>

                            <?php
                            // Exibir ícone de novidades
                            $dataCadastro = new DateTime($produto['data']);
                            $dataAtual = new DateTime();
                            $diasDesdeCadastro = $dataCadastro->diff($dataAtual)->days;

                            if ($diasDesdeCadastro <= 30): ?>
                                <span class="icone-novidades" title="Novidades">🆕</span>
                            <?php endif; ?>

                            <?php if ($produto['vende_crediario'] == '1' && $status_crediario == 1): ?>
                                <span class="icone-crediario" title="Disponível no crediário">🤝</span>
                            <?php endif; ?>

                            <h3><?php echo htmlspecialchars($produto['nome_produto']); ?></h3>
                            <p class="moeda">R$ <?php echo number_format($valorProduto, 2, ',', '.'); ?></p>
                            <a href="detalhes_produto.php?id_cliente=<?php echo $id; ?>&id_produto=<?php echo $produto['id_produto']; ?>"
                                class="btn">Detalhes</a>

                            <?php if (isset($usuarioLogado) && $usuarioLogado): ?>
                                <a href="#" class="btn"
                                    onclick="abrirPopup('<?php echo $produto['id_produto']; ?>', '<?php echo $produto['nome_produto']; ?>', '<?php echo $valorProduto; ?>', '<?php echo $valorFrete; ?>')">Adicionar
                                    ao Carrinho</a>
                            <?php else: ?>
                                <a href="login/lib/login.php" class="btn">Faça login para comprar</a>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <script>exibirCampoPesquisaFreteGratis(false);</script>
                    <p>Não há produtos no momento.</p>
                <?php endif; ?>
                <p id="mensagemNaoEncontradoFrete_gratis" style="display: none;">Produto não encontrado.</p>
            </div>
        </div>

        <!-- Conteúdos correspondentes às abas -->
        <div id="conteudo-novidades" class="conteudo-aba" style="display: none;">
            <h2>Nossos Parceiros</h2>
            <div class="container">
                <!-- Pesquisa de Parceiros -->
                <input id="inputPesquisaParceiroNovidades" style="display: none;" class="input" type="text"
                    placeholder="Pesquisar Parceiro.">
            </div>

            <?php
            // Consulta para buscar parceiros com produtos novos
            $sql_parceiros = "
                SELECT DISTINCT mp.* 
                FROM meus_parceiros mp
                JOIN produtos p ON mp.id = p.id_parceiro
                WHERE mp.status = '1'
                AND p.oculto != '1' 
                AND p.produto_aprovado = '1'
                AND DATEDIFF(NOW(), p.data) <= 30
            ";
            $result_parceiros = $mysqli->query($sql_parceiros) or die($mysqli->error);
            ?>

            <!-- Carrossel de Parceiros -->
            <div class="parceiros-carousel owl-carousel">
                <?php if ($result_parceiros->num_rows > 0):
                    while ($parceiro = $result_parceiros->fetch_assoc()):
                        $logoParceiro = !empty($parceiro['logo']) ? $parceiro['logo'] : 'placeholder.jpg';
                        ?>
                        <div class="parceiro-card"
                            onclick="window.location.href='loja_parceiro.php?id=<?php echo $parceiro['id']; ?>&id_cliente=<?php echo $usuario['id']; ?>'">
                            <img src="../parceiros/arquivos/<?php echo htmlspecialchars($logoParceiro); ?>"
                                alt="Loja não encontrada">
                            <h3><?php echo mb_strimwidth(htmlspecialchars($parceiro['nomeFantasia'] ?? ''), 0, 18, '...'); ?>
                            </h3>
                            <p><?php echo htmlspecialchars($parceiro['categoria']); ?></p>
                        </div>
                    <?php endwhile; else: ?>
                    <p>Nenhum parceiro com novidades no momento.</p>
                <?php endif; ?>
            </div>

            <!-- Mensagem de Parceiro Não Encontrado -->
            <p id="mensagemParNaoEncontradoNovidades" style="display: none;">Parceiro não encontrado.</p>

            <!-- Produtos -->
            <h2>Produtos</h2>
            <div class="container">
                <!-- Pesquisa de Produtos -->
                <input id="inputPesquisaNovidades" style="display: none;" class="input" type="text"
                    placeholder="Pesquisar Produto.">
            </div>

            <div class="products">
                <?php
                // Consulta para buscar produtos novos
                $sql_produtos = "
                    SELECT * 
                    FROM produtos 
                    WHERE oculto != '1' 
                    AND produto_aprovado = '1'
                    AND DATEDIFF(NOW(), data) <= 30
                ";
                $result_produtos = $mysqli->query($sql_produtos) or die($mysqli->error);
                ?>

                <?php if ($result_produtos->num_rows > 0): ?>
                    <script>exibirCampoPesquisaNovidades(true);</script>
                    <?php while ($produto = $result_produtos->fetch_assoc()): ?>
                        <div class="product-card">
                            <?php
                            $imagens = !empty($produto['imagens']) ? explode(',', $produto['imagens']) : [];
                            $primeira_imagem = $imagens[0] ?? 'placeholder.jpg';

                            // Determinar o valor do frete
                            if ($produto['promocao'] === '1' && $produto['frete_gratis_promocao'] === '1') {
                                $valorFrete = 0;
                            } else if ($produto['promocao'] === '1' && $produto['frete_gratis_promocao'] === '0') {
                                $valorFrete = $produto['valor_frete_promocao'];
                            } else if ($produto['frete_gratis'] === '1') {
                                $valorFrete = 0;
                            } else {
                                $valorFrete = $produto['valor_frete'];
                            }

                            // Determinar o valor do frete
                            $valorFrete = ($produto['frete_gratis'] === '1' || ($produto['promocao'] === '1' && $produto['frete_gratis_promocao'] === '1')) ? 0 : $produto['valor_frete'];
                            ?>
                            <img src="../parceiros/produtos/img_produtos/<?php echo htmlspecialchars($primeira_imagem); ?>"
                                alt="<?php echo htmlspecialchars($produto['nome_produto']); ?>">

                            <?php
                            // Exibir ícone de frete grátis
                            if ($produto['frete_gratis'] === '1' || ($produto['promocao'] === '1' && $produto['frete_gratis_promocao'] === '1')): ?>
                                <span class="icone-frete-gratis" title="Frete grátis">🚚</span>
                            <?php endif; ?>

                            <?php
                            // Exibir ícone de promoção
                            if ($produto['promocao'] === '1'): ?>
                                <span class="icone-promocao" title="Produto em promoção">🔥</span>
                            <?php endif; ?>

                            <?php
                            // Exibir ícone de novidades
                            $dataCadastro = new DateTime($produto['data']);
                            $dataAtual = new DateTime();
                            $diasDesdeCadastro = $dataCadastro->diff($dataAtual)->days;

                            if ($diasDesdeCadastro <= 30): ?>
                                <span class="icone-novidades" title="Novidades">🆕</span>
                            <?php endif; ?>

                            <?php if ($produto['vende_crediario'] == '1' && $status_crediario == 1): ?>
                                <span class="icone-crediario" title="Disponível no crediário">🤝</span>
                            <?php endif; ?>

                            <h3><?php echo htmlspecialchars($produto['nome_produto']); ?></h3>
                            <p class="moeda">R$ <?php echo number_format($valorProduto, 2, ',', '.'); ?></p>
                            <a href="detalhes_produto.php?id_cliente=<?php echo $id; ?>&id_produto=<?php echo $produto['id_produto']; ?>"
                                class="btn">Detalhes</a>

                            <?php if (isset($usuarioLogado) && $usuarioLogado): ?>
                                <a href="#" class="btn"
                                    onclick="abrirPopup('<?php echo $produto['id_produto']; ?>', '<?php echo $produto['nome_produto']; ?>', '<?php echo $valorProduto; ?>', '<?php echo $valorFrete; ?>')">Adicionar
                                    ao Carrinho</a>
                            <?php else: ?>
                                <a href="login/lib/login.php" class="btn">Faça login para comprar</a>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <script>exibirCampoPesquisaNovidades(false);</script>
                    <p>Não há produtos no momento.</p>
                <?php endif; ?>
                <p id="mensagemNaoEncontradoNovidades" style="display: none;">Produto não encontrado.</p>
            </div>
        </div>

        <!-- Conteúdos correspondentes às abas -->
        <?php if ($status_crediario == 1): ?>
            <div id="conteudo-crediario" class="conteudo-aba" style="display: none;">
                <h2>Nossos Parceiros</h2>
                <div class="container">
                    <!-- Pesquisa de Parceiros -->
                    <input id="inputPesquisaParceiroCrediario" class="input" type="text" placeholder="Pesquisar Parceiro.">
                </div>

                <!-- Carrossel de Parceiros -->
                <div class="parceiros-carousel owl-carousel">
                    <?php
                    // Consulta para buscar parceiros com produtos vendidos no crediário
                    $sql_parceiros_crediario = "
                        SELECT DISTINCT mp.* 
                        FROM meus_parceiros mp
                        JOIN produtos p ON mp.id = p.id_parceiro
                        WHERE mp.status = '1'
                        AND p.vende_crediario = '1'
                        AND p.oculto != '1'
                        AND p.produto_aprovado = '1'
                    ";
                    $result_parceiros_crediario = $mysqli->query($sql_parceiros_crediario) or die($mysqli->error);

                    if ($result_parceiros_crediario->num_rows > 0):
                        while ($parceiro = $result_parceiros_crediario->fetch_assoc()):
                            $logoParceiro = !empty($parceiro['logo']) ? $parceiro['logo'] : 'placeholder.jpg';
                            ?>
                            <div class="parceiro-card"
                                onclick="window.location.href='loja_parceiro.php?id=<?php echo $parceiro['id']; ?>&id_cliente=<?php echo $usuario['id']; ?>'">
                                <img src="../parceiros/arquivos/<?php echo htmlspecialchars($logoParceiro); ?>"
                                    alt="Loja não encontrada">
                                <h3><?php echo mb_strimwidth(htmlspecialchars($parceiro['nomeFantasia'] ?? ''), 0, 18, '...'); ?>
                                </h3>
                                <p><?php echo htmlspecialchars($parceiro['categoria']); ?></p>
                            </div>
                        <?php endwhile; else: ?>
                        <p>Nenhum parceiro com produtos no crediário no momento.</p>
                    <?php endif; ?>
                </div>

                <!-- Mensagem de Parceiro Não Encontrado -->
                <p id="mensagemParNaoEncontradoCrediario" style="display: none;">Parceiro não encontrado.</p>

                <!-- Produtos -->
                <h2>Produtos Disponíveis no Crediário</h2>
                <div class="container">
                    <!-- Pesquisa de Produtos -->
                    <input id="inputPesquisaCrediario" class="input" type="text" placeholder="Pesquisar Produto.">
                </div>

                <div class="products">
                    <?php
                    // Consulta para buscar produtos que podem ser vendidos no crediário
                    $sql_produtos_crediario = "
                        SELECT * 
                        FROM produtos 
                        WHERE vende_crediario = '1' 
                        AND oculto != '1' 
                        AND produto_aprovado = '1'
                    ";
                    $result_produtos_crediario = $mysqli->query($sql_produtos_crediario) or die($mysqli->error);

                    if ($result_produtos_crediario->num_rows > 0): ?>
                        <script>exibirCampoPesquisaCrediario(true);</script>
                        <?php while ($produto = $result_produtos_crediario->fetch_assoc()):
                            $imagens = !empty($produto['imagens']) ? explode(',', $produto['imagens']) : [];
                            $primeira_imagem = $imagens[0] ?? 'placeholder.jpg';

                            // Determinar o valor do produto
                            $valorProduto = ($produto['promocao'] === '1')
                                ? $produto['valor_promocao'] + ($produto['valor_promocao'] * ($taxa['taxa_padrao'] / 100))
                                : $produto['valor_venda_vista'];

                            // Determinar o valor do frete
                            $valorFrete = ($produto['promocao'] === '1' && $produto['frete_gratis_promocao'] === '1') ? 0 : $produto['valor_frete'];
                            ?>
                            <div class="product-card">
                                <img src="../parceiros/produtos/img_produtos/<?php echo htmlspecialchars($primeira_imagem); ?>"
                                    alt="<?php echo htmlspecialchars($produto['nome_produto']); ?>">
                                <?php if ($produto['frete_gratis'] == '1' || ($produto['promocao'] == '1' && $produto['frete_gratis_promocao'] == '1')): ?>
                                    <span class="icone-frete-gratis" title="Frete grátis">🚚</span>
                                <?php endif; ?>
                                <?php if ($produto['promocao'] == '1'): ?>
                                    <span class="icone-promocao" title="Produto em promoção">🔥</span>
                                <?php endif; ?>
                                <?php
                                $dataCadastro = new DateTime($produto['data']);
                                $dataAtual = new DateTime();
                                $diasDesdeCadastro = $dataCadastro->diff($dataAtual)->days;
                                if ($diasDesdeCadastro <= 30): ?>
                                    <span class="icone-novidades" title="Novidades">🆕</span>
                                <?php endif; ?>
                                <?php if ($produto['vende_crediario'] == '1' && $status_crediario == 1): ?>
                                    <span class="icone-crediario" title="Disponível no crediário">🤝</span>
                                <?php endif; ?>
                                <h3><?php echo htmlspecialchars($produto['nome_produto']); ?></h3>
                                <p class="moeda">R$ <?php echo number_format($valorProduto, 2, ',', '.'); ?></p>
                                <a href="detalhes_produto.php?id_cliente=<?php echo $id; ?>&id_produto=<?php echo $produto['id_produto']; ?>"
                                    class="btn">Detalhes</a>

                                <?php if (isset($usuarioLogado) && $usuarioLogado): ?>
                                    <a href="#" class="btn"
                                        onclick="abrirPopup('<?php echo $produto['id_produto']; ?>', '<?php echo $produto['nome_produto']; ?>', '<?php echo $valorProduto; ?>', '<?php echo $valorFrete; ?>')">Adicionar
                                        ao Carrinho</a>
                                <?php else: ?>
                                    <a href="login/lib/login.php" class="btn">Faça login para comprar</a>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <script>exibirCampoPesquisaCrediario(false);</script>
                        <p>Não há produtos disponíveis no crediário no momento.</p>
                    <?php endif; ?>
                    <p id="mensagemNaoEncontradoCrediario" style="display: none;">Produto não encontrado.</p>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <div class="popup" id="popup">
        <h2>Detalhes do Produto</h2>
        <form id="formCarrinho" action="comprar/carrinho.php">
            <aside id="info">
                <input type="hidden" id="id_cliente" name="id_cliente" value="<?php echo htmlspecialchars($id); ?>">
                <input type="hidden" id="id_produto_carrinho" name="id_produto_carrinho">
                <input type="text" id="produtoNome" name="produtoNome" readonly>

                <p>Preço R$:
                    <input type="text" id="produtoPreco" name="produtoPreco" readonly>
                </p>

                <p>Quantidade:
                    <input type="number" id="quantidade" name="quantidade" value="1" min="1" oninput="calcularTotal()">
                </p>

                <p>Valor Total: R$
                    <input type="text" id="total" name="total" readonly>
                </p>

                <p>Valor Frete:
                    <span id="frete"></span>
                </p>

            </aside>

            <button type="submit" class="confirm-btn">Adicionar ao Carrinho</button>
        </form>
        <button class="close-btn" onclick="fecharPopup()">Cancelar</button>
    </div>

    <div id="resposra-carrinho" style="display: none;">
        <!-- Mensagem de retorno -->
        <p id="mensagem"></p>
    </div>

    <div class="overlay" id="overlay" onclick="fecharPopup()"></div>

    <script>
        let precoProduto = 0; // Variável global para armazenar o preço do produto

        function abrirPopup(id, produto, preco, frete) {
            // Converte para float e garante apenas 2 casas decimais
            precoProduto = parseFloat(preco).toFixed(2);

            // Formata corretamente no padrão brasileiro
            let precoFormatado = Number(precoProduto).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            let freteFormatado = frete == 0
                ? "<span style='color: green; font-weight: bold;'>Frete Grátis</span>"
                : `R$ ${Number(frete).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

            // Define os valores nos inputs
            document.getElementById('id_produto_carrinho').value = id;
            document.getElementById('produtoNome').value = produto;
            document.getElementById('produtoPreco').value = precoFormatado;
            document.getElementById('total').value = precoFormatado;
            document.getElementById('frete').innerHTML = freteFormatado;

            // Exibe o popup
            document.getElementById('popup').style.display = 'block';
            document.getElementById('overlay').style.display = 'block';
        }

        function calcularTotal() {
            let quantidade = parseInt(document.getElementById('quantidade').value);

            if (isNaN(quantidade) || quantidade < 1) {
                quantidade = 1; // Evita valores inválidos
            }

            // Calcula o total
            let total = (precoProduto * quantidade).toFixed(2);

            // Formata corretamente no padrão brasileiro
            let totalFormatado = Number(total).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            // Atualiza o valor total no input
            document.getElementById('total').value = totalFormatado;
        }

        function fecharPopup() {
            document.getElementById('quantidade').value = 1;
            document.getElementById('popup').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }

        document.addEventListener('DOMContentLoaded', function () {
            const overlay = document.getElementById('overlay');

            overlay.addEventListener('click', function (event) {
                fecharPopup();
            });
        });

        document.getElementById("formCarrinho").addEventListener("submit", function (event) {
            event.preventDefault(); // Evita o envio tradicional do formulário

            let formData = new FormData(this);

            fetch("comprar/carrinho.php", {
                method: "POST",
                body: formData
            })
                .then(response => response.text())  // Recebe a resposta como texto
                .then(data => {
                    //console.log("Resposta recebida:", data);  // Verifique o conteúdo da resposta
                    try {
                        let jsonResponse = JSON.parse(data);  // Tente fazer o parse
                        let mensagem = document.getElementById("mensagem");
                        mensagem.innerText = jsonResponse.message;
                        mensagem.style.color = jsonResponse.status === "success" ? "green" : "red";
                        fecharPopup();
                        abrirResposta();
                    } catch (e) {
                        console.error('Erro ao interpretar JSON:', e);
                    }
                })
                .catch(error => {
                    console.error("Erro:", error);
                });
        });

        function abrirResposta() {
            // Exibe o popup
            document.getElementById('resposra-carrinho').style.display = 'block';

            // Esconde o popup após 3 segundos (3000 milissegundos)
            setTimeout(function () {
                document.getElementById('resposra-carrinho').style.display = 'none';
            }, 3000);
        }

    </script>

    <footer class="menu-mobile">
        <ul>
            <li><a href="perfil_cliente.php" title="Meu Perfil"><i class="fas fa-user"></i></a></li>
            <li><a href="crediario.php" title="Crediário"><i class="fas fa-handshake"></i></a></li>
            <li><a href="comprar/meu_carrinho.php?id_cliente=<?php echo urlencode($id); ?>" title="Meu Carrinho"><i
                        class="fas fa-shopping-cart"></i></a>
                <!-- Exibir a contagem de notificações -->
                <?php if ($total_carrinho > 0): ?>
                    <span id="carrinho-count-rodape"
                        class="carrinho-count-rodape"><?php echo htmlspecialchars($total_carrinho); ?></span>
                <?php else: ?>
                    <span id="carrinho-count-rodape" class="carrinho-count-rodape" style="display: none;"></span>
                <?php endif; ?>
            </li>
            <li>
                <a href="comprar/meus_pedidos.php?id=<?php echo urlencode($id); ?>" title="Meus Pedidos">
                    <i class="fas fa-box"></i> <!-- Ícone de pedido -->
                </a>
            </li>
            <li><a href="configuracoes.php?id_cliente=<?php echo urlencode($id); ?>" title="Configurações"><i
                        class="fas fa-cog"></i></a></li>
            <li><a href="cliente_logout.php" title="Sair"><i class="fas fa-sign-out-alt"></i></a></li>
        </ul>
    </footer>

    <script src="cliente_home.js"></script>

    <script>
        // Obtém o ID da sessão do PHP
        var sessionId = <?php echo json_encode($id); ?>;

        function abrirNotificacao(id) {
            let url = ""; // Inicializa a URL como uma string vazia

            // Define a URL com base no ID da notificação
            switch (id) {
                case 1:
                    url = `not_detalhes_mensagens.php?session_id=${sessionId}`;
                    break;
                default:
                    console.error("ID de notificação inválido:", id);
                    return; // Sai da função se o ID não for válido
            }

            // Redireciona para a URL correspondente
            window.location.href = url;
        }

        function solicitacoes() {
            // Redireciona para a página de detalhes com o ID da notificação e o ID da sessão
            var url = `detalhes_notificacao.php?id=&session_id=${sessionId}`;
            //console.log("Redirecionando para:", url);

            // Verifica se a URL está correta antes de redirecionar
            window.location.href = url;
        }

        function fetchNotifications() {
            fetch(`get_notifications.php?id=${sessionId}`)
                .then(response => response.json())
                .then(data => {
                    const notificationCount = document.getElementById('notificacao-count');
                    notificationCount.innerText = data.total_notificacoes;

                    // Ocultar o contador se for zero
                    if (data.total_notificacoes > 0) {
                        notificationCount.style.display = 'inline';
                    } else {
                        notificationCount.style.display = 'none';
                    }
                }).catch(error => console.error('Error fetching notifications:', error));
            //console.log('oi');
        }

        // Chama a função pela primeira vez
        fetchNotifications();

        // Configura um intervalo para chamar a função a cada 5 segundos (5000 milissegundos)
        setInterval(fetchNotifications, 2000);

        function fetchCarrinho() {
            fetch(`get_carrinho.php?id=${sessionId}`)
                .then(response => response.json())
                .then(data => {
                    const carrinhoCount = document.getElementById('carrinho-count');
                    const carrinhoCountRodape = document.getElementById('carrinho-count-rodape');
                    carrinhoCount.innerText = data.total_carrinho;
                    carrinhoCountRodape.innerText = data.total_carrinho;

                    // Ocultar o contador se for zero
                    if (data.total_carrinho > 0) {
                        carrinhoCount.style.display = 'inline';
                        carrinhoCountRodape.style.display = 'inline';
                    } else {
                        carrinhoCount.style.display = 'none';
                        carrinhoCountRodape.style.display = 'none';
                    }
                }).catch(error => console.error('Error fetching notifications:', error));
            //console.log('oi');
        }

        // Chama a função pela primeira vez
        fetchCarrinho();

        // Configura um intervalo para chamar a função a cada 5 segundos (5000 milissegundos)
        setInterval(fetchCarrinho, 2000);


        $(document).ready(function () {
            var totalParceiros = <?php echo $result_parceiros->num_rows; ?>; // Total de parceiros no banco

            $(".parceiros-carousel").owlCarousel({
                loop: totalParceiros > 1, // Loop apenas se houver mais de 1 parceiro
                margin: 10,
                center: true, // Centraliza os itens no carrossel
                nav: true,
                autoplay: true,
                autoplayTimeout: 3000,
                responsive: {
                    0: { items: 1 },       // Mostra 1 parceiro por vez em telas pequenas
                    600: { items: 2 },    // Mostra 2 parceiros em telas médias
                    1000: { items: 4 }    // Mostra 4 parceiros em telas grandes
                }
            });
        });

        function mostrarConteudo(aba, element) {
            // Oculta todos os conteúdos das abas
            var conteudos = document.querySelectorAll('.conteudo-aba');
            conteudos.forEach(function (conteudo) {
                conteudo.style.display = 'none';
            });

            // Remove a classe 'active' de todas as abas
            var tabs = document.querySelectorAll('.tab');
            tabs.forEach(function (tab) {
                tab.classList.remove('active');
            });

            // Mostra o conteúdo da aba clicada
            document.getElementById('conteudo-' + aba).style.display = 'block';

            // Adiciona a classe 'active' à aba clicada
            element.classList.add('active');
            //console.log('eee');

        }

        // Define que a aba "catalogo" está ativa ao carregar a página
        window.onload = function () {
            mostrarConteudo('catalogo', document.querySelector('.tab.active'));
        };

        // Adiciona o evento de clique a cada aba
        document.addEventListener("DOMContentLoaded", function () {
            function configurarPesquisa(inputId, itemSelector, mensagemId, abaId) {
                const inputPesquisa = document.getElementById(inputId);
                const itens = document.querySelectorAll(`#${abaId} ${itemSelector}`);
                const mensagem = document.getElementById(mensagemId);

                inputPesquisa.addEventListener("input", function () {
                    const termo = inputPesquisa.value.toLowerCase();
                    let encontrou = false;

                    itens.forEach(item => {
                        const textoItem = item.textContent.toLowerCase();
                        if (textoItem.includes(termo)) {
                            item.style.display = "block";
                            encontrou = true;
                        } else {
                            item.style.display = "none";
                        }
                    });

                    mensagem.style.display = encontrou ? "none" : "block";
                });
            }
            // configurar pesquisa para aba "Catálogo"
            configurarPesquisa(
                "inputPesquisaParceiroCatalogo",
                ".parceiro-card",
                "mensagemParNaoEncontradoCatalogo",
                "conteudo-catalogo"
            );
            configurarPesquisa(
                "inputPesquisaCatalogo",
                ".product-card",
                "mensagemNaoEncontradoCatalogo",
                "conteudo-catalogo"
            );

            // Configurar pesquisa para aba "Promoções"
            configurarPesquisa(
                "inputPesquisaParceiroPromocao",
                ".parceiro-card",
                "mensagemParNaoEncontradoPromocao",
                "conteudo-promocoes"
            );
            configurarPesquisa(
                "inputPesquisaPromocao",
                ".product-card",
                "mensagemNaoEncontradoPromocao",
                "conteudo-promocoes"
            );

            // Configurar pesquisa para aba "Frete Grátis"
            configurarPesquisa(
                "inputPesquisaParceiroFrete_gratis",
                ".parceiro-card",
                "mensagemParNaoEncontradoFrete_gratis",
                "conteudo-frete_gratis"
            );
            configurarPesquisa(
                "inputPesquisaFrete_gratis",
                ".product-card",
                "mensagemNaoEncontradoFrete_gratis",
                "conteudo-frete_gratis"
            );

            // Configurar pesquisa para aba "Novidades"
            configurarPesquisa(
                "inputPesquisaParceiroNovidades",
                ".parceiro-card",
                "mensagemParNaoEncontradoNovidades",
                "conteudo-novidades"
            );
            configurarPesquisa(
                "inputPesquisaNovidades",
                ".product-card",
                "mensagemNaoEncontradoNovidades",
                "conteudo-novidades"
            );

            // Configurar pesquisa para aba "Crediário"
            configurarPesquisa(
                "inputPesquisaParceiroCrediario",
                ".parceiro-card",
                "mensagemParNaoEncontradoCrediario",
                "conteudo-crediario"
            );
            configurarPesquisa(
                "inputPesquisaCrediario",
                ".product-card",
                "mensagemNaoEncontradoCrediario",
                "conteudo-crediario"
            );
        });
    </script>

</body>
<!-- Footer 
    <footer>
        <p>&copy; 2024 <?php //echo htmlspecialchars($dadosEscolhido['nomeFantasia']); ?> - Todos os direitos reservados</p>
        <div class="contato">
            <p><strong>Contato:</strong></p>
            <p>Email: <?php //echo htmlspecialchars($dadosEscolhido['email_suporte']); ?> | WhatsApp: <?php //echo htmlspecialchars($dadosEscolhido['telefoneComercial']); ?></p>
        </div>
    </footer>-->

</html>