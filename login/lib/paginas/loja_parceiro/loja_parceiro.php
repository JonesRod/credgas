<?php

include('../../conexao.php');

if(!isset($_SESSION)) {
    session_start();
}

if (isset($_GET['id'])) {
    $idParceiro = intval($_GET['id']);

    // Consulta para buscar os dados do parceiro
    $sql = "SELECT * FROM meus_parceiros WHERE id = $idParceiro AND status = 'ATIVO' AND aberto_fechado = 'Aberto'";
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
}

    // Consulta para buscar produtos do catálogo
    $produtos_catalogo = $mysqli->query(query: "SELECT * FROM produtos 
    WHERE id_parceiro = '$idParceiro' AND oculto != 'sim' AND produto_aprovado = 'sim'") or die($mysqli->error);

    // Verifica se existem promoções, mais vendidos e frete grátis
    // Supondo que já exista uma conexão com o banco de dados ($mysqli)
    //$id_parceiro_sessao = $id;
    $promocoes =  $mysqli->query("SELECT * FROM produtos 
    WHERE id_parceiro = '$idParceiro' AND promocao = 'sim' AND oculto != 'sim' AND produto_aprovado = 'sim'") or die($mysqli->error);

    //$mais_vendidos = $mysqli->query(query: "SELECT * FROM produtos WHERE mais_vendidos = 1 AND id_parceiro = '$id'");
    $frete_gratis = $mysqli->query(query: "SELECT * FROM produtos WHERE 
        (id_parceiro = '$idParceiro' AND promocao = 'sim' AND frete_gratis_promocao = 'sim') 
        OR 
        (id_parceiro = '$idParceiro' AND promocao = 'nao' AND frete_gratis = 'sim')
    ") or die($mysqli->error);
    //echo "<p>Produtos ocultos encontrados: " . $frete_gratis->num_rows . "</p>";

    // Consulta para obter o valor de not_inscr_parceiro da primeira linha
    $sql_query_not_par = "SELECT * FROM contador_notificacoes_parceiro WHERE id_parceiro = $idParceiro";
    $result = $mysqli->query(query: $sql_query_not_par);
    $row = $result->fetch_assoc();
    $platafoma= $row['plataforma'] ?? 0; // Define 0 se não houver resultado
    $not_novo_produto= $row['not_novo_produto'] ?? 0;
    $not_adicao_produto= $row['not_adicao_produto'] ?? 0; // Define 0 se não houver resultado
    $pedidos = $row['pedidos'] ?? 0; // Define 0 se não houver resultado


    // Soma todos os valores de notificações
    $total_notificacoes = $not_novo_produto + $not_adicao_produto + $pedidos;

    //$produtos_novidades = $mysqli->query("SELECT * FROM produtos WHERE id_parceiro = '$idParceiro' AND oculto != 'sim' AND produto_aprovado = 'sim'") or die($mysqli->error);
    $produtos_novidades = $mysqli->query("SELECT *, DATEDIFF(NOW(), data) AS dias_desde_cadastro 
    FROM produtos 
    WHERE id_parceiro = $idParceiro 
    AND oculto != 'sim' 
    AND produto_aprovado = 'sim'") or die($mysqli->error);

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

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $parceiro['nomeFantasia']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="loja_parceiro_home.css">
    <script src="loja_parceiro_home.js"></script> 
<style>

</style>
</head>
<body>

    <!-- Header -->
    <header>
        <div class="logo">
            <img src="<?php echo $logo; ?>" alt="Logo da Loja" class="logo-img">
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

    <!-- Painel de notificações que aparece ao clicar no ícone de notificações -->
    <aside id="painel-notificacoes">
        <h2>Notificações: <?php echo htmlspecialchars(string: $total_notificacoes); ?></h2>
        <ul id="lista-notificacoes">
            <li onclick="abrirNotificacao(1)">Novo Produtos: <?php echo $not_novo_produto; ?></li>
            <li onclick="abrirNotificacao(2)">Edição de Produtos: <?php echo $not_adicao_produto; ?></li>
            <li onclick="abrirNotificacao(3)">Pedidos: <?php echo $pedidos; ?></li>

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
                    ?>
                    <div class="categoria-item <?php echo $categoriaNome === 'Alimenticios' ? 'selected' : ''; ?>" data-categoria="<?php echo $categoriaNome; ?>">
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

    <form id="formCategoria" method="POST" action="">
        <input type="text" name="categoria_selecionada">
        <button type="submit" style="display: none;"></button>
    </form>

        <?php
        
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (isset($_POST['categoria_selecionada'])) {
                    $categoriaSelecionada = $_POST['categoria_selecionada'];

                    // Realizar a consulta com a categoria selecionada
                    $produtos_catalogo = $mysqli->query("SELECT * FROM produtos 
                    WHERE id_parceiro = '$idParceiro' 
                    AND categoria = '$categoriaSelecionada' 
                    AND oculto != 'sim' 
                    AND produto_aprovado = 'sim'") or die($mysqli->error);
                    //$produtos_catalogo = $mysqli->query($sql);

                    /*$promocoes = $mysqli->query("SELECT * FROM produtos 
                    WHERE id_parceiro = '$idParceiro' 
                    AND categoria = '$categoriaSelecionada' 
                    AND promocao = 'sim' 
                    AND oculto != 'sim' 
                    AND produto_aprovado = 'sim'") or die($mysqli->error);*/

                    $frete_gratis = $mysqli->query(query: "SELECT * FROM produtos 
                    WHERE (id_parceiro = '$idParceiro' 
                    AND categoria = '$categoriaSelecionada'  
                    AND promocao = 'sim' 
                    AND frete_gratis_promocao = 'sim') 
                    OR (id_parceiro = '$idParceiro' 
                    AND promocao = 'nao' 
                    AND frete_gratis = 'sim')") or die($mysqli->error);
                } else {
                    echo "Nenhuma categoria foi selecionada.";
                }
            }
        ?>
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
            <?php 
                if ($produtos_catalogo->num_rows > 0): 
            ?>            
            <div class="container">
                <input id="inputPesquisaCatalogo" class="input" type="text" placeholder="Pesquisar Produto.">

                <form method="POST" action="produtos/adicionar_produto.php" class="catalogo-form">
                    <input type="hidden" name="id_parceiro" value="<?php echo $idParceiro; ?>">
                    <button class="button">Cadastrar produto</button>    
                </form>
            </div>

            <!-- Lista de produtos aqui -->
            <div class="lista-produtos">
                <?php 
                    while ($produto = $produtos_catalogo->fetch_assoc()): 
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
                        <?php echo $produto['nome_produto']; ?>
                    </h3>
                        
                    <p class="produto-descricao"><?php echo $produto['descricao_produto']; ?></p>

                    <!-- Converte o valor do produto para float e formata -->
                    <?php
                        $valor_produto = str_replace(',', '.', $produto['valor_produto_taxa']);
                        $valor_produto = floatval($valor_produto);
                    ?>

                    <p class="produto-preco">R$ <?php echo number_format($valor_produto, 2, ',', '.'); ?></p>

                    <a href="produtos/editar_produto.php?id_produto=<?php echo $produto['id_produto']; ?>" class="button-editar">Editar</a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <!-- Mensagem de produto não encontrado -->
            <p id="mensagemNaoEncontradoCatalogo" style="display: none;">Nenhum produto encontrado no catálogo.</p>

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
                    <div class="produto-item promocao">
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
                                ?>
                                <?php echo $produto['nome_produto']; ?>
                            </h3>

                            <p class="produto-descricao"><?php echo $produto['descricao_produto']; ?></p>

                            <?php
                                // Formatação do valor promocional
                                $valor_produto_promocao = floatval(str_replace(',', '.', $produto['valor_produto_taxa']));
                            ?>
                            <p class="produto-preco">R$ <?php echo number_format($valor_produto_promocao, 2, ',', '.'); ?></p>
                            <a href="produtos/editar_produto.php?id_produto=<?php echo $produto['id_produto']; ?>" class="button-editar">Editar</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Mensagem de produto não encontrado -->
            <p id="mensagemNaoEncontradoPromocao" style="display: none;">Nenhum produto encontrado em promoção.</p>
            
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

            <!-- Lista de promoções aqui -->
            <div class="lista-promocoes">
                <?php while ($produto = $frete_gratis->fetch_assoc()): ?>
                    <div class="produto-item frete-gratis">
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
                                <?php echo $produto['nome_produto']; ?>
                            </h3>

                            <p class="produto-descricao"><?php echo $produto['descricao_produto']; ?></p>

                            <?php
                                // Formatação do valor promocional
                                $valor_produto_promocao = floatval(str_replace(',', '.', $produto['valor_produto_taxa']));
                            ?>
                            <p class="produto-preco">R$ <?php echo number_format($valor_produto_promocao, 2, ',', '.'); ?></p>
                            <a href="produtos/editar_produto.php?id_produto=<?php echo $produto['id_produto']; ?>" class="button-editar">Editar</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Mensagem de produto não encontrado -->
            <p id="mensagemNaoEncontradoFreteGratis" style="display: none;">Nenhum produto encontrado com frete grátis.</p>
            
            <?php else: ?>
                <p style="margin-top: 30px;">Nenhuma promoção disponível.</p>
            <?php endif; ?>
        </div>

        <div id="conteudo-novidades" class="conteudo-aba" style="display: none;">
            <?php 
                if ($produtos_novidades->num_rows > 0): 
            ?>            
            <div class="container">
                <input id="inputPesquisaNovidades" class="input" type="text" placeholder="Pesquisar Produto.">
            </div>        

            <!-- Lista de promoções aqui -->
            <div class="lista-novidades">
                <?php while ($produto = $produtos_novidades->fetch_assoc()): ?>
                    <?php
                    // Verifica se o produto foi cadastrado há 30 dias ou menos
                    if ($produto['dias_desde_cadastro'] <= 30): ?>
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
                                <?php echo $produto['nome_produto']; ?>
                            </h3>

                            <p class="produto-descricao"><?php echo $produto['descricao_produto']; ?></p>

                            <?php
                                // Formatação do valor promocional
                                $valor_produto_promocao = floatval(str_replace(',', '.', $produto['valor_produto_taxa']));
                            ?>
                            <p class="produto-preco">R$ <?php echo number_format($valor_produto_promocao, 2, ',', '.'); ?></p>
                            <a href="produtos/editar_produto.php?id_produto=<?php echo $produto['id_produto']; ?>" class="button-editar">Editar</a>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endwhile; ?>
            </div>

            <!-- Mensagem de produto não encontrado -->
            <p id="mensagemNaoEncontradoNovidades" style="display: none;">Nenhum produto encontrado em novidades.</p>
            
            <?php else: ?>
                <p style="margin-top: 30px;">Nenhuma promoção disponível.</p>
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
    <script src="parceiro_home.js"></script> 
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

        // Referencia todos os campos de pesquisa
        const camposPesquisa = [
            document.getElementById('inputPesquisaCatalogo'),
            document.getElementById('inputPesquisaPromocao'),
            document.getElementById('inputPesquisaFreteGratis'),
            document.getElementById('inputPesquisaNovidades')
        ];

        // Função que sincroniza os valores dos campos e executa a pesquisa por categoria
        function sincronizarPesquisa(origem) {
            const termoPesquisa = origem.value.toLowerCase();

            // Atualiza todos os campos de pesquisa com o mesmo valor
            camposPesquisa.forEach(campo => {
                if (campo !== origem) {
                    campo.value = origem.value;
                }
            });

            // Executa a lógica de pesquisa em cada categoria separadamente
            const categorias = [
                { produtos: document.querySelectorAll('.produto-item.catalogo'), mensagem: 'mensagemNaoEncontradoCatalogo' },
                { produtos: document.querySelectorAll('.produto-item.promocao'), mensagem: 'mensagemNaoEncontradoPromocao' },
                { produtos: document.querySelectorAll('.produto-item.frete-gratis'), mensagem: 'mensagemNaoEncontradoFreteGratis' },
                { produtos: document.querySelectorAll('.produto-item.novidades'), mensagem: 'mensagemNaoEncontradoNovidades' }
            ];

            categorias.forEach(categoria => {
                let produtoEncontrado = false;

                categoria.produtos.forEach(produto => {
                    const nomeProduto = produto.querySelector('.produto-nome').textContent.toLowerCase();

                    if (nomeProduto.includes(termoPesquisa) || termoPesquisa === '') {
                        produto.style.display = 'block';
                        produtoEncontrado = true;
                    } else {
                        produto.style.display = 'none';
                    }
                });

                // Exibe ou oculta a mensagem de "Produto não encontrado" por categoria
                const mensagemNaoEncontrado = document.getElementById(categoria.mensagem);
                if (mensagemNaoEncontrado) {
                    mensagemNaoEncontrado.style.display = produtoEncontrado ? 'none' : 'block';
                }
            });
        }

        // Adiciona o evento de entrada para todos os campos
        camposPesquisa.forEach(campo => {
            campo.addEventListener('input', function () {
                sincronizarPesquisa(this);
            });
        });


        document.addEventListener('DOMContentLoaded', () => {
            const categorias = document.querySelectorAll('.categoria-item');
            const inputCategoria = document.querySelector('input[name="categoria_selecionada"]'); // Campo hidden
            const formCategoria = document.querySelector('#formCategoria'); // Formulário

            // Atribuir valor inicial ao campo categoria_selecionada
            const alimenticios = Array.from(categorias).find(categoria => 
                categoria.querySelector('p').textContent.trim() === 'Alimenticios'
            );

            if (alimenticios) {
                categorias.forEach(categoria => categoria.classList.remove('selected'));
                alimenticios.classList.add('selected');
                inputCategoria.value = 'Alimenticios';
            }

            categorias.forEach(categoria => {
                categoria.addEventListener('click', () => {
                    categorias.forEach(cat => cat.classList.remove('selected'));
                    categoria.classList.add('selected');
                    inputCategoria.value = categoria.querySelector('p').textContent.trim();
                    enviarFormularioAjax(); // Enviar via AJAX
                    
                });
            });
        });

        // Função para enviar o formulário via AJAX
        function enviarFormularioAjax() {
            const formData = new FormData(formCategoria); // Cria FormData com os dados do formulário
            console.log('Resposta do servidor:', data); 
            fetch('', { // A URL vazia significa que o próprio arquivo será o destino
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                console.log('Resposta do servidor:', data); // Exibe a resposta do servidor no console
                // Aqui você pode atualizar a página ou outras partes do DOM
            })
            .catch(error => console.error('Erro ao enviar formulário via AJAX:', error));
            console.log('Resposta do servidor:', data); 
        }
        


        // Define que a aba "catalogo" está ativa ao carregar a página
        window.onload = function() {
            mostrarConteudo('catalogo', document.querySelector('.tab.active'));
        };


        document.addEventListener("DOMContentLoaded", function () {
            const categoriaItems = document.querySelectorAll(".categoria-item");

            categoriaItems.forEach((item) => {
                item.addEventListener("click", function () {
                    // Remove a classe 'selected' de todos os itens
                    categoriaItems.forEach((el) => el.classList.remove("selected"));

                    // Adiciona a classe 'selected' ao item clicado
                    item.classList.add("selected");

                    // Pegue o nome da categoria
                    const categoriaSelecionada = item.getAttribute("data-categoria");
                    console.log("Categoria selecionada:", categoriaSelecionada);
                    
                    // Aqui você pode adicionar lógica adicional, como atualizar produtos da categoria na página.
                });
            });
            enviarFormularioAjax(); // Enviar via AJAX
        });

   
    </script>

</body>
</html>

