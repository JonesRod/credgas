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
            $logo = $parceiro['logo'];
            if($logo === ''){
                $logo = '../arquivos_fixos/icone_loja.jpg';
            } else {
                $logo = '../arquivos_fixos/'. $logo;
            }
        }
    } else {
        session_unset();
        session_destroy(); 
        header("Location: ../../../../index.php");
        exit();
    }

    // Consulta para buscar produtos do catálogo
    $produtos_catalogo = $mysqli->query(query: "SELECT * FROM produtos WHERE id_parceiro = '$id'") or die($mysqli->error);

    // Verifica se existem promoções, mais vendidos e frete grátis
    // Supondo que já exista uma conexão com o banco de dados ($mysqli)
    //$id_parceiro_sessao = $id;
    $queryPromocoes = "SELECT * FROM produtos WHERE promocao = 'sim' AND id_parceiro = $id";
    $promocoes = $mysqli->query($queryPromocoes);

    //$mais_vendidos = $mysqli->query(query: "SELECT * FROM produtos WHERE mais_vendidos = 1 AND id_parceiro = '$id'");
    $frete_gratis = $mysqli->query(query: "SELECT * FROM produtos WHERE 
            (promocao = 'sim' AND frete_gratis_promocao = 'sim' AND id_parceiro = '$id') 
            OR 
            (promocao = 'nao' AND frete_gratis = 'sim' AND id_parceiro = '$id')
    ");


    // Consulta para obter o valor de not_inscr_parceiro da primeira linha
    $sql_query_not_par = "SELECT * FROM contador_notificacoes_parceiro WHERE id = 1";
    $result = $mysqli->query(query: $sql_query_not_par);
    $row = $result->fetch_assoc();
    $platafoma= $row['plataforma'] ?? 0; // Define 0 se não houver resultado
    $pedidos = $row['pedidos'] ?? 0; // Define 0 se não houver resultado


    // Soma todos os valores de notificações
    $total_notificacoes = $platafoma + $pedidos;
    //echo $total_notificacoes; 

    //Consulta para buscar produtos ocultos do catálogo
    $produtos_ocultos = $mysqli->query("SELECT * FROM produtos WHERE id_parceiro = '$id' AND oculto = 'sim'") or die($mysqli->error);
    
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
    <link rel="stylesheet" href="parceiro_home.css">
    <script src="parceiro_home.js"></script> 
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
            <li onclick="abrirNotificacao(1)">Plataforma: <?php echo $platafoma; ?></li>
            <li onclick="abrirNotificacao(2)">Pedidos: <?php echo $pedidos; ?></li>
        </ul>
    </aside>

    <!-- Menu lateral que aparece abaixo do ícone de menu -->
    <aside id="menu-lateral" >
        <ul>
            <li><a href="perfil_loja.php"><i class="fas fa-user"></i> Perfil da Loja</a></li>
            <li><a href="configuracoes.php"><i class="fas fa-cog"></i> Configurações</a></li>
            <li><a href="parceiro_logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
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
                <span>Promoções</span>
            </div>

            <div class="tab" onclick="mostrarConteudo('frete_gratis',this)">
                <span>Frete Grátis</span>
            </div>

            <div class="tab" onclick="mostrarConteudo('produtos_ocultos',this)">
                <span>Produtos Ocultos</span>
            </div>

        </div>

        <!-- Conteúdos correspondentes às abas -->
        <div id="conteudo-catalogo" class="conteudo-aba" style="display: block;">
            <div class="container">
                <?php 
                    if ($produtos_catalogo->num_rows > 0): 
                ?>
                <input id="inputPesquisaCatalogo" class="input" type="text" placeholder="Pesquisar Produto.">

                <form method="POST" action="produtos/adicionar_produto.php" class="catalogo-form">
                    <input type="hidden" name="id_parceiro" value="<?php echo $id; ?>">
                    <button class="button">Cadastrar produto</button>    
                </form>
            </div>

            <!-- Lista de produtos aqui -->
            <div class="lista-produtos">
                <?php 
                    while ($produto = $produtos_catalogo->fetch_assoc()): 
                ?>
                <div class="produto-item">
                    <?php
                        // Verifica se o campo 'imagens' está definido e não está vazio
                        if (isset($produto['imagens']) && !empty($produto['imagens'])) {
                            // Divide a string de imagens em um array, assumindo que as imagens estão separadas por virgula
                            $imagensArray = explode(',', $produto['imagens']);
                            
                            // Pega a primeira imagem do array
                            $primeiraImagem = $imagensArray[0];
                            // Exibe a primeira imagem
                            ?>
                            <?php 
                                // Exibe o ícone de oculto, se o produto estiver oculto
                                if ($produto['oculto'] === 'sim'): 
                            ?>
                                <span class="icone-oculto" title="Produto oculto">👁️‍🗨️</span>
                            <?php endif; ?>

                            <img src="produtos/img_produtos/<?php echo $primeiraImagem; ?>" alt="Imagem do Produto" class="produto-imagem">
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
            <p id="mensagemNaoEncontradoCatalogo" style="display: none;">Produto não encontrado.</p>

        </div>

        <?php else: ?>
            <div class="conteudo">
                <form method="POST" action="produtos/adicionar_produto.php">
                    <input type="hidden" name="id_parceiro" value="<?php echo $id; ?>">
                    <p>Nenhuma produto cadastrado ainda!.</p>
                    <button class="button">Inclua seu primeiro produto</button>
                </form>
            </div>    
            <?php endif; ?>                               
        </div>
        
        <div id="conteudo-promocoes" class="conteudo-aba" style="display: none;">
            <div class="container">
                <?php 
                    if ($promocoes->num_rows > 0): 
                ?>
                <input id="inputPesquisaPromocao" class="input" type="text" placeholder="Pesquisar Produto.">
            </div>        

            <!-- Lista de promoções aqui -->
            <div class="lista-promocoes">
                <?php while ($produto = $promocoes->fetch_assoc()): ?>
                    <div class="produto-item">
                        <?php
                            // Exibe a imagem do produto, caso haja uma
                            $imagensArray = explode(',', $produto['imagens']);
                            $primeiraImagem = !empty($imagensArray[0]) ? $imagensArray[0] : 'default_image.jpg';
                        ?>
                        <?php 
                            // Exibe o ícone de oculto, se o produto estiver oculto
                            if ($produto['oculto'] === 'sim'): 
                        ?>
                            <span class="icone-oculto" title="Produto oculto">👁️‍🗨️</span>
                        <?php endif; ?>
                        <img src="produtos/img_produtos/<?php echo $primeiraImagem; ?>" alt="Imagem do Produto" class="produto-imagem">

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
            <p id="mensagemNaoEncontrado" style="display: none;">Produto não encontrado.</p>
            
            <?php else: ?>
                <p>Nenhuma promoção disponível.</p>
            <?php endif; ?>

        </div>

        <div id="conteudo-frete_gratis" class="conteudo-aba" style="display: none;">
            <div class="container">
                <?php 
                    if ($frete_gratis->num_rows > 0): 
                ?>
                <input id="inputPesquisafreteGratis" class="input" type="text" placeholder="Pesquisar Produto.">
            </div> 

            <!-- Lista de promoções aqui -->
            <div class="lista-frete_gratis">
                <?php while ($produto = $frete_gratis->fetch_assoc()): ?>
                    <div class="produto-item">
                        <?php
                            // Exibe a imagem do produto, caso haja uma
                            $imagensArray = explode(',', $produto['imagens']);
                            $primeiraImagem = !empty($imagensArray[0]) ? $imagensArray[0] : 'default_image.jpg';
                        ?>
                        <?php 
                            // Exibe o ícone de oculto, se o produto estiver oculto
                            if ($produto['oculto'] === 'sim'): 
                        ?>
                            <span class="icone-oculto" title="Produto oculto">👁️‍🗨️</span>
                        <?php endif; ?>                        
                        <img src="produtos/img_produtos/<?php echo $primeiraImagem; ?>" alt="Imagem do Produto" class="produto-imagem">

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
            <p id="mensagemNaoEncontrado" style="display: none;">Produto não encontrado.</p>
            
            <?php else: ?>
                <p>Nenhum produto com o frete Grátis disponível.</p>
            <?php endif; ?>
        </div>

        <div id="conteudo-produtos_ocultos" class="conteudo-aba" style="display: none;">
            <div class="container">
                <?php 
                    // Verifica se há produtos ocultos
                    if ($produtos_ocultos->num_rows > 0): 
                        //echo $produtos_ocultos->num_rows;
                ?>
                    <input id="inputPesquisaProdutosOcultos" class="input" type="text" placeholder="Pesquisar Produto.">
                </div> 

                <!-- Lista de produtos ocultos aqui -->
                <div class="lista-produtos_ocultos">
                    <?php while ($produto = $produtos_ocultos->fetch_assoc()): ?>
                        <div class="produto-item">
                            <?php
                                // Exibe a imagem do produto, caso haja uma
                                $imagensArray = explode(',', $produto['imagens']);
                                $primeiraImagem = !empty($imagensArray[0]) ? $imagensArray[0] : 'default_image.jpg';
                            ?>
                            <?php 
                                // Exibe o ícone de oculto, se o produto estiver oculto
                                if ($produto['oculto'] === 'sim'): 
                            ?>
                            <span class="icone-oculto" title="Produto oculto">👁️‍🗨️</span>
                            <?php endif; ?>
                            <img src="produtos/img_produtos/<?php echo $primeiraImagem; ?>" alt="Imagem do Produto" class="produto-imagem">

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
                <p id="mensagemNaoEncontrado" style="display: none;">Produto não encontrado.</p>
                
                <?php else: ?>
                    <p>Nenhum Produto Oculto.</p>
                <?php endif; ?>
        </div>

    </main>

    <footer class="menu-mobile">
        <ul>
            <li><a href="admin_home.php"><i class="fas fa-home"></i></a></li>
            <li><i class="fas fa-user"></i></li>
            <li><i class="fas fa-box"></i></li> <!-- pedidos -->
            <li><i class="fas fa-cog"></i></li>
            <li><a href="parceiro_logout.php"><i class="fas fa-sign-out-alt"></i></a></li>
        </ul>
    </footer>
    <script src="parceiro_home.js"></script> 
    <script>
        // Obtém o ID da sessão do PHP
        var sessionId = <?php echo $id; ?>;

        function abrirNotificacao(id) {
            
            // Redireciona para a página de detalhes com o ID da notificação e o ID da sessão
            var url = `detalhes_notificacao.php?id=${id}&session_id=${sessionId}`;
            //console.log("Redirecionando para:", url);
            
            // Verifica se a URL está correta antes de redirecionar
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
        }

        // Chama a função pela primeira vez
        fetchNotifications();

        // Configura um intervalo para chamar a função a cada 5 segundos (5000 milissegundos)
        setInterval(fetchNotifications, 5000);

        ///pesquizador de produto no catalogo
        document.getElementById('inputPesquisaCatalogo').addEventListener('input', function() {
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
        });


        ///pesquizador de produto na promoção
        document.getElementById('inputPesquisaPromocao').addEventListener('input', function() {
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
            const mensagemNaoEncontrado = document.getElementById('mensagemNaoEncontrado');
            mensagemNaoEncontrado.style.display = produtoEncontrado ? 'none' : 'block';
        });

        ///pesquizador de produto na promoção
        document.getElementById('inputPesquisaProdutosOcultos').addEventListener('input', function() {
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
            const mensagemNaoEncontrado = document.getElementById('mensagemNaoEncontrado');
            mensagemNaoEncontrado.style.display = produtoEncontrado ? 'none' : 'block';
        });
        
    </script>

</body>
</html>
