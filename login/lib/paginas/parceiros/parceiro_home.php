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
    $promocoes = $mysqli->query(query: "SELECT * FROM produtos WHERE promocao = 1 AND id_loja = '$id'");
    $mais_vendidos = $mysqli->query(query: "SELECT * FROM produtos WHERE mais_vendidos = 1 AND id_loja = '$id'");
    $frete_gratis = $mysqli->query(query: "SELECT * FROM produtos WHERE frete_gratis = 1 AND id_loja = '$id'");

    // Consulta para obter o valor de not_inscr_parceiro da primeira linha
    $sql_query_not_par = "SELECT * FROM contador_notificacoes_parceiro WHERE id = 1";
    $result = $mysqli->query(query: $sql_query_not_par);
    $row = $result->fetch_assoc();
    $platafoma= $row['plataforma'] ?? 0; // Define 0 se não houver resultado
    $pedidos = $row['pedidos'] ?? 0; // Define 0 se não houver resultado


    // Soma todos os valores de notificações
    $total_notificacoes = $platafoma + $pedidos;
    //echo $total_notificacoes; 
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
        .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 10px;
            /*margin-top: -30px;*/
        }

        .titulo {
            font-size: 20px;
            font-weight: bold;
        }

        .input {
            width: 60%;
            padding: 5px;
            font-size: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            text-align: left;
        }

        .form {
            margin: 0;
        }
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

            <div class="tab" onclick="mostrarConteudo('vendidos',this)">
                <span>Mais Vendidos</span>
            </div>

            <div class="tab" onclick="mostrarConteudo('frete',this)">
                <span>Frete Grátis</span>
            </div>

        </div>

        <!-- Conteúdos correspondentes às abas -->
        <div id="conteudo-catalogo" class="conteudo-aba" style="display: block;">
            <div class="container">
                <?php if ($produtos_catalogo->num_rows > 0): ?>
                    <span class="titulo">Catálogo de Produtos</span>
                    <input class="input" type="text" placeholder="Pesquizar Produto.">
                    <form method="POST" action="produtos/adicionar_produto.php" class="catalogo-form">
                        <input type="hidden" name="id_parceiro" value="<?php echo $id; ?>">
                        <button class="button">Cadastrar produto</button>    
                    </form>
            </div>
            <!-- Lista de produtos aqui -->
            <div class="lista-produtos">
                <?php while ($produto = $produtos_catalogo->fetch_assoc()): ?>
                    <div class="produto-item">

                        <?php
                            // Verifica se o campo 'imagens' está definido e não está vazio
                            if (isset($produto['img1']) && !empty($produto['img1'])) {
                                // Divide a string de imagens em um array, assumindo que as imagens estão separadas por virgula
                                $imagensArray = explode(separator: ',', string: $produto['img1']);
                                
                                // Pega a primeira imagem do array
                                $primeiraImagem = $imagensArray[0];
                                //echo $primeiraImagem;
                                // Exibe a primeira imagem
                                ?>
                                <img src="produtos/<?php echo $primeiraImagem; ?>" alt="pro" class="produto-imagem">
                                <?php
                            } else {
                                // Caso não haja imagens, exibe uma imagem padrão
                                ?>
                                <img src="/default_image.jpg" alt="Imagem Padrão" class="produto-imagem">
                                <?php
                            }
                        ?>
                        <div class="produto-detalhes">
                            <h3 class="produto-nome"><?php echo $produto['nome_produto']; ?></h3>
                            <p class="produto-descricao"><?php echo $produto['descricao_produto']; ?></p>

                            <!-- Converte o valor do produto para float e formata -->
                                
                            <?php
                                $valor_produto = str_replace(search: ',', replace: '.', subject: $produto['valor_produto']);
                                $valor_produto = floatval(value: $valor_produto);
                            ?>
                            <p class="produto-preco">R$ <?php echo number_format(num: $valor_produto, decimals: 2, decimal_separator: ',', thousands_separator: '.'); ?></p>

                            <a href="produtos/editar_produto.php?id=<?php echo $produto['id_produto']; ?>" class="button-editar">Editar</a>
                        </div>
                    </div>

                <?php endwhile; ?>
            </div>

            <?php else: ?>
                <a href="produtos/adicionar_produto.php">
                    <form method="POST" action="produtos/adicionar_produto.php">
                        <input type="hidden" name="id_parceiro" value="<?php echo $id; ?>">
                        <p>Nenhuma produto cadastrado ainda!.</p>
                        <button class="button">Inclua seu primeiro produto</button>
                    </form>
                </a>
            <?php endif; ?>                               
        </div>
        
        <div id="conteudo-promocoes" class="conteudo-aba" style="display: none;">
            <div class="container">
                <?php if ($promocoes->num_rows > 0): ?>
                    <span class="titulo">Promoções</span>
                    <input class="input" type="text" placeholder="Pesquizar Produto.">
                    <!-- Lista de promoções aqui -->
                <?php else: ?>
                    <p>Nenhuma promoção disponível.</p>
                <?php endif; ?>
            </div>
        </div>

        <div id="conteudo-vendidos" class="conteudo-aba" style="display: none;">
            <div class="container">
                <?php if ($vendidos->num_rows > 0): ?>
                    <span class="titulo">+ vendidos</span>
                    <input class="input" type="text" placeholder="Pesquizar Produto.">
                    <!-- Lista de frete grátis aqui -->
                <?php else: ?>
                    <p>Nenhum produto na categoria "Mais Vendidos".</p>
                <?php endif; ?>
            </div>
        </div>

        <div id="conteudo-frete" class="conteudo-aba" style="display: none;">
            <div class="container">
                <?php if ($frete_gratis->num_rows > 0): ?>
                    <span class="titulo">Frete Grátis</span>
                    <input class="input" type="text" placeholder="Pesquizar Produto.">
                    <!-- Lista de frete grátis aqui -->
                <?php else: ?>
                    <p>Nenhum produto com frete grátis disponível.</p>
                <?php endif; ?>
            </div>
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

    </script>

</body>
</html>
