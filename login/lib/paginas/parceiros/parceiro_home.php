<?php
include('../../conexao.php');

// Inicia a sessão
if (!isset($_SESSION)) {
    session_start();
}

// Verifica se o usuário está logado
if (isset($_SESSION['id'])) {
    $id = $_SESSION['id'];

    // Consulta para buscar o parceiro
    $sql_query = $mysqli->query(query: "SELECT * FROM meus_parceiros WHERE id = '$id'") or die($mysqli->error);
    $parceiro = $sql_query->fetch_assoc();

    // Verifica e ajusta a logo
    if (isset($parceiro['logo'])) {
        $minhaLogo = $parceiro['logo'];

        if ($minhaLogo != '') {
            // Se existe e não está vazio, atribui o valor à variável logo
            $logo = $parceiro['logo'];
            //echo ('oii');
        }
    } else {
        $logo = '../arquivos_fixos/icone_loja.jpg';
    }
} else {
    session_unset();
    session_destroy();
    header("Location: ../../../../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['categoria_selecionada'])) {
    $categoriaSelecionada = $_POST['categoria_selecionada'];
} else {
    // Consulta para buscar categorias únicas dos produtos do parceiro
    $sql_categorias = "SELECT categoria FROM produtos WHERE id_parceiro = $id";
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

    // Define a categoria padrão como a primeira da lista, se disponível
    $categoriaSelecionada = $primeiraCategoria ?? null;
}

// Consulta para buscar produtos do catálogo
$catalogo = $mysqli->query("
        SELECT * FROM produtos 
        WHERE id_parceiro = '$id'
        AND categoria = '$categoriaSelecionada'
    ") or die($mysqli->error);

// Consulta para buscar promoções
$promocoes = $mysqli->query("
        SELECT * FROM produtos 
        WHERE id_parceiro = '$id' 
        AND categoria = '$categoriaSelecionada' 
        AND promocao = '1'
    ") or die($mysqli->error);

// Consulta para buscar produtos com frete grátis
$frete_gratis = $mysqli->query("
        SELECT * FROM produtos 
        WHERE id_parceiro = '$id'
        AND categoria = '$categoriaSelecionada'
        AND (
            frete_gratis = '1' 
            OR (promocao = '1' AND frete_gratis_promocao = '1')
        )
    ") or die("Erro na consulta de frete grátis: " . $mysqli->error);

// Debug para verificar os resultados
/*if ($frete_gratis->num_rows > 0) {
    echo "Categoria selecionada: " . htmlspecialchars($categoriaSelecionada) . "<br>";
    echo "Número de produtos com frete grátis: " . $frete_gratis->num_rows . "<br>";

    while ($produto = $frete_gratis->fetch_assoc()) {
        echo "Produto ID: " . htmlspecialchars($produto['id_produto']) . " - Nome: " . htmlspecialchars($produto['nome_produto']) . "<br>";
    }
} else {
    echo "Nenhum produto com frete grátis encontrado para a categoria: " . htmlspecialchars($categoriaSelecionada) . "<br>";
}*/

// Consulta para buscar novidades
$novidades = $mysqli->query("
        SELECT *, DATEDIFF(NOW(), data) AS dias_desde_cadastro
        FROM produtos 
        WHERE id_parceiro = '$id' 
        AND categoria = '$categoriaSelecionada'  
        AND DATEDIFF(NOW(), data) <= 30
    ") or die($mysqli->error);

// Consulta para buscar produtos ocultos
$produtos_ocultos = $mysqli->query("
        SELECT * FROM produtos 
        WHERE id_parceiro = '$id' 
        AND categoria = '$categoriaSelecionada' 
        AND oculto = '1'
    ") or die($mysqli->error);

// Consulta para somar todas as notificações por coluna
$sql_query_not_par = "
    SELECT 
        SUM(plataforma) AS total_plataforma, 
        SUM(not_novo_produto) AS total_not_novo_produto,
        SUM(not_adicao_produto) AS total_not_adicao_produto,
        SUM(pedidos) AS total_pedidos
    FROM contador_notificacoes_parceiro
    WHERE id_parceiro = $id";  // Pode adicionar mais condições se necessário

$result = $mysqli->query($sql_query_not_par);

// Verifica se há resultados
if ($result) {
    $row = $result->fetch_assoc();

    // Recupera as somas
    $total_plataforma = $row['total_plataforma'] ?? 0;
    $total_not_novo_produto = $row['total_not_novo_produto'] ?? 0;
    $total_not_adicao_produto = $row['total_not_adicao_produto'] ?? 0;
    $total_pedidos = $row['total_pedidos'] ?? 0;

    // Soma os totais das colunas
    $total_notificacoes = $total_plataforma + $total_not_novo_produto + $total_not_adicao_produto + $total_pedidos;

    //echo "Total de notificações: $total_notificacoes";
} else {
    echo "Erro na consulta!";
}

// Obtenha a data atual
$data_atual = date('Y-m-d');

// Consulta para buscar todos os produtos com promoção
$produtos_promocao = $mysqli->query("SELECT id_produto, promocao, ini_promocao, fim_promocao FROM produtos") or die($mysqli->error);

while ($produtos_encontrados = $produtos_promocao->fetch_assoc()) {
    $id_produto = $produtos_encontrados['id_produto'];
    $promocao = $produtos_encontrados['promocao'];
    $data_inicio = $produtos_encontrados['ini_promocao'];
    $data_fim = $produtos_encontrados['fim_promocao'];

    // Verifica se a promoção deve estar ativa ou inativa
    if ($promocao === 1 && $data_inicio <= $data_atual && $data_fim >= $data_atual) {
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

// Adicionar consulta para buscar a taxa padrão
$taxa_padrao = $mysqli->query("SELECT * FROM config_admin WHERE taxa_padrao != '' ORDER BY data_alteracao DESC LIMIT 1") or die($mysqli->error);
$taxa = $taxa_padrao->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $parceiro['nomeFantasia']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!--<link rel="stylesheet" href="parceiro_home.css">-->
    <style>
        /* Estilos gerais */
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
            overflow-y: scroll;
            /* Garante que o corpo da página possa rolar */
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
            font-size: 40px;
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
            font-size: 27px;
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
            top: 60px;
            /* Ajuste conforme a altura do cabeçalho */
            right: 20px;
            /* Posiciona o menu à direita */
            width: 250px;
            height: 400px;
            background-color: white;
            border: 2px solid #ffb300;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            padding: 10px;
        }

        aside#menu-lateral ul {
            list-style: none;
            padding: 0;
        }

        aside#menu-lateral ul li {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #ddd;
        }

        aside#menu-lateral ul li:hover {
            background-color: #f0f0f0;
        }

        aside#painel-notificacoes {
            display: none;
            position: fixed;
            top: 60px;
            /* Ajuste conforme a altura do cabeçalho */
            right: 20px;
            /* Posiciona o menu à direita */
            width: 250px;
            height: 400px;
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

        /* Fechamento do seletor corrigido */

        /* Estilo para ícones */
        aside#menu-lateral ul li i {
            margin-right: 10px;
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
            width: 100%;
            padding: 10px;
            /*margin-top: -30px;*/
        }

        .titulo {
            font-size: 20px;
            font-weight: bold;
        }

        .input {
            width: 50%;
            padding: 10px;
            font-size: 20px;
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
            right: -20px;
            background-color: red;
            color: white;
            padding: 5px;
            border-radius: 50%;
            font-size: 12px;
            font-weight: bold;
        }

        .pedidos {
            position: relative;
            display: inline-block;
            margin-left: 15px;
        }

        .pedidos a {
            color: #f0f0f0;
            text-decoration: none;
        }

        .pedidos i {
            font-size: 27px;
            transition: transform 0.3s ease, color 0.3s ease;
            cursor: pointer;
        }

        .pedido-count {
            position: absolute;
            top: -8px;
            right: -20px;
            background-color: green;
            color: white;
            padding: 5px;
            border-radius: 50%;
            font-size: 10px;
            font-weight: bold;



            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.2);
            /* Adiciona sombra para destaque */
            transition: transform 0.3s ease, background-color 0.3s ease;
            /* Suaviza animações */
        }

        .pedido-count[style="display: none;"] {
            display: none;
        }

        .pedido-count:hover {
            transform: scale(1.1);
            /* Aumenta ligeiramente ao passar o mouse */
            background-color: #218838;
            /* Cor verde mais escura ao passar o mouse */
        }

        /* Painel de notificações estilo semelhante ao menu lateral */
        #painel-notificacoes {
            display: none;
            position: fixed;
            top: 60px;
            /* Ajuste conforme a altura do cabeçalho */
            right: 20px;
            /* Posiciona o menu à direita */
            width: 250px;
            height: 400px;
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

        .lista-frete_gratis {
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

        .lista-produtos_ocultos {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            justify-content: center;
            padding-bottom: 50px;
        }

        .produto-item {
            background-color: aliceblue;
            display: flex;
            flex-direction: column;
            align-items: center;
            border: 1px solid #ccc;
            padding: 3px;
            border-radius: 8px;
            width: 170px;
            height: 320px;
            /* Adjusted height */
            text-align: center;
            box-shadow: 0px 0px 8px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s ease;
        }

        .produto-item:hover {
            box-shadow: 0px 0px 12px rgba(0, 0, 0, 0.2);
        }

        .produto-imagem {
            width: 100%;
            height: auto;
            max-height: 160px;
            margin-bottom: 5px;
            border-radius: 5px;
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

        .button-editar {
            display: inline-block;
            padding: 5px 10px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
            width: 100%;
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
            /*right: 5px;*/
            left: 5px;
            font-size: 20px;
            color: black;
            /* Cor do ícone */
            border-radius: 50%;
            padding: 2px;
        }

        .button-editar:hover {
            background-color: darkorange;
        }

        .button {
            margin: 5px;
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            font-size: 18px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .button:hover {
            background-color: darkorange;
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

        /* Para telas menores que 768px */
        @media (max-width: 768px) {
            header h1 {
                font-size: 20px;
                /* Diminui o tamanho do título em telas pequenas */
                /*margin: 20px 0; /* Adiciona margem para descer o título em telas pequenas */
            }

            header .logo img {
                height: 100px;
                /* Diminui o tamanho do logo em telas pequenas */
                width: 100px;
                /* Ajuste proporcional ao tamanho */
            }

            aside#menu-lateral {
                display: none;
                /* Oculta a barra lateral em telas pequenas */
            }

            /* Adicionando esta linha para esconder o ícone do menu */
            .menu-superior-direito .fa-store {
                display: none;
                /* Oculta o ícone do menu em telas pequenas */
            }

            .pedidos {
                display: none;
                /* Esconde o ícone dos pedidos em telas menores */
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
                /* Bordas arredondadas só no topo, estilo de aba */
                background-color: #007BFF;
                cursor: pointer;
                font-size: 20px;
                font-weight: bold;
                text-align: center;
                transition: background-color 0.3s ease, transform 0.3s ease;
                display: flex;
                /* Garante que o conteúdo interno seja flexível */
                padding: 10px 50px;
                width: auto;
                /* Garante que as abas se ajustem ao conteúdo */
                /*width: 5%; /* Garante que as abas ocupem a largura completa em telas pequenas */
                justify-content: center;
                /* Centraliza o texto dentro da aba */
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

            .produto-item {
                width: 100%;
                width: 160px;
                height: 160;
                padding: 3px;
            }

            .produto-imagem {
                width: 100%;
                height: auto;
                max-height: 155px;
            }

            .produto-nome {
                font-size: 1.1em;
            }

            .button-editar {
                padding: 6px 12px;
            }

        }

        /* Para telas menores que 480px */
        @media (max-width: 480px) {
            .logo-img {
                width: 80px;
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

            .lista-frete_gratis {
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

            .lista-produtos_ocultos {
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
                justify-content: center;
                padding-bottom: 50px;
            }

            .produto-item {
                width: 40%;
                max-width: 250px;
                height: 350px;
                padding: 3px;

            }

            .produto-imagem {
                width: 100%;
                height: auto;
                max-height: 190px;
            }

            .produto-nome {
                font-size: 1.1em;
            }

            .button-editar {
                width: 100%;
                padding: 10px;
            }

            .produto-descricao {
                font-size: 0.85em;
            }
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

        .icone-pedido-wrapper {
            position: relative;
            display: inline-block;
            margin: 0 10px;
        }

        .icone-pedido-wrapper a {
            text-decoration: none;
            color: inherit;
            transition: color 0.3s ease, transform 0.3s ease;
        }

        .icone-pedido-wrapper a:hover {
            color: #007BFF;
            transform: scale(1.1);
        }

        .icone-pedido-wrapper i {
            font-size: 24px;
            position: relative;
            transition: transform 0.3s ease, color 0.3s ease;
        }

        .icone-pedido-wrapper i:hover {
            color: #ffbb09;
            transform: translateY(-3px);
        }

        .pedido-count-rodape {
            position: absolute;
            top: -8px;
            right: -10px;
            background-color: green;
            color: white;
            padding: 5px;
            border-radius: 50%;
            font-size: 12px;
            font-weight: bold;
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .pedido-count-rodape:hover {
            background-color: #218838;
            transform: scale(1.1);
        }
    </style>
    <script src="parceiro_home.js"></script>
</head>

<body>
    <form id="formCategoria" method="POST" action="">
        <input type="hidden" name="id_parceiro" id="id_parceiro" value="<?php echo $id; ?>">
        <input type="hidden" name="categoria_selecionada" id="categoria_selecionada"
            value="<?php echo $categoriaSelecionada; ?>">
        <button type="submit" id="carregar_categoria" class="carregar_categoria" style="display: none;">enviar</button>
    </form>

    <!-- Header -->
    <header>
        <div class="logo">
            <img src="<?php echo 'arquivos/' . $logo; ?>" alt="Logo da Loja" class="logo-img">
        </div>

        <h1><?php echo $parceiro['nomeFantasia']; ?></h1>

        <div class="menu-superior-direito">
            <!-- Ícone de notificações com contagem -->
            <div class="notificacoes">
                <i class="fas fa-bell" onclick="toggleNotificacoes()"></i>
                <!-- Exibir a contagem de notificações -->
                <?php if ($total_notificacoes > 0): ?>
                    <span id="notificacao-count"
                        class="notificacao-count"><?php echo htmlspecialchars($total_notificacoes); ?></span>
                <?php else: ?>
                    <span id="notificacao-count" class="notificacao-count" style="display: none;"></span>
                <?php endif; ?>
            </div>
            <div class="pedidos">
                <a href="vendas/pedidos.php?id_parceiro=<?php echo urlencode($id); ?>">
                    <i class="fas fa-box" title="Pedidos">
                        <!-- Exibir a contagem de produtos no carrinho -->
                        <?php if ($total_pedidos > 0): ?>
                            <span id="pedido-count" class="pedido-count">
                                <?php echo htmlspecialchars($total_pedidos); ?>
                            </span>
                        <?php else: ?>
                            <span id="pedido-count" class="pedido-count" style="display: none;"></span>
                        <?php endif; ?>
                    </i>
                </a>
            </div>
            <i class="fas fa-store" onclick="toggleMenu()"></i><!--Configuraçõa da Loja-->
        </div>
    </header>

    <aside id="painel-notificacoes">
        <h2>Notificações: <span
                id="notificacao-header-count"><?php echo htmlspecialchars($total_notificacoes); ?></span></h2>
        <ul id="lista-notificacoes">
            <li data-id="1" onclick="abrirNotificacao(1)">Plataforma: <?php echo $total_plataforma; ?></li>
            <li data-id="2" onclick="abrirNotificacao(2)">Novo Produto: <?php echo $total_not_novo_produto; ?></li>
            <li data-id="3" onclick="abrirNotificacao(3)">Edição de Produtos: <?php echo $total_not_adicao_produto; ?>
            </li>
            <li data-id="4" onclick="abrirNotificacao(4)">Pedidos: <?php echo $total_pedidos; ?></li>
        </ul>
    </aside>

    <!-- Menu lateral que aparece abaixo do ícone de menu -->
    <aside id="menu-lateral">
        <ul>
            <li><a href="perfil_loja.php"><i class="fas fa-user"></i> Perfil da Loja</a></li>
            <li><a href="configuracoes.php?id_parceiro=<?php echo urlencode($id); ?>"><i class="fas fa-cog"></i>
                    Configurações</a></li>
            <li><a href="parceiro_logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
        </ul>
    </aside>

    <div class="categorias">
        <?php
        // Consulta para buscar parceiros pelo CEP
        $sql_parceiros = "SELECT * FROM meus_parceiros WHERE id = $id AND status = '1'";
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
                //var_dump($categoriasUnicas);
        
                // Pega a primeira categoria, se existir
                $primeiraCategoria = !empty($categoriasUnicas) ? reset($categoriasUnicas) : null;
                // Use reset() para obter o primeiro elemento do array
                ?>

                <div class="parceiro-card">
                    <div class="categorias-parceiro">
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
            <!-- Conteúdo -->
            <div class="tab active" onclick="mostrarConteudo('catalogo',this)">
                <span>Catálogo</span>
            </div>

            <div class="tab" onclick="mostrarConteudo('promocoes',this)">
                <span>🔥Promoções</span>
            </div>

            <div class="tab" onclick="mostrarConteudo('frete_gratis',this)">
                <span>🚚Frete Grátis</span>
            </div>

            <div class="tab" onclick="mostrarConteudo('novidades',this)">
                <span>🆕Novidades</span>
            </div>

            <div class="tab" onclick="mostrarConteudo('produtos_ocultos',this)">
                <span>👁️‍🗨️Produtos Ocultos</span>
            </div>

        </div>

        <!-- Conteúdos correspondentes às abas -->
        <div id="conteudo-catalogo" class="conteudo-aba" style="display: none;">
            <?php
            if ($catalogo->num_rows > 0):
                // Exibe o número de produtos encontrados
                $numProdutos = $catalogo->num_rows;
                //echo "<p style='margin-top: 30px;'>$numProdutos produto(s) encontrado(s).</p>";
                ?>
                <div class="container">
                    <input id="inputPesquisaCatalogo" class="input" type="text" placeholder="Pesquisar Produto.">

                    <form method="POST" action="produtos/adicionar_produto.php" class="catalogo-form">
                        <input type="hidden" name="id_parceiro" value="<?php echo $id; ?>">
                        <button class="button">Cadastrar novo produto</button>
                    </form>
                </div>

                <!-- Lista de produtos aqui -->
                <div class="lista-produtos">
                    <?php while ($produto = $catalogo->fetch_assoc()): ?>
                        <div class="produto-item">
                            <?php
                            // Verifica e processa as imagens do produto
                            $primeiraImagem = '/default_image.jpg'; // Imagem padrão
                            if (!empty($produto['imagens'])) {
                                $imagensArray = explode(',', $produto['imagens']);
                                $primeiraImagem = 'produtos/img_produtos/' . $imagensArray[0];
                            }

                            // Determinar se o produto é novo
                            $dataCadastro = new DateTime($produto['data']); // Data do produto
                            $dataAtual = new DateTime(); // Data atual
                            $intervalo = $dataCadastro->diff($dataAtual); // Calcula a diferença entre as datas
                            $diasDesdeCadastro = $intervalo->days; // Número de dias de diferença
                            $isNovo = $diasDesdeCadastro <= 30;

                            // Determinar o preço do produto
                            if (!empty($produto['promocao']) && $produto['promocao'] == 1) {
                                $valorProduto = $produto['valor_promocao'] + ($produto['valor_promocao'] * ($taxa['taxa_padrao'] / 100));
                            } else {
                                $valorProduto = $produto['valor_venda_vista'];
                            }
                            ?>

                            <!-- Imagem do produto -->
                            <img src="<?php echo htmlspecialchars($primeiraImagem, ENT_QUOTES, 'UTF-8'); ?>"
                                alt="Imagem do Produto" class="produto-imagem">

                            <!-- Ícones de status do produto -->
                            <div class="produto-status">
                                <?php if (isset($produto['oculto']) && $produto['oculto'] == 1): ?>
                                    <span class="icone-oculto" title="Produto oculto">👁️‍🗨️</span>
                                <?php endif; ?>

                                <?php if ($isNovo): ?>
                                    <span class="icone-novidades" title="Novidades">🆕</span>
                                <?php endif; ?>

                                <?php if (!empty($produto['frete_gratis']) && $produto['frete_gratis'] == 1): ?>
                                    <span class="icone-frete-gratis" title="Frete grátis">🚚</span>
                                <?php elseif (!empty($produto['promocao']) && $produto['promocao'] == 1 && !empty($produto['frete_gratis_promocao']) && $produto['frete_gratis_promocao'] == 1): ?>
                                    <span class="icone-frete-gratis" title="Frete grátis (promoção)">🚚</span>
                                <?php endif; ?>

                                <?php if (!empty($produto['promocao']) && $produto['promocao'] == 1): ?>
                                    <span class="icone-promocao" title="Produto em promoção">🔥</span>
                                <?php endif; ?>
                            </div>

                            <div class="produto-detalhes">
                                <h3 class="produto-nome">
                                    <?php echo htmlspecialchars($produto['nome_produto'] ?? 'Produto não especificado', ENT_QUOTES, 'UTF-8'); ?>
                                </h3>

                                <!-- Preço do produto -->
                                <p class="produto-preco">R$ <?php echo number_format($valorProduto, 2, ',', '.'); ?></p>

                                <!-- Botão de edição -->
                                <?php if (isset($produto['produto_aprovado']) && $produto['produto_aprovado'] == 1): ?>
                                    <a href="produtos/editar_produto.php?id_produto=<?php echo htmlspecialchars($produto['id_produto'], ENT_QUOTES, 'UTF-8'); ?>"
                                        class="button-editar">Editar</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- Mensagem de produto não encontrado -->
                <p id="mensagemNaoEncontradoCatalogo" style="display: none;">Produto não encontrado.</p>

            </div>

        <?php else: ?>
            <div class="conteudo">
                <form method="POST" action="produtos/adicionar_produto.php">
                    <input type="hidden" name="id_parceiro" value="<?php echo $id; ?>">
                    <p style="margin-top: 30px;">Nenhuma produto cadastrado ainda!.</p>
                    <button class="button">Inclua seu primeiro produto</button>
                </form>
            </div>
        <?php endif; ?>
        </div>

        <div id="conteudo-promocoes" class="conteudo-aba" style="display: none;">
            <?php
            if ($promocoes->num_rows > 0):
                // Exibe o número de produtos encontrados
                $numProdutosPromocao = $promocoes->num_rows;
                //echo "<p style='margin-top: 30px;'>$numProdutosPromocao produto(s) encontrado(s) em promoção.</p>";
                ?>
                <div class="container">
                    <input id="inputPesquisaPromocoes" class="input" type="text" placeholder="Pesquisar Produto.">
                </div>

                <!-- Lista de produtos em promoção -->
                <div class="lista-promocoes">
                    <?php while ($produto = $promocoes->fetch_assoc()): ?>
                        <div class="produto-item">
                            <?php
                            // Verifica e processa as imagens do produto
                            $primeiraImagem = '/default_image.jpg'; // Imagem padrão
                            if (!empty($produto['imagens'])) {
                                $imagensArray = explode(',', $produto['imagens']);
                                $primeiraImagem = 'produtos/img_produtos/' . $imagensArray[0];
                            }

                            // Determinar se o produto é novo
                            $dataCadastro = new DateTime($produto['data']); // Data do produto
                            $dataAtual = new DateTime(); // Data atual
                            $intervalo = $dataCadastro->diff($dataAtual); // Calcula a diferença entre as datas
                            $diasDesdeCadastro = $intervalo->days; // Número de dias de diferença
                            $isNovo = $diasDesdeCadastro <= 30;

                            // Determinar o preço do produto
                            if (!empty($produto['promocao']) && $produto['promocao'] == 1) {
                                $valorProduto = $produto['valor_promocao'] + ($produto['valor_promocao'] * ($taxa['taxa_padrao'] / 100));
                            } else {
                                $valorProduto = $produto['valor_venda_vista'];
                            }
                            ?>

                            <!-- Imagem do produto -->
                            <img src="<?php echo htmlspecialchars($primeiraImagem, ENT_QUOTES, 'UTF-8'); ?>"
                                alt="Imagem do Produto" class="produto-imagem">

                            <!-- Ícones de status do produto -->
                            <div class="produto-status">
                                <?php if (isset($produto['oculto']) && $produto['oculto'] == 1): ?>
                                    <span class="icone-oculto" title="Produto oculto">👁️‍🗨️</span>
                                <?php endif; ?>

                                <?php if ($isNovo): ?>
                                    <span class="icone-novidades" title="Novidades">🆕</span>
                                <?php endif; ?>

                                <?php if (!empty($produto['frete_gratis']) && $produto['frete_gratis'] == 1): ?>
                                    <span class="icone-frete-gratis" title="Frete grátis">🚚</span>
                                <?php elseif (!empty($produto['promocao']) && $produto['promocao'] == 1 && !empty($produto['frete_gratis_promocao']) && $produto['frete_gratis_promocao'] == 1): ?>
                                    <span class="icone-frete-gratis" title="Frete grátis (promoção)">🚚</span>
                                <?php endif; ?>

                                <?php if (!empty($produto['promocao']) && $produto['promocao'] == 1): ?>
                                    <span class="icone-promocao" title="Produto em promoção">🔥</span>
                                <?php endif; ?>
                            </div>

                            <div class="produto-detalhes">
                                <h3 class="produto-nome">
                                    <?php echo htmlspecialchars($produto['nome_produto'] ?? 'Produto não especificado', ENT_QUOTES, 'UTF-8'); ?>
                                </h3>

                                <!-- Preço do produto -->
                                <p class="produto-preco">R$ <?php echo number_format($valorProduto, 2, ',', '.'); ?></p>

                                <!-- Botão de edição -->
                                <?php if (isset($produto['produto_aprovado']) && $produto['produto_aprovado'] == 1): ?>
                                    <a href="produtos/editar_produto.php?id_produto=<?php echo htmlspecialchars($produto['id_produto'], ENT_QUOTES, 'UTF-8'); ?>"
                                        class="button-editar">Editar</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- Mensagem de produto não encontrado -->
                <p id="mensagemNaoEncontradoPromocoes" style="display: none;">Produto não encontrado.</p>

            <?php else: ?>
                <div class="conteudo">
                    <p style="margin-top: 30px;">Nenhuma promoção disponível no momento.</p>
                </div>
            <?php endif; ?>
        </div>

        <div id="conteudo-frete_gratis" class="conteudo-aba" style="display: none;">
            <?php
            if ($frete_gratis->num_rows > 0):
                //echo "<p style='margin-top: 30px;'>".$frete_gratis->num_rows." produto(s) encontrado(s) com frete grátis.</p>";
                ?>
                <div class="container">
                    <input id="inputPesquisaFreteGratis" class="input" type="text" placeholder="Pesquisar Produto.">
                </div>

                <!-- Lista de produtos com frete grátis -->
                <div class="lista-frete_gratis">
                    <?php
                    // Reiniciar o ponteiro do resultado para garantir que os produtos sejam exibidos
                    $frete_gratis->data_seek(0);
                    while ($produto = $frete_gratis->fetch_assoc()):
                        ?>
                        <div class="produto-item">
                            <?php
                            // Verifica e processa as imagens do produto
                            $primeiraImagem = '/default_image.jpg'; // Imagem padrão
                            if (!empty($produto['imagens'])) {
                                $imagensArray = explode(',', $produto['imagens']);
                                $primeiraImagem = 'produtos/img_produtos/' . $imagensArray[0];
                            }

                            // Determinar se o produto é novo
                            $dataCadastro = new DateTime($produto['data']); // Data do produto
                            $dataAtual = new DateTime(); // Data atual
                            $intervalo = $dataCadastro->diff($dataAtual); // Calcula a diferença entre as datas
                            $diasDesdeCadastro = $intervalo->days; // Número de dias de diferença
                            $isNovo = $diasDesdeCadastro <= 30;

                            // Determinar o preço do produto
                            if (!empty($produto['promocao']) && $produto['promocao'] == 1) {
                                $valorProduto = $produto['valor_promocao'] + ($produto['valor_promocao'] * ($taxa['taxa_padrao'] / 100));
                            } else {
                                $valorProduto = $produto['valor_venda_vista'];
                            }
                            ?>

                            <!-- Imagem do produto -->
                            <img src="<?php echo htmlspecialchars($primeiraImagem, ENT_QUOTES, 'UTF-8'); ?>"
                                alt="Imagem do Produto" class="produto-imagem">

                            <!-- Ícones de status do produto -->
                            <div class="produto-status">
                                <?php if (isset($produto['oculto']) && $produto['oculto'] == 1): ?>
                                    <span class="icone-oculto" title="Produto oculto">👁️‍🗨️</span>
                                <?php endif; ?>

                                <?php if ($isNovo): ?>
                                    <span class="icone-novidades" title="Novidades">🆕</span>
                                <?php endif; ?>

                                <?php if (!empty($produto['frete_gratis']) && $produto['frete_gratis'] == 1): ?>
                                    <span class="icone-frete-gratis" title="Frete grátis">🚚</span>
                                <?php elseif (!empty($produto['promocao']) && $produto['promocao'] == 1 && !empty($produto['frete_gratis_promocao']) && $produto['frete_gratis_promocao'] == 1): ?>
                                    <span class="icone-frete-gratis" title="Frete grátis (promoção)">🚚</span>
                                <?php endif; ?>

                                <?php if (!empty($produto['promocao']) && $produto['promocao'] == 1): ?>
                                    <span class="icone-promocao" title="Produto em promoção">🔥</span>
                                <?php endif; ?>
                            </div>

                            <div class="produto-detalhes">
                                <h3 class="produto-nome">
                                    <?php echo htmlspecialchars($produto['nome_produto'] ?? 'Produto não especificado', ENT_QUOTES, 'UTF-8'); ?>
                                </h3>

                                <!-- Preço do produto -->
                                <p class="produto-preco">R$ <?php echo number_format($valorProduto, 2, ',', '.'); ?></p>

                                <!-- Botão de edição -->
                                <?php if (isset($produto['produto_aprovado']) && $produto['produto_aprovado'] == 1): ?>
                                    <a href="produtos/editar_produto.php?id_produto=<?php echo htmlspecialchars($produto['id_produto'], ENT_QUOTES, 'UTF-8'); ?>"
                                        class="button-editar">Editar</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- Mensagem de produto não encontrado -->
                <p id="mensagemNaoEncontrado" style="display: none;">Produto não encontrado.</p>

            <?php else: ?>
                <p style="margin-top: 30px;">Nenhum produto disponível com frete grátis.</p>
            <?php endif; ?>
        </div>

        <div id="conteudo-novidades" class="conteudo-aba" style="display: none;">
            <?php
            if ($novidades->num_rows > 0):
                ?>
                <div class="container">
                    <input id="inputPesquisaNovidades" class="input" type="text" placeholder="Pesquisar Produto.">
                </div>

                <!-- Lista de promoções aqui -->
                <div class="lista-novidades">
                    <?php while ($produto = $novidades->fetch_assoc()): ?>
                        <div class="produto-item">
                            <?php
                            // Verifica e processa as imagens do produto
                            $primeiraImagem = '/default_image.jpg'; // Imagem padrão
                            if (!empty($produto['imagens'])) {
                                $imagensArray = explode(',', $produto['imagens']);
                                $primeiraImagem = 'produtos/img_produtos/' . $imagensArray[0];
                            }

                            // Determinar se o produto é novo
                            $dataCadastro = new DateTime($produto['data']); // Data do produto
                            $dataAtual = new DateTime(); // Data atual
                            $intervalo = $dataCadastro->diff($dataAtual); // Calcula a diferença entre as datas
                            $diasDesdeCadastro = $intervalo->days; // Número de dias de diferença
                            $isNovo = $diasDesdeCadastro <= 30;

                            // Determinar o preço do produto
                            if (!empty($produto['promocao']) && $produto['promocao'] == 1) {
                                $valorProduto = $produto['valor_promocao'] + ($produto['valor_promocao'] * ($taxa['taxa_padrao'] / 100));
                            } else {
                                $valorProduto = $produto['valor_venda_vista'];
                            }
                            ?>

                            <!-- Imagem do produto -->
                            <img src="<?php echo htmlspecialchars($primeiraImagem, ENT_QUOTES, 'UTF-8'); ?>"
                                alt="Imagem do Produto" class="produto-imagem">

                            <!-- Ícones de status do produto -->
                            <div class="produto-status">
                                <?php if (isset($produto['oculto']) && $produto['oculto'] == 1): ?>
                                    <span class="icone-oculto" title="Produto oculto">👁️‍🗨️</span>
                                <?php endif; ?>

                                <?php if ($isNovo): ?>
                                    <span class="icone-novidades" title="Novidades">🆕</span>
                                <?php endif; ?>

                                <?php if (!empty($produto['frete_gratis']) && $produto['frete_gratis'] == 1): ?>
                                    <span class="icone-frete-gratis" title="Frete grátis">🚚</span>
                                <?php elseif (!empty($produto['promocao']) && $produto['promocao'] == 1 && !empty($produto['frete_gratis_promocao']) && $produto['frete_gratis_promocao'] == 1): ?>
                                    <span class="icone-frete-gratis" title="Frete grátis (promoção)">🚚</span>
                                <?php endif; ?>

                                <?php if (!empty($produto['promocao']) && $produto['promocao'] == 1): ?>
                                    <span class="icone-promocao" title="Produto em promoção">🔥</span>
                                <?php endif; ?>
                            </div>

                            <div class="produto-detalhes">
                                <h3 class="produto-nome">
                                    <?php echo htmlspecialchars($produto['nome_produto'] ?? 'Produto não especificado', ENT_QUOTES, 'UTF-8'); ?>
                                </h3>

                                <!-- Preço do produto -->
                                <p class="produto-preco">R$ <?php echo number_format($valorProduto, 2, ',', '.'); ?></p>

                                <!-- Botão de edição -->
                                <?php if (isset($produto['produto_aprovado']) && $produto['produto_aprovado'] == 1): ?>
                                    <a href="produtos/editar_produto.php?id_produto=<?php echo htmlspecialchars($produto['id_produto'], ENT_QUOTES, 'UTF-8'); ?>"
                                        class="button-editar">Editar</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- Mensagem de produto não encontrado -->
                <p id="mensagemNaoEncontrado" style="display: none;">Produto não encontrado.</p>

            <?php else: ?>
                <p style="margin-top: 30px;">Nenhuma novidade no momento.</p>
            <?php endif; ?>
        </div>

        <div id="conteudo-produtos_ocultos" class="conteudo-aba" style="display: none;">
            <?php
            // Verifica se há produtos ocultos
            if ($produtos_ocultos->num_rows > 0):
                ?>
                <div class="container">
                    <input id="inputPesquisaProdutosOcultos" class="input" type="text" placeholder="Pesquisar Produto.">
                </div>

                <!-- Lista de produtos ocultos aqui -->
                <div class="lista-produtos_ocultos">
                    <?php while ($produto = $produtos_ocultos->fetch_assoc()): ?>
                        <div class="produto-item">
                            <?php
                            // Verifica e processa as imagens do produto
                            $primeiraImagem = '/default_image.jpg'; // Imagem padrão
                            if (!empty($produto['imagens'])) {
                                $imagensArray = explode(',', $produto['imagens']);
                                $primeiraImagem = 'produtos/img_produtos/' . $imagensArray[0];
                            }

                            // Determinar se o produto é novo
                            $dataCadastro = new DateTime($produto['data']); // Data do produto
                            $dataAtual = new DateTime(); // Data atual
                            $intervalo = $dataCadastro->diff($dataAtual); // Calcula a diferença entre as datas
                            $diasDesdeCadastro = $intervalo->days; // Número de dias de diferença
                            $isNovo = $diasDesdeCadastro <= 30;

                            // Determinar o preço do produto
                            if (!empty($produto['promocao']) && $produto['promocao'] == 1) {
                                $valorProduto = $produto['valor_promocao'] + ($produto['valor_promocao'] * ($taxa['taxa_padrao'] / 100));
                            } else {
                                $valorProduto = $produto['valor_venda_vista'];
                            }
                            ?>

                            <!-- Imagem do produto -->
                            <img src="<?php echo htmlspecialchars($primeiraImagem, ENT_QUOTES, 'UTF-8'); ?>"
                                alt="Imagem do Produto" class="produto-imagem">

                            <!-- Ícones de status do produto -->
                            <div class="produto-status">
                                <?php if (isset($produto['oculto']) && $produto['oculto'] == 1): ?>
                                    <span class="icone-oculto" title="Produto oculto">👁️‍🗨️</span>
                                <?php endif; ?>

                                <?php if ($isNovo): ?>
                                    <span class="icone-novidades" title="Novidades">🆕</span>
                                <?php endif; ?>

                                <?php if (!empty($produto['frete_gratis']) && $produto['frete_gratis'] == 1): ?>
                                    <span class="icone-frete-gratis" title="Frete grátis">🚚</span>
                                <?php elseif (!empty($produto['promocao']) && $produto['promocao'] == 1 && !empty($produto['frete_gratis_promocao']) && $produto['frete_gratis_promocao'] == 1): ?>
                                    <span class="icone-frete-gratis" title="Frete grátis (promoção)">🚚</span>
                                <?php endif; ?>

                                <?php if (!empty($produto['promocao']) && $produto['promocao'] == 1): ?>
                                    <span class="icone-promocao" title="Produto em promoção">🔥</span>
                                <?php endif; ?>
                            </div>

                            <div class="produto-detalhes">
                                <h3 class="produto-nome">
                                    <?php echo htmlspecialchars($produto['nome_produto'] ?? 'Produto não especificado', ENT_QUOTES, 'UTF-8'); ?>
                                </h3>

                                <!-- Preço do produto -->
                                <p class="produto-preco">R$ <?php echo number_format($valorProduto, 2, ',', '.'); ?></p>

                                <!-- Botão de edição -->
                                <?php if (isset($produto['produto_aprovado']) && $produto['produto_aprovado'] == 1): ?>
                                    <a href="produtos/editar_produto.php?id_produto=<?php echo htmlspecialchars($produto['id_produto'], ENT_QUOTES, 'UTF-8'); ?>"
                                        class="button-editar">Editar</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- Mensagem de produto não encontrado -->
                <p id="mensagemNaoEncontrado" style="display: none;">Produto não encontrado.</p>

            <?php else: ?>
                <p style="margin-top: 30px;">Nenhum Produto Oculto.</p>
            <?php endif; ?>
        </div>

    </main>

    <footer class="menu-mobile">
        <ul>
            <!--<li><a href="parceiro_home.php" title="Página Inicial"><i class="fas fa-home"></i></a></li>-->
            <li><a href="perfil_loja.php" title="Perfil da Loja"><i class="fas fa-user"></i></a></li>
            <li>
                <div class="icone-pedido-wrapper">
                    <a href="vendas/pedidos.php?id_parceiro=<?php echo urlencode($id); ?>" title="Pedidos">
                        <i class="fas fa-box"> <!-- Ícone do carrinho -->
                            <!-- Contagem de produtos -->
                            <?php if ($total_pedidos > 0): ?>
                                <span id="pedido-count-rodape"
                                    class="pedido-count-rodape"><?php echo htmlspecialchars($total_pedidos); ?></span>
                            <?php else: ?>
                                <span id="pedido-count-rodape" class="pedido-count-rodape" style="display: none;"></span>
                            <?php endif; ?>
                        </i>
                    </a>
                </div>
            </li>
            <li><a href="configuracoes.php?id_parceiro=<?php echo urlencode($id); ?>" title="Configurações"><i
                        class="fas fa-cog"></i></a></li>
            <li><a href="parceiro_logout.php" title="Sair"><i class="fas fa-sign-out-alt"></i></a></li>
        </ul>
    </footer>

    <script src="parceiro_home.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const categorias = document.querySelectorAll('.categoria-item'); // Todas as categorias
            const inputCategoria = document.querySelector('input[name="categoria_selecionada"]'); // Campo hidden
            const formCategoria = document.querySelector('#formCategoria'); // Formulário
            const inputAbaAtual = document.createElement('input'); // Campo hidden para aba atual
            inputAbaAtual.type = 'hidden';
            inputAbaAtual.name = 'aba_atual';
            formCategoria.appendChild(inputAbaAtual);

            // Recupera a aba atual do localStorage ou define como 'catalogo' por padrão
            let abaAtual = localStorage.getItem('abaAtual') || 'catalogo';
            inputAbaAtual.value = abaAtual;

            // Define a aba ativa com base no localStorage
            mostrarConteudo(abaAtual, document.querySelector(`.tab[onclick="mostrarConteudo('${abaAtual}',this)"]`));

            // Configurar evento de clique para as categorias
            categorias.forEach(categoria => {
                categoria.addEventListener('click', () => {
                    categorias.forEach(cat => cat.classList.remove('selected')); // Remove a classe 'selected' de todas
                    categoria.classList.add('selected'); // Adiciona a classe 'selected' à categoria clicada
                    inputCategoria.value = categoria.querySelector('p').textContent.trim(); // Atualiza o valor no campo hidden
                    enviar(); // Envia o formulário
                });
            });

            // Atualiza o localStorage ao mudar de aba
            const abas = document.querySelectorAll('.tab');
            abas.forEach(aba => {
                aba.addEventListener('click', () => {
                    abaAtual = aba.getAttribute('onclick').match(/mostrarConteudo\('(.+?)'/)[1];
                    localStorage.setItem('abaAtual', abaAtual);
                    inputAbaAtual.value = abaAtual;
                });
            });

            function enviar() {
                // Simula o clique no botão "Enviar"
                const botaoEnviar = document.getElementById('carregar_categoria');
                botaoEnviar.click();
            }
        });

        function mostrarConteudo(aba, elemento) {
            const conteudos = document.querySelectorAll('.conteudo-aba');
            const abas = document.querySelectorAll('.tab');

            // Esconde todos os conteúdos e remove a classe 'active' de todas as abas
            conteudos.forEach(conteudo => conteudo.style.display = 'none');
            abas.forEach(tab => tab.classList.remove('active'));

            // Mostra o conteúdo da aba selecionada e adiciona a classe 'active' à aba
            document.getElementById(`conteudo-${aba}`).style.display = 'block';
            elemento.classList.add('active');
        }

        // Obtém o ID da sessão do PHP
        var sessionId = <?php echo json_encode($id); ?>;
        var id_produto = <?php echo json_encode($id_produto); ?>;

        document.addEventListener('DOMContentLoaded', () => {
            const categorias = document.querySelectorAll('.categoria-item'); // Todas as categorias
            const inputCategoria = document.querySelector('input[name="categoria_selecionada"]'); // Campo hidden
            const formCategoria = document.querySelector('#formCategoria'); // Formulário

            // Recupera a categoria selecionada do input hidden após o recarregamento da página
            const categoriaSelecionada = inputCategoria.value;

            // Se houver uma categoria previamente selecionada, destaca-a
            if (categoriaSelecionada) {
                categorias.forEach(categoria => {
                    if (categoria.querySelector('p').textContent.trim() === categoriaSelecionada) {
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
                inputCategoria.value = primeiraCategoria.querySelector('p').textContent.trim(); // Define o valor no campo hidden
            }

            // Configurar evento de clique para as categorias
            categorias.forEach(categoria => {
                categoria.addEventListener('click', () => {
                    categorias.forEach(cat => cat.classList.remove('selected')); // Remove a classe 'selected' de todas
                    categoria.classList.add('selected'); // Adiciona a classe 'selected' à categoria clicada
                    inputCategoria.value = categoria.querySelector('p').textContent.trim(); // Atualiza o valor no campo hidden
                    enviar(); // Envia o formulário
                });
            });

            const inputPesquisaCatalogo = document.getElementById('inputPesquisaCatalogo');
            if (inputPesquisaCatalogo) {
                inputPesquisaCatalogo.addEventListener('input', function () {
                    const termoPesquisa = this.value.toLowerCase();
                    const produtos = document.querySelectorAll('.produto-item');
                    let produtoEncontrado = false;

                    produtos.forEach(produto => {
                        const nomeProduto = produto.querySelector('.produto-nome').textContent.toLowerCase();
                        if (nomeProduto.includes(termoPesquisa) || termoPesquisa === '') {
                            produto.style.display = 'block';
                            produtoEncontrado = true;
                        } else {
                            produto.style.display = 'none';
                        }
                    });

                    const mensagemNaoEncontrado = document.getElementById('mensagemNaoEncontradoCatalogo');
                    if (mensagemNaoEncontrado) {
                        mensagemNaoEncontrado.style.display = produtoEncontrado ? 'none' : 'block';
                    }
                });
            }

            const inputPesquisaPromocao = document.getElementById('inputPesquisaPromocao');
            if (inputPesquisaPromocao) {
                inputPesquisaPromocao.addEventListener('input', function () {
                    const termoPesquisa = this.value.toLowerCase();
                    const produtos = document.querySelectorAll('.produto-item');
                    let produtoEncontrado = false;

                    produtos.forEach(produto => {
                        const nomeProduto = produto.querySelector('.produto-nome').textContent.toLowerCase();
                        if (nomeProduto.includes(termoPesquisa) || termoPesquisa === '') {
                            produto.style.display = 'block';
                            produtoEncontrado = true;
                        } else {
                            produto.style.display = 'none';
                        }
                    });

                    const mensagemNaoEncontrado = document.getElementById('mensagemNaoEncontrado');
                    if (mensagemNaoEncontrado) {
                        mensagemNaoEncontrado.style.display = produtoEncontrado ? 'none' : 'block';
                    }
                });
            }

            const inputPesquisaFreteGratis = document.getElementById('inputPesquisaFreteGratis');
            if (inputPesquisaFreteGratis) {
                inputPesquisaFreteGratis.addEventListener('input', function () {
                    const termoPesquisa = this.value.toLowerCase();
                    const produtos = document.querySelectorAll('.produto-item');
                    let produtoEncontrado = false;

                    produtos.forEach(produto => {
                        const nomeProduto = produto.querySelector('.produto-nome').textContent.toLowerCase();
                        if (nomeProduto.includes(termoPesquisa) || termoPesquisa === '') {
                            produto.style.display = 'block';
                            produtoEncontrado = true;
                        } else {
                            produto.style.display = 'none';
                        }
                    });

                    const mensagemNaoEncontrado = document.getElementById('mensagemNaoEncontrado');
                    if (mensagemNaoEncontrado) {
                        mensagemNaoEncontrado.style.display = produtoEncontrado ? 'none' : 'block';
                    }
                });
            }

            const inputPesquisaNovidades = document.getElementById('inputPesquisaNovidades');
            if (inputPesquisaNovidades) {
                inputPesquisaNovidades.addEventListener('input', function () {
                    const termoPesquisa = this.value.toLowerCase();
                    const produtos = document.querySelectorAll('.produto-item');
                    let produtoEncontrado = false;

                    produtos.forEach(produto => {
                        const nomeProduto = produto.querySelector('.produto-nome').textContent.toLowerCase();
                        if (nomeProduto.includes(termoPesquisa) || termoPesquisa === '') {
                            produto.style.display = 'block';
                            produtoEncontrado = true;
                        } else {
                            produto.style.display = 'none';
                        }
                    });

                    const mensagemNaoEncontrado = document.getElementById('mensagemNaoEncontrado');
                    if (mensagemNaoEncontrado) {
                        mensagemNaoEncontrado.style.display = produtoEncontrado ? 'none' : 'block';
                    }
                });
            }

            const inputPesquisaProdutosOcultos = document.getElementById('inputPesquisaProdutosOcultos');
            if (inputPesquisaProdutosOcultos) {
                inputPesquisaProdutosOcultos.addEventListener('input', function () {
                    const termoPesquisa = this.value.toLowerCase();
                    const produtos = document.querySelectorAll('.produto-item');
                    let produtoEncontrado = false;

                    produtos.forEach(produto => {
                        const nomeProduto = produto.querySelector('.produto-nome').textContent.toLowerCase();
                        if (nomeProduto.includes(termoPesquisa) || termoPesquisa === '') {
                            produto.style.display = 'block';
                            produtoEncontrado = true;
                        } else {
                            produto.style.display = 'none';
                        }
                    });

                    const mensagemNaoEncontrado = document.getElementById('mensagemNaoEncontrado');
                    if (mensagemNaoEncontrado) {
                        mensagemNaoEncontrado.style.display = produtoEncontrado ? 'none' : 'block';
                    }
                });
            }
        });

        function enviar() {
            // Simula o clique no botão "Enviar"
            const botaoEnviar = document.getElementById('carregar_categoria');
            botaoEnviar.click();
        }

        function abrirNotificacao(id) {
            let url = ""; // Inicializa a URL como uma string vazia

            // Define a URL com base no ID da notificação
            switch (id) {
                case 1:
                    url = `detalhes_notificacao_plataforma.php?id=${id}&session_id=${sessionId}&id_produto=${id_produto}`;
                    break;
                case 2:
                    url = `detalhes_notificacao_novo_prod.php?id=${id}&session_id=${sessionId}&id_produto=${id_produto}`;
                    break;
                case 3:
                    url = `detalhes_notificacao_edi_prod.php?id=${id}&session_id=${sessionId}&id_produto=${id_produto}`;
                    break;
                case 4:
                    url = `vendas/pedidos.php?id_parceiro=${sessionId}`;
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
            fetch(`get_notifications.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    //console.log("Dados recebidos:", data); // Debug no console

                    // Atualiza a contagem no ícone 🔔
                    const notificationCount = document.getElementById('notificacao-count');
                    if (notificationCount) {
                        if (data.total_notificacoes > 0) {
                            notificationCount.innerText = data.total_notificacoes;
                            notificationCount.style.display = 'inline-block';
                        } else {
                            notificationCount.style.display = 'none';
                        }
                    }

                    // Atualiza a contagem no h2 📋
                    const notificationHeaderCount = document.getElementById('notificacao-header-count');
                    if (notificationHeaderCount) {
                        notificationHeaderCount.innerText = data.total_notificacoes;
                    }

                    // Atualiza os valores das notificações mantendo as li sempre visíveis
                    const notificacoes = {
                        1: "Plataforma",
                        2: "Novo Produto",
                        3: "Edição de Produtos",
                        4: "Pedidos"
                    };

                    Object.keys(notificacoes).forEach(id => {
                        const li = document.querySelector(`#lista-notificacoes li[data-id="${id}"]`);
                        if (li) {
                            const count = data.notificacoes.find(n => n.id == id)?.mensagem.match(/\d+/)?.[0] || 0;
                            li.innerText = `${notificacoes[id]}: ${count}`;
                        }
                    });

                })
                .catch(error => console.error('Erro ao buscar notificações:', error));
        }

        // Executa a busca inicial e define o intervalo para atualizar a cada 3 segundos
        fetchNotifications(sessionId);
        setInterval(() => fetchNotifications(sessionId), 3000);

    </script>

</body>

</html>