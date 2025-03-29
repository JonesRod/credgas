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
        if(isset($parceiro['logo'])) {
            $minhaLogo = $parceiro['logo'];

            if ($minhaLogo !=''){
                // Se existe e não está vazio, atribui o valor à variável logo
                $logo = $parceiro['logo'];
                //echo ('oii');
            }
        }else{
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
        //echo ('oii1');
    }else{
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
        //var_dump($categoriasUnicas);

        // Pega a primeira categoria, se existir
        $primeiraCategoria = !empty($categoriasUnicas) ? reset($categoriasUnicas) : null; 
        // Use reset() para obter o primeiro elemento do array
        
        $categoriaSelecionada = $primeiraCategoria;
        //echo ('oii22');
    }

    // Consulta para buscar produtos do catálogo
    $catalogo = $mysqli->query(query: "SELECT * FROM produtos 
    WHERE id_parceiro = '$id'
    AND categoria = '$categoriaSelecionada'") or die($mysqli->error);

    // Verifica se existem promoções, mais vendidos e frete grátis
    $promocoes =  $mysqli->query("SELECT * FROM produtos 
    WHERE id_parceiro = '$id' 
    AND categoria = '$categoriaSelecionada' 
    AND promocao = '1'") or die($mysqli->error);

    // Consulta SQL corrigida
    $queryFreteGratis = "SELECT * FROM produtos 
    WHERE id_parceiro = '$id'
    AND categoria = '$categoriaSelecionada'
    AND frete_gratis = '1'
    OR (promocao = '1' 
    AND frete_gratis_promocao = '1')";

    // Executa a consulta e verifica erros
    $frete_gratis = $mysqli->query($queryFreteGratis) or die($mysqli->error);

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

    //Consulta para buscar produtos ocultos do catálogo
    $produtos_ocultos = $mysqli->query("SELECT * FROM produtos 
    WHERE id_parceiro = '$id' 
    AND categoria = '$categoriaSelecionada' 
    AND oculto = '1'") or die($mysqli->error);
    //echo "<p>Produtos ocultos encontrados: " . $produtos_ocultos->num_rows . "</p>";
    // Obtenha a data atual
    $data_atual = date('Y-m-d');

    // Consulta para buscar todos os produtos com promoção
    $produtos_promocao = $mysqli->query("SELECT id_produto, promocao, ini_promocao, fim_promocao FROM produtos") or die($mysqli->error);

    // Consulta SQL
    $novidades = $mysqli->query("
        SELECT *, DATEDIFF(NOW(), data) AS dias_desde_cadastro
        FROM produtos 
        WHERE id_parceiro = '$id' 
        AND categoria = '$categoriaSelecionada'  
        AND DATEDIFF(NOW(), data) <= 30
    ") or die("Erro na consulta: " . $mysqli->error);

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

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $parceiro['nomeFantasia']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="parceiro_home.css">
    <script src="parceiro_home.js"></script> 
    <style>
        .conteudo-secao {
            display: none;
        }

        .conteudo-secao.ativo {
            display: block;
        }
        .categorias-parceiro {
            display: flex;
            justify-content: center; /* Centraliza horizontalmente */
            align-items: center; /* Centraliza verticalmente */
            height: 100%; /* Garante que o elemento ocupe o espaço necessário */
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

    </style>
</head>
<body>
    <form id="formCategoria" method="POST" action="">
        <input type="hidden" name="id_parceiro" id="id_parceiro" value="<?php echo $id; ?>">
        <input type="hidden" name="categoria_selecionada" id="categoria_selecionada" value="<?php echo $categoriaSelecionada; ?>">
        <button type="submit" id="carregar_categoria" class="carregar_categoria" style="display: none;">enviar</button>
    </form>

    <!-- Header -->
    <header>
        <div class="logo">
            <img src="<?php echo 'arquivos/'.$logo; ?>" alt="Logo da Loja" class="logo-img">
        </div>

        <h1><?php echo $parceiro['nomeFantasia']; ?></h1>

        <div class="menu-superior-direito">
            <!-- Ícone de notificações com contagem -->
            <div class="notificacoes">
                <i class="fas fa-bell" onclick="toggleNotificacoes()"></i>
                <!-- Exibir a contagem de notificações -->
                <?php if ($total_notificacoes > 0): ?>
                    <span id="notificacao-count" class="notificacao-count"><?php echo htmlspecialchars($total_notificacoes); ?></span>
                <?php else: ?>
                    <span id="notificacao-count" class="notificacao-count" style="display: none;"></span>
                <?php endif; ?>
            </div>

            <i class="fas fa-store" onclick="toggleMenu()"></i><!--Configuraçõa da Loja-->
        </div>
    </header>

    <aside id="painel-notificacoes">
        <h2>Notificações: <span id="notificacao-header-count"><?php echo htmlspecialchars($total_notificacoes); ?></span></h2>
        <ul id="lista-notificacoes">
            <li data-id="1" onclick="abrirNotificacao(1)">Plataforma: <?php echo $total_plataforma; ?></li>
            <li data-id="2" onclick="abrirNotificacao(2)">Novo Produto: <?php echo $total_not_novo_produto; ?></li>
            <li data-id="3" onclick="abrirNotificacao(3)">Edição de Produtos: <?php echo $total_not_adicao_produto; ?></li>
            <li data-id="4" onclick="abrirNotificacao(4)">Pedidos: <?php echo $total_pedidos; ?></li>
        </ul>
    </aside>

    <!-- Menu lateral que aparece abaixo do ícone de menu -->
    <aside id="menu-lateral" >
        <ul>
            <li><a href="perfil_loja.php"><i class="fas fa-user"></i> Perfil da Loja</a></li>
            <li><a href="configuracoes.php?id_parceiro=<?php echo urlencode($id); ?>"><i class="fas fa-cog"></i> Configurações</a></li>
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

            <div class="tab" onclick="mostrarConteudo('produtos_ocultos',this)">
                <span>Produtos Ocultos</span>
            </div>

        </div>

        <!-- Conteúdos correspondentes às abas -->
        <div id="conteudo-catalogo" class="conteudo-aba" style="display: none;">
            <?php 
                if ($catalogo->num_rows > 0): 
            // Exibe o número de produtos encontrados
            $numProdutos = $catalogo->num_rows;
            echo "<p style='margin-top: 30px;'>$numProdutos produto(s) encontrado(s).</p>";
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
                    ?>
                    
                    <!-- Ícones de status do produto -->
                    <div class="produto-status">
                        <?php if (isset($produto['oculto']) && $produto['oculto'] == 1): ?>
                            <span class="icone-oculto" title="Produto oculto">👁️‍🗨️</span>
                        <?php endif; ?>
                        
                        <?php if (isset($produto['produto_aprovado']) && $produto['produto_aprovado'] != 1): ?>
                            <i class="fas fa-clock" title="Em análise"></i>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Imagem do produto -->
                    <img src="<?php echo htmlspecialchars($primeiraImagem, ENT_QUOTES, 'UTF-8'); ?>" alt="Imagem do Produto" class="produto-imagem">

                    <div class="produto-detalhes">
                        <p>
                            <!-- Ícones de promoção e frete grátis -->
                            <?php if (!empty($produto['frete_gratis']) && $produto['frete_gratis'] == 1): ?>
                                <span class="icone-frete-gratis" title="Frete grátis">🚚</span>
                            <?php endif; ?>
                            
                            <?php if (!empty($produto['promocao']) && $produto['promocao'] == 1): ?>
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
                        </p>                       
                        <h3 class="produto-nome">
                            <?php echo htmlspecialchars($produto['nome_produto'] ?? 'Produto não especificado', ENT_QUOTES, 'UTF-8'); ?>
                        </h3>

                        <!-- Preço do produto -->
                        <?php
                        $taxa_padrao = floatval($produto['taxa_padrao'] ?? 0);
                        $valor_base = isset($produto['promocao']) && $produto['promocao'] == 1 
                            ? floatval($produto['valor_promocao'] ?? 0) 
                            : floatval($produto['valor_produto'] ?? 0);  
                        $valor_produto = $produto['valor_venda_vista'] ?? 0;
                        ?>
                        <p class="produto-preco">R$ <?php echo number_format($valor_produto, 2, ',', '.'); ?></p>

                        <!-- Botão de edição -->
                        <?php if (isset($produto['produto_aprovado']) && $produto['produto_aprovado'] == 1): ?>
                            <a href="produtos/editar_produto.php?id_produto=<?php echo htmlspecialchars($produto['id_produto'], ENT_QUOTES, 'UTF-8'); ?>" class="button-editar">Editar</a>
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
            ?>
            <div class="container">
                <input id="inputPesquisaPromocao" class="input" type="text" placeholder="Pesquisar Produto.">
            </div>        

            <!-- Lista de promoções aqui -->
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
                    ?>
                    
                    <!-- Ícones de status do produto -->
                    <div class="produto-status">
                        <?php if (isset($produto['oculto']) && $produto['oculto'] == 1): ?>
                            <span class="icone-oculto" title="Produto oculto">👁️‍🗨️</span>
                        <?php endif; ?>
                        
                        <?php if (isset($produto['produto_aprovado']) && $produto['produto_aprovado'] != 1): ?>
                            <i class="fas fa-clock" title="Em análise"></i>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Imagem do produto -->
                    <img src="<?php echo htmlspecialchars($primeiraImagem, ENT_QUOTES, 'UTF-8'); ?>" alt="Imagem do Produto" class="produto-imagem">

                    <div class="produto-detalhes">
                        <p>
                            <!-- Ícones de promoção e frete grátis -->
                            <?php if (!empty($produto['frete_gratis']) && $produto['frete_gratis'] == 1): ?>
                                <span class="icone-frete-gratis" title="Frete grátis">🚚</span>
                            <?php endif; ?>
                            
                            <?php if (!empty($produto['promocao']) && $produto['promocao'] == 1): ?>
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
                        </p>                       
                        <h3 class="produto-nome">
                            <?php echo htmlspecialchars($produto['nome_produto'] ?? 'Produto não especificado', ENT_QUOTES, 'UTF-8'); ?>
                        </h3>

                        <!-- Preço do produto -->
                        <?php
                        $taxa_padrao = floatval($produto['taxa_padrao'] ?? 0);
                        $valor_base = isset($produto['promocao']) && $produto['promocao'] == 1 
                            ? floatval($produto['valor_promocao'] ?? 0) 
                            : floatval($produto['valor_produto'] ?? 0);  
                        $valor_produto = $produto['valor_venda_vista'] ?? 0;
                        ?>
                        <p class="produto-preco">R$ <?php echo number_format($valor_produto, 2, ',', '.'); ?></p>

                        <!-- Botão de edição -->
                        <?php if (isset($produto['produto_aprovado']) && $produto['produto_aprovado'] == 1): ?>
                            <a href="produtos/editar_produto.php?id_produto=<?php echo htmlspecialchars($produto['id_produto'], ENT_QUOTES, 'UTF-8'); ?>" class="button-editar">Editar</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <!-- Mensagem de produto não encontrado -->
            <p id="mensagemNaoEncontrado" style="display: none;">Produto não encontrado.</p>
            
            <?php else: ?>
                <p style="margin-top: 30px;">Nenhuma promoção disponível.</p>
            <?php endif; ?>
        </div>

        <div id="conteudo-frete_gratis" class="conteudo-aba" style="display: none;">
            <?php 
                if ($frete_gratis->num_rows > 0): 
            ?>            
            <div class="container">
                <input id="inputPesquisaFreteGratis" class="input" type="text" placeholder="Pesquisar Produto.">
            </div> 

            <!-- Lista de frete gratis -->
            <div class="lista-frete_gratis">
                <?php while ($produto = $frete_gratis->fetch_assoc()): ?>
                <div class="produto-item">
                    <?php
                    // Verifica e processa as imagens do produto
                    $primeiraImagem = '/default_image.jpg'; // Imagem padrão
                    if (!empty($produto['imagens'])) {
                        $imagensArray = explode(',', $produto['imagens']);
                        $primeiraImagem = 'produtos/img_produtos/' . $imagensArray[0];
                    }
                    ?>
                    
                    <!-- Ícones de status do produto -->
                    <div class="produto-status">
                        <?php if (isset($produto['oculto']) && $produto['oculto'] == 1): ?>
                            <span class="icone-oculto" title="Produto oculto">👁️‍🗨️</span>
                        <?php endif; ?>
                        
                        <?php if (isset($produto['produto_aprovado']) && $produto['produto_aprovado'] != 1): ?>
                            <i class="fas fa-clock" title="Em análise"></i>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Imagem do produto -->
                    <img src="<?php echo htmlspecialchars($primeiraImagem, ENT_QUOTES, 'UTF-8'); ?>" alt="Imagem do Produto" class="produto-imagem">

                    <div class="produto-detalhes">
                        <p>
                            <!-- Ícones de promoção e frete grátis -->
                            <?php if (!empty($produto['frete_gratis']) && $produto['frete_gratis'] == 1): ?>
                                <span class="icone-frete-gratis" title="Frete grátis">🚚</span>
                            <?php endif; ?>
                            
                            <?php if (!empty($produto['promocao']) && $produto['promocao'] == 1): ?>
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
                        </p>                       
                        <h3 class="produto-nome">
                            <?php echo htmlspecialchars($produto['nome_produto'] ?? 'Produto não especificado', ENT_QUOTES, 'UTF-8'); ?>
                        </h3>

                        <!-- Preço do produto -->
                        <?php
                        $taxa_padrao = floatval($produto['taxa_padrao'] ?? 0);
                        $valor_base = isset($produto['promocao']) && $produto['promocao'] == 1 
                            ? floatval($produto['valor_promocao'] ?? 0) 
                            : floatval($produto['valor_produto'] ?? 0);  
                        $valor_produto = $produto['valor_venda_vista'] ?? 0;
                        ?>
                        <p class="produto-preco">R$ <?php echo number_format($valor_produto, 2, ',', '.'); ?></p>

                        <!-- Botão de edição -->
                        <?php if (isset($produto['produto_aprovado']) && $produto['produto_aprovado'] == 1): ?>
                            <a href="produtos/editar_produto.php?id_produto=<?php echo htmlspecialchars($produto['id_produto'], ENT_QUOTES, 'UTF-8'); ?>" class="button-editar">Editar</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <!-- Mensagem de produto não encontrado -->
            <p id="mensagemNaoEncontrado" style="display: none;">Produto não encontrado.</p>
            
            <?php else: ?>
                <p style="margin-top: 30px;">Nenhuma produto disponível.</p>
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
                    ?>
                    
                    <!-- Ícones de status do produto -->
                    <div class="produto-status">
                        <?php if (isset($produto['oculto']) && $produto['oculto'] == 1): ?>
                            <span class="icone-oculto" title="Produto oculto">👁️‍🗨️</span>
                        <?php endif; ?>
                        
                        <?php if (isset($produto['produto_aprovado']) && $produto['produto_aprovado'] != 1): ?>
                            <i class="fas fa-clock" title="Em análise"></i>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Imagem do produto -->
                    <img src="<?php echo htmlspecialchars($primeiraImagem, ENT_QUOTES, 'UTF-8'); ?>" alt="Imagem do Produto" class="produto-imagem">

                    <div class="produto-detalhes">
                        <p>
                            <!-- Ícones de promoção e frete grátis -->
                            <?php if (!empty($produto['frete_gratis']) && $produto['frete_gratis'] == 1): ?>
                                <span class="icone-frete-gratis" title="Frete grátis">🚚</span>
                            <?php endif; ?>
                            
                            <?php if (!empty($produto['promocao']) && $produto['promocao'] == 1): ?>
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
                        </p>                       
                        <h3 class="produto-nome">
                            <?php echo htmlspecialchars($produto['nome_produto'] ?? 'Produto não especificado', ENT_QUOTES, 'UTF-8'); ?>
                        </h3>

                        <!-- Preço do produto -->
                        <?php
                        $taxa_padrao = floatval($produto['taxa_padrao'] ?? 0);
                        $valor_base = isset($produto['promocao']) && $produto['promocao'] == 1 
                            ? floatval($produto['valor_promocao'] ?? 0) 
                            : floatval($produto['valor_produto'] ?? 0);  
                        $valor_produto = $produto['valor_venda_vista'] ?? 0;
                        ?>
                        <p class="produto-preco">R$ <?php echo number_format($valor_produto, 2, ',', '.'); ?></p>

                        <!-- Botão de edição -->
                        <?php if (isset($produto['produto_aprovado']) && $produto['produto_aprovado'] == 1): ?>
                            <a href="produtos/editar_produto.php?id_produto=<?php echo htmlspecialchars($produto['id_produto'], ENT_QUOTES, 'UTF-8'); ?>" class="button-editar">Editar</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <!-- Mensagem de produto não encontrado -->
            <p id="mensagemNaoEncontrado" style="display: none;">Produto não encontrado.</p>
            
            <?php else: ?>
                <p style="margin-top: 30px;">Nenhuma promoção disponível.</p>
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
                    ?>
                    
                    <!-- Ícones de status do produto -->
                    <div class="produto-status">
                        <?php if (isset($produto['oculto']) && $produto['oculto'] == 1): ?>
                            <span class="icone-oculto" title="Produto oculto">👁️‍🗨️</span>
                        <?php endif; ?>
                        
                        <?php if (isset($produto['produto_aprovado']) && $produto['produto_aprovado'] != 1): ?>
                            <i class="fas fa-clock" title="Em análise"></i>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Imagem do produto -->
                    <img src="<?php echo htmlspecialchars($primeiraImagem, ENT_QUOTES, 'UTF-8'); ?>" alt="Imagem do Produto" class="produto-imagem">

                    <div class="produto-detalhes">
                        <p>
                            <!-- Ícones de promoção e frete grátis -->
                            <?php if (!empty($produto['frete_gratis']) && $produto['frete_gratis'] == 1): ?>
                                <span class="icone-frete-gratis" title="Frete grátis">🚚</span>
                            <?php endif; ?>
                            
                            <?php if (!empty($produto['promocao']) && $produto['promocao'] == 1): ?>
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
                        </p>                       
                        <h3 class="produto-nome">
                            <?php echo htmlspecialchars($produto['nome_produto'] ?? 'Produto não especificado', ENT_QUOTES, 'UTF-8'); ?>
                        </h3>

                        <!-- Preço do produto -->
                        <?php
                        $taxa_padrao = floatval($produto['taxa_padrao'] ?? 0);
                        $valor_base = isset($produto['promocao']) && $produto['promocao'] == 1 
                            ? floatval($produto['valor_promocao'] ?? 0) 
                            : floatval($produto['valor_produto'] ?? 0);  
                        $valor_produto = $produto['valor_venda_vista'] ?? 0;
                        ?>
                        <p class="produto-preco">R$ <?php echo number_format($valor_produto, 2, ',', '.'); ?></p>

                        <!-- Botão de edição -->
                        <?php if (isset($produto['produto_aprovado']) && $produto['produto_aprovado'] == 1): ?>
                            <a href="produtos/editar_produto.php?id_produto=<?php echo htmlspecialchars($produto['id_produto'], ENT_QUOTES, 'UTF-8'); ?>" class="button-editar">Editar</a>
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
            <li title="Pedidos"><i class="fas fa-box"></i></li> <!-- pedidos -->
            <li><a href="configuracoes.php?id_parceiro=<?php echo urlencode($id); ?>" title="Configurações"><i class="fas fa-cog"></i></a></li>
            <li><a href="parceiro_logout.php" title="Sair"><i class="fas fa-sign-out-alt"></i></a></li>
        </ul>
    </footer>

    <script src="parceiro_home.js"></script> 
    <script>
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
