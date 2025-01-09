<?php
    include('../../conexao.php');

    if(!isset($_SESSION)){
        session_start(); 

    }
        if($_SERVER["REQUEST_METHOD"] === "POST") {  

            if (isset($_POST["tipoLogin"])) {

                if(isset($_SESSION['id'])){ 

                    // Obter o valor do input radio
                    $usuario = $_SESSION['id'];
                    $valorSelecionado = $_POST["tipoLogin"];
                    $admin = $valorSelecionado;

                    if($admin == 0){

                        header(header: "Location: ../clientes/cliente_home.php"); 

                    }else if($admin == 1){

                        $usuario = $_SESSION['id'];
                        $admin = $_SESSION['admin'];
                        $_SESSION['id'];
                        $_SESSION['admin'];  
                        
                        $id = $_SESSION['id'];
                        //echo ('').$id;
                        $sql_query = $mysqli->query("SELECT * FROM config_admin WHERE razao != '' ORDER BY data_alteracao DESC LIMIT 1") or die($mysqli->error);
                    
                        $usuario = $sql_query->fetch_assoc();
                        
                        // Exemplo de como acessar a última alteração
                        $data_alteracao = $usuario['data_alteracao']; // Certifique-se de que a coluna existe no BD
                        //echo "Última alteração: $data_alteracao";

                        //$logo = $dadosEscolhido['logo'];
                        if(isset($usuario['logo'])) {
                            $logo = $usuario['logo'];
                            
                            if($logo == ''){
                                $logo = '../arquivos_fixos/imagem_credgas.jpg';
                            }else{
                                $logo = 'arquivos/'. $logo;
                                //echo $logo;
                            }
                        }

                    }else{
                        //echo '8';
                        session_unset();
                        session_destroy();
                        header(header: "Location: ../../../../index.php"); 
                    }
                }else{

                    session_unset();
                    session_destroy();
                    header(header: "Location: ../../../../index.php"); 
                }    
            }else{

                session_unset();
                session_destroy();
                header(header: "Location: ../../../../index.php"); 
            }  
        }else if(isset($_SESSION['id'])){    

            $usuario = $_SESSION['id'];
            $admin = $_SESSION['admin'];
            $_SESSION['id'];
            $_SESSION['admin'];  
    
            $id = $_SESSION['id'];
            
            //echo ('').$id;
            $sql_query = $mysqli->query("SELECT * FROM config_admin WHERE razao != '' ORDER BY data_alteracao DESC LIMIT 1") or die($mysqli->error);
        
            $usuario = $sql_query->fetch_assoc();
            
            // Exemplo de como acessar a última alteração
            $data_alteracao = $usuario['data_alteracao']; // Certifique-se de que a coluna existe no BD
            //echo "Última alteração: $data_alteracao";
        
            
            //$logo = $dadosEscolhido['logo'];
            if(isset($usuario['logo'])) {
                $logo = $usuario['logo'];
                
                if($logo == ''){
                    $logo = '../arquivos_fixos/imagem_credgas.jpg';
                }else{
                    $logo = 'arquivos/'. $logo;
                    //echo $logo;
                }
            }
    
        }else{
            session_unset();
            session_destroy();
            header(header: "Location: ../../../../index.php"); 
        }

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
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo</title>
    <link rel="stylesheet" href="admin_home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="admin_home.js?v=<?php echo time(); ?>"></script><!--força a tualização-->
        <style>
            .opcoes-gerenciamento{
                background-color: #d3d0ce;
                display: flex;
                justify-content: center;
                align-items: center;
                gap: 10px;
                margin-top: 0px;
                padding: auto;
            }
            #conteudo-parceiros, #conteudo-produtos{
                background-color: #fff;
            }

            /* Estilização da tabela de parceiros e produtos */
            .tabela-parceiros, .tabela-produtos {
                width: 100%;
                border-collapse: collapse;
                border-radius: 8px;
                background-color: #fff;
                margin: 0; /* Remove as margens */
                padding: 0; /* Remove qualquer padding interno */
            }
            /* Ajuste para as células da tabela */
            .tabela-parceiros th, .tabela-produtos th,
            .tabela-parceiros td, .tabela-produtos td {
                padding: 12px;
                text-align: left;
                border-bottom: 1px solid #ddd;
            }
            

            .tabela-parceiros th, .tabela-produtos th {
                background-color: #f4f4f4;
                font-weight: bold;
                border-radius: 8px;
            }

            .tabela-parceiros img.logo-parceiro {
                width: 50px;
                height: 50px;
                border-radius: 50%;
                object-fit: cover;
            }

            .tabela-parceiros .detalhes-link, .tabela-produtos .detalhes-link {
                color: #007bff;
                text-decoration: none;
                font-weight: bold;
            }

            .tabela-parceiros .detalhes-link:hover, .tabela-produtos .detalhes-link:hover {
                text-decoration: underline;
            }
            .logo-produto {
                width: 80px;
                height: 80px;
                object-fit: cover;
                border-radius: 10px;
                border: 1px solid #ddd;
            }
            /* Estilo dos filtros de produtos */
/* Estilo dos filtros de produtos */
.filtros-produtos {
    margin-bottom: 20px;
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}

.filtros-produtos label {
    display: flex;
    align-items: center;
    font-size: 14px;
    cursor: pointer;
}

.filtros-produtos select {
    margin-left: 8px;
    padding: 5px;
    border-radius: 4px;
    border: 1px solid #ccc;
    font-size: 14px;
}

.filtros-produtos input[type="checkbox"] {
    margin-right: 5px;
}
/* Caixa de seleção estilizada */
.filtros-produtos select {
    padding: 8px;
    border-radius: 5px;
    border: 1px solid #ccc;
    font-size: 14px;
    background-color: #f9f9f9;
    width: 200px;
}

.filtros-produtos button {
    background-color: #007bff; /* Cor de fundo azul */
    color: #fff; /* Cor do texto */
    border: none; /* Sem borda */
    border-radius: 8px; /* Bordas arredondadas */
    padding: 10px 20px; /* Espaçamento interno */
    font-size: 16px; /* Tamanho da fonte */
    cursor: pointer; /* Cursor de ponteiro */
    transition: background-color 0.3s ease; /* Transição suave para o hover */
}

.filtros-produtos button:hover {
    background-color: #0056b3; /* Cor de fundo mais escura no hover */
}

.filtros-produtos button:active {
    background-color: #003f7f; /* Cor mais escura quando pressionado */
}

@media (max-width: 768px) {
    .filtros-produtos button {
        width: 100%;
        font-size: 14px;
        padding: 12px;
    }
}

        </style>
</head>
<body>

    <!-- Cabeçalho com logo e notificações -->
    <header>
        <div class="logo">
            <img src="<?php echo $logo; ?>" alt="Logo da Loja" class="logo-img">
        </div>

        <h1><?php echo $usuario['nomeFantasia']; ?></h1>
        
        <div class="menu-superior-direito">
            <span>Olá, <strong><?php echo explode(' ', trim($usuario['nome']))[0]; ?></strong></span>
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
            <i class="fas fa-bars" onclick="toggleMenu()"></i>
        </div>
    </header>

    <!-- Painel de notificações que aparece ao clicar no ícone de notificações -->
    <aside id="painel-notificacoes">
        <h2>Notificações: <?php echo htmlspecialchars(string: $total_notificacoes); ?></h2>
        <ul id="lista-notificacoes">
            <li onclick="abrirNotificacao(1)">Novo Cliente: <?php echo $not_novo_cliente; ?></li>  
            <li onclick="abrirNotificacao(2)">Solicitação de cadastro de Parceiro: <?php echo $not_inscr_parceiro; ?></li>
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
                <a href="perfil_loja.php?id_admin=<?php echo urlencode($id); ?>" title="Perfil da Loja">
                    <i class="fas fa-user"></i>
                    <span >Perfil</span>
                </a>
            </li>

            <!-- Ver produtos -->
            <li>
                <a href="produtos.php?id_admin=<?php echo urlencode($id); ?>" title="Configurações">
                    <i class="fas fa-box"></i>
                    <span>Produtos</span>
                </a>
            </li>
            
            <!-- Ver parceiros -->
            <li>
                <a href="parceiros.php?id_admin=<?php echo urlencode($id); ?>" title="Configurações">
                    <i class="fas fa-handshake"></i>
                    <span>Parceiros</span>
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
        <div class="opcoes">
            <div class="tab active" onclick="mostrarConteudo('dashboard',this)">
                <span>Dashboard</span>
            </div>
            <div class="tab" onclick="mostrarConteudo('gerenciamento',this)">
                <span>Gerenciamento</span>
            </div>
        </div>

        <!-- Conteúdos correspondentes às abas -->
        <div id="conteudo-dashboard" class="conteudo-aba" style="display: block;">
            <h2>Dashboard</h2>
            <p>Conteúdo do Dashboard aparece aqui.</p>
        </div>

        <div id="conteudo-gerenciamento" class="conteudo-aba" style="display:none;">
            <div class="opcoes-gerenciamento">
                <div class="tab active" onclick="mostrarConteudoGerenciamento('parceiros',this)">
                    <span>Parceiros</span>
                </div>
                <div class="tab" onclick="mostrarConteudoGerenciamento('produtos',this)">
                    <span>Produtos</span>
                </div>
            </div>

            <!-- Conteúdos correspondentes às abas -->
            <div id="conteudo-parceiros" class="conteudo-aba" style="display: block;">
                <table class="tabela-parceiros">
                    <thead>
                        <tr>
                            <th>Data de Cadastro</th>
                            <th>Logo</th>
                            <th>Nome Fantasia</th>
                            <th>Categoria</th>
                            <th>Detalhes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        include('../../conexao.php');
                        $sql = "SELECT id, data_cadastro, logo, nomeFantasia, categoria FROM meus_parceiros ORDER BY data_cadastro DESC";
                        $result = $mysqli->query($sql);

                        if ($result->num_rows > 0) {
                            while ($parceiro = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . date('d/m/Y', strtotime($parceiro['data_cadastro'])) . "</td>";
                                echo "<td><img src='../parceiros/arquivos/" . $parceiro['logo'] . "' alt='Logo' class='logo-parceiro'></td>";
                                echo "<td>" . htmlspecialchars($parceiro['nomeFantasia']) . "</td>";
                                echo "<td>" . htmlspecialchars($parceiro['categoria']) . "</td>";
                                echo "<td><a href='detalhes_parceiro.php?id=" . $parceiro['id'] . "' class='detalhes-link'>Ver Detalhes</a></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>Nenhum parceiro encontrado.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            
            <div id="conteudo-produtos" class="conteudo-aba" style="display: block;">
                <div class="filtros-produtos">
                    <label for="categoria">
                        Categoria:
                        <select name="categoria" id="categoria">
                            <option value="">Todas as Categorias</option>
                            <?php
                            $queryCategorias = "SELECT id, categorias FROM categorias ORDER BY categorias ASC";
                            $resultCategorias = $mysqli->query($queryCategorias);

                            if ($resultCategorias->num_rows > 0) {
                                while ($categoria = $resultCategorias->fetch_assoc()) {
                                    echo "<option value='" . htmlspecialchars($categoria['id']) . "'>" . htmlspecialchars($categoria['categorias']) . "</option>";
                                }
                            } else {
                                echo "<option value=''>Nenhuma categoria encontrada</option>";
                            }
                            ?>
                        </select>
                    </label>

                    <!-- Filtros com checkboxes -->
                    <label for="ativo">
                        <input type="checkbox" name="status[]" value="ativo" id="ativo"> Ativo
                    </label>
                    <label for="inativo">
                        <input type="checkbox" name="status[]" value="inativo" id="inativo"> Inativo
                    </label>
                    <label for="mais-vendidos">
                        <input type="checkbox" name="status[]" value="mais-vendidos" id="mais-vendidos"> Mais Vendidos
                    </label>
                    <label for="novidades">
                        <input type="checkbox" name="status[]" value="novidades" id="novidades"> Novidades
                    </label>
                    <label for="promocao">
                        <input type="checkbox" name="status[]" value="promocao" id="promocao"> Promoção
                    </label>
                    <label for="frete-gratis">
                        <input type="checkbox" name="status[]" value="frete-gratis" id="frete-gratis"> Frete Grátis
                    </label>

                    <button type="button" onclick="filtrarProdutos()">
                        🔍 Filtrar
                    </button>

                </div>

                <table class="tabela-produtos">
                    <thead>
                        <tr>
                            <th>Data de Cadastro</th>
                            <th>Imagem</th>
                            <th>Produto</th>
                            <th>Categoria</th>
                            <th>Detalhes</th>
                        </tr>
                    </thead>

                    <tbody id="produtos-tabela">
                        <?php
                        include('../../conexao.php');

                        // Consulta SQL para carregar os produtos
                        $sql = "SELECT id_produto, data, imagens, nome_produto, categoria FROM produtos ORDER BY data DESC";
                        $result = $mysqli->query($sql);

                        if ($result->num_rows > 0) {
                            while ($produto = $result->fetch_assoc()) {
                                // Obtém a primeira imagem
                                $imagens = explode(',', $produto['imagens']);
                                $primeiraImagem = $imagens[0];

                                echo "<tr>";
                                echo "<td>" . date('d/m/Y', strtotime($produto['data'])) . "</td>";
                                echo "<td><img src='../parceiros/produtos/img_produtos/" . $primeiraImagem . "' alt='Imagem do Produto' class='logo-produto'></td>";
                                echo "<td>" . htmlspecialchars($produto['nome_produto']) . "</td>";
                                echo "<td>" . htmlspecialchars($produto['categoria']) . "</td>";
                                echo "<td><a href='detalhes_produto.php?id=" . $produto['id_produto'] . "' class='detalhes-link'>Ver Detalhes</a></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>Nenhum produto encontrado.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>

            </div> 
        </div>
    </main>

    <footer class="menu-mobile">
        <ul>
            <li><a href="perfil_loja.php" title="Perfil da Loja"><i class="fas fa-user"></i></a></li>
            <li><a href="configuracoes.php?id_parceiro=<?php echo urlencode($id); ?>" title="Configurações"><i class="fas fa-cog"></i></a></li>
            <li><a href="admin_logout.php" title="Sair"><i class="fas fa-sign-out-alt"></i></a></li>
        </ul>
    </footer>
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

        function filtrarProdutos() {
            // Obtém os valores dos filtros
            const categoria = document.getElementById('categoria').value;
            const status = Array.from(document.querySelectorAll('input[name="status[]"]:checked'))
                .map(checkbox => checkbox.value);

            // Cria uma requisição AJAX
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'filtrar_produtos.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            // Quando a requisição for concluída, atualiza a tabela
            xhr.onload = function () {
                if (xhr.status === 200) {
                    document.getElementById('produtos-tabela').innerHTML = xhr.responseText;
                }
            };

            // Envia os dados dos filtros para o servidor
            xhr.send('categoria=' + categoria + '&status=' + JSON.stringify(status));
            console.log(status);
        }

    </script>

</body>
</html>
