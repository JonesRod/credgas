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

}/* elseif (isset($_GET['id'])) {
    // Se o ID for passado pela URL
    $id = intval($_GET['id']); // Usa o ID da URL, e sempre converta para inteiro

    // Consulta para buscar os dados do cliente
    $sql_query = $mysqli->prepare("SELECT * FROM meus_clientes WHERE id = ?");
    $sql_query->bind_param("i", $id); // Bind para evitar injeção de SQL
    $sql_query->execute();
    $usuario = $sql_query->get_result()->fetch_assoc();
    echo 'oii2'; // Para verificar que está no bloco do GET

}*/ else {
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

    // Exibir o total de notificações
    //echo "Total de notificações: $total_notificacoes";

    

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
        #lista-notificacoes a {
            text-decoration: none; /* Remove o sublinhado */
            color: inherit; /* Mantém a cor do texto herdada */
            display: block; /* Faz o link ocupar toda a área do <li> */
            padding: 5px; /* Adiciona espaçamento interno para melhor interação */
        }

        #lista-notificacoes a:hover {
            background-color: #f0f0f0; /* Cor de fundo ao passar o mouse */
            border-radius: 4px; /* Bordas arredondadas */
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
            height: 310px;
            text-align: center;
        }
        .popup #info {
            margin: 12px 12px 8px 12px;
            border: 1px solid black; /* Adiciona uma borda */
            border-radius: 5px; /* Arredonda os cantos */
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
        .popup p{
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
        .popup #produtoNome{
            font-weight: bold; /* Deixa o texto em negrito */
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

        .close-btn, .confirm-btn {
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

        .close-btn:hover, .confirm-btn:hover {
            transform: translateY(-3px);
        }

        #resposra-carrinho {
        position: fixed;  /* Fixa a posição na tela */
        top: 50%;         /* Coloca no centro vertical */
        left: 50%;        /* Coloca no centro horizontal */
        transform: translate(-50%, -50%); /* Ajusta para centralizar exatamente */
        background-color: rgba(0, 0, 0, 0.7);  /* Fundo semitransparente */
        color: white;     /* Cor do texto */
        padding: 20px;    /* Espaçamento interno */
        border-radius: 10px; /* Bordas arredondadas */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3); /* Sombra para dar destaque */
        font-size: 16px;  /* Tamanho da fonte */
        z-index: 9999;    /* Garante que o popup fique acima de outros elementos */
        display: none;    /* Inicialmente escondido */
    }
    </style>
</head>
<body>

    <!-- Header -->
    <header>
        <div class="logo">
            <img src="<?php if(isset($logo)) echo $logo; ?>" alt="Logo" class="logo-img">
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
                <span>Bem-vindo, <strong><?php echo htmlspecialchars(explode(' ', $usuario['nome_completo'])[0]); ?></strong></span>
                <!-- Ícone de notificações com contagem -->
                <div class="notificacoes">
                    <i class="fas fa-bell" title="Notificações" onclick="toggleNotificacoes()"></i>
                    <!-- Exibir a contagem de notificações -->
                    <?php if ($total_notificacoes > 0): ?>
                        <span id="notificacao-count" class="notificacao-count"><?php echo htmlspecialchars($total_notificacoes); ?></span>
                    <?php else: ?>
                        <span id="notificacao-count" class="notificacao-count" style="display: none;"></span>
                    <?php endif; ?>
                </div>
                <i class="fas fa-shopping-cart" title="Meu Carrinho" onclick=""></i>
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
                    <span >Perfil</span>
                </a>
            </li>

            <!-- Item crediario-->
            <li>
                <a href="perfil_crediario.php?id=<?php echo urlencode($id); ?>" title="Crediario">
                    <i class="fas fa-user"></i>
                    <span >Perfil Crediario</span>
                </a>
            </li>

            <!-- Item crediario-->
            <li>
                <a href="meu_crediario.php?id=<?php echo urlencode($id); ?>" title="Crediario">
                    <i class="fas fa-handshake"></i>
                    <span >Meu Crediario</span>
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
                <div class="parceiro-card" onclick="window.location.href='loja_parceiro.php?id=<?php echo $parceiro['id']; ?>&id_cliente=<?php echo $usuario['id']; ?>'">
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
                            <a href="#" class="btn" onclick="abrirPopup(
                            '<?php echo $produto['id_produto']; ?>',
                            '<?php echo $produto['nome_produto']; ?>', 
                            '<?php echo $valor_produto; ?>')">Adicionar ao Carrinho</a>
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
                        AND promocao = 'sim'
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
                                    <a href="#" class="btn" onclick="abrirPopup(
                                    '<?php echo $produto['id_produto']; ?>',
                                    '<?php echo $produto['nome_produto']; ?>', 
                                    '<?php echo $valor_produto; ?>')">Adicionar ao Carrinho</a>
                                <?php else: ?>
                                    <a href="login/lib/login.php" class="btn">Faça login para comprar</a>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
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
                                    <a href="#" class="btn" onclick="abrirPopup(
                                    '<?php echo $produto['id_produto']; ?>',
                                    '<?php echo $produto['nome_produto']; ?>', 
                                    '<?php echo $valor_produto; ?>')">Adicionar ao Carrinho</a>
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
                                    <a href="#" class="btn" onclick="abrirPopup(
                                    '<?php echo $produto['id_produto']; ?>',
                                    '<?php echo $produto['nome_produto']; ?>', 
                                    '<?php echo $valor_produto; ?>')">Adicionar ao Carrinho</a> 
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

    <div class="popup" id="popup">
        <h2>Detalhes do Produto</h2>
        <form id="formCarrinho" action="comprar/carrinho.php">
            <aside id="info">
                <input type="hidden" id="id_cli" name="id_cli" value="<?php echo htmlspecialchars( $id); ?>">
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

    <div id="resposra-carrinho" style="display: none;">
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

        document.addEventListener('DOMContentLoaded', function() {
            const overlay = document.getElementById('overlay');

            overlay.addEventListener('click', function(event) {
                fecharPopup();
            });
        });

        document.getElementById("formCarrinho").addEventListener("submit", function(event) {
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
            setTimeout(function() {
                document.getElementById('resposra-carrinho').style.display = 'none';
            }, 3000);
        }


    </script>


    <footer class="menu-mobile">
        <ul>
            <li><a href="perfil_cliente.php" title="Meu Perfil"><i class="fas fa-user"></i></a></li>
            <li><a href="crediario.php" title="Crediário"><i class="fas fa-handshake"></i></a></li>
            <li><a href="configuracoes.php?id_parceiro=<?php echo urlencode($id); ?>" title="Meu Carrinho"><i class="fas fa-shopping-cart"></i></a></li>
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
    <!-- Footer 
    <footer>
        <p>&copy; 2024 <?php //echo htmlspecialchars($dadosEscolhido['nomeFantasia']); ?> - Todos os direitos reservados</p>
        <div class="contato">
            <p><strong>Contato:</strong></p>
            <p>Email: <?php //echo htmlspecialchars($dadosEscolhido['email_suporte']); ?> | WhatsApp: <?php //echo htmlspecialchars($dadosEscolhido['telefoneComercial']); ?></p>
        </div>
    </footer>-->
</html>

