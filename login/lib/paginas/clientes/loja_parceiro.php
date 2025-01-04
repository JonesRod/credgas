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

        // Consulta para buscar os dados do parceiro
        $sql = "SELECT * FROM meus_parceiros WHERE id = $idParceiro AND status = 'ATIVO' AND aberto_fechado_manual = 'Aberto'";
        $result = $mysqli->query($sql);

        if ($result->num_rows > 0) {
            $parceiro = $result->fetch_assoc();
            // Exibir os dados da loja do parceiro
            // Verifica e ajusta a logo
            if(isset($parceiro['logo'])) {
                $minhaLogo = $parceiro['logo'];

                if ($minhaLogo !=''){
                    // Se existe e não está vazio, atribui o valor à variável logo
                    $logo = '../parceiros/arquivos/'.$parceiro['logo'];
                    //echo ('oii');
                }
            }else{
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

    /*if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['categoria_selecionada'])) {
        $categoriaSelecionada = $_POST['categoria_selecionada'];

    } */

    // Consulta para somar todas as notificações de um cliente específico
    $sql_query = "SELECT COUNT(*) AS total_notificacoes FROM contador_notificacoes_cliente WHERE id_cliente = ? AND lida = 1";
    $stmt = $mysqli->prepare($sql_query);
    $stmt->bind_param("i", $id); // Substituir $id pelo ID do cliente
    $stmt->execute();
    $stmt->bind_result($total_notificacoes);
    $stmt->fetch();
    $stmt->close();

    // Obtenha a data atual
    $data_atual = date('Y-m-d');
    // Atualiza os produtos com promoção
    $produtos_promocao = $mysqli->query("SELECT id_produto, promocao, ini_promocao, fim_promocao FROM produtos") or die($mysqli->error);
    while ($produtos_encontrados = $produtos_promocao->fetch_assoc()) {
        $id_produto = $produtos_encontrados['id_produto'];
        $promocao = $produtos_encontrados['promocao'];
        $data_inicio = $produtos_encontrados['ini_promocao'];
        $data_fim = $produtos_encontrados['fim_promocao'];

        // Verifica se a promoção deve estar ativa ou inativa
        if ($promocao === 'sim' && $data_inicio <= $data_atual && $data_fim >= $data_atual) {
            // A promoção deve continuar como "sim"
            continue;
        } elseif ($data_fim < $data_atual) {
            // A promoção terminou; atualize para "não"
            $mysqli->query("UPDATE produtos SET promocao = 'não' WHERE id_produto = '$id_produto'");
        } elseif ($data_inicio > $data_atual) {
            // A promoção ainda não começou; continue com "sim" se for o caso
            $mysqli->query("UPDATE produtos SET promocao = 'sim' WHERE id_produto = '$id_produto'");
        }
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['categoria_selecionada'])) {
    
        $categoriaSelecionada = $_POST['categoria_selecionada'];
        //echo ('oii1');
    }else{
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
    AND oculto != 'sim' 
    AND produto_aprovado = 'sim'") or die($mysqli->error);

    // Verifica se existem promoções, mais vendidos e frete grátis
    $promocoes =  $mysqli->query("SELECT * FROM produtos 
    WHERE id_parceiro = '$idParceiro' 
    AND categoria = '$categoriaSelecionada' 
    AND promocao = 'sim' 
    AND oculto != 'sim' 
    AND produto_aprovado = 'sim'") or die($mysqli->error);

    // Consulta SQL corrigida
    $queryFreteGratis = "SELECT * FROM produtos 
    WHERE id_parceiro = '$idParceiro'
    AND categoria = '$categoriaSelecionada'
    AND oculto != 'sim' 
    AND produto_aprovado = 'sim' 
    AND frete_gratis = 'sim' 
    OR (promocao = 'sim' 
    AND frete_gratis_promocao = 'sim')";

    // Executa a consulta e verifica erros
    $freteGratis = $mysqli->query($queryFreteGratis) or die($mysqli->error);
    /*if ($freteGratis->num_rows > 0){
        echo ('oi1');
    }else{
        echo ('oi2');
    }*/
    //$produtos_novidades = $mysqli->query("SELECT * FROM produtos WHERE id_parceiro = '$idParceiro' AND oculto != 'sim' AND produto_aprovado = 'sim'") or die($mysqli->error);

    // Consulta SQL
    $novidades = $mysqli->query("
        SELECT *, DATEDIFF(NOW(), data) AS dias_desde_cadastro
        FROM produtos 
        WHERE id_parceiro = '$idParceiro' 
        AND categoria = '$categoriaSelecionada' 
        AND oculto != 'sim' 
        AND produto_aprovado = 'sim'
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
    <link rel="stylesheet" href="loja_parceiro_home.css">
    <script src="loja_parceiro_home.js"></script> 
    <style>

aside#menu-lateral {
    display: none;
    position: fixed;
    top: 40px; /* Ajuste conforme a altura do cabeçalho */
    right: 20px; /* Posiciona o menu à direita */
    width: 200px;
    height: auto;
    background-color: white;
    border: 2px solid #ffb300;
    border-radius: 8px;
    box-shadow: 0px 4px 8px rgba(20, 5, 232, 0.31);
    z-index: 1000;
    padding: 10px;
    color: rgb(24, 8, 235);
    width: 210px; /* Largura fixa da barra lateral */
    position: absolute; /* Mantém a barra lateral fixa */
    transition: all 0.3s ease; /* Transição suave */
}

aside#menu-lateral ul {
    list-style: none;
    padding: 0;
}

aside#menu-lateral ul li {
    margin: 0; /* Margem entre os itens */
    font-size: 16px; /* Tamanho da fonte */
    display: flex; /* Flexbox para alinhar ícone e texto */
    align-items: center; /* Alinha verticalmente */
    transition: background-color 0.3s ease; /* Transição suave para a cor de fundo */
    border-radius: 5px; /* Bordas arredondadas */
    padding: 5px; /* Espaçamento interno */
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
    margin-right: 5px; /* Espaçamento entre ícone e texto */
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

/* Botões */
.btn {
display: inline-block;
background: #27ae60; /* Cor do botão */
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
background:darkorange;
}
    </style>

</head>
<body>
    <form id="formCategoria" method="POST" action="">
        <input type="hidden" name="id_parceiro" id="id_parceiro" value="<?php echo $idParceiro; ?>">
        <input type="hidden" name="categoria_selecionada" id="categoria_selecionada" value="<?php echo $categoriaSelecionada; ?>">
        <button type="submit" id="carregar_categoria" class="carregar_categoria" style="display: none;">enviar</button>
    </form>

    <!-- Header -->
    <header>
        <div class="logo">
            <img src="<?php echo $logo; ?>" alt="Logo da Loja" class="logo-img">
        </div>

        <h1><?php echo $parceiro['nomeFantasia']; ?></h1>

        <div class="menu-superior-direito">
            <?php if ($usuario): ?>
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
                <a href="perfil_crediario.php?id=<?php echo urlencode($id); ?>" title="Crediario">
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

    <div class="categorias">
        <?php 
            // Consulta para buscar parceiros pelo CEP
            $sql_parceiros = "SELECT * FROM meus_parceiros WHERE id = $idParceiro AND status = 'ATIVO'";
            $result_parceiros = $mysqli->query($sql_parceiros) or die($mysqli->error);

            if ($result_parceiros->num_rows > 0): 
                while ($parceiro = $result_parceiros->fetch_assoc()): 
                    // Consulta para buscar categorias únicas dos produtos do parceiro
                    $sql_categorias = "SELECT categoria FROM produtos WHERE id_parceiro = ".$parceiro['id'];
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
                <h2 class="voltar">
                <a href="cliente_home.php?id=<?php echo urlencode($usuario['id']); ?>" class="voltar-link"><< Voltar</a>
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
                    <div class="categoria-item <?php echo $selectedClass; ?>" onclick="selecionarCategoria('<?php echo $categoriaNome; ?>')" data-categoria="<?php echo $categoriaNome; ?>">
                        <img src="<?php echo htmlspecialchars('../arquivos_fixos/'.$imagem); ?>" alt="<?php echo $categoriaNome; ?>" class="categoria-imagem">
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
        </div>

        <!-- Conteúdos correspondentes às abas -->
        <div id="conteudo-catalogo" class="conteudo-aba" style="display: none;">
            <?php 
                if ($catalogo->num_rows > 0): 
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
                            <img src="../parceiros/produtos/img_produtos/<?php echo $primeiraImagem; ?>" alt="Imagem do Produto" class="produto-imagem">
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
                        </h3>
                        <?php echo $produto['nome_produto']; ?>
                        <!-- Preço do produto -->
                        <?php
                            $taxa_padrao = floatval($produto['taxa_padrao'] ?? 0);
                            $valor_base = isset($produto['promocao']) && $produto['promocao'] === 'sim' 
                                ? floatval($produto['valor_promocao'] ?? 0) 
                                : floatval($produto['valor_produto'] ?? 0);  
                            $valor_produto = $valor_base + (($valor_base * $taxa_padrao)/ 100);
                        ?>
                        <p class="produto-preco">R$ <?php echo number_format($valor_produto, 2, ',', '.'); ?></p>
                        <a href="detalhes_novos_produtos.php?id_produto=<?php echo $produto['id_produto']; ?>&id_parceiro=<?php echo $idParceiro; ?>" class="btn">Detalhes</a>                        </div>
                        <a href="#" class="btn">Comprar</a>
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
                            <img src="../parceiros/produtos/img_produtos/<?php echo $primeiraImagem; ?>" alt="Imagem do Produto" class="produto-imagem">
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
                            
                        </h3>
                        <?php echo $produto['nome_produto']; ?>
                        <!-- Preço do produto -->
                        <?php
                        $taxa_padrao = floatval($produto['taxa_padrao'] ?? 0);
                        $valor_base = isset($produto['promocao']) && $produto['promocao'] === 'sim' 
                            ? floatval($produto['valor_promocao'] ?? 0) 
                            : floatval($produto['valor_produto'] ?? 0);  
                        $valor_produto = $valor_base + (($valor_base * $taxa_padrao)/ 100);
                        ?>
                        <p class="produto-preco">R$ <?php echo number_format($valor_produto, 2, ',', '.'); ?></p>
                        <a href="detalhes_novos_produtos.php?id_produto=<?php echo $produto['id_produto']; ?>&id_parceiro=<?php echo $idParceiro; ?>" class="btn">Detalhes</a>                        </div>
                        <a href="#" class="btn">Comprar</a>
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
                                <img src="../parceiros/produtos/img_produtos/<?php echo $primeiraImagem; ?>" alt="Imagem do Produto" class="produto-imagem">
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
                            </h3>
                            <?php echo $produto['nome_produto']; ?>

                            <!-- Preço do produto -->
                            <?php
                            $taxa_padrao = floatval($produto['taxa_padrao'] ?? 0);
                            $valor_base = isset($produto['promocao']) && $produto['promocao'] === 'sim' 
                                ? floatval($produto['valor_promocao'] ?? 0) 
                                : floatval($produto['valor_produto'] ?? 0);  
                            $valor_produto = $valor_base + (($valor_base * $taxa_padrao)/ 100);
                            ?>
                            <p class="produto-preco">R$ <?php echo number_format($valor_produto, 2, ',', '.'); ?></p>
                            <a href="detalhes_novos_produtos.php?id_produto=<?php echo $produto['id_produto']; ?>&id_parceiro=<?php echo $idParceiro; ?>" class="btn">Detalhes</a>
                            <a href="#" class="btn">Comprar</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Mensagem de produto não encontrado -->
            <p id="mensagemNaoEncontradoFreteGratis" style="display: none;">Nenhum produto encontrado com frete grátis.</p>
            
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
                                <img src="../parceiros/produtos/img_produtos/<?php echo $primeiraImagem; ?>" alt="Imagem do Produto" class="produto-imagem">
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
                            </h3>
                            <?php echo $produto['nome_produto']; ?>

                            <!-- Preço do produto -->
                            <?php
                            $taxa_padrao = floatval($produto['taxa_padrao'] ?? 0);
                            $valor_base = isset($produto['promocao']) && $produto['promocao'] === 'sim' 
                                ? floatval($produto['valor_promocao'] ?? 0) 
                                : floatval($produto['valor_produto'] ?? 0);  
                            $valor_produto = $valor_base + (($valor_base * $taxa_padrao)/ 100);
                            ?>
                            <p class="produto-preco">R$ <?php echo number_format($valor_produto, 2, ',', '.'); ?></p>
                            <a href="detalhes_novos_produtos.php?id_produto=<?php echo $produto['id_produto']; ?>&id_parceiro=<?php echo $idParceiro; ?>" class="btn">Detalhes</a>
                            <a href="#" class="btn">Comprar</a>
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

    </main>

    <footer class="menu-mobile">
        <ul>
            <!--<li><a href="parceiro_home.php" title="Página Inicial"><i class="fas fa-home"></i></a></li>-->
            <li><a href="perfil_loja.php" title="Perfil da Loja"><i class="fas fa-user"></i></a></li>
            <li title="Pedidos"><i class="fas fa-box"></i></li> <!-- pedidos -->
            <li><a href="configuracoes.php?id_parceiro=<?php echo urlencode($idParceiro); ?>" title="Configurações"><i class="fas fa-cog"></i></a></li>
            <li><a href="parceiro_logout.php" title="Sair"><i class="fas fa-sign-out-alt"></i></a></li>
        </ul>
    </footer>

    <script>
        // Obtém o ID da sessão do PHP
        var sessionId = <?php echo json_encode($idParceiro); ?>;
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
        });

        function enviar() {
            // Simula o clique no botão "Enviar"
            const botaoEnviar = document.getElementById('carregar_categoria');
            botaoEnviar.click();
        }
    </script>

</body>
</html>

