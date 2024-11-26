<?php
include('login/lib/conexao.php');

if(!isset($_SESSION)) {
    session_start();
}

// Verifica se o usuário está logado
$usuarioLogado = isset($_SESSION['id']);
//$id_conf = '1';


$dados = $mysqli->query("SELECT * FROM config_admin WHERE logo != '' ORDER BY data_alteracao DESC LIMIT 1") or die($mysqli->error);

$dadosEscolhido = $dados->fetch_assoc();


$nomeFantasia = $dadosEscolhido['nomeFantasia'];

// Carrega a logo
if (isset($dadosEscolhido['logo'])) {
    $logo = $dadosEscolhido['logo'];
    if ($logo == '') {
        $logo = 'login/lib/paginas/arquivos_fixos/imagem_credgas.jpg';
    } else {
        $logo = 'login/lib/paginas/administrativo/arquivos/' . $logo;
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $nomeFantasia;?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>

    <!--<script src="cadastro_inicial/localizador.js" defer></script>-->
    <!--<link rel="stylesheet" href="index.css">-->

    <style>
        header .container {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 10px 20px;
                position: relative;
        }

        .logo {
            display: flex;
            align-items: center;
        }

        .logo-img {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .nome-fantasia {
            font-size: 2.5rem; /* Tamanho maior */
            font-weight: bold;
            color: #333; /* Cor mais suave para o texto */
            line-height: 1.2;
            display: flex;
            justify-content: flex-start; /* Garante que o texto fique alinhado à esquerda */
            align-items: center;
            flex-grow: 1; /* Permite que o nome ocupe o máximo de espaço disponível ao lado da logo */
            padding-left: 15px; /* Espaço entre a logo e o nome */
            text-align: center; /* Centralizar o texto horizontalmente */
            margin: 20px 0; /* Espaçamento acima e abaixo */
            text-transform: uppercase; /* Transformar o texto para letras maiúsculas */
            letter-spacing: 1.5px; /* Espaçamento entre as letras */

        }
        .user-area {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            align-items: center;
            gap: 10px;   
            padding-right: 30px;
        }

        .btn-login {
            background-color: #007bff;
            color: white;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
        }

        .btn-login:hover {
            background-color: #0056b3;
        }
        .profile-dropdown {
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
        }
        /* Faixa de Navegação */
        .sub-nav {
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: #f8f8f8; /* Cor de fundo suave */
        padding: 10px 0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Sombras sutis para destacar */
        }

        .sub-nav div {
        font-size: 1.2rem;
        font-weight: bold;
        color: #333; /* Cor do texto */
        margin: 0 20px; /* Espaçamento entre os itens */
        cursor: pointer;
        transition: all 0.3s ease; /* Suavização do efeito de hover */
        }

        .sub-nav div:hover {
        color: #007bff; /* Cor de destaque quando o item é hover */
        text-decoration: underline; /* Adiciona um sublinhado no hover */
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
            align-items: center; /* Centraliza horizontalmente */
            justify-content: center; /* Centraliza verticalmente */
            text-align: center;
        }

        /* Estilos para as abas */
        main .opcoes {
            background-color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 0px;
            padding: auto;
        }
        main .tab {
            padding: 10px;
            border-radius: 8px 8px 0 0; /* Bordas arredondadas só no topo, estilo de aba */
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
            background-color: #ffb300; /* Aba ativa com cor diferente */
            color: white;
            transform: scale(1.05);
        }
            /* Estilos para o conteúdo das abas */
        .conteudo-aba {
            flex-grow: 1; /* Faz o conteúdo ocupar todo o espaço restante */
            margin-left: 2px;
            margin-right: 2px;
            margin-top: 0px;
            padding: 10px;
            border: 2px solid #ffb300;
            border-radius: 8px;
            display: none; /* Por padrão, todos os conteúdos estão escondidos */
            padding-top: 5px;
            box-sizing: border-box; /* Garante que o padding seja incluído no tamanho */
            /*overflow: auto; /* Para que o conteúdo role se for maior que a tela */
            background-color: #d3d0ce;
            width: 100%;
            text-align: center; /* Centraliza o texto */
            display: flex; /* Define um layout flexível */
            flex-direction: column; /* Coloca os elementos verticalmente */
            align-items: center; /* Centraliza horizontalmente os itens */
            justify-content: center; /* Centraliza verticalmente os itens */
            height: auto;
            /*min-height: 200px; /* Define uma altura mínima para centralização adequada */
            /*padding: 20px; /* Adiciona espaçamento interno */
        /* padding-bottom: 50px; /* Ajuste conforme o tamanho do seu menu */
        }
        .container{
            display: flex;
            /*flex-direction: column;*/
            align-items: center; /* Centraliza horizontalmente */
            justify-content: center; /* Centraliza verticalmente */
            /*left: 50vh;
            height: 40vh; /* Altura total da tela */
            text-align: center;
            /*width: 95%;
            /*padding: 10px;
            margin-left: 10px;*/
        } 
        .parceiros-carousel {
            width: 100%; /* Ocupar toda a largura */
            margin: 0 auto; /* Centralizar o carrossel */
            display: flex; /* Flexbox para alinhar elementos */
            justify-content: center; /* Centraliza o conteúdo dentro */
        }
        .parceiros-carousel .parceiro-card {
            text-align: center;
            padding: 10px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 60px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin: 10px auto; /* Centraliza e ajusta margens vertical e horizontal */
            max-width: 200px; /* Define o comprimento máximo do cartão */
        }
        .input{
            width: 250px;
            padding: 3px;
            padding-left: 5px;
            border-radius: 5px;
            height: 20px;
            border: 1px solid #ffb300;
        }

        .parceiros-carousel .parceiro-card img {
            max-width: 120px; /* Ajuste o tamanho da logo */
            height: 120px;   /* Para mantê-la circular */
            margin: auto; /* Centraliza horizontalmente e adiciona espaço abaixo */
            border-radius: 50%; /* Torna a imagem redonda */
            display: block; /* Garante que o elemento seja tratado como bloco */
            border: 2px solid #ddd; /* Borda ao redor da imagem */
        }
        .parceiros-carousel .parceiro-card h3 {
            font-size: 1.2em;
            font-weight: bold;
            margin: 5px 0;
            color: #333; /* Cor do texto */
        }

        .parceiros-carousel .parceiro-card p {
            font-size: 0.9em;
            color: #666; /* Cor da categoria */
            margin: 5px 0 0;
        }

        /* Contêiner da seção de produtos */
        .products {
            display: flex;
            flex-wrap: wrap;
            gap: 10px; /* Espaçamento entre os cartões */
            justify-content: center; /* Centraliza os produtos */
            margin: 10px 0;
        
        }
        /* Cartão do produto */
        .product-card {
            background: #ffffff;
            border: 1px solid #ddd;
            border-radius: 10px;
            width: 200px; /* Largura do cartão */
            height: 420px; /* Define a altura fixa */
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
            margin-bottom: 5px;
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
            color: #666;
            margin-top: 5px;
            margin-bottom: 5px;
            line-height: 1.4;
        }

        /* Preço do produto */
        .product-card p:last-child {
            font-size: 1em;
            color: #27ae60; /* Verde para o preço */
            font-weight: bold;
        }
        /* Botões */
        .product-card .btn {
            display: inline-block;
            background: #27ae60; /* Cor do botão */
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 10px;
            transition: background-color 0.3s ease;
            font-size: 0.9em;
        }

        /* Efeito ao passar o mouse no botão */
        .product-card .btn:hover {
            background:darkorange;
        }
        .descricao {
            display: -webkit-box;
            -webkit-line-clamp: 2; /* Limita a 2 linhas */
            -webkit-box-orient: vertical;
            overflow: hidden; /* Oculta o texto excedente */
            text-overflow: ellipsis; /* Adiciona "..." ao final do texto cortado */
            max-width: 100%; /* Define uma largura máxima para o texto */
        }
        .conteudo-aba h2 {
            border-radius: 3px;
            background-color: #fff;
            text-align: left; /* Alinha o texto à esquerda */
            /*margin-left: 0;   /* Garante que não há margem que afaste do lado esquerdo */
            padding-left: 5px;  /* Garante que não há espaçamento interno */
        }
        /* Efeito hover */
        .nome-fantasia:hover {
            color: #007BFF; /* Muda a cor ao passar o mouse */
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2); /* Adiciona uma leve sombra no texto */
        }
        @media (max-width: 768px) {
            /*.sub-nav {
                flex-direction: column; /* Coloca os itens em coluna em telas menores */
                /*align-items: flex-start; /* Alinha os itens à esquerda */
                /*padding: 15px; /* Aumenta o padding em telas menores */
            /*}*/

            .sub-nav div {
                margin: 10px 0; /* Reduz o espaçamento entre os itens em telas menores */
                text-align: left; /* Alinha os itens à esquerda */
            }

            .nome-fantasia {
                font-size: 1.8rem; /* Tamanho reduzido para o nome fantasia */
                font-weight: bold;
                color: #333; /* Cor mais suave para o texto */
                text-align: left; /* Alinha à esquerda para ficar mais natural ao lado da logo */
                margin: 0;
                line-height: 1.2;
                display: flex;
                justify-content: flex-start; /* Garante que o texto fique alinhado à esquerda */
                align-items: center;
                flex-grow: 1; /* Permite que o nome ocupe o máximo de espaço disponível ao lado da logo */
                padding-left: 15px; /* Espaço entre a logo e o nome */
                margin: 15px 0; /* Ajusta o espaçamento para telas pequenas */
    
            }
                    /* Cartão do produto */
            .product-card {
                background: #ffffff;
                border: 1px solid #ddd;
                border-radius: 10px;
                width: 180px; /* Largura do cartão */
                height: 400px; /* Define a altura fixa */
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                overflow: hidden;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                text-align: center;
                padding: 3px;
            }
        }
        @media (max-width: 480px) {
            .nome-fantasia {
                font-size: 1.2rem; /* Ainda menor para dispositivos móveis */
                letter-spacing: 1px; /* Reduz o espaçamento entre as letras */
            }
            .logo-img {
                width: 130px;
                height: 130px;
                border-radius: 50%;
                margin-right: 10px;
            }
            
        }
        /* Footer */
        footer {
            text-align: center;
            padding: 20px 0;
            background-color: #333;
            color: white;
            margin-top: 20px;
            border-radius: 10px;
        }

        footer .contato {
            margin: 0;
        }

    </style>

</head>
<body>

    <!-- Header -->
    <header>
        <div class="container">
            <div class="logo">
                <img src="<?php if(isset($logo)) echo $logo; ?>" alt="Logo" class="logo-img">
                <h1 class="nome-fantasia">
                    <?php 
                    if (!empty($nomeFantasia)) {
                        echo htmlspecialchars($nomeFantasia);
                    } else {
                        echo "Nome Fantasia Indisponível";
                    }
                    ?>
                </h1>
            </div>
            <div class="user-area">
                <?php if ($usuarioLogado): ?>
                    <span>Bem-vindo, <strong><?php echo htmlspecialchars($_SESSION['nome_completo']); ?></strong></span>
                    <i class="fas fa-bell"></i>
                    <i class="fas fa-shopping-cart"></i>
                <?php else: ?>
                    <span>Seja bem-vindo!</span>
                    <a href="login/lib/login.php" class="btn-login">Entrar</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Conteúdo principal -->
    <main id="main-content">
        <!-- Conteúdo -->
        <div class="opcoes">
            <!-- Conteúdo -->
            <div class="tab active" onclick="mostrarConteudo('catalogo',this)">
                <span>Catálogo</span>
            </div>

            <div class="tab" onclick="mostrarConteudo('promocoes',this)">
                <span>Promoções</span>
            </div>

            <div class="tab" onclick="mostrarConteudo('frete_gratis',this)">
                <span>Frete Grátis</span>
            </div>

            <div class="tab" onclick="mostrarConteudo('novidades',this)">
                <span>Novidades</span>
            </div>

        </div>

        <!-- Conteúdos correspondentes às abas -->
        <div id="conteudo-catalogo" class="conteudo-aba" style="display: none;">

            <h2>Nossos Parceiros</h2>
            <?php

                // Consulta para buscar parceiros pelo CEP
                $sql_parceiros = "SELECT * FROM meus_parceiros WHERE status = 'ATIVO' && aberto_fechado = 'Aberto'";
                $result_parceiros = $mysqli->query($sql_parceiros) or die($mysqli->error);

                if ($result_parceiros->num_rows > 0) {
                    while ($parceiro = $result_parceiros->fetch_assoc()) {
                        $id_parceiro = $parceiro['id'];
                        
                        // Consulta para carregar produtos do parceiro
                        $sql_produtos = "SELECT * FROM produtos WHERE id_parceiro = $id_parceiro AND oculto != 'sim' AND produto_aprovado = 'sim'";
                        $result_produtos = $mysqli->query($sql_produtos) or die($mysqli->error);
                    }
                } else {
                    echo "<p>Nenhum parceiro encontrado.</p>";
                }
                
            ?>
            <!-- Pesquisa de Parceiros -->
            <input id="inputPesquisaParceiroCatalogo" class="input" type="text" placeholder="Pesquisar Parceiro.">

            <!-- Carrossel de Parceiros -->
            <div class="parceiros-carousel owl-carousel">
                <?php 
                    //echo ('oii');
                    // Consulta para buscar parceiros ativos e abertos
                    $sql_parceiros = "SELECT * FROM meus_parceiros WHERE status = 'ATIVO' AND aberto_fechado = 'Aberto'";
                    $result_parceiros = $mysqli->query($sql_parceiros) or die($mysqli->error);

                    if ($result_parceiros->num_rows > 0): 
                        while ($parceiro = $result_parceiros->fetch_assoc()): 
                            // Exibe cada parceiro no carrossel
                            $logoParceiro = !empty($parceiro['logo']) ? $parceiro['logo'] : 'placeholder.jpg'; 
                ?>
                <div class="parceiro-card">
                    <img src="login/lib/paginas/parceiros/arquivos/<?php echo htmlspecialchars($logoParceiro); ?>" 
                    alt="<?php echo htmlspecialchars($parceiro['nomeFantasia']); ?>">
                    <h3><?php echo htmlspecialchars($parceiro['nomeFantasia']); ?></h3>
                    <p><?php echo htmlspecialchars($parceiro['categoria']); ?></p>
                </div>
                <?php endwhile; ?>
                <?php else: ?>
                <p>Nenhum parceiro ativo no momento.</p>
                <?php endif; ?>
            </div>

            <!-- Mensagem de Parceiro Não Encontrado -->
            <p id="mensagemParNaoEncontradoCatalogo" style="display: none;">Parceiro não encontrado.</p>          

            <!-- Produtos -->
            <h2>Produtos</h2>

            <div class="container">
                <!-- Pesquisa de Produtos -->
                <input id="inputPesquisaCatalogo" class="input" type="text" placeholder="Pesquisar Produto."></div>

                <div class="products">
                    <?php if (isset($result_produtos) && $result_produtos->num_rows > 0): ?>
                    <?php while ($produto = $result_produtos->fetch_assoc()): ?>
                    <div class="product-card">
                        <?php
                            // Supondo que a coluna 'imagens' contém os nomes das imagens separados por vírgulas
                            $imagens = !empty($produto['imagens']) ? explode(',', $produto['imagens']) : [];
                            $primeira_imagem = $imagens[0] ?? 'placeholder.jpg'; // Usa uma imagem padrão se não houver imagens
                        ?>

                        <img src="login/lib/paginas/parceiros/produtos/img_produtos/<?php echo htmlspecialchars($primeira_imagem); ?>" alt="<?php echo htmlspecialchars($produto['nome_produto']); ?>">
                        <?php 
                            // Exibe o ícone de frete grátis, se o produto tiver frete grátis
                            if ($produto['frete_gratis'] = 'sim' || ($produto['promocao'] = 'sim' && $produto['frete_gratis_promocao'] = 'sim')): 
                        ?>
                        <span class="icone-frete-gratis" title="Frete grátis">🚚</span>
                        <?php 
                            endif;

                            // Exibe o ícone de promoção, se o produto estiver em promoção
                            if ($produto['promocao'] === 'sim'): 
                        ?>
                            <span class="icone-promocao" title="Produto em promoção">🔥</span>
                        <?php 
                            endif; 
                        ?>                          
                        <h3><?php echo htmlspecialchars($produto['nome_produto']); ?></h3>
                        <p class="descricao">
                            <?php
                            $descricao = htmlspecialchars($produto['descricao_produto'] ?? '');
                            echo mb_strimwidth($descricao, 0, 100, '...'); // Limita a 100 caracteres com "..."
                            ?>
                        </p>
                        <p>R$ <?php echo number_format($produto['valor_produto'], 2, ',', '.'); ?></p>
                        <a href="login/lib/detalhes_produto.php?id_produto=<?php echo $produto['id_produto']; ?>" class="btn">Detalhes</a>

                        <!-- Verifica se o usuário está logado para permitir a compra -->
                        <?php if (isset($usuarioLogado) && $usuarioLogado): ?>
                            <a href="#" class="btn">Comprar</a>
                        <?php else: ?>
                            <a href="login/lib/login.php" class="btn">Faça login para comprar</a>
                        <?php endif; ?>
                    </div>
                    <?php endwhile; ?>
                    <?php else: ?>
                        <p>Não há produtos no momento.</p>
                    <?php endif; ?>
                    <!-- Mensagem de produto não encontrado -->
                    <p id="mensagemNaoEncontradoCatalogo" style="display: none;">Produto não encontrado.</p>
                </div>
            </div>
        </div>

        <!-- Conteúdos correspondentes às abas -->
        <div id="conteudo-promocoes" class="conteudo-aba" style="display: none;">
            <h2>Nossos Parceiros</h2>

            <!-- Pesquisa de Parceiros -->
            <input id="inputPesquisaParceiroPromocao" class="input" type="text" placeholder="Pesquisar Parceiro.">

            <!-- Carrossel de Parceiros -->
            <div class="parceiros-carousel owl-carousel">
                <?php 
                    // Consulta para buscar parceiros que têm produtos em promoção, visíveis e aprovados
                    $sql_parceiros = "
                        SELECT DISTINCT mp.* 
                        FROM meus_parceiros mp
                        JOIN produtos p ON mp.id = p.id_parceiro
                        WHERE 
                            mp.status = 'ATIVO' 
                            AND mp.aberto_fechado = 'Aberto'
                            AND p.promocao = 'sim' 
                            AND p.oculto != 'sim' 
                            AND p.produto_aprovado = 'sim'
                    ";
                
                    $result_parceiros = $mysqli->query($sql_parceiros) or die($mysqli->error);

                    if ($result_parceiros->num_rows > 0): 
                        while ($parceiro = $result_parceiros->fetch_assoc()): 
                            // Exibe cada parceiro no carrossel
                            $logoParceiro = !empty($parceiro['logo']) ? $parceiro['logo'] : 'placeholder.jpg'; 
                            $id_parceiro = $parceiro['id'];
                            
                            // Consulta para carregar produtos do parceiro
                            $sql_produtos = "SELECT * FROM produtos WHERE id_parceiro = $id_parceiro AND oculto != 'sim' AND produto_aprovado = 'sim'
                            AND promocao = 'sim'";
                            $result_produtos = $mysqli->query($sql_produtos) or die($mysqli->error);
                ?>
                <div class="parceiro-card">
                    <img src="login/lib/paginas/parceiros/arquivos/<?php echo htmlspecialchars($logoParceiro); ?>" 
                    alt="<?php echo htmlspecialchars($parceiro['nomeFantasia']); ?>">
                    <h3><?php echo htmlspecialchars($parceiro['nomeFantasia']); ?></h3>
                    <p><?php echo htmlspecialchars($parceiro['categoria']); ?></p>
                </div>
                <?php endwhile; ?>
                <?php else: ?>
                <p>Nenhum parceiro ativo no momento.</p>
                <?php endif; ?>
            </div>

            <!-- Mensagem de Parceiro Não Encontrado -->
            <p id="mensagemParNaoEncontradoPromocao" style="display: none;">Parceiro não encontrado.</p> 

            <!-- Produtos -->
            <h2>Produtos</h2>
            <div class="container">

                <!-- Pesquisa de Produtos -->
                <input id="inputPesquisaPromocao" class="input" type="text" placeholder="Pesquisar Produto."></div>

                <div class="products">
                    <?php if (isset($result_produtos) && $result_produtos->num_rows > 0): ?>
                        <?php while ($produto = $result_produtos->fetch_assoc()): ?>
                            <div class="product-card">
                                <?php
                                    // Supondo que a coluna 'imagens' contém os nomes das imagens separados por vírgulas
                                    $imagens = !empty($produto['imagens']) ? explode(',', $produto['imagens']) : [];
                                    $primeira_imagem = $imagens[0] ?? 'placeholder.jpg'; // Usa uma imagem padrão se não houver imagens
                                ?>

                                <img src="login/lib/paginas/parceiros/produtos/img_produtos/<?php echo htmlspecialchars($primeira_imagem); ?>" alt="<?php echo htmlspecialchars($produto['nome_produto']); ?>">
                                <?php 
                                    // Exibe o ícone de frete grátis, se o produto tiver frete grátis
                                    if ($produto['frete_gratis'] = 'sim' || ($produto['promocao'] = 'sim' && $produto['frete_gratis_promocao'] = 'sim')): 
                                ?>
                                    <span class="icone-frete-gratis" title="Frete grátis">🚚</span>
                                <?php 
                                    endif;

                                    // Exibe o ícone de promoção, se o produto estiver em promoção
                                    if ($produto['promocao'] === 'sim'): 
                                ?>
                                    <span class="icone-promocao" title="Produto em promoção">🔥</span>
                                <?php 
                                    endif; 
                                ?>                        
                                
                                <h3><?php echo htmlspecialchars($produto['nome_produto']); ?></h3>
                                <p class="descricao">
                                    <?php
                                    $descricao = htmlspecialchars($produto['descricao_produto'] ?? '');
                                    echo mb_strimwidth($descricao, 0, 100, '...'); // Limita a 100 caracteres com "..."
                                    ?>
                                </p>
                                <p>R$ <?php echo number_format($produto['valor_produto'], 2, ',', '.'); ?></p>
                                <a href="login/lib/detalhes_produto.php?id_produto=<?php echo $produto['id_produto']; ?>" class="btn">Detalhes</a>

                                <!-- Verifica se o usuário está logado para permitir a compra -->
                                <?php if (isset($usuarioLogado) && $usuarioLogado): ?>
                                    <a href="#" class="btn">Comprar</a>
                                <?php else: ?>
                                    <a href="login/lib/login.php" class="btn">Faça login para comprar</a>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>Não há produtos no momento.</p>
                    <?php endif; ?>
                    <!-- Mensagem de produto não encontrado -->
                    <p id="mensagemNaoEncontradoPromocao" style="display: none;">Produto não encontrado.</p>
                </div>
            </div>
        </div>

        <!-- Conteúdos correspondentes às abas -->
        <div id="conteudo-frete_gratis" class="conteudo-aba" style="display: none;">
            <h2>Nossos Parceiros</h2>

            <!-- Pesquisa de Parceiros -->
            <input id="inputPesquisaParceiroFrete_gratis" class="input" type="text" placeholder="Pesquisar Parceiro.">

            <!-- Carrossel de Parceiros -->
            <div class="parceiros-carousel owl-carousel">

                <?php 
                // Consulta para buscar parceiros que têm produtos em promoção, visíveis e aprovados
                $sql_parceiros = "
                    SELECT DISTINCT mp.* 
                    FROM meus_parceiros mp
                    JOIN produtos p ON mp.id = p.id_parceiro
                    WHERE 
                        mp.status = 'ATIVO' 
                        AND mp.aberto_fechado = 'Aberto'
                        AND p.promocao = 'sim' 
                        AND p.oculto != 'sim' 
                        AND p.produto_aprovado = 'sim'
                ";
    
                $result_parceiros = $mysqli->query($sql_parceiros) or die($mysqli->error);

                if ($result_parceiros->num_rows > 0): 
                    while ($parceiro = $result_parceiros->fetch_assoc()): 
                        // Exibe cada parceiro no carrossel
                        $logoParceiro = !empty($parceiro['logo']) ? $parceiro['logo'] : 'placeholder.jpg'; 
                        $id_parceiro = $parceiro['id'];
                        
                        // Consulta para carregar produtos do parceiro
                        $sql_produtos = "SELECT * FROM produtos WHERE id_parceiro = $id_parceiro AND oculto != 'sim' AND produto_aprovado = 'sim'
                        AND frete_gratis = 'sim' || frete_gratis_promocao = 'sim'";
                        $result_produtos = $mysqli->query($sql_produtos) or die($mysqli->error);
                ?>
                    <div class="parceiro-card">
                        <img src="login/lib/paginas/parceiros/arquivos/<?php echo htmlspecialchars($logoParceiro); ?>" 
                        alt="<?php echo htmlspecialchars($parceiro['nomeFantasia']); ?>">
                        <h3><?php echo htmlspecialchars($parceiro['nomeFantasia']); ?></h3>
                        <p><?php echo htmlspecialchars($parceiro['categoria']); ?></p>
                    </div>
                <?php endwhile; ?>
                <?php else: ?>
                    <p>Nenhum parceiro ativo no momento.</p>
                <?php endif; ?>

            </div>

            <!-- Mensagem de Parceiro Não Encontrado -->
            <p id="mensagemParNaoEncontradoFrete_gratis" style="display: none;">Parceiro não encontrado.</p> 

            <!-- Produtos -->
            <h2>Produtos</h2>
            <div class="container">
                <!-- Pesquisa de Produtos -->
                <input id="inputPesquisaFrete_gratis" class="input" type="text" placeholder="Pesquisar Produto."></div>
                <div class="products">
                    <?php if (isset($result_produtos) && $result_produtos->num_rows > 0): ?>
                        <?php while ($produto = $result_produtos->fetch_assoc()): ?>
                            <div class="product-card">
                                <?php
                                    // Supondo que a coluna 'imagens' contém os nomes das imagens separados por vírgulas
                                    $imagens = !empty($produto['imagens']) ? explode(',', $produto['imagens']) : [];
                                    $primeira_imagem = $imagens[0] ?? 'placeholder.jpg'; // Usa uma imagem padrão se não houver imagens
                                ?>

                                <img src="login/lib/paginas/parceiros/produtos/img_produtos/<?php echo htmlspecialchars($primeira_imagem); ?>" alt="<?php echo htmlspecialchars($produto['nome_produto']); ?>">
                                <?php 
                                    // Exibe o ícone de frete grátis, se o produto tiver frete grátis
                                    if ($produto['frete_gratis'] = 'sim' || ($produto['promocao'] = 'sim' && $produto['frete_gratis_promocao'] = 'sim')): 
                                ?>
                                    <span class="icone-frete-gratis" title="Frete grátis">🚚</span>
                                <?php 
                                    endif;

                                    // Exibe o ícone de promoção, se o produto estiver em promoção
                                    if ($produto['promocao'] === 'sim'): 
                                ?>
                                    <span class="icone-promocao" title="Produto em promoção">🔥</span>
                                <?php 
                                    endif; 
                                ?>                        
                                
                                <h3><?php echo htmlspecialchars($produto['nome_produto']); ?></h3>
                                <p class="descricao">
                                    <?php
                                    $descricao = htmlspecialchars($produto['descricao_produto'] ?? '');
                                    echo mb_strimwidth($descricao, 0, 100, '...'); // Limita a 100 caracteres com "..."
                                    ?>
                                </p>
                                <p>R$ <?php echo number_format($produto['valor_produto'], 2, ',', '.'); ?></p>
                                <a href="login/lib/detalhes_produto.php?id_produto=<?php echo $produto['id_produto']; ?>" class="btn">Detalhes</a>

                                <!-- Verifica se o usuário está logado para permitir a compra -->
                                <?php if (isset($usuarioLogado) && $usuarioLogado): ?>
                                    <a href="#" class="btn">Comprar</a>
                                <?php else: ?>
                                    <a href="login/lib/login.php" class="btn">Faça login para comprar</a>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>Não há produtos no momento.</p>
                    <?php endif; ?>
                    <!-- Mensagem de produto não encontrado -->
                    <p id="mensagemNaoEncontradoFrete_gratis" style="display: none;">Produto não encontrado.</p>
                </div>
            </div>
        </div>

        <!-- Conteúdos correspondentes às abas -->
        <div id="conteudo-novidades" class="conteudo-aba" style="display: none;">
            <h2>Nossos Parceiros</h2>

            <!-- Pesquisa de Parceiros -->
            <input id="inputPesquisaParceiroNovidades" class="input" type="text" placeholder="Pesquisar Parceiro.">

            <!-- Carrossel de Parceiros -->
            <div class="parceiros-carousel owl-carousel">

                <?php 
                // Consulta para buscar parceiros que têm produtos em promoção, visíveis e aprovados
                $sql_parceiros = "
                    SELECT DISTINCT mp.* 
                    FROM meus_parceiros mp
                    JOIN produtos p ON mp.id = p.id_parceiro
                    WHERE 
                        mp.status = 'ATIVO' 
                        AND mp.aberto_fechado = 'Aberto'
                        AND p.promocao = 'sim' 
                        AND p.oculto != 'sim' 
                        AND p.produto_aprovado = 'sim'
                ";

                $result_parceiros = $mysqli->query($sql_parceiros) or die($mysqli->error);

                if ($result_parceiros->num_rows > 0): 
                    while ($parceiro = $result_parceiros->fetch_assoc()): 
                        // Exibe cada parceiro no carrossel
                        $logoParceiro = !empty($parceiro['logo']) ? $parceiro['logo'] : 'placeholder.jpg'; 
                        $id_parceiro = $parceiro['id'];
                        
                        $sql_produtos = "
                        SELECT * 
                        FROM produtos 
                        WHERE id_parceiro = $id_parceiro 
                        AND oculto != 'sim' 
                        AND produto_aprovado = 'sim'
                        AND (frete_gratis = 'sim' OR frete_gratis_promocao = 'sim')
                        AND DATEDIFF(NOW(), data) <= 30
                    ";
                    
                    $result_produtos = $mysqli->query($sql_produtos) or die($mysqli->error);
                ?>
                <div class="parceiro-card">
                    <img src="login/lib/paginas/parceiros/arquivos/<?php echo htmlspecialchars($logoParceiro); ?>" 
                    alt="<?php echo htmlspecialchars($parceiro['nomeFantasia']); ?>">
                    <h3><?php echo htmlspecialchars($parceiro['nomeFantasia']); ?></h3>
                    <p><?php echo htmlspecialchars($parceiro['categoria']); ?></p>
                </div>
                <?php endwhile; ?>
                <?php else: ?>
                    <p>Nenhum parceiro ativo no momento.</p>
                <?php endif; ?>
            </div>

            <!-- Mensagem de Parceiro Não Encontrado -->
            <p id="mensagemParNaoEncontradoNovidades" style="display: none;">Parceiro não encontrado.</p> 

            <!-- Produtos -->
            <h2>Produtos</h2>
            <div class="container">
                <!-- Pesquisa de Produtos -->
                <input id="inputPesquisaNovidades" class="input" type="text" placeholder="Pesquisar Produto."></div>
                <div class="products">
                    <?php if (isset($result_produtos) && $result_produtos->num_rows > 0): ?>
                        <?php while ($produto = $result_produtos->fetch_assoc()): ?>
                            <div class="product-card">
                                <?php
                                    // Supondo que a coluna 'imagens' contém os nomes das imagens separados por vírgulas
                                    $imagens = !empty($produto['imagens']) ? explode(',', $produto['imagens']) : [];
                                    $primeira_imagem = $imagens[0] ?? 'placeholder.jpg'; // Usa uma imagem padrão se não houver imagens
                                ?>

                                <img src="login/lib/paginas/parceiros/produtos/img_produtos/<?php echo htmlspecialchars($primeira_imagem); ?>" alt="<?php echo htmlspecialchars($produto['nome_produto']); ?>">
                                <?php 
                                    // Exibe o ícone de frete grátis, se o produto tiver frete grátis
                                    if ($produto['frete_gratis'] = 'sim' || ($produto['promocao'] = 'sim' && $produto['frete_gratis_promocao'] = 'sim')): 
                                ?>
                                    <span class="icone-frete-gratis" title="Frete grátis">🚚</span>
                                <?php 
                                    endif;

                                    // Exibe o ícone de promoção, se o produto estiver em promoção
                                    if ($produto['promocao'] === 'sim'): 
                                ?>
                                    <span class="icone-promocao" title="Produto em promoção">🔥</span>
                                <?php 
                                    endif; 
                                ?>                        
                                
                                <h3><?php echo htmlspecialchars($produto['nome_produto']); ?></h3>
                                <p class="descricao">
                                    <?php
                                    $descricao = htmlspecialchars($produto['descricao_produto'] ?? '');
                                    echo mb_strimwidth($descricao, 0, 100, '...'); // Limita a 100 caracteres com "..."
                                    ?>
                                </p>
                                <p>R$ <?php echo number_format($produto['valor_produto'], 2, ',', '.'); ?></p>
                                <a href="login/lib/detalhes_produto.php?id_produto=<?php echo $produto['id_produto']; ?>" class="btn">Detalhes</a>

                                <!-- Verifica se o usuário está logado para permitir a compra -->
                                <?php if (isset($usuarioLogado) && $usuarioLogado): ?>
                                    <a href="#" class="btn">Comprar</a>
                                <?php else: ?>
                                    <a href="login/lib/login.php" class="btn">Faça login para comprar</a>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>Não há produtos no momento.</p>
                    <?php endif; ?>
                    <!-- Mensagem de produto não encontrado -->
                    <p id="mensagemNaoEncontradoNovidades" style="display: none;">Produto não encontrado.</p>
                </div>
            </div>
        </div>
    </main>

    <script>

        $(document).ready(function() {
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
            //console.log('eee');
            var conteudos = document.querySelectorAll('.conteudo-aba');
            conteudos.forEach(function(conteudo) {
                conteudo.style.display = 'none';
            });

            // Remove a classe 'active' de todas as abas
            var tabs = document.querySelectorAll('.tab');
            tabs.forEach(function(tab) {
                tab.classList.remove('active');
            });

            // Mostra o conteúdo da aba clicada
            document.getElementById('conteudo-'+ aba).style.display = 'block';

            // Adiciona a classe 'active' à aba clicada
            element.classList.add('active');
            //console.log('eee');

        }

        // Define que a aba "catalogo" está ativa ao carregar a página
        window.onload = function() {
            mostrarConteudo('catalogo', document.querySelector('.tab.active'));
        };

        document.getElementById('inputPesquisaParceiroCatalogo').addEventListener('input', function () {
            const termoPesquisa = this.value.toLowerCase();
            const parceiros = document.querySelectorAll('.parceiros-carousel .parceiro-card');
            let parceiroEncontrado = false;

            // Itera sobre os parceiros
            parceiros.forEach(parceiro => {
                const nomeParceiro = parceiro.querySelector('h3').textContent.toLowerCase();
                
                // Verifica se o termo de pesquisa corresponde ao nome do parceiro
                if (nomeParceiro.includes(termoPesquisa) || termoPesquisa === '') {
                    parceiro.style.display = 'block'; // Mostra o parceiro
                    parceiroEncontrado = true;
                } else {
                    parceiro.style.display = 'none'; // Esconde o parceiro
                }
            });

            // Exibe ou oculta a mensagem de "Parceiro não encontrado"
            const mensagemNaoEncontrado = document.getElementById('mensagemParNaoEncontradoCatalogo');
            mensagemNaoEncontrado.style.display = parceiroEncontrado ? 'none' : 'block';
        });

        document.getElementById('inputPesquisaCatalogo').addEventListener('input', function () {
            const termoPesquisa = this.value.toLowerCase();
            const produtos = document.querySelectorAll('.products .product-card');
            let produtoEncontrado = false;

            produtos.forEach(produto => {
                const nomeProduto = produto.querySelector('h3').textContent.toLowerCase();

                if (nomeProduto.includes(termoPesquisa) || termoPesquisa === '') {
                    produto.style.display = 'block';
                    produtoEncontrado = true;
                } else {
                    produto.style.display = 'none';
                }
            });

            // Exibe mensagem de "Produto não encontrado" se nenhum produto for exibido
            const mensagemNaoEncontrado = document.getElementById('mensagemNaoEncontradoCatalogo');
            mensagemNaoEncontrado.style.display = produtoEncontrado ? 'none' : 'block';
        });

        document.getElementById('inputPesquisaParceiroPromocao').addEventListener('input', function () {
            const termoPesquisa = this.value.toLowerCase();
            const parceiros = document.querySelectorAll('.parceiros-carousel .parceiro-card');
            let parceiroEncontrado = false;

            // Itera sobre os parceiros
            parceiros.forEach(parceiro => {
                const nomeParceiro = parceiro.querySelector('h3').textContent.toLowerCase();
                
                // Verifica se o termo de pesquisa corresponde ao nome do parceiro
                if (nomeParceiro.includes(termoPesquisa) || termoPesquisa === '') {
                    parceiro.style.display = 'block'; // Mostra o parceiro
                    parceiroEncontrado = true;
                } else {
                    parceiro.style.display = 'none'; // Esconde o parceiro
                }
            });

            // Exibe ou oculta a mensagem de "Parceiro não encontrado"
            const mensagemNaoEncontrado = document.getElementById('mensagemParNaoEncontradoPromocao');
            mensagemNaoEncontrado.style.display = parceiroEncontrado ? 'none' : 'block';
        });

        document.getElementById('inputPesquisaPromocao').addEventListener('input', function () {
            const termoPesquisa = this.value.toLowerCase();
            const produtos = document.querySelectorAll('.products .product-card');
            let produtoEncontrado = false;

            produtos.forEach(produto => {
                const nomeProduto = produto.querySelector('h3').textContent.toLowerCase();

                if (nomeProduto.includes(termoPesquisa) || termoPesquisa === '') {
                    produto.style.display = 'block';
                    produtoEncontrado = true;
                } else {
                    produto.style.display = 'none';
                }
            });

            // Exibe mensagem de "Produto não encontrado" se nenhum produto for exibido
            const mensagemNaoEncontrado = document.getElementById('mensagemNaoEncontradoPromocao');
            mensagemNaoEncontrado.style.display = produtoEncontrado ? 'none' : 'block';
        });      

        document.getElementById('inputPesquisaParceiroFrete_gratis').addEventListener('input', function () {
            const termoPesquisa = this.value.toLowerCase();
            const parceiros = document.querySelectorAll('.parceiros-carousel .parceiro-card');
            let parceiroEncontrado = false;

            // Itera sobre os parceiros
            parceiros.forEach(parceiro => {
                const nomeParceiro = parceiro.querySelector('h3').textContent.toLowerCase();
                
                // Verifica se o termo de pesquisa corresponde ao nome do parceiro
                if (nomeParceiro.includes(termoPesquisa) || termoPesquisa === '') {
                    parceiro.style.display = 'block'; // Mostra o parceiro
                    parceiroEncontrado = true;
                } else {
                    parceiro.style.display = 'none'; // Esconde o parceiro
                }
            });

            // Exibe ou oculta a mensagem de "Parceiro não encontrado"
            const mensagemNaoEncontrado = document.getElementById('mensagemParNaoEncontradoFrete_gratis');
            mensagemNaoEncontrado.style.display = parceiroEncontrado ? 'none' : 'block';
        });

        document.getElementById('inputPesquisaFrete_gratis').addEventListener('input', function () {
            const termoPesquisa = this.value.toLowerCase();
            const produtos = document.querySelectorAll('.products .product-card');
            let produtoEncontrado = false;

            produtos.forEach(produto => {
                const nomeProduto = produto.querySelector('h3').textContent.toLowerCase();

                if (nomeProduto.includes(termoPesquisa) || termoPesquisa === '') {
                    produto.style.display = 'block';
                    produtoEncontrado = true;
                } else {
                    produto.style.display = 'none';
                }
            });

            // Exibe mensagem de "Produto não encontrado" se nenhum produto for exibido
            const mensagemNaoEncontrado = document.getElementById('mensagemNaoEncontradoFrete_gratis');
            mensagemNaoEncontrado.style.display = produtoEncontrado ? 'none' : 'block';
        });

        document.getElementById('inputPesquisaParceiroNovidades').addEventListener('input', function () {
            const termoPesquisa = this.value.toLowerCase();
            const parceiros = document.querySelectorAll('.parceiros-carousel .parceiro-card');
            let parceiroEncontrado = false;

            // Itera sobre os parceiros
            parceiros.forEach(parceiro => {
                const nomeParceiro = parceiro.querySelector('h3').textContent.toLowerCase();
                
                // Verifica se o termo de pesquisa corresponde ao nome do parceiro
                if (nomeParceiro.includes(termoPesquisa) || termoPesquisa === '') {
                    parceiro.style.display = 'block'; // Mostra o parceiro
                    parceiroEncontrado = true;
                } else {
                    parceiro.style.display = 'none'; // Esconde o parceiro
                }
            });

            // Exibe ou oculta a mensagem de "Parceiro não encontrado"
            const mensagemNaoEncontrado = document.getElementById('mensagemParNaoEncontradoNovidades');
            mensagemNaoEncontrado.style.display = parceiroEncontrado ? 'none' : 'block';
        });


        document.getElementById('inputPesquisaNovidades').addEventListener('input', function () {
            const termoPesquisa = this.value.toLowerCase();
            const produtos = document.querySelectorAll('.products .product-card');
            let produtoEncontrado = false;

            produtos.forEach(produto => {
                const nomeProduto = produto.querySelector('h3').textContent.toLowerCase();

                if (nomeProduto.includes(termoPesquisa) || termoPesquisa === '') {
                    produto.style.display = 'block';
                    produtoEncontrado = true;
                } else {
                    produto.style.display = 'none';
                }
            });

            // Exibe mensagem de "Produto não encontrado" se nenhum produto for exibido
            const mensagemNaoEncontrado = document.getElementById('mensagemNaoEncontradoNovidades');
            mensagemNaoEncontrado.style.display = produtoEncontrado ? 'none' : 'block';
        });
    </script>



    </body>
    <!-- Footer -->
    <footer>
        <p>&copy; 2024 <?php echo htmlspecialchars($dadosEscolhido['nomeFantasia']); ?> - Todos os direitos reservados</p>
        <div class="contato">
            <p><strong>Contato:</strong></p>
            <p>Email: <?php echo htmlspecialchars($dadosEscolhido['email_suporte']); ?> | WhatsApp: <?php echo htmlspecialchars($dadosEscolhido['telefoneComercial']); ?></p>
        </div>
    </footer>
</html>

