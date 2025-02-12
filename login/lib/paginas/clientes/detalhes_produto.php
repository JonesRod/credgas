<?php
    include('../../conexao.php');

    if (!isset($_SESSION)) {
        session_start();
    }

    // Verificar se a sessão está ativa
    if (isset($_SESSION['id']) && isset($_GET['id_cliente']) && isset($_GET['id_produto'])) {
        $id = $_SESSION['id'];
        $sql_query = $mysqli->query("SELECT * FROM meus_clientes WHERE id = '$id'") or die($mysqli->error);
        $usuario = $sql_query->fetch_assoc();
    } else {
        session_unset();
        session_destroy();
        header("Location: ../../../../index.php");
        exit();
    }

    // Obter os IDs da URL
    $id_cliente = isset($_GET['id_cliente']) ? intval($_GET['id_cliente']) : null;
    $id_produto = isset($_GET['id_produto']) ? intval($_GET['id_produto']) : null;

    //echo $id_produto;
    //echo $id_cliente;

    $produto = [];
    $imagens = [];

    if ($id_produto) {
        $stmt = $mysqli->prepare("SELECT * FROM produtos WHERE id_produto = ?");
        $stmt->bind_param("i", $id_produto);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $produto = $result->fetch_assoc();
            $nome_produto = $produto['nome_produto'];
            $imagens = isset($produto['imagens']) ? explode(',', $produto['imagens']) : [];
        } else {
            $error_msg = "Produto não encontrado ou indisponível.";
        }
        $stmt->close();
    } else {
        $error_msg = "ID do parceiro ou produto inválido.";
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id_produto = $_GET['id_produto'];
        if (isset($_POST['aprovar'])) {
            $sql_aprovar = "UPDATE produtos SET produto_aprovado = 'sim' WHERE id_produto = ?";
            $stmt = $mysqli->prepare($sql_aprovar);
            $stmt->bind_param("i", $id_produto);

            if ($stmt->execute()) {
                //$sql_not_admin = "UPDATE contador_notificacoes_admin SET not_atualizar_produto = '0' WHERE id_produto = $id_produto";
                if (isset($_GET['id'])) {
                    //echo ('oi');
                
                    $id = $_GET['id'];
                
                    // Consulta para buscar a notificação com o ID fornecido
                    $sql_not = "SELECT * FROM contador_notificacoes_admin WHERE id = $id";
                    $result = $mysqli->query($sql_not) or die($mysqli->error);
                
                    if (isset($_GET['id'])) {
                        //echo ('oi');
                    
                        $id = $_GET['id'];
                    
                        // Consulta para buscar a notificação com o ID fornecido
                        $sql_not = "SELECT * FROM contador_notificacoes_admin WHERE id = $id";
                        $result = $mysqli->query($sql_not) or die($mysqli->error);
                    
                        // Verifica se a notificação foi encontrada
                        if ($result->num_rows > 0) {
                            // Exclui a notificação da tabela
                            $sql_delete = "DELETE FROM contador_notificacoes_admin WHERE id = $id";
                            if ($mysqli->query($sql_delete)) {
                                //echo "Notificação excluída com sucesso.";
                            } else {
                                //echo "Erro ao excluir a notificação: " . $mysqli->error;
                            }
                        } else {
                            //echo "Notificação não encontrada.";
                        }
                    }
                    
                }
                
                $sql_not_parc = "INSERT INTO contador_notificacoes_parceiro (data, id_parceiro, id_produto, not_adicao_produto, analize)
                VALUES (NOW(), '$id_parceiro', '$id_produto', '1', 'APROVADO')";
            
                if ($mysqli->query($sql_not_parc)) {
                    // Redirecionar se todas as operações forem bem-sucedidas
                    header("Location: not_detalhes_edicao_produtos.php?id_produto=$id_produto");
                    exit();
                } else {
                    $error_msg = "Erro ao processar notificações: " . $mysqli->error;
                }
            } else {
                $error_msg = "Erro ao aprovar o produto.";
            }
            
            $stmt->close();
        } elseif (isset($_POST['reprovar'])) {
            $sql_reprovar = "UPDATE produtos SET produto_aprovado = 'nao' WHERE id_produto = ?";
            $stmt = $mysqli->prepare($sql_reprovar);
            $stmt->bind_param("i", $id_produto);
        
            if ($stmt->execute()) {
                if (isset($_GET['id'])) {
                    //echo ('oi');
                
                    $id = $_GET['id'];
                
                    // Consulta para buscar a notificação com o ID fornecido
                    $sql_not = "SELECT * FROM contador_notificacoes_admin WHERE id = $id";
                    $result = $mysqli->query($sql_not) or die($mysqli->error);
                
                    if (isset($_GET['id'])) {
                        //echo ('oi');
                    
                        $id = $_GET['id'];
                    
                        // Consulta para buscar a notificação com o ID fornecido
                        $sql_not = "SELECT * FROM contador_notificacoes_admin WHERE id = $id";
                        $result = $mysqli->query($sql_not) or die($mysqli->error);
                    
                        // Verifica se a notificação foi encontrada
                        if ($result->num_rows > 0) {
                            // Exclui a notificação da tabela
                            $sql_delete = "DELETE FROM contador_notificacoes_admin WHERE id = $id";
                            if ($mysqli->query($sql_delete)) {
                                //echo "Notificação excluída com sucesso.";
                            } else {
                                //echo "Erro ao excluir a notificação: " . $mysqli->error;
                            }
                        } else {
                            //echo "Notificação não encontrada.";
                        }
                    }
                    
                }
        
                // Inserir notificação para o parceiro
                $sql_not_parc = "INSERT INTO contador_notificacoes_parceiro (data, id_parceiro, id_produto, not_adicao_produto, msg, analize)
                VALUES (NOW(), '$id_parceiro', '$id_produto', '1', 'Verifique os dados editados do seu produto e tente novamente!', 'REPROVADO')";
        
                if ($mysqli->query($sql_not_admin) && $mysqli->query($sql_not_parc)) {
                    // Redirecionar se todas as operações forem bem-sucedidas
                    header("Location: not_detalhes_edicao_produtos.php?id_produto=$id_produto");
                    exit();
                } else {
                    $error_msg = "Erro ao processar notificações: " . $mysqli->error;
                }
            } else {
                $error_msg = "Erro ao reprovar o produto.";
            }
        
            $stmt->close();
        }
        
    }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Produto</title>
    <style>
        /* Estilo para o título */
        h2 {
            text-align: center;
            font-size: 1.8em;
            color: #333;
            margin-bottom: 20px;
            text-transform: uppercase;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
        }

        /* Estilo para o parágrafo */
        p {
            font-size: 1.1em;
        line-height: 1.5;
        color: #555;
        margin: 5px 0;
        }

        /* Destaque para os rótulos */
        p strong {
            color: #333;
            font-weight: bold;
        }

        /* Estilo para o contêiner da descrição */
        .descricao-box { 
            width: 90%;  
            height: auto;
            /*max-height: 150px;*/
            overflow-y: auto;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
            font-size: 1em;
            color: #444;
            margin: 5px 0;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }

        .image-slider {
            display: flex;
            flex-direction: column;
            align-items: center; /* Centraliza horizontalmente */
            justify-content: center; /* Centraliza verticalmente */
            margin: 0px auto; /* Adiciona espaçamento e centraliza horizontalmente */
            max-width: 500px; /* Define uma largura máxima */
            padding: 10px; /* Espaçamento interno */
            /*border: 1px solid #ddd; /* Borda para destaque */
            border-radius: 10px; /* Bordas arredondadas */
            /*background-color: #f9f9f9; /* Cor de fundo */
            /*box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); /* Sombra */
        }

        /* Ajuste para a imagem principal */
        .image-slider .main-image img {
            width: 100%; /* Responsivo: Ajusta ao tamanho do contêiner */
            max-width: 500px; /* Largura máxima da imagem */
            height: auto; /* Mantém a proporção da imagem */
            /*border: 3px solid #ddd;*/
            border-radius: 5px;
        }

        /* Ajuste para o contêiner de miniaturas */
        .image-slider .thumbnail-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap; /* Permite que as miniaturas quebrem linha */
            gap: 10px; /* Espaço entre as miniaturas */ 
            margin-top: 10px;
        }

        /* Ajuste para as miniaturas */
        .image-slider .thumbnail {
            width: 80px; /* Tamanho das miniaturas */
            height: auto; /* Mantém a proporção */
            cursor: pointer;
            border: 2px solid transparent;
            transition: border 0.3s ease;
        }
        .image-slider .thumbnail:hover {
            border-color: #007BFF;
        }

        .image-slider .thumbnail.active {
            border-color: #007BFF;
            border-width: 3px;
        }

        .container {
            margin: 20px auto; /* Centraliza horizontalmente e adiciona espaçamento */
            max-width: 500px; /* Define a largura máxima */
            text-align: center; /* Centraliza o conteúdo interno */
            padding: 5px;
            padding-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #ffffff;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }
        .thumbnail-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
            flex-wrap: wrap;
        }

        .thumbnail {
            width: 100px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: border 0.3s;
        }

        .thumbnail:hover {
            border-color: #007BFF;
        }

        .thumbnail.active {
            border-color: #007BFF;
            border-width: 3px;
        }

        .buttons-container {
            text-align: center;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-success {
            background-color: #28a745;
            color: white;
            border: none;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
            border: none;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }
        .descricao-box {
            min-height: 100px;
            max-height: 150px; /* Altura máxima */
            height: auto; /* Altura ajustável automaticamente */
            font-size: 0.9em;
            overflow: auto; /* Adiciona rolagem caso o conteúdo ultrapasse 150px */
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
            height: 380px;
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
        .popup input[type="number"] {
            border: 1px solid #000; /* Cor da borda */
            padding: 5px; /* Espaçamento interno */
            border-radius: 4px; /* Bordas arredondadas */
            outline: none; /* Remove o contorno ao focar */
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


        /* Responsividade para telas menores */
        @media (max-width: 768px) {
            h2 {
                font-size: 1.5em;
            }
            p {
                font-size: 1em;
            }
            .descricao-box {
                min-height: 100px;
                max-height: 150px; /* Altura máxima */
                height: auto; /* Altura ajustável automaticamente */
                font-size: 0.9em;
                overflow: auto; /* Adiciona rolagem caso o conteúdo ultrapasse 150px */
            }
            .container {
                max-width: 95%;
                padding: 15px;
            }
            .image-slider {
                max-width: 90%; /* Ajusta a largura para telas menores */
                padding: 10px;
            }

            .image-slider .main-image img {
                max-width: 90%; /* Ajusta a imagem principal */
            }

            .image-slider .thumbnail {
                width: 60px; /* Reduz o tamanho das miniaturas em telas menores */
            }
        }

    </style>
</head>
<body>
    <div class="container">
        <?php if (!empty($error_msg)) : ?>
        <p class="error"><?= htmlspecialchars($error_msg); ?></p>
        <?php elseif (!empty($produto)) : ?>
            <h2>Detalhes do produto</h2>
            <?php if (!empty($imagens)) : ?>
                <div class="image-slider">
                    <div class="main-image">
                        <img class="active" src="../../paginas/parceiros/produtos/img_produtos/<?= htmlspecialchars($imagens[0]); ?>" alt="Imagem Principal do Produto">
                    </div>
                    <div class="thumbnail-container">
                        <?php foreach ($imagens as $index => $imagem) : ?>
                            <img class="thumbnail <?= $index === 0 ? 'active' : ''; ?>" src="../../paginas/parceiros/produtos/img_produtos/<?= htmlspecialchars($imagem); ?>" alt="Imagem do Produto" onclick="changeMainImage(this)">
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else : ?>
                <p>Sem imagens disponíveis para este produto.</p>
            <?php endif; ?>
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
            <p><strong>Nome:</strong> <?= htmlspecialchars($produto['nome_produto'] ?? 'Produto sem nome'); ?></p>
            <p><strong>Descrição:</strong></p>
            <textarea class="descricao-box" readonly><?= nl2br(htmlspecialchars($produto['descricao_produto'] ?? 'Sem descrição disponível')); ?></textarea>
            <p><strong>Preço:</strong> R$ <?= number_format($produto['valor_produto'] ?? 0, 2, ',', '.'); ?></p>
            <?php if ($produto['frete_gratis'] === 'sim' || ($produto['promocao'] === 'sim' && $produto['frete_gratis_promocao'] === 'sim')): ?>
                <p><strong style="color: green;">Frete Grátis</strong></p>
            <?php else: ?>
                <p><strong style="color: red;">Frete:</strong> R$ <?= number_format($produto['valor_frete'] ?? 0, 2, ',', '.'); ?></p>
            <?php endif; ?>

            <div class="buttons-container">
                
                <form method="POST" action="">
                    <!-- Preço do produto -->
                    <?php
                        $taxa_padrao = floatval($produto['taxa_padrao'] ?? 0);
                        $valor_base = isset($produto['promocao']) && $produto['promocao'] === 'sim' 
                            ? floatval($produto['valor_promocao'] ?? 0) 
                            : floatval($produto['valor_produto'] ?? 0);  
                        $valor_produto = $valor_base + (($valor_base * $taxa_padrao)/ 100);
                    ?>

                    <?php if (isset($usuarioLogado) && $usuarioLogado): ?>
                        <!-- Botões para usuários logados -->
                        <button type="submit" name="adicionar" class="btn btn-success">Adicionar ao Carrinho</button>
                        <button type="submit" name="comprar" class="btn btn-danger">Comprar</button>
                    <?php else: ?>
                        <!-- Botões que redirecionam para a página de login -->
                        <a href="cliente_home.php" class="btn btn-success">Voltar</a>
                        <a href="#" class="btn btn-success" 
                            onclick="abrirPopup(
                            '<?php echo $produto['id_produto']; ?>',
                            '<?php echo $produto['nome_produto']; ?>', 
                            '<?php echo $valor_produto; ?>')
                        ">Adicionar ao Carrinho</a>

                        <a href="" class="btn btn-success">Comprar</a>
                    <?php endif; ?>

                </form>
            </div>
        <?php endif; ?>
    </div>
    <div class="popup" id="popup">
        <h2>Detalhes do Produto</h2>
        <form id="formCarrinho" action="comprar/carrinho.php">
            <aside id="info">
                <input type="hidden" id="id_cli" name="id_cli" value="<?php echo htmlspecialchars( $id_cliente); ?>">
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
        function changeMainImage(thumbnail) {
            const mainImage = document.querySelector('.main-image img');
            mainImage.src = thumbnail.src;

            const thumbnails = document.querySelectorAll('.thumbnail');
            thumbnails.forEach((thumb) => thumb.classList.remove('active'));

            thumbnail.classList.add('active');
        }

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
</body>
</html>
