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
    <title>Minha Loja</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>

    <!--<script src="cadastro_inicial/localizador.js" defer></script>-->
    <link rel="stylesheet" href="index.css">

    <style>
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
    width: 95%;
    padding: 10px;
    /*margin-top: -30px;*/
} 
.parceiros-carousel {
    width: 100%; /* Ocupar toda a largura */
    margin: 0 auto; /* Centralizar o carrossel */
    display: flex; /* Flexbox para alinhar elementos */
    justify-content: center; /* Centraliza o conteúdo dentro */
    margin: ;
}
/*.parc {
    width: 100%; /* Ocupar toda a largura */
    /*text-align: center; /* Centraliza o texto ou os elementos dentro 
}*/
.parceiros-carousel .parceiro-card {
    text-align: center;
    padding: 10px;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    margin: 10px auto; /* Centraliza e ajusta margens vertical e horizontal */
    max-width: 200px; /* Define o comprimento máximo do cartão */
}


.parceiros-carousel .parceiro-card img {
    max-width: 120px; /* Ajuste o tamanho da logo */
    height: 120px;   /* Para mantê-la circular */
    margin: 0 auto 10px; /* Centraliza horizontalmente e adiciona espaço abaixo */
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
    text-align: left; /* Alinha o texto à esquerda */
    margin-left: 0;   /* Garante que não há margem que afaste do lado esquerdo */
    padding-left: 0;  /* Garante que não há espaçamento interno */
}
.user-area{
    padding-right: 30px;
}


/* Footer */
footer {
    text-align: center;
    padding: 30px 0;
    background-color: #333;
    color: white;
    margin-top: 30px;
}

footer .contato {
    margin: 10px 0;
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
                    //echo "<p>Nenhum parceiro encontrado.</p>";
                }
                
            ?>
        
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
                    <?php 
                        endwhile; ?>
                    <?php else: ?>
                        <p>Nenhum parceiro ativo no momento.</p>
                    <?php endif; 
                    ?>

            </div>

            <!-- Produtos -->
            <h2>Produtos</h2>
            <div class="container">
                
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
                </div>
            </div>

        </div>

        <!-- Conteúdos correspondentes às abas -->
        <div id="conteudo-promocoes" class="conteudo-aba" style="display: none;">

            <h2>Nossos Parceiros</h2>

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
                <?php 
                    endwhile; 
                else: 
                ?>
                    <p>Nenhum parceiro ativo no momento.</p>
                <?php endif; ?>

            </div>


            <!-- Produtos -->
            <h2>Produtos</h2>
            <div class="container">
                
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
                </div>
            </div>

        </div>

        <!-- Conteúdos correspondentes às abas -->
        <div id="conteudo-frete_gratis" class="conteudo-aba" style="display: none;">

            <h2>Nossos Parceiros</h2>

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
                <?php 
                    endwhile; 
                else: 
                ?>
                    <p>Nenhum parceiro ativo no momento.</p>
                <?php endif; ?>

            </div>

            <!-- Produtos -->
            <h2>Produtos</h2>
            <div class="container">
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
                </div>
            </div>
        </div>

        <!-- Conteúdos correspondentes às abas -->
        <div id="conteudo-novidades" class="conteudo-aba" style="display: none;">

            <h2>Nossos Parceiros</h2>

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
                <?php 
                    endwhile; 
                else: 
                ?>
                    <p>Nenhum parceiro ativo no momento.</p>
                <?php endif; ?>

            </div>

            <!-- Produtos -->
            <h2>Produtos</h2>
            <div class="container">
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
                </div>
            </div>
        </div>
    </main>

    <script>


        // Função para simular o clique no botão ao carregar a página
        /*window.onload = function() {
            setTimeout(function() {
                var cep = document.getElementById('cep').value;
                if (cep) {
                    document.getElementById('buscarButton').click();
                }
            }, 5000); // 2000 milissegundos = 2 segundos
        };*/

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

        ///pesquizador de produto no catalogo
        /*document.getElementById('inputPesquisaCatalogo').addEventListener('input', function() {
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

            // Exibe mensagem de "Produto não encontrado" se nenhum produto for exibido
            const mensagemNaoEncontrado = document.getElementById('mensagemNaoEncontradoCatalogo');
            mensagemNaoEncontrado.style.display = produtoEncontrado ? 'none' : 'block';
        });*/


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
        

    </script>



    </body>
    <!-- Footer -->
    <footer>
        <p>&copy; 2024 <?php echo htmlspecialchars($dadosEscolhido['nomeFantasia']); ?> - Todos os direitos reservados</p>
        <div class="contato">
            <p><strong>Contato:</strong></p>
            <p>Email: <?php echo htmlspecialchars($dadosEscolhido['email_suporte']); ?> | Telefone: <?php echo htmlspecialchars($dadosEscolhido['telefoneComercial']); ?></p>
        </div>
    </footer>
</html>

