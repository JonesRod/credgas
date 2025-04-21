<?php

include('../../conexao.php');

//if(!isset($_SESSION)) {
session_start();
//}

if (isset($_SESSION['id']) && isset($_GET['id']) && isset($_GET['id_cliente'])) {
    $idParceiro = intval($_GET['id']);
    $id_cliente = intval($_GET['id_cliente']);

    $sql_query = $mysqli->query(query: "SELECT * FROM meus_clientes WHERE id = '$id_cliente'") or die($mysqli->$error);
    $usuario = $sql_query->fetch_assoc();

    // Verifica se o cliente tem status_crediario = 1
    $status_crediario_cliente = $usuario['status_crediario'] ?? 0;

    // Consulta para buscar os dados do parceiro
    $sql = "SELECT * FROM meus_parceiros WHERE id = $idParceiro AND status = '1'";
    $result = $mysqli->query($sql);

    if ($result->num_rows > 0) {
        $parceiro = $result->fetch_assoc();
        // Exibir os dados da loja do parceiro
        // Verifica e ajusta a logo
        if (isset($parceiro['logo'])) {
            $minhaLogo = $parceiro['logo'];

            if ($minhaLogo != '') {
                // Se existe e não está vazio, atribui o valor à variável logo
                $logo = '../parceiros/arquivos/' . $parceiro['logo'];
                //echo ('oii');
            }
        } else {
            $logo = '../arquivos_fixos/icone_loja.jpg';
        }
    } else {
        echo "<p>Parceiro não encontrado ou inativo.</p>";
    }
} else {
    echo "<p>ID do parceiro não fornecido.</p>";
    echo 'oii3';
    // Redirecionamento opcional para a página de login
    session_unset();
    session_destroy();
    header("Location: ../../../../index.php");
    exit(); // Importante parar a execução do código aqui
}

// Consulta para somar todas as notificações de um cliente específico
$sql_query = "SELECT COUNT(*) AS total_notificacoes FROM contador_notificacoes_cliente WHERE id_cliente = ? AND lida = 1";
$stmt = $mysqli->prepare($sql_query);
$stmt->bind_param("i", $id_cliente); // Substituir $id pelo ID do cliente
$stmt->execute();
$stmt->bind_result($total_notificacoes);
$stmt->fetch();
$stmt->close();

// Obtenha a data atual
$data_atual = date('Y-m-d');

// Obtém a data de hoje menos 1 dias
$data_limite = date('Y-m-d', strtotime('-1 days'));

// Exclui produtos do carrinho do cliente adicionados há mais de 2 dias
$sql_delete = "DELETE FROM carrinho WHERE id_cliente = ? AND DATE(data) < ?";
$stmt_delete = $mysqli->prepare($sql_delete);
$stmt_delete->bind_param("is", $id_cliente, $data_limite);
$stmt_delete->execute();
$stmt_delete->close();

// Consulta para somar todas as quantidades de produtos no carrinho de um cliente específico
$sql_query = "SELECT SUM(qt) AS total_carrinho FROM carrinho WHERE id_cliente = ?";
$stmt = $mysqli->prepare($sql_query);
$stmt->bind_param("i", $id_cliente); // Substituir $id_cliente pelo ID do cliente
$stmt->execute();
$stmt->bind_result($total_carrinho);
$stmt->fetch();
$stmt->close();

// Se não houver produtos no carrinho, definir como 0 para evitar retorno null
$total_carrinho = $total_carrinho ?? 0;

// Atualiza os produtos com promoção
$produtos_promocao = $mysqli->query("SELECT id_produto, promocao, ini_promocao, fim_promocao FROM produtos") or die($mysqli->error);
while ($produtos_encontrados = $produtos_promocao->fetch_assoc()) {
    $id_produto = $produtos_encontrados['id_produto'];
    $promocao = $produtos_encontrados['promocao'];
    $data_inicio = $produtos_encontrados['ini_promocao'];
    $data_fim = $produtos_encontrados['fim_promocao'];

    // Verifica se a promoção deve estar ativa ou inativa
    if ($promocao === '1' && $data_inicio <= $data_atual && $data_fim >= $data_atual) {
        // A promoção deve continuar como "sim"
        continue;
    } elseif ($data_fim < $data_atual) {
        // A promoção terminou; atualize para "não"
        $mysqli->query("UPDATE produtos SET promocao = '0' WHERE id_produto = '$id_produto'");
    } elseif ($data_inicio > $data_atual) {
        // A promoção ainda não começou; continue com "sim" se for o caso
        $mysqli->query("UPDATE produtos SET promocao = '1' WHERE id_produto = '$id_produto'");
    }
}

// Consulta para verificar se existem produtos que vendem a crédito
$crediario_query = "SELECT COUNT(*) AS total_crediario FROM produtos 
    WHERE id_parceiro = ? AND vende_crediario = 1 AND oculto != '1' AND produto_aprovado = '1'";
$stmt_crediario = $mysqli->prepare($crediario_query);
$stmt_crediario->bind_param("i", $idParceiro);
$stmt_crediario->execute();
$stmt_crediario->bind_result($total_crediario);
$stmt_crediario->fetch();
$stmt_crediario->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['categoria_selecionada'])) {

    $categoriaSelecionada = $_POST['categoria_selecionada'];
    //echo ('oii1');
} else {
    // Consulta para buscar categorias únicas dos produtos do parceiro
    $sql_categorias = "SELECT categoria FROM produtos WHERE id_parceiro = $idParceiro";
    $result_categorias = $mysqli->query($sql_categorias) or die($mysqli->error);

    // Array para armazenar todas as categorias
    $categoriasArray = [];

    while ($categoria = $result_categorias->fetch_assoc()) {
        $categoriasArray[] = $categoria['categoria']; // Adiciona as categorias no array
    }

    // Remove as duplicatas do array de categorias
    $categoriasUnicas = array_unique($categoriasArray);
    //var_dump($categoriasUnicas);

    // Pega a primeira categoria, se existir
    $primeiraCategoria = !empty($categoriasUnicas) ? reset($categoriasUnicas) : null;
    // Use reset() para obter o primeiro elemento do array

    $categoriaSelecionada = $primeiraCategoria;
    //echo ('oii22');
}

// Consulta para buscar produtos do catálogo
$catalogo = $mysqli->query(query: "SELECT * FROM produtos 
    WHERE id_parceiro = '$idParceiro'
    AND categoria = '$categoriaSelecionada'  
    AND oculto != '1' 
    AND produto_aprovado = '1'") or die($mysqli->error);

// Verifica se existem promoções, mais vendidos e frete grátis
$promocoes = $mysqli->query("SELECT * FROM produtos 
    WHERE id_parceiro = '$idParceiro' 
    AND categoria = '$categoriaSelecionada' 
    AND promocao = '1' 
    AND oculto != '1' 
    AND produto_aprovado = '1'") or die($mysqli->error);

// Consulta para buscar produtos com frete grátis na categoria selecionada
$queryFreteGratis = "
    SELECT * FROM produtos 
    WHERE id_parceiro = '$idParceiro'
    AND categoria = '$categoriaSelecionada'
    AND oculto != '1' 
    AND produto_aprovado = '1' 
    AND (frete_gratis = '1' OR (promocao = '1' AND frete_gratis_promocao = '1'))
";
$freteGratis = $mysqli->query($queryFreteGratis) or die($mysqli->error);

// Consulta para buscar produtos disponíveis no crediário na categoria selecionada
$crediario_produtos = $mysqli->query("
    SELECT * FROM produtos 
    WHERE id_parceiro = '$idParceiro' 
    AND categoria = '$categoriaSelecionada'
    AND vende_crediario = '1' 
    AND oculto != '1' 
    AND produto_aprovado = '1'
") or die($mysqli->error);

// Consulta SQL
$novidades = $mysqli->query("
        SELECT *, DATEDIFF(NOW(), data) AS dias_desde_cadastro
        FROM produtos 
        WHERE id_parceiro = '$idParceiro' 
        AND categoria = '$categoriaSelecionada' 
        AND oculto != '1' 
        AND produto_aprovado = '1'
        AND DATEDIFF(NOW(), data) <= 30
    ") or die("Erro na consulta: " . $mysqli->error);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $parceiro['nomeFantasia']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
    <!--<link rel="stylesheet" href="loja_parceiro_home.css">-->
    <script src="loja_parceiro_home.js"></script>
    <style>
        /* Estilos gerais */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        header {
            background-color: #007BFF;
            color: white;
            justify-content: space-between;
            align-items: flex-start;
            /* Alinha itens ao topo */
        }

        #logo-header {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 10px;
        }

        .logo {
            width: 150px;
            /* largura fixa para o logo */
            height: 150px;
            /* altura opcional */
            flex-shrink: 0;
            /* impede que o logo diminua */

        }

        .logo-img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            /* bordas arredondadas */
        }

        #logo-header h1 {
            flex: 1;
            /* ocupa todo o espaço restante */
            margin-left: 20%;
            /* espaço entre logo e texto */
            font-size: 1.8rem;
            /* ajuste conforme necessário */
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #007BFF;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            position: relative;
            overflow-y: scroll;
            /* Garante que o corpo da página possa rolar */
        }

        .menu-superior-direito {
            display: flex;
            justify-content: flex-end;
            /* Alinha os itens à direita */
            align-items: center;
            /* Alinha verticalmente */
            gap: 10px;
            /* Espaçamento entre os itens */
            padding: 15px;
            /* Espaço interno opcional */
        }

        .menu-superior-direito span {
            margin-right: 3px;
            /* Espaçamento entre o nome do usuário e os ícones */
            transition: color 0.3s ease;
            /* Transição suave para a cor */
        }

        .menu-superior-direito i {
            font-size: 20px;
            /* Aumenta o tamanho dos ícones */
            margin-left: 3px;
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
            right: 20px;
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
            position: absolute;
            transition: all 0.3s ease;
        }

        aside#menu-lateral ul {
            list-style: none;
            padding: 0;
        }

        aside#menu-lateral ul li {
            margin: 0;
            font-size: 16px;
            display: flex;
            align-items: center;
            transition: background-color 0.3s ease;
            border-radius: 5px;
            padding: 5px;
            font-weight: bold;
        }

        /* Remove o sublinhado do link "Sair" */
        #menu-lateral a {
            text-decoration: none;
            color: inherit;
            transition: color 0.3s ease;
        }

        /* Efeito ao passar o mouse sobre o link */
        #menu-lateral a:hover {
            cursor: pointer;
            color: #007BFF;
        }

        /* Efeito ao passar o mouse sobre o item do menu */
        aside#menu-lateral ul li:hover {
            cursor: pointer;
            background-color: rgba(0, 123, 255, 0.1);
        }

        /* Estilo para ícones */
        aside#menu-lateral ul li i {
            margin-right: 5px;
            font-size: 20px;
            transition: transform 0.3s ease, color 0.3s ease;
        }

        /* Efeito ao passar o mouse sobre o ícone */
        aside#menu-lateral ul li:hover i {
            cursor: pointer;
            transform: translateY(-3px);
            color: #ffbb09;
        }

        /* Efeito ao clicar em um ícone */
        aside#menu-lateral ul li i:active {
            transform: scale(0.9);
            color: #ffbb09;
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
            top: -13px;
            left: 10px;
            background-color: red;
            color: white;
            padding: 3px;
            border-radius: 50%;
            font-size: 12px;
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

        .conteudo-secao {
            display: none;
        }

        .conteudo-secao.ativo {
            display: block;
        }

        .categorias-parceiro {
            display: flex;
            justify-content: center;
            /* Centraliza horizontalmente */
            align-items: center;
            /* Centraliza verticalmente */
            height: 100%;
            /* Garante que o elemento ocupe o espaço necessário */
        }

        .tab {
            cursor: pointer;
            padding: 10px;
            display: inline-block;
            margin-right: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        .tab.active {
            background-color: #eaeaea;
            border-bottom: 2px solid #000;
        }

        .voltar {
            margin: 0;
            /* Remove margens padrão */
            font-size: 1.5rem;
            /* Ajuste o tamanho da fonte conforme necessário */
        }

        .voltar-link {
            text-decoration: none;
            /* Remove sublinhado */
            color: #333;
            /* Cor do texto */
            transition: color 0.3s ease;
            /* Transição suave ao passar o mouse */
        }

        .voltar-link:hover {
            color: #fff;
            /* Cor ao passar o mouse */
        }

        /* Seção de Produtos, Promoções, Mais Vendidos e Frete Grátis */
        .section {
            margin: 40px auto;
            width: 70%;
            max-width: 1200px;
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }

        /* Botão "Inclua seu primeiro produto" */
        .button {
            /*display: inline-block;*/
            margin-top: 50px;
            font-weight: bold;
            /* Deixa o texto em negrito */
            padding: 15px 30px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            transition: background-color 0.3s, transform 0.3s;
            /* Suaviza a mudança de cor e transformação */
            font-size: 16px;
            border-color: #fad102;
        }

        .button:hover {
            background-color: #fad102;
            /* Cor de fundo ao passar o mouse */
            transform: scale(1.1);
            /* Aumenta o tamanho do botão em 10% */
        }

        main {
            display: flex;
            flex-direction: column;
            height: 100vh;
            /* O contêiner principal ocupa a altura total da tela */
            box-sizing: border-box;
            align-items: center;
            /* Centraliza horizontalmente */
            justify-content: center;
            /* Centraliza verticalmente */
            text-align: center;
        }

        /* Estilos para as abas */
        main .opcoes {
            background-color: #007BFF;
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
            background-color: #007BFF;
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
            overflow: auto;
            /* Para que o conteúdo role se for maior que a tela */
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
            min-height: 200px;
            /* Define uma altura mínima para centralização adequada */
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
            width: 100%;
            padding: 10px;
            /*margin-top: -30px;*/
        }

        .titulo {
            font-size: 20px;
            font-weight: bold;
        }

        .input {
            width: 500px;
            padding: 10px;
            font-size: 15px;
            border: 1px solid #ccc;
            border-radius: 10px;
            text-align: left;
            margin: 10px;
        }

        .conteudo {
            display: flex;
            flex-direction: column;
            align-items: center;
            /* Centraliza horizontalmente */
            justify-content: center;
            /* Centraliza verticalmente */
            left: 50vh;
            height: 40vh;
            /* Altura total da tela */
            text-align: center;
        }

        .conteudo p {
            margin-bottom: 10px;
            /* Espaçamento entre o parágrafo e o botão */
        }

        .conteudo .button {
            padding: 10px 20px;
        }

        h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
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

        .lista-freteGratis {
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

        /* Cartão do produto */
        .produto-item {
            background: #ffffff;
            border: 1px solid #ddd;
            border-radius: 10px;
            width: 200px;
            /* Largura do cartão */
            height: 390px;
            /* Define a altura fixa */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-align: center;
            padding: 3px;
        }

        /* Efeito ao passar o mouse */
        .produto-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
        }

        /* Imagem do produto */
        .produto-item img {
            width: 300px;
            max-width: 100%;
            max-height: 250px;
            height: 200px;
            border-radius: 5px;
            margin-bottom: 2px;
        }

        .produto-detalhes {
            display: flex;
            flex-direction: column;
            gap: 5px;
            width: 100%;
        }

        /* Estilo para o nome do produto com limite de espaço */
        .produto-nome {
            /*font-size: 1.2em;*/
            margin: 5px 0;
            font-size: 16px;
            font-weight: bold;
            white-space: nowrap;
            /* Não permite quebra de linha */
            overflow: hidden;
            /* Oculta o conteúdo que ultrapassa */
            text-overflow: ellipsis;
            /* Adiciona os três pontos '...' */
        }

        /* Estilo para a descrição do produto com limite de linhas */
        .produto-descricao {
            font-size: 14px;
            line-height: 1.4;
            /* Espaçamento entre as linhas */
            max-height: 4.2em;
            /* Limita a altura da descrição para 3 linhas (1.4 * 3) */
            overflow: hidden;
            /* Oculta o conteúdo que ultrapassa */
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
            /* Limita o texto a 3 linhas */
            text-overflow: ellipsis;
            /* Adiciona os três pontos '...' */
            font-size: 0.9em;
            color: #666;
            margin-bottom: 5px;

        }

        .produto-preco {
            font-size: 1.2em;
            color: #28a745;
            font-weight: bold;
        }


        .produto-item {
            position: relative;
            /* Define o contêiner da imagem como relativo */
            display: inline-block;
        }

        .icone-oculto {
            position: absolute;
            top: -5px;
            right: 3px;
            font-size: 24px;
            color: red;
            /* Cor do ícone */
            border-radius: 50%;
            padding: -3px;
        }

        .fa-clock {
            position: absolute;
            top: 3px;
            left: 5px;
            font-size: 20px;
            color: black;
            /* Cor do ícone */
            border-radius: 50%;
            padding: 2px;
        }

        .button {
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

        .catalogo-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .categorias {
            padding-bottom: 50px;
        }

        .categorias-parceiro {
            display: flex;
            /* Flexbox para organizar os itens em linha */
            flex-wrap: wrap;
            /* Permite quebrar para outra linha se necessário */
            justify-content: center;
            /* Centraliza os itens horizontalmente */
            gap: 15px;
            /* Espaçamento entre os itens */
        }

        .categoria-item {
            text-align: center;
            margin: 5px;
            /* Margem ao redor de cada item */
        }

        .categoria-imagem {
            width: 60px;
            /* Ajuste o tamanho das imagens conforme necessário */
            height: 60px;
            object-fit: contain;
            margin-bottom: 5px;
            /* Espaçamento entre imagem e texto */
            border-radius: 50%;
            transition: all 0.3s ease;
            /* Suaviza o efeito de transição */
        }

        .categorias-parceiro p {
            font-size: 14px;
            color: black;
            margin: 0;
            transition: all 0.3s ease;
            /* Suaviza o efeito de transição */
        }

        .categoria-item:hover .categoria-imagem {
            width: 70px;
            /* Ajuste o tamanho das imagens conforme necessário */
            height: 70px;
            object-fit: contain;
            margin-bottom: -5px;
            /* Espaçamento entre imagem e texto */
            border-radius: 50%;
            /* Torna a imagem circular */
            transform: translateY(-5px);
            /* Move o texto 5px para cima */
        }

        .categoria-item:hover p {
            font-size: 16px;
            color: rgb(201, 231, 9);
            transform: translateY(-5px);
            /* Move o texto 5px para cima */
            margin: 0;
        }

        .categoria-item.selected .categoria-imagem {
            width: 70px;
            /* Ajuste o tamanho das imagens conforme necessário */
            height: 70px;
            object-fit: contain;
            margin-bottom: -5px;
            /* Espaçamento entre imagem e texto */
            border-radius: 50%;
            /* Torna a imagem circular */
            transform: translateY(-5px);
            /* Move o texto 5px para cima */
        }

        .categoria-item.selected p {
            font-size: 16px;
            color: rgb(220, 200, 10);
            transform: translateY(-5px);
            /* Move o texto 5px para cima */
            margin: 0;
            text-decoration: underline;
            /* Adiciona sublinhado ao texto */
        }


        .categoria-item {
            cursor: pointer;
            transition: transform 0.3s, color 0.3s;
        }

        .carrinho-count {
            position: absolute;
            top: 0px;
            right: 35px;
            background-color: green;
            color: white;
            padding: 3px;
            border-radius: 50%;
            font-size: 13px;
            font-weight: bold;
        }

        /* Botões */
        .btn {
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
        .btn:hover {
            background: darkorange;
        }

        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            width: 280px;
            height: 320px;
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

        #resposta-carrinho {
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

        .menu-mobile {
            background-color: #343a40;
            color: white;
            padding: 15px;
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


            .conteudo-aba {
                flex-grow: 1;
                overflow-y: auto;
                /* Permite que o conteúdo dentro das abas role */
                max-height: calc(100vh - 100px);
                /* Ajuste para que o conteúdo role corretamente */
            }
            main .opcoes {
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

        main .tab {
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
</head>

<body>
    <form id="formCategoria" method="POST" action="">
        <input type="hidden" name="id_parceiro" id="id_parceiro" value="<?php echo $idParceiro; ?>">
        <input type="hidden" name="categoria_selecionada" id="categoria_selecionada"
            value="<?php echo $categoriaSelecionada; ?>">
        <button type="submit" id="carregar_categoria" class="carregar_categoria" style="display: none;">enviar</button>
    </form>

    <!-- Header -->
    <header>
        <div class="menu-superior-direito">
            <?php if ($usuario): ?>
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
                <a href="comprar/meu_carrinho.php?id_cliente=<?php echo urlencode($id_cliente); ?>" style="color:#f0f0f0;">
                    <i class="fas fa-shopping-cart" title="Meu Carrinho" onmouseover="moverCarrinho()"></i>
                </a>
                <!-- Exibir a contagem de produtos no carrinho -->
                <?php if ($total_carrinho > 0): ?>
                    <span id="carrinho-count" class="carrinho-count"
                        onmouseover="moverCarrinho()"><?php echo htmlspecialchars($total_carrinho); ?></span>
                <?php else: ?>
                    <span id="carrinho-count" class="carrinho-count" style="display: none;"
                        onmouseover="moverCarrinho()"></span>
                <?php endif; ?>
                <i class="fas fa-bars" title="Menu" onclick="toggleMenu()"></i>
            <?php else: ?>
                <span>Seja bem-vindo!</span>
                <a href="login/lib/login.php" class="btn-login">Entrar</a>
            <?php endif; ?>
        </div>
        <div id="logo-header">
            <div class="logo">
                <img src="<?php echo $logo; ?>" alt="Logo da Loja" class="logo-img">
            </div>
            <h1><?php echo $parceiro['nomeFantasia']; ?></h1>
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
                <a href="perfil_crediario.php?id=<?php echo urlencode($id); ?>" title="Crediario">
                    <i class="fas fa-user"></i>
                    <span>Perfil Crediario</span>
                </a>
            </li>

            <!-- Item crediario-->
            <li>
                <a href="perfil_crediario.php?id=<?php echo urlencode($id); ?>" title="Crediario">
                    <i class="fas fa-handshake"></i>
                    <span>Meu Crediario</span>
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

    <div class="categorias">
        <?php
        // Consulta para buscar parceiros pelo CEP
        $sql_parceiros = "SELECT * FROM meus_parceiros WHERE id = $idParceiro AND status = '1'";
        $result_parceiros = $mysqli->query($sql_parceiros) or die($mysqli->error);

        if ($result_parceiros->num_rows > 0):
            while ($parceiro = $result_parceiros->fetch_assoc()):
                // Consulta para buscar categorias únicas dos produtos do parceiro
                $sql_categorias = "SELECT categoria FROM produtos WHERE id_parceiro = " . $parceiro['id'];
                $result_categorias = $mysqli->query($sql_categorias) or die($mysqli->error);

                // Array para armazenar todas as categorias
                $categoriasArray = [];

                while ($categoria = $result_categorias->fetch_assoc()) {

                    $categoriasArray[] = $categoria['categoria']; // Adiciona as categorias no array
        
                }

                // Remove as duplicatas do array de categorias
                $categoriasUnicas = array_unique($categoriasArray);

                // Pega a primeira categoria, se existir
                $primeiraCategoria = !empty($categoriasUnicas) ? reset($categoriasUnicas) : null;
                // Use reset() para obter o primeiro elemento do array
                ?>

                <div class="parceiro-card">
                    <div class="categorias-parceiro">
                        <h2 class="voltar">
                            <a href="cliente_home.php?id=<?php echo urlencode($usuario['id']); ?>" class="voltar-link">
                                << Voltar</a>
                        </h2>
                        <?php if (count($categoriasUnicas) > 0): ?>
                            <?php foreach ($categoriasUnicas as $categoriaNome):
                                $categoriaNome = htmlspecialchars($categoriaNome);

                                // Define a imagem correspondente à categoria
                                $imagem = '';
                                switch ($categoriaNome) {
                                    case 'Alimenticios':
                                        $imagem = 'alimenticio.png';
                                        break;
                                    case 'Utilitarios':
                                        $imagem = 'utilitarios.jpg';
                                        break;
                                    case 'Limpeza':
                                        $imagem = 'limpeza.jpg';
                                        break;
                                    case 'Bebidas':
                                        $imagem = 'bebidas.png';
                                        break;
                                    default:
                                        $imagem = 'img/categorias/padrao.png';
                                        break;
                                }
                                $selectedClass = ($categoriaNome === $categoriaSelecionada) ? 'selected' : ''; // Adiciona a classe 'selected' se for a selecionada
                
                                ?>
                                <div class="categoria-item <?php echo $selectedClass; ?>"
                                    onclick="selecionarCategoria('<?php echo $categoriaNome; ?>')"
                                    data-categoria="<?php echo $categoriaNome; ?>">
                                    <img src="<?php echo htmlspecialchars('../arquivos_fixos/' . $imagem); ?>"
                                        alt="<?php echo $categoriaNome; ?>" class="categoria-imagem">
                                    <p><?php echo $categoriaNome; ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>Sem categorias</p>
                        <?php endif; ?>
                    </div>
                </div>

            <?php endwhile; ?>
        <?php else: ?>
            <p>Nenhum parceiro ativo no momento.</p>
        <?php endif; ?>
    </div>

    <!-- Conteúdo principal -->
    <main id="main-content">
        <!-- Conteúdo -->
        <div class="opcoes">
            <!-- Abas -->
            <div class="tab active" onclick="mostrarConteudo('catalogo', this)">
                <span>Catálogo</span>
            </div>

            <div class="tab" onclick="mostrarConteudo('promocoes', this)">
                <span class="icone-promocao" title="Produto em promoção">🔥</span><span>Promoções</span>
            </div>

            <div class="tab" onclick="mostrarConteudo('freteGratis', this)">
                <span class="icone-freteGratis" title="Frete grátis">🚚</span><span>Frete Grátis</span>
            </div>

            <div class="tab" onclick="mostrarConteudo('novidades', this)">
                <span class="icone-novidades" title="Novidades">🆕</span><span>Novidades</span>
            </div>

            <!-- Exibe a aba Crediário apenas se o cliente tiver status_crediario = 1 e houver produtos que vendem a crédito -->
            <?php if ($status_crediario_cliente == 1 && $total_crediario > 0): ?>
                <div class="tab" onclick="mostrarConteudo('crediario', this)">
                    <span class="icone-crediario" title="Crediário">🤝</span><span>Crediário</span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Conteúdos correspondentes às abas -->
        <div id="conteudo-catalogo" class="conteudo-aba" style="display: none;">
            <?php
            if ($catalogo->num_rows > 0):
                //echo $promocoes->num_rows;
                ?>
                <div class="container">
                    <input id="inputPesquisaCatalogo" class="input" type="text" placeholder="Pesquisar Produto.">
                </div>

                <!-- Lista de produtos aqui -->
                <div class="lista-produtos">
                    <?php
                    while ($produto = $catalogo->fetch_assoc()):
                        ?>
                        <div class="produto-item catalogo">
                            <?php
                            // Verifica se o campo 'imagens' está definido e não está vazio
                            if (isset($produto['imagens']) && !empty($produto['imagens'])) {
                                // Divide a string de imagens em um array, assumindo que as imagens estão separadas por virgula
                                $imagensArray = explode(',', $produto['imagens']);

                                // Pega a primeira imagem do array
                                $primeiraImagem = $imagensArray[0];
                                // Exibe a primeira imagem
                                ?>
                                <img src="../parceiros/produtos/img_produtos/<?php echo $primeiraImagem; ?>" alt="Imagem do Produto"
                                    class="produto-imagem">
                                <?php
                            } else {
                                // Caso não haja imagens, exibe uma imagem padrão
                                ?>
                                <img src="/default_image.jpg" alt="Imagem Padrão" class="produto-imagem">
                                <?php
                            }
                            ?>

                            <div class="produto-detalhes">
                                <h3 class="produto-nome">
                                    <?php
                                    // Exibe o ícone de frete grátis, se o produto tiver frete grátis
                                    if ($produto['frete_gratis'] === '1' || ($produto['promocao'] === '1' && $produto['frete_gratis_promocao'] === '1')):
                                        ?>
                                        <span class="icone-frete-gratis" title="Frete grátis">🚚</span>
                                        <?php
                                    endif;

                                    // Exibe o ícone de promoção, se o produto estiver em promoção
                                    if ($produto['promocao'] === '1'):
                                        ?>
                                        <span class="icone-promocao" title="Produto em promoção">🔥</span>
                                        <?php
                                    endif;

                                    // Exibe o ícone de crediário, se o produto for vendido a crédito
                                    if ($produto['vende_crediario'] === '1'):
                                        ?>
                                        <span class="icone-crediario" title="Disponível no crediário">🤝</span>
                                        <?php
                                    endif;

                                    $dataCadastro = new DateTime($produto['data']); // Data do produto
                                    $dataAtual = new DateTime(); // Data atual
                                    $intervalo = $dataCadastro->diff($dataAtual); // Calcula a diferença entre as datas
                                    $diasDesdeCadastro = $intervalo->days; // Número de dias de diferença
                            
                                    if ($diasDesdeCadastro <= 30):
                                        ?>
                                        <span class="icone-novidades" title="Novidades">🆕</span>
                                        <?php
                                    endif;
                                    ?>
                                </h3>
                                <?php echo $produto['nome_produto']; ?>
                                <!-- Preço do produto -->
                                <?php
                                $taxa_padrao = floatval($produto['taxa_padrao'] ?? 0);
                                $valor_base = isset($produto['promocao']) && $produto['promocao'] === '1'
                                    ? floatval($produto['valor_promocao'] ?? 0)
                                    : floatval($produto['valor_produto'] ?? 0);
                                $valor_produto = $valor_base + (($valor_base * $taxa_padrao) / 100);
                                ?>
                                <p class="produto-preco">R$ <?php echo number_format($valor_produto, 2, ',', '.'); ?></p>
                                <a href="detalhes_novos_produtos.php?id_produto=<?php echo $produto['id_produto']; ?>&id_parceiro=<?php echo $idParceiro; ?>"
                                    class="btn">Detalhes</a>
                            </div>
                            <a href="#" class="btn" onclick="abrirPopup(
                            '<?php echo $produto['id_produto']; ?>',
                            '<?php echo $produto['nome_produto']; ?>', 
                            '<?php echo $valor_produto; ?>')">Adicionar ao Carrinho</a>
                        </div>
                    <?php endwhile; ?>
                </div>
                <!-- Mensagem de produto não encontrado -->
                <p id="mensagemNaoEncontradoCatalogo" style="display: none;">Nenhum produto encontrado no catálogo.</p>
            </div>
        <?php else: ?>
            <div class="conteudo">
                <!--<form method="POST" action="produtos/adicionar_produto.php">
                    <input type="hidden" name="id_parceiro" value="<?php //echo $idParceiro; ?>">
                    <p style="margin-top: 30px;">Nenhuma produto cadastrado ainda!.</p>
                    <button class="button">Inclua seu primeiro produto</button>
                </form>-->
                <p style="margin-top: 30px;">Nenhuma produto cadastrado ainda!.</p>
            </div>
        <?php endif; ?>
        </div>

        <div id="conteudo-promocoes" class="conteudo-aba" style="display: none;">
            <?php
            if ($promocoes->num_rows > 0):
                ?>
                <div class="container">
                    <input id="inputPesquisaPromocoes" class="input" type="text" placeholder="Pesquisar Produto.">
                </div>

                <!-- Lista de produtos aqui -->
                <div class="lista-produtos">
                    <?php
                    while ($produto = $promocoes->fetch_assoc()):
                        ?>
                        <div class="produto-item promocoes">
                            <?php
                            // Verifica se o campo 'imagens' está definido e não está vazio
                            if (isset($produto['imagens']) && !empty($produto['imagens'])) {
                                // Divide a string de imagens em um array, assumindo que as imagens estão separadas por virgula
                                $imagensArray = explode(',', $produto['imagens']);

                                // Pega a primeira imagem do array
                                $primeiraImagem = $imagensArray[0];
                                // Exibe a primeira imagem
                                ?>
                                <img src="../parceiros/produtos/img_produtos/<?php echo $primeiraImagem; ?>" alt="Imagem do Produto"
                                    class="produto-imagem">
                                <?php
                            } else {
                                // Caso não haja imagens, exibe uma imagem padrão
                                ?>
                                <img src="/default_image.jpg" alt="Imagem Padrão" class="produto-imagem">
                                <?php
                            }
                            ?>

                            <div class="produto-detalhes">
                                <h3 class="produto-nome">
                                    <?php
                                    // Exibe o ícone de frete grátis, se o produto tiver frete grátis
                                    if ($produto['frete_gratis'] === '1' || ($produto['promocao'] === '1' && $produto['frete_gratis_promocao'] === '1')):
                                        ?>
                                        <span class="icone-frete-gratis" title="Frete grátis">🚚</span>
                                        <?php
                                    endif;

                                    // Exibe o ícone de promoção, se o produto estiver em promoção
                                    if ($produto['promocao'] === '1'):
                                        ?>
                                        <span class="icone-promocao" title="Produto em promoção">🔥</span>
                                        <?php
                                    endif;

                                    // Exibe o ícone de crediário, se o produto for vendido a crédito
                                    if ($produto['vende_crediario'] === '1'):
                                        ?>
                                        <span class="icone-crediario" title="Disponível no crediário">🤝</span>
                                        <?php
                                    endif;

                                    $dataCadastro = new DateTime($produto['data']); // Data do produto
                                    $dataAtual = new DateTime(); // Data atual
                                    $intervalo = $dataCadastro->diff($dataAtual); // Calcula a diferença entre as datas
                                    $diasDesdeCadastro = $intervalo->days; // Número de dias de diferença
                            
                                    if ($diasDesdeCadastro <= 30):
                                        ?>
                                        <span class="icone-novidades" title="Novidades">🆕</span>
                                        <?php
                                    endif;
                                    ?>

                                </h3>
                                <?php echo $produto['nome_produto']; ?>
                                <!-- Preço do produto -->
                                <?php
                                $taxa_padrao = floatval($produto['taxa_padrao'] ?? 0);
                                $valor_base = isset($produto['promocao']) && $produto['promocao'] === '1'
                                    ? floatval($produto['valor_promocao'] ?? 0)
                                    : floatval($produto['valor_produto'] ?? 0);
                                $valor_produto = $valor_base + (($valor_base * $taxa_padrao) / 100);
                                ?>
                                <p class="produto-preco">R$ <?php echo number_format($valor_produto, 2, ',', '.'); ?></p>
                                <a href="detalhes_novos_produtos.php?id_produto=<?php echo $produto['id_produto']; ?>&id_parceiro=<?php echo $idParceiro; ?>"
                                    class="btn">Detalhes</a>
                            </div>
                            <a href="#" class="btn" onclick="abrirPopup(
                            '<?php echo $produto['id_produto']; ?>',
                            '<?php echo $produto['nome_produto']; ?>', 
                            '<?php echo $valor_produto; ?>')">Adicionar ao Carrinho</a>
                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- Mensagem de produto não encontrado -->
                <p id="mensagemNaoEncontradoPromocao" style="display: none;">Nenhum produto encontrado.</p>

            <?php else: ?>
                <p style="margin-top: 30px;">Nenhuma produto disponível.</p>
            <?php endif; ?>
        </div>

        <div id="conteudo-freteGratis" class="conteudo-aba" style="display: none;">
            <?php if ($freteGratis->num_rows > 0): ?>
                <div class="container">
                    <input id="inputPesquisaFreteGratis" class="input" type="text" placeholder="Pesquisar Produto.">
                </div>

                <!-- Lista de promoções aqui -->
                <div class="lista-produtos">
                    <?php while ($produto = $freteGratis->fetch_assoc()): ?>
                        <div class="produto-item freteGratis">
                            <?php
                            // Verifica se o campo 'imagens' está definido e não está vazio
                            if (isset($produto['imagens']) && !empty($produto['imagens'])) {
                                // Divide a string de imagens em um array, assumindo que as imagens estão separadas por virgula
                                $imagensArray = explode(',', $produto['imagens']);

                                // Pega a primeira imagem do array
                                $primeiraImagem = $imagensArray[0];
                                // Exibe a primeira imagem
                                ?>
                                <img src="../parceiros/produtos/img_produtos/<?php echo $primeiraImagem; ?>" alt="Imagem do Produto"
                                    class="produto-imagem">
                                <?php
                            } else {
                                // Caso não haja imagens, exibe uma imagem padrão
                                ?>
                                <img src="/default_image.jpg" alt="Imagem Padrão" class="produto-imagem">
                                <?php
                            }
                            ?>
                            <div class="produto-detalhes">
                                <h3 class="produto-nome">
                                    <?php
                                    // Exibe o ícone de frete grátis, se o produto tiver frete grátis
                                    if ($produto['frete_gratis'] === '1' || ($produto['promocao'] === '1' && $produto['frete_gratis_promocao'] === '1')):
                                        ?>
                                        <span class="icone-frete-gratis" title="Frete grátis">🚚</span>
                                        <?php
                                    endif;

                                    // Exibe o ícone de promoção, se o produto estiver em promoção
                                    if ($produto['promocao'] === '1'):
                                        ?>
                                        <span class="icone-promocao" title="Produto em promoção">🔥</span>
                                        <?php
                                    endif;

                                    // Exibe o ícone de crediário, se o produto for vendido a crédito
                                    if ($produto['vende_crediario'] === '1'):
                                        ?>
                                        <span class="icone-crediario" title="Disponível no crediário">🤝</span>
                                        <?php
                                    endif;

                                    $dataCadastro = new DateTime($produto['data']); // Data do produto
                                    $dataAtual = new DateTime(); // Data atual
                                    $intervalo = $dataCadastro->diff($dataAtual); // Calcula a diferença entre as datas
                                    $diasDesdeCadastro = $intervalo->days; // Número de dias de diferença
                            
                                    if ($diasDesdeCadastro <= 30):
                                        ?>
                                        <span class="icone-novidades" title="Novidades">🆕</span>
                                        <?php
                                    endif;
                                    ?>
                                </h3>
                                <?php echo $produto['nome_produto']; ?>

                                <!-- Preço do produto -->
                                <?php
                                $taxa_padrao = floatval($produto['taxa_padrao'] ?? 0);
                                $valor_base = isset($produto['promocao']) && $produto['promocao'] === '1'
                                    ? floatval($produto['valor_promocao'] ?? 0)
                                    : floatval($produto['valor_produto'] ?? 0);
                                $valor_produto = $valor_base + (($valor_base * $taxa_padrao) / 100);
                                ?>
                                <p class="produto-preco">R$ <?php echo number_format($valor_produto, 2, ',', '.'); ?></p>
                                <a href="detalhes_novos_produtos.php?id_produto=<?php echo $produto['id_produto']; ?>&id_parceiro=<?php echo $idParceiro; ?>"
                                    class="btn">Detalhes</a>
                                <a href="#" class="btn" onclick="abrirPopup(
                            '<?php echo $produto['id_produto']; ?>',
                            '<?php echo $produto['nome_produto']; ?>', 
                            '<?php echo $valor_produto; ?>')">Adicionar ao Carrinho</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- Mensagem de produto não encontrado -->
                <p id="mensagemNaoEncontradoFreteGratis" style="display: none;">Nenhum produto encontrado com frete grátis.
                </p>

            <?php else: ?>
                <p style="margin-top: 30px;">Nenhuma produto com frete grátis disponível.</p>
            <?php endif; ?>
        </div>

        <div id="conteudo-novidades" class="conteudo-aba" style="display: none;">
            <?php
            if ($novidades->num_rows > 0): ?>

                <div class="container">
                    <input id="inputPesquisaNovidades" class="input" type="text" placeholder="Pesquisar Produto.">
                </div>

                <!-- Lista de promoções aqui -->
                <div class="lista-produtos">
                    <?php while ($produto = $novidades->fetch_assoc()): ?>
                        <div class="produto-item novidades">
                            <?php
                            // Verifica se o campo 'imagens' está definido e não está vazio
                            if (isset($produto['imagens']) && !empty($produto['imagens'])) {
                                // Divide a string de imagens em um array, assumindo que as imagens estão separadas por virgula
                                $imagensArray = explode(',', $produto['imagens']);

                                // Pega a primeira imagem do array
                                $primeiraImagem = $imagensArray[0];
                                // Exibe a primeira imagem
                                ?>
                                <img src="../parceiros/produtos/img_produtos/<?php echo $primeiraImagem; ?>" alt="Imagem do Produto"
                                    class="produto-imagem">
                                <?php
                            } else {
                                // Caso não haja imagens, exibe uma imagem padrão
                                ?>
                                <img src="/default_image.jpg" alt="Imagem Padrão" class="produto-imagem">
                                <?php
                            }
                            ?>
                            <div class="produto-detalhes">
                                <h3 class="produto-nome">
                                    <?php
                                    // Exibe o ícone de frete grátis, se o produto tiver frete grátis
                                    if ($produto['frete_gratis'] === '1' || ($produto['promocao'] === '1' && $produto['frete_gratis_promocao'] === '1')):
                                        ?>
                                        <span class="icone-frete-gratis" title="Frete grátis">🚚</span>
                                        <?php
                                    endif;

                                    // Exibe o ícone de promoção, se o produto estiver em promoção
                                    if ($produto['promocao'] === '1'):
                                        ?>
                                        <span class="icone-promocao" title="Produto em promoção">🔥</span>
                                        <?php
                                    endif;

                                    // Exibe o ícone de crediário, se o produto for vendido a crédito
                                    if ($produto['vende_crediario'] === '1'):
                                        ?>
                                        <span class="icone-crediario" title="Disponível no crediário">🤝</span>
                                        <?php
                                    endif;

                                    $dataCadastro = new DateTime($produto['data']); // Data do produto
                                    $dataAtual = new DateTime(); // Data atual
                                    $intervalo = $dataCadastro->diff($dataAtual); // Calcula a diferença entre as datas
                                    $diasDesdeCadastro = $intervalo->days; // Número de dias de diferença
                            
                                    if ($diasDesdeCadastro <= 30):
                                        ?>
                                        <span class="icone-novidades" title="Novidades">🆕</span>
                                        <?php
                                    endif;
                                    ?>
                                </h3>
                                <?php echo $produto['nome_produto']; ?>

                                <!-- Preço do produto -->
                                <?php
                                $taxa_padrao = floatval($produto['taxa_padrao'] ?? 0);
                                $valor_base = isset($produto['promocao']) && $produto['promocao'] === 'sim'
                                    ? floatval($produto['valor_promocao'] ?? 0)
                                    : floatval($produto['valor_produto'] ?? 0);
                                $valor_produto = $valor_base + (($valor_base * $taxa_padrao) / 100);
                                ?>
                                <p class="produto-preco">R$ <?php echo number_format($valor_produto, 2, ',', '.'); ?></p>
                                <a href="detalhes_novos_produtos.php?id_produto=<?php echo $produto['id_produto']; ?>&id_parceiro=<?php echo $idParceiro; ?>"
                                    class="btn">Detalhes</a>
                                <a href="#" class="btn" onclick="abrirPopup(
                            '<?php echo $produto['id_produto']; ?>',
                            '<?php echo $produto['nome_produto']; ?>', 
                            '<?php echo $valor_produto; ?>')">Adicionar ao Carrinho</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- Mensagem de produto não encontrado -->
                <p id="mensagemNaoEncontradoNovidades" style="display: none;">Nenhum produto encontrado.</p>

            <?php else: ?>
                <p style="margin-top: 30px;">Nenhuma produto disponível.</p>
            <?php endif; ?>
        </div>

        <div id="conteudo-crediario" class="conteudo-aba" style="display: none;">
            <?php if ($crediario_produtos->num_rows > 0): ?>
                <div class="container">
                    <input id="inputPesquisaCrediario" class="input" type="text" placeholder="Pesquisar Produto.">
                </div>

                <!-- Lista de produtos disponíveis no crediário -->
                <div class="lista-produtos" style="display: flex; flex-wrap: wrap; gap: 15px;">
                    <?php while ($produto = $crediario_produtos->fetch_assoc()): ?>
                        <div class="produto-item crediario">
                            <?php
                            // Verifica se o campo 'imagens' está definido e não está vazio
                            if (isset($produto['imagens']) && !empty($produto['imagens'])) {
                                $imagensArray = explode(',', $produto['imagens']);
                                $primeiraImagem = $imagensArray[0];
                                ?>
                                <img src="../parceiros/produtos/img_produtos/<?php echo $primeiraImagem; ?>" alt="Imagem do Produto"
                                    class="produto-imagem">
                            <?php } else { ?>
                                <img src="/default_image.jpg" alt="Imagem Padrão" class="produto-imagem">
                            <?php } ?>

                            <div class="produto-detalhes">
                                <h3 class="produto-nome">
                                    <!-- Ícone de crediário -->
                                    <span class="icone-crediario" title="Disponível no crediário">🤝</span>

                                    <!-- Ícone de frete grátis -->
                                    <?php if ($produto['frete_gratis'] === '1' || ($produto['promocao'] === '1' && $produto['frete_gratis_promocao'] === '1')): ?>
                                        <span class="icone-frete-gratis" title="Frete grátis">🚚</span>
                                    <?php endif; ?>

                                    <!-- Ícone de promoção -->
                                    <?php if ($produto['promocao'] === '1'): ?>
                                        <span class="icone-promocao" title="Produto em promoção">🔥</span>
                                    <?php endif; ?>

                                    <!-- Ícone de novidades -->
                                    <?php
                                    $dataCadastro = new DateTime($produto['data']); // Data do produto
                                    $dataAtual = new DateTime(); // Data atual
                                    $intervalo = $dataCadastro->diff($dataAtual); // Calcula a diferença entre as datas
                                    $diasDesdeCadastro = $intervalo->days; // Número de dias de diferença
                            
                                    if ($diasDesdeCadastro <= 30): ?>
                                        <span class="icone-novidades" title="Novidades">🆕</span>
                                    <?php endif; ?>
                                </h3>
                                <?php echo $produto['nome_produto']; ?>
                                <!-- Preço do produto -->
                                <?php
                                $taxa_padrao = floatval($produto['taxa_padrao'] ?? 0);
                                $valor_base = floatval($produto['valor_produto'] ?? 0);
                                $valor_produto = $valor_base + (($valor_base * $taxa_padrao) / 100);
                                ?>
                                <p class="produto-preco">R$ <?php echo number_format($valor_produto, 2, ',', '.'); ?></p>
                                <a href="detalhes_novos_produtos.php?id_produto=<?php echo $produto['id_produto']; ?>&id_parceiro=<?php echo $idParceiro; ?>"
                                    class="btn">Detalhes</a>
                            </div>
                            <a href="#" class="btn" onclick="abrirPopup(
                                '<?php echo $produto['id_produto']; ?>',
                                '<?php echo $produto['nome_produto']; ?>', 
                                '<?php echo $valor_produto; ?>')">Adicionar ao Carrinho</a>
                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- Mensagem de produto não encontrado -->
                <p id="mensagemNaoEncontradoCrediario" style="display: none;">Nenhum produto encontrado no crediário.</p>
            <?php else: ?>
                <p style="margin-top: 30px;">Nenhum produto disponível no crediário.</p>
            <?php endif; ?>
        </div>

    </main>

    <div class="popup" id="popup">
        <h2>Detalhes do Produto</h2>
        <form id="formCarrinho" action="comprar/carrinho.php">
            <aside id="info">
                <input type="hidden" id="id_cli" name="id_cli" value="<?php echo htmlspecialchars($id_cliente); ?>">
                <input type="hidden" id="id_produto_carrinho" name="id_produto_carrinho">
                <input type="text" id="produtoNome" name="produtoNome" readonly>

                <p>Preço R$:
                    <input type="text" id="produtoPreco" name="produtoPreco" readonly>
                </p>

                <p>Quantidade:
                    <input type="number" id="quantidade" name="quantidade" value="1" min="1" oninput="calcularTotal()">
                </p>

                <p>Valor Total R$:
                    <input type="text" id="total" name="total" readonly>
                </p>

            </aside>

            <button type="submit" class="confirm-btn">Adicionar ao Carrinho</button>
        </form>
        <button class="close-btn" onclick="fecharPopup()">Cancelar</button>
    </div>

    <div id="resposta-carrinho" style="display: none;">
        <!-- Mensagem de retorno -->
        <p id="mensagem"></p>
    </div>

    <div class="overlay" id="overlay" onclick="fecharPopup()"></div>

    <script>
        let precoProduto = 0; // Variável global para armazenar o preço do produto

        function abrirPopup(id, produto, preco) {
            // Converte para float e garante apenas 2 casas decimais
            precoProduto = parseFloat(preco).toFixed(2);

            // Formata corretamente no padrão brasileiro
            let precoFormatado = Number(precoProduto).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            // Define os valores nos inputs
            document.getElementById('id_produto_carrinho').value = id;
            document.getElementById('produtoNome').value = produto;
            document.getElementById('produtoPreco').value = precoFormatado;
            document.getElementById('total').value = precoFormatado;

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

        function moverCarrinho() {
            const carrinhoIcon = document.querySelector('.fa-shopping-cart');
            const carrinhoCount = document.getElementById('carrinho-count');

            // Adiciona animação ao ícone do carrinho
            carrinhoIcon.style.transition = 'transform 0.3s ease';
            carrinhoIcon.style.transform = 'translateY(-5px)';

            // Adiciona animação ao contador
            if (carrinhoCount) {
                carrinhoCount.style.transition = 'transform 0.3s ease';
                carrinhoCount.style.transform = 'translateY(-5px)';
            }

            // Remove a animação após um tempo
            setTimeout(() => {
                carrinhoIcon.style.transform = 'translateY(0)';
                if (carrinhoCount) {
                    carrinhoCount.style.transform = 'translateY(0)';
                }
            }, 300);
        }

        function mostrarConteudo(conteudoId, aba) {
            // Oculta todos os conteúdos
            document.querySelectorAll('.conteudo-aba').forEach(conteudo => conteudo.style.display = 'none');

            // Remove a classe ativa de todas as abas
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));

            // Exibe o conteúdo correspondente e ativa a aba
            document.getElementById(`conteudo-${conteudoId}`).style.display = 'block';
            aba.classList.add('active');
        }

        document.addEventListener('DOMContentLoaded', () => {
            const categorias = document.querySelectorAll('.categoria-item'); // Todas as categorias
            const inputCategoria = document.querySelector('input[name="categoria_selecionada"]'); // Campo hidden
            const formCategoria = document.querySelector('#formCategoria'); // Formulário

            // Recupera a categoria selecionada do input hidden após o recarregamento da página
            const categoriaSelecionada = inputCategoria.value;

            // Se houver uma categoria previamente selecionada, destaca-a
            if (categoriaSelecionada) {
                categorias.forEach(categoria => {
                    if (categoria.dataset.categoria === categoriaSelecionada) {
                        categoria.classList.add('selected'); // Adiciona a classe 'selected' à categoria correspondente
                    } else {
                        categoria.classList.remove('selected'); // Remove a classe 'selected' de outras categorias
                    }
                });
            } else if (categorias.length > 0) {
                // Caso contrário, seleciona a primeira categoria como padrão
                const primeiraCategoria = categorias[0];
                categorias.forEach(categoria => categoria.classList.remove('selected')); // Remove a classe 'selected' de todas
                primeiraCategoria.classList.add('selected'); // Adiciona a classe 'selected' à primeira categoria
                inputCategoria.value = primeiraCategoria.dataset.categoria; // Define o valor no campo hidden
            }

            // Configurar evento de clique para as categorias, garantindo que não sejam duplicados
            categorias.forEach(categoria => {
                if (!categoria.hasAttribute('data-listener')) { // Verifica se o listener já foi adicionado
                    categoria.addEventListener('click', () => {
                        categorias.forEach(cat => cat.classList.remove('selected')); // Remove a classe 'selected' de todas
                        categoria.classList.add('selected'); // Adiciona a classe 'selected' à categoria clicada
                        inputCategoria.value = categoria.dataset.categoria; // Atualiza o valor no campo hidden
                        formCategoria.submit(); // Envia o formulário
                    });
                    categoria.setAttribute('data-listener', 'true'); // Marca o elemento como tendo o listener
                }
            });
        });

        function selecionarCategoria(categoria) {
            const inputCategoria = document.querySelector('input[name="categoria_selecionada"]'); // Campo hidden
            const formCategoria = document.querySelector('#formCategoria'); // Formulário

            // Atualiza o valor do campo hidden com a categoria selecionada
            inputCategoria.value = categoria;

            // Envia o formulário para carregar os produtos da categoria selecionada
            formCategoria.submit();
        }

    </script>

    <footer class="menu-mobile">
        <ul>
            <!--<li><a href="parceiro_home.php" title="Página Inicial"><i class="fas fa-home"></i></a></li>-->
            <li><a href="perfil_loja.php" title="Perfil da Loja"><i class="fas fa-user"></i></a></li>
            <li>
                <div class="icone-carrinho-wrapper">
                    <!-- Contagem de produtos -->
                    <?php if ($total_carrinho > 0): ?>
                        <span id="carrinho-count-rodape"
                            class="carrinho-count-rodape"><?php echo htmlspecialchars($total_carrinho); ?></span>
                    <?php else: ?>
                        <span id="carrinho-count-rodape" class="carrinho-count-rodape" style="display: none;"></span>
                    <?php endif; ?>

                    <!-- Ícone do carrinho -->
                    <a href="configuracoes.php?id_parceiro=<?php echo urlencode($id); ?>" title="Meu Carrinho">
                        <i class="fas fa-shopping-cart"></i>
                    </a>
                </div>
            </li>
            <li><a href="configuracoes.php?id_parceiro=<?php echo urlencode($idParceiro); ?>" title="Configurações"><i
                        class="fas fa-cog"></i></a></li>
            <li><a href="parceiro_logout.php" title="Sair"><i class="fas fa-sign-out-alt"></i></a></li>
        </ul>
    </footer>

    <script>
        // Obtém o ID da sessão do PHP
        var sessionId = <?php echo json_encode($id_cliente); ?>;
        //var sessionId = <?php echo json_encode($idParceiro); ?>;
        var id_produto = <?php echo json_encode($id_produto); ?>;

        function abrirNotificacao(id) {
            let url = ""; // Inicializa a URL como uma string vazia

            // Define a URL com base no ID da notificação
            switch (id) {
                case 1:
                    url = `detalhes_notificacao_novo_prod.php?id=${id}&session_id=${sessionId}&id_produto=${id_produto}`;
                    break;
                case 2:
                    url = `detalhes_notificacao_edi_prod.php?id=${id}&session_id=${sessionId}&id_produto=${id_produto}`;
                    break;
                case 3:
                    url = `not_detalhes_crediario.php?session_id=${sessionId}`;
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


        function fetchNotifications(id) {
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
        }

        // Chama a função pela primeira vez
        fetchNotifications();

        // Configura um intervalo para chamar a função a cada 5 segundos (5000 milissegundos)
        setInterval(fetchNotifications, 5000);

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

        document.addEventListener('DOMContentLoaded', () => {
            // Referencia todos os campos de pesquisa
            const camposPesquisa = [
                document.getElementById('inputPesquisaCatalogo'),
                document.getElementById('inputPesquisaPromocao'),
                document.getElementById('inputPesquisaFreteGratis'),
                document.getElementById('inputPesquisaNovidades')
            ].filter(Boolean); // Remove campos que não existem

            // Função que sincroniza os valores dos campos e executa a pesquisa por categoria
            function sincronizarPesquisa(origem) {
                const termoPesquisa = origem.value.toLowerCase();

                // Atualiza todos os campos de pesquisa com o mesmo valor
                camposPesquisa.forEach(campo => {
                    if (campo !== origem) {
                        campo.value = origem.value;
                    }
                });

                // Configura as categorias para busca
                const categorias = [
                    {
                        produtos: document.querySelectorAll('.produto-item.catalogo'),
                        mensagem: document.getElementById('mensagemNaoEncontradoCatalogo')
                    },
                    {
                        produtos: document.querySelectorAll('.produto-item.promocao'),
                        mensagem: document.getElementById('mensagemNaoEncontradoPromocao')
                    },
                    {
                        produtos: document.querySelectorAll('.produto-item.freteGratis'),
                        mensagem: document.getElementById('mensagemNaoEncontradoFreteGratis')
                    },
                    {
                        produtos: document.querySelectorAll('.produto-item.novidades'),
                        mensagem: document.getElementById('mensagemNaoEncontradoNovidades')
                    }
                ];

                categorias.forEach(categoria => {
                    let produtoEncontrado = false;

                    categoria.produtos.forEach(produto => {
                        const nomeProduto = produto.querySelector('.produto-detalhes')?.textContent.toLowerCase() || '';

                        if (nomeProduto.includes(termoPesquisa) || termoPesquisa === '') {
                            produto.style.display = 'block';
                            produtoEncontrado = true;
                        } else {
                            produto.style.display = 'none';
                        }
                    });

                    // Exibe ou oculta a mensagem de "Produto não encontrado"
                    if (categoria.mensagem) {
                        categoria.mensagem.style.display = produtoEncontrado ? 'none' : 'block';
                    }
                });
            }

            // Adiciona o evento de entrada para todos os campos
            camposPesquisa.forEach(campo => {
                campo.addEventListener('input', function () {
                    sincronizarPesquisa(this);
                });
            });
        });

    </script>

</body>

</html>