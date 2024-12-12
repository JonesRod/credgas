<?php
    include('../../conexao.php');

    if(!isset($_SESSION)){
        session_start(); 
    }

    if(isset($_SESSION['id'])){
        $id = $_SESSION['id'];
        //$id = $_SESSION['usuario'];
        $sql_query = $mysqli->query(query: "SELECT * FROM meus_clientes WHERE id = '$id'") or die($mysqli->$error);
        $usuario = $sql_query->fetch_assoc(); 

    } else {
        // Se não houver uma sessão de usuário, redirecione para a página de login
        session_unset();
        session_destroy(); 
        header(header: "Location: ../../../../index.php");  
        exit(); // Importante adicionar exit() após o redirecionamento
    }
    /*$id_conf = '1';
    $dados = $mysqli->query(query: "SELECT * FROM config_admin WHERE id = '$id_conf'") or die($mysqli->error);
    $dadosEscolhido = $dados->fetch_assoc();
    // Verifica se o usuário está logado*/
    $usuarioLogado = isset($_SESSION['id']);
    //$id_conf = '1';*/


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

    // Consulta para somar todas as notificações de todas as linhas
    $sql_query = "
    SELECT 
        SUM(not_novo_cliente) AS total_not_novo_cliente,
        SUM(not_inscr_parceiro) AS total_not_inscr_parceiro,
        SUM(not_crediario) AS total_not_crediario,
        SUM(not_novos_produtos) AS total_not_novos_produtos,
        SUM(not_atualizar_produto) AS total_not_edicao_produtos,
        SUM(not_msg) AS total_not_msg
    FROM contador_notificacoes_admin
    WHERE id > '0'";
    
    // Executar a consulta
    $result = $mysqli->query($sql_query);

    // Verificar se há resultados
    if ($result) {
    $row = $result->fetch_assoc();
    $total_notificacoes = 
        ($row['total_not_novo_cliente'] ?? 0) + 
        ($row['total_not_inscr_parceiro'] ?? 0) + 
        ($row['total_not_crediario'] ?? 0) + 
        ($row['total_not_novos_produtos'] ?? 0) + 
        ($row['total_not_edicao_produtos'] ?? 0) + 
        ($row['total_not_msg'] ?? 0);

    //echo "Total de notificações: $total_notificacoes";
    } else {
    //echo "Erro ao executar a consulta: " . $mysqli->error;
    }

    $not_novo_cliente = $row['total_not_novo_cliente'] ?? 0;
    $not_inscr_parceiro = $row['total_not_inscr_parceiro'] ?? 0; // Define 0 se não houver resultado
    $not_crediario = $row['total_not_crediario'] ?? 0; // Define 0 se não houver resultado
    $not_novos_produtos = $row['total_not_novos_produtos'] ?? 0; // Define 0 se não houver resultado
    $not_edicao_produtos = $row['total_not_edicao_produtos'] ?? 0; // Define 0 se não houver resultado
    $not_msg = $row['total_not_msg'] ?? 0; // Define 0 se não houver resultado

    // Soma todos os valores de notificações
    $total_notificacoes = $not_novo_cliente + $not_inscr_parceiro + $not_crediario + $not_novos_produtos + $not_edicao_produtos + $not_msg;
    //echo $total_notificacoes; 

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
    <script src="cliente_home.js?v=<?php echo time(); ?>"></script><!--força a tualização-->
    <!--<script src="cadastro_inicial/localizador.js" defer></script>-->
    <link rel="stylesheet" href="cliente_home.css">
<style>
    header .logo img {
    height: 150px; /* Aumenta o tamanho do logo */
    width: 150px; /* Ajuste proporcional ao tamanho */
    border-radius: 50%; /* Mantém o logo redondo */
}

.menu-superior-direito {
    font-size: 20px;
    display: flex;
    align-items: flex-start; /* Alinha o conteúdo no topo */
    margin-top: -10px; /* Ajuste para alinhar ao topo */
}

.menu-superior-direito span {
    margin-right: 15px; /* Espaçamento entre o nome do usuário e os ícones */
    transition: color 0.3s ease; /* Transição suave para a cor */
}

.menu-superior-direito i {
    font-size: 24px; /* Aumenta o tamanho dos ícones */
    margin-left: 15px;
    transition: transform 0.3s ease, color 0.3s ease; /* Transição para o movimento e cor */
    cursor: pointer; /* Cursor de ponteiro ao passar o mouse */
}
/* Efeito ao passar o mouse */
.menu-superior-direito span:hover {
    color: #f0a309; /* Muda a cor do texto ao passar o mouse */
}

.menu-superior-direito i:hover {
    transform: translateY(-5px); /* Move o ícone para cima ao passar o mouse */
    color: #ff9d00; /* Muda a cor do ícone ao passar o mouse */
}
/* Efeito ao clicar */
.menu-superior-direito i:active {
    transform: scale(0.9); /* Diminui o tamanho do ícone ao clicar */
    color: #ff9d09; /* Muda a cor do ícone ao passar o mouse */
}
aside#menu-lateral {
    font-weight: bold; /* Aplica negrito ao texto */
    background-color: #d3d0ce;
    color: rgb(24, 8, 235);
    width: 210px; /* Largura fixa da barra lateral */
    padding: 10px;
    position: absolute; /* Mantém a barra lateral fixa */
    top: 60px; /* Ajusta a posição abaixo do cabeçalho */
    right: 20px; /* Posiciona o menu à direita */
    display: none; /* Inicialmente escondido */
    transition: all 0.3s ease; /* Transição suave */
    border-radius: 8px; /* Bordas arredondadas */
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2); /* Sombra para dar destaque */
}

aside#menu-lateral ul {
    list-style: none;
    padding: 0;
}

aside#menu-lateral ul li {
    margin: 15px 0; /* Margem entre os itens */
    font-size: 16px; /* Tamanho da fonte */
    display: flex; /* Flexbox para alinhar ícone e texto */
    align-items: center; /* Alinha verticalmente */
    transition: background-color 0.3s ease; /* Transição suave para a cor de fundo */
    border-radius: 5px; /* Bordas arredondadas */
    padding: 10px; /* Espaçamento interno */
    font-weight: bold; /* Aplica negrito ao texto */
}
/* Remove o sublinhado do link "Sair" */
#menu-lateral a {
    text-decoration: none; /* Remove o sublinhado */
    color: inherit; /* Mantém a cor do texto herdada */
    transition: color 0.3s ease; /* Suave transição de cor */
}

/* Efeito ao passar o mouse sobre o link */
#menu-lateral a:hover {
    cursor: pointer;
    color: #007BFF; /* Muda a cor ao passar o mouse */
}
/* Efeito ao passar o mouse sobre o item do menu */
aside#menu-lateral ul li:hover {
    cursor: pointer;
    background-color: rgba(0, 123, 255, 0.1); /* Cor de fundo ao passar o mouse */
}

/* Estilo para ícones */
aside#menu-lateral ul li i {
    margin-right: 10px; /* Espaçamento entre ícone e texto */
    font-size: 20px; /* Tamanho dos ícones */
    transition: transform 0.3s ease, color 0.3s ease; /* Transição para movimento e cor */
}

/* Efeito ao passar o mouse sobre o ícone */
aside#menu-lateral ul li:hover i {
    cursor: pointer;
    transform: translateY(-3px); /* Move o ícone para cima ao passar o mouse */
    color: #ffbb09; /* Muda a cor do ícone ao passar o mouse */
}

/* Efeito ao clicar em um ícone */
aside#menu-lateral ul li i:active {
    transform: scale(0.9); /* Diminui o tamanho do ícone ao clicar */
    color: #ffbb09; /* Muda a cor do ícone ao passar o mouse */
}
/* Efeitos para os spans */
aside#menu-lateral ul li span {
    transition: transform 0.3s ease, color 0.3s ease; /* Transição para movimento e cor */
}

/* Efeito ao passar o mouse sobre o span */
aside#menu-lateral ul li:hover span {
    cursor: pointer;
    transform: translateY(-3px); /* Move o ícone para cima ao passar o mouse */
    color: #bf9c44; /* Muda a cor do texto ao passar o mouse */
    /*text-decoration: underline; /* Adiciona sublinhado ao passar o mouse */
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
                    <span>Bem-vindo, <strong><?php echo htmlspecialchars($usuario['nome_completo']); ?></strong></span>
                    <i class="fas fa-bell" onclick="toggleNotificacoes()"></i>
                    <i class="fas fa-shopping-cart"onclick=""></i>
                    <i class="fas fa-bars" onclick="toggleMenu()"></i>
                <?php else: ?>
                    <span>Seja bem-vindo!</span>
                    <a href="login/lib/login.php" class="btn-login">Entrar</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Painel de notificações que aparece ao clicar no ícone de notificações -->
    <aside id="painel-notificacoes">
        <h2>Notificações: <?php echo htmlspecialchars(string: $total_notificacoes); ?></h2>
        <ul id="lista-notificacoes">
            <li onclick="abrirNotificacao(1)">Pedidos: <?php echo $not_novo_cliente; ?></li>  
            <li onclick="abrirNotificacao(2)">Bonus: <?php echo $not_inscr_parceiro; ?></li>
            <li onclick="abrirNotificacao(3)">Solicitação de crediario: <?php echo $not_crediario; ?></li>
            <li onclick="abrirNotificacao(4)">Novo Produto: <?php echo $not_novos_produtos; ?></li>    
            <li onclick="abrirNotificacao(5)">Edição de Produto: <?php echo $not_edicao_produtos; ?></li>         
            <li onclick="abrirNotificacao(6)">Nova mensagem recebida: <?php echo $not_msg; ?></li>
        </ul>
    </aside>

    <!-- Menu lateral que aparece abaixo do ícone de menu -->
    <aside id="menu-lateral">
        <ul>
            <!-- Item Perfil da Loja -->
            <li>
                <a href="perfil_loja.php?id_admin=<?php echo urlencode($id); ?>" title="Meu Perfil">
                    <i class="fas fa-user"></i>
                    <span >Perfil</span>
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
                <a href="admin_logout.php" title="Sair">
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

        </div>

        <!-- Conteúdos correspondentes às abas -->
        <div id="conteudo-catalogo" class="conteudo-aba" style="display: none;">
       
            <h2>Nossos Parceiros</h2>
            <?php

                // Consulta para buscar parceiros pelo CEP
                $sql_parceiros = "SELECT * FROM meus_parceiros WHERE status = 'ATIVO' AND aberto_fechado_manual = 'Aberto'";
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
                    
                    // Consulta para buscar parceiros ativos e abertos
                    $sql_parceiros = "SELECT * FROM meus_parceiros WHERE status = 'ATIVO' AND aberto_fechado_manual = 'Aberto'";
                    $result_parceiros = $mysqli->query($sql_parceiros) or die($mysqli->error);

                    if ($result_parceiros->num_rows > 0): 
                        
                        while ($parceiro = $result_parceiros->fetch_assoc()): 
                            // Exibe cada parceiro no carrossel
                            $logoParceiro = !empty($parceiro['logo']) ? $parceiro['logo'] : 'placeholder.jpg'; 
                ?>
                <div class="parceiro-card" onclick="window.location.href='../loja_parceiro/loja_parceiro.php?id=<?php echo $parceiro['id']; ?>'">
                    <img src="../parceiros/arquivos/<?php echo htmlspecialchars($logoParceiro); ?>" 
                    alt="Loja não encontrada">
                    <h3>
                        <?php
                            $nomeFantasia = htmlspecialchars($parceiro['nomeFantasia'] ?? '');
                            echo mb_strimwidth($nomeFantasia, 0, 18, '...'); // Limita a 100 caracteres com "..."
                        ?>
                    </h3>
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

                        <img src="../parceiros/produtos/img_produtos/<?php echo htmlspecialchars($primeira_imagem); ?>" alt="<?php echo htmlspecialchars($produto['nome_produto']); ?>">
                        <?php 
                            // Exibe o ícone de frete grátis, se o produto tiver frete grátis
                            if ($produto['frete_gratis'] === 'sim' || ($produto['promocao'] === 'sim' && $produto['frete_gratis_promocao'] === 'sim')): 
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
                        <h3><?php echo htmlspecialchars($produto['nome_produto']); ?></h3>

                        <!-- Preço do produto -->
                        <?php
                            $taxa_padrao = floatval($produto['taxa_padrao'] ?? 0);
                            $valor_base = isset($produto['promocao']) && $produto['promocao'] === 'sim' 
                                ? floatval($produto['valor_promocao'] ?? 0) 
                                : floatval($produto['valor_produto'] ?? 0);  
                            $valor_produto = $valor_base + (($valor_base * $taxa_padrao)/ 100);
                        ?>
                        
                        <p class="moeda">R$ <?php echo number_format($valor_produto, 2, ',', '.'); ?></p>
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
                            AND mp.aberto_fechado_manual = 'Aberto'
                    ";

                    $result_parceiros = $mysqli->query($sql_parceiros) or die($mysqli->error);

                    // Variável para rastrear se algum parceiro será exibido
                    $parceiro_exibido = false;

                    if ($result_parceiros->num_rows > 0): 
                        while ($parceiro = $result_parceiros->fetch_assoc()): 
                            $id_parceiro = (int)$parceiro['id'];
                            
                            // Consulta para verificar se o parceiro possui produtos em promoção
                            $sql_produtos = "
                                SELECT COUNT(*) AS total 
                                FROM produtos 
                                WHERE id_parceiro = $id_parceiro 
                                    AND oculto != 'sim' 
                                    AND produto_aprovado = 'sim' 
                                    AND promocao = 'sim'
                            ";
                            $result_produtos = $mysqli->query($sql_produtos) or die($mysqli->error);
                            $produto_data = $result_produtos->fetch_assoc();

                            // Se o parceiro tiver ao menos um produto em promoção
                            if ($produto_data['total'] > 0): 
                                $parceiro_exibido = true; // Marca que pelo menos um parceiro foi exibido
                                $logoParceiro = !empty($parceiro['logo']) ? htmlspecialchars($parceiro['logo']) : 'placeholder.jpg';
                                ?>
                                <div class="parceiro-card" onclick="window.location.href='login/lib/paginas/loja_parceiro/loja_parceiro.php?id=<?php echo $id_parceiro; ?>'">
                                    <img src="../parceiros/arquivos/<?php echo $logoParceiro; ?>" 
                                        alt="Loja não encontrada">
                                    <h3>
                                        <?php
                                            $nomeFantasia = htmlspecialchars($parceiro['nomeFantasia'] ?? '');
                                            echo mb_strimwidth($nomeFantasia, 0, 18, '...'); // Limita a 18 caracteres com "..."
                                        ?>
                                    </h3>
                                    <p><?php echo htmlspecialchars($parceiro['categoria'] ?? 'Categoria não informada'); ?></p>
                                </div>
                            <?php endif; ?>
                        <?php endwhile; ?>

                        <?php 
                        // Caso nenhum parceiro tenha produtos em promoção
                        if (!$parceiro_exibido): ?>
                            <p>Não há Lojas com promoção no momento.</p>
                        <?php endif; ?>

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

                                <img src="../parceiros/produtos/img_produtos/<?php echo htmlspecialchars($primeira_imagem); ?>" alt="<?php echo htmlspecialchars($produto['nome_produto']); ?>">
                                <?php 
                                    // Exibe o ícone de frete grátis, se o produto tiver frete grátis
                                    if ($produto['frete_gratis'] === 'sim' || ($produto['promocao'] === 'sim' && $produto['frete_gratis_promocao'] === 'sim')): 
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
                                
                                <h3><?php echo htmlspecialchars($produto['nome_produto']); ?></h3>
                                <p class="moeda">R$ <?php echo number_format($produto['valor_produto'], 2, ',', '.'); ?></p>
                                <a href="login/lib/detalhes_produto.php?id_produto=<?php echo $produto['id_produto']; ?>" class="btn">Detalhes</a>

                                <!-- Verifica se o usuário está logado para permitir a compra -->
                                <?php if (isset($usuarioLogado) && $usuarioLogado): ?>
                                    <a href="#" class="btn">Comprar</a>
                                <?php else: ?>
                                    <a href="login/lib/login.php" class="btn">Faça login para comprar</a>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    
                        <p>Não há produtos na promoção no momento.</p>
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
                        AND mp.aberto_fechado_manual = 'Aberto'
                ";
    
                $result_parceiros = $mysqli->query($sql_parceiros) or die($mysqli->error);

                if ($result_parceiros->num_rows > 0): 
                    while ($parceiro = $result_parceiros->fetch_assoc()): 
                        // Exibe cada parceiro no carrossel
                        $logoParceiro = !empty($parceiro['logo']) ? $parceiro['logo'] : 'placeholder.jpg'; 
                        $id_parceiro = $parceiro['id'];
                        
                        // Consulta para carregar produtos do parceiro
                        $sql_produtos = "
                            SELECT * FROM produtos 
                            WHERE id_parceiro = $id_parceiro 
                            AND oculto != 'sim' 
                            AND produto_aprovado = 'sim' 
                            AND (
                                frete_gratis = 'sim' 
                                OR (promocao = 'sim' AND frete_gratis_promocao = 'sim')
                            )
                        ";
                        $result_produtos = $mysqli->query($sql_produtos) or die($mysqli->error);
                ?>
                <div class="parceiro-card" onclick="window.location.href='../loja_parceiro/loja_parceiro.php?id=<?php echo $parceiro['id']; ?>'">
                    <img src="../parceiros/arquivos/<?php echo htmlspecialchars($logoParceiro); ?>" 
                    alt="Loja não encontrada">
                    <h3>
                        <?php
                            $nomeFantasia = htmlspecialchars($parceiro['nomeFantasia'] ?? '');
                            echo mb_strimwidth($nomeFantasia, 0, 18, '...'); // Limita a 100 caracteres com "..."
                        ?>
                    </h3>
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

                                <img src="../parceiros/produtos/img_produtos/<?php echo htmlspecialchars($primeira_imagem); ?>" alt="<?php echo htmlspecialchars($produto['nome_produto']); ?>">
                                <?php 
                                    // Exibe o ícone de frete grátis, se o produto tiver frete grátis
                                    if ($produto['frete_gratis'] === 'sim' || ($produto['promocao'] === 'sim' && $produto['frete_gratis_promocao'] === 'sim')): 
                                ?>
                                    <span class="icone-frete-gratis" title="Frete grátis">🚚</span>
                                <?php 
                                    endif;

                                    // Exibe o ícone de promoção, se o produto estiver em promoção
                                    if ($produto['promocao'] === 'sim' ): 
                                ?>
                                    <span class="icone-promocao" title="Produto em promoção">🔥</span>
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
                                <h3><?php echo htmlspecialchars($produto['nome_produto']); ?></h3>
                                <p class="moeda">R$ <?php echo number_format($produto['valor_produto'], 2, ',', '.'); ?></p>
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
                        AND mp.aberto_fechado_manual = 'Aberto'
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
                        AND DATEDIFF(NOW(), data) <= 30
                    ";
                    
                    $result_produtos = $mysqli->query($sql_produtos) or die($mysqli->error);
                ?>
                <div class="parceiro-card" onclick="window.location.href='../loja_parceiro/loja_parceiro.php?id=<?php echo $parceiro['id']; ?>'">
                    <img src="../parceiros/arquivos/<?php echo htmlspecialchars($logoParceiro); ?>" 
                    alt="Loja não encontrada">
                    <h3>
                        <?php
                            $nomeFantasia = htmlspecialchars($parceiro['nomeFantasia'] ?? '');
                            echo mb_strimwidth($nomeFantasia, 0, 18, '...'); // Limita a 100 caracteres com "..."
                        ?>
                    </h3>
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

                                <img src="../parceiros/produtos/img_produtos/<?php echo htmlspecialchars($primeira_imagem); ?>" alt="<?php echo htmlspecialchars($produto['nome_produto']); ?>">
                                <?php 
                                    // Exibe o ícone de frete grátis, se o produto tiver frete grátis
                                    if ($produto['frete_gratis'] === 'sim' || ($produto['promocao'] === 'sim' && $produto['frete_gratis_promocao'] === 'sim')): 
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
                                                     
                                <h3><?php echo htmlspecialchars($produto['nome_produto']); ?></h3>
                                <p class="moeda">R$ <?php echo number_format($produto['valor_produto'], 2, ',', '.'); ?></p>
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

    <script src="admin_home.js"></script> 
    <script>
        // Obtém o ID da sessão do PHP
        var sessionId = <?php echo json_encode($id); ?>;

        function abrirNotificacao(id) {
            let url = ""; // Inicializa a URL como uma string vazia

            // Define a URL com base no ID da notificação
            switch (id) {
                case 1:
                    url = `not_novo_cliente.php?session_id=${sessionId}`;
                    break;
                case 2:
                    url = `not_detalhes_parceiro.php?session_id=${sessionId}`;
                    break;
                case 3:
                    url = `not_detalhes_crediario.php?session_id=${sessionId}`;
                    break;
                case 4:
                    url = `not_detalhes_novos_produtos.php?session_id=${sessionId}`;
                    break;
                case 5:
                    url = `not_detalhes_edicao_produtos.php?session_id=${sessionId}`;
                    break;
                case 6:
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
            fetch('get_notifications.php')
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

