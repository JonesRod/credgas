<?php
    include('../../../conexao.php');

    if (!isset($_SESSION)) {
        session_start();
    }

    // Verificar se a sessão está ativa
    if (isset($_SESSION['id'])) {
        $id = $_SESSION['id'];
        $sql_query = $mysqli->query("SELECT * FROM meus_clientes WHERE id = '$id'") or die($mysqli->error);
        $usuario = $sql_query->fetch_assoc();
    } else {
        session_unset();
        session_destroy();
        header("Location: ../../../../../index.php");
        exit();
    }

    // Obter os IDs da URL
    $id_parceiro = isset($_GET['id_parceiro']) ? intval($_GET['id_parceiro']) : null;
    $id_produto = isset($_GET['id_produto']) ? intval($_GET['id_produto']) : null;

    $sql_parceiros = $mysqli->query("SELECT * FROM meus_parceiros WHERE id = '$id_parceiro'") or die($mysqli->error);
    $parceiro = $sql_parceiros->fetch_assoc();

    $produto = [];
    $imagens = [];

    if ($id_parceiro && $id_produto) {
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
        $id_produto = $_GET['id_produto']; // Pegue o ID do produto do POST
        $taxa_padrao = isset($_POST['taxa_padrao']) ? $_POST['taxa_padrao'] : '';

        // Remove pontos e substitui a vírgula por ponto
        $taxa_padrao = str_replace('.', '', $taxa_padrao); // Remove pontos (separadores de milhar)
        $taxa_padrao = str_replace(',', '.', $taxa_padrao); // Troca a vírgula por ponto
        $valor_venda_vista = $produto['valor_produto'] + ($produto['valor_produto'] * $taxa_padrao ) /100;
        $vende_crediario = isset($_POST['vende_crediario']) && $_POST['vende_crediario'] === 'sim' ? 'sim' : 'nao';
        $parcelas = isset($_POST['parcelas']) ? intval($_POST['parcelas']) : 0;
        
        //echo ($id_produto);
        //echo ($vende_crediario);
        //echo ($parcelas);
        //echo $valor_venda_vista;
        //die();

        if (isset($_POST['salvar'])) {
            // Atualiza o produto com segurança
            $sql_aprovar = "UPDATE produtos SET taxa_padrao = ?, valor_venda_vista = ?, produto_aprovado = 'sim', vende_crediario = ?, qt_parcelas = ? WHERE id_produto = ?";
            $stmt = $mysqli->prepare($sql_aprovar);

            $stmt->bind_param("ssssi", $taxa_padrao, $valor_venda_vista ,$vende_crediario, $parcelas, $id_produto);

            if ($stmt->execute()) {
            
                // Redireciona mantendo os parâmetros na URL
                header("Location: " . $_SERVER['PHP_SELF'] . "?id_parceiro=$id_parceiro&id_produto=$id_produto");
                exit; // Garante que o código pare aqui
            } else {
                $error_msg = "Erro ao salvar alteração.";
            }
            //echo ('oii');
            $stmt->close();

        }elseif (isset($_POST['bloquear'])) {
            // Atualiza o produto com segurança
            $sql_aprovar = "UPDATE produtos SET taxa_padrao = ?, produto_aprovado = 'nao', vende_crediario = ?, qt_parcelas = ? WHERE id_produto = ?";
            $stmt = $mysqli->prepare($sql_aprovar);

            $stmt->bind_param("sssi", $taxa_padrao, $vende_crediario, $parcelas, $id_produto);

            if ($stmt->execute()) {

                // Inserir notificação para o parceiro
                $sql_not_parc = "INSERT INTO contador_notificacoes_parceiro (data, id_parceiro, id_produto, not_adicao_produto, msg, analize)
                VALUES (NOW(), '$id_parceiro', '$id_produto', '1', 'Verifique as informações do seu produto e tente novamente!', 'REPROVADO')";
        
                if ($mysqli->query($sql_not_parc)) {
                    // Redirecionar se todas as operações forem bem-sucedidas
                    header("Location: " . $_SERVER['PHP_SELF'] . "?id_parceiro=$id_parceiro&id_produto=$id_produto");
                    exit();
                    
                } else {
                    $error_msg = "Erro ao processar notificações: " . $mysqli->error;
                }   

                // Redireciona mantendo os parâmetros na URL
                header("Location: " . $_SERVER['PHP_SELF'] . "?id_parceiro=$id_parceiro&id_produto=$id_produto");
                exit; // Garante que o código pare aqui
            } else {
                $error_msg = "Erro ao salvar alteração.";
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
        margin: 10px 0;
        }

        /* Destaque para os rótulos */
        p strong {
            color: #333;
            font-weight: bold;
        }

        /* Estilo para o contêiner da descrição */
        .descricao-box {   
            max-height: 150px;
            overflow-y: auto;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
            font-size: 1em;
            color: #444;
            margin: 10px 0;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }

        .image-slider {
            display: flex;
            flex-direction: column;
            align-items: center; /* Centraliza horizontalmente */
            justify-content: center; /* Centraliza verticalmente */
            margin: 30px auto; /* Adiciona espaçamento e centraliza horizontalmente */
            max-width: 600px; /* Define uma largura máxima */
            padding: 15px; /* Espaçamento interno */
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
            margin: 30px auto; /* Centraliza horizontalmente e adiciona espaçamento */
            max-width: 500px; /* Define a largura máxima */
            text-align: center; /* Centraliza o conteúdo interno */
            padding: 20px;
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
        .crediario{
            margin-bottom: 20px;
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
                

                font-size: 0.9em;
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
        <h2>Detalhes do Produto</h2>           
        <?php if (!empty($imagens)) : ?>
            <div class="image-slider">
                <div class="main-image">
                    <img class="active" src="../../parceiros/produtos/img_produtos/<?= htmlspecialchars($imagens[0]); ?>" alt="Imagem Principal do Produto">
                </div>
                <div class="thumbnail-container">
                    <?php foreach ($imagens as $index => $imagem) : ?>
                        <img class="thumbnail <?= $index === 0 ? 'active' : ''; ?>" src="../../parceiros/produtos/img_produtos/<?= htmlspecialchars($imagem); ?>" alt="Imagem do Produto" onclick="changeMainImage(this)">
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else : ?>
            <p>Sem imagens disponíveis para este produto.</p>
        <?php endif; ?>        

            
            <p><strong>Parceiro:</strong> <?= htmlspecialchars($parceiro['nomeFantasia']); ?></p>
            <p><strong>Status:</strong> <?= ($produto['produto_aprovado'] == 'sim') ? 'Ativo' : 'Inativo'; ?></p>
            <p><strong>Categoria:</strong> <?= htmlspecialchars($produto['categoria']); ?></p>
            <p><strong>Nome:</strong> <?= htmlspecialchars($produto['nome_produto'] ?? 'Produto sem nome'); ?></p>
            <p><strong>Qt vendido:</strong> <?= htmlspecialchars($produto['qt_vendido']); ?></p>
            <p><strong>Descrição:</strong></p>
            <div class="descricao-box"><?= nl2br(htmlspecialchars($produto['descricao_produto'] ?? 'Sem descrição disponível')); ?></div>
            <p><strong>Preço:</strong> R$ <?= number_format($produto['valor_produto'] ?? 0, 2, ',', '.'); ?></p>
            <p><strong>Frete Grátis:</strong> <?= htmlspecialchars($produto['frete_gratis'] === 'sim' ? 'SIM' : 'NÃO'); ?></p>
            <p><strong>Frete:</strong> R$ <?= number_format($produto['valor_frete'] ?? 0, 2, ',', '.'); ?></p>

            <form method="POST" action="">
                <p><strong>Taxa padrão: </strong>
                    <input type="text" id="taxa_padrao" name="taxa_padrao" 
                    style="max-width: 70px;
                    font-size: 15px;
                    text-align: center;"
                    required value="<?= number_format($produto['taxa_padrao'] ?? 0, 2, ',', '.'); ?>" 
                    oninput="formatarValor(this)"> %
                </p>
                
                <p>
                    <strong>Preço de venda na plataforma: R$ 
                        <input type="text" id="preco_venda_vista"
                        style="max-width: 100px; 
                        border: 0px;
                        font-size: 18px;
                        outline: none;"  
                        readonly
                        value="<?= number_format($produto['valor_venda_vista'] ?? 0, 2, ',', '.'); ?>">
                    </strong>
                </p>
            
                <!-- Opção de Vender no Crediário --> 
                <p><strong>Vender no Crediário:</strong></p>
                <div class="crediario">
                    <label>
                        <?php
                            $vender_crediario = $produto['vende_crediario'] ?? 'nao'; // Valor salvo no banco, padrão "não"
                            $parcelas_selecionadas = $produto['qt_parcelas'] ?? 1; // Número de parcelas salvo no banco, padrão 1
                        ?>
                        <input type="radio" name="vende_crediario" value="sim" 
                        <?= $vender_crediario === 'sim' ? 'checked' : ''; ?> 
                        onclick="toggleParcelas(true)"> Sim
                    </label>
                    <label>
                        <input type="radio" name="vende_crediario" value="nao" 
                        <?= $vender_crediario === 'nao' || $vender_crediario === '' ? 'checked' : ''; ?> 
                        onclick="toggleParcelas(false)"> Não
                    </label>                        
                </div>

                <!-- Select de Parcelas -->
                <div class="crediario" id="parcelas-container" style="display: <?= $vender_crediario === 'sim' ? 'block' : 'none'; ?>; margin-top: 10px;">
                    <label for="parcelas"><strong>Quantidade de Parcelas:</strong></label>
                    <select name="parcelas" id="parcelas">
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?= $i; ?>" <?= $i == $parcelas_selecionadas ? 'selected' : ''; ?>><?= $i; ?>x</option>
                        <?php endfor; ?>
                    </select>
                </div>

                <button type="submit" name="salvar" class="btn btn-success">Salvar</button>
                <button type="submit" name="bloquear" class="btn btn-danger">Bloquear</button>
            </form>

            <script>
                function toggleParcelas(mostrar) {
                    document.getElementById('parcelas-container').style.display = mostrar ? 'block' : 'none';
                }

                function changeMainImage(thumbnail) {
                    const mainImage = document.querySelector('.main-image img');
                    mainImage.src = thumbnail.src;

                    const thumbnails = document.querySelectorAll('.thumbnail');
                    thumbnails.forEach((thumb) => thumb.classList.remove('active'));

                    thumbnail.classList.add('active');
                }

                function updateVendaVista(input) {
                    const precoVendaVistaInput = document.getElementById('preco_venda_vista');
                    const valorProduto = <?= $produto['valor_produto'] ?? 0; ?>;
                    
                    let taxaPadrao = input.value.replace('.', '').replace(',', '.');
                    taxaPadrao = parseFloat(taxaPadrao) || 0;

                    // Limitar a 2 casas decimais
                    taxaPadrao = Math.round(taxaPadrao * 100) / 100;

                    const valorVendaVista = valorProduto + (valorProduto * taxaPadrao / 100);
                    precoVendaVistaInput.value = valorVendaVista.toFixed(2).replace('.', ',');

                    // Atualizar o valor do input com 2 casas decimais
                    input.value = taxaPadrao.toFixed(2).replace('.', ',');
                }

                // Função para formatar o valor digitado no campo "taxa"
                function formatarValor(input) {
                    let taxa = input.value.replace(/\D/g, '');  // Remove todos os caracteres não numéricos
                    let valorProduto = <?= $produto['valor_produto'] ?? 0; ?>;  // Valor do produto
                    
                    taxa = (taxa / 100).toFixed(2);  // Divide por 100 para ajustar para formato de decimal (0.00)

                    taxa = taxa.replace('.', ',');  // Substitui o ponto pela vírgula
                    taxa = taxa.replace(/\B(?=(\d{3})+(?!\d))/g, ".");  // Adiciona os pontos para separar os milhares

                    input.value = taxa;  // Atualiza o valor no campo

                    const valorVendaVista = valorProduto + (valorProduto * parseFloat(taxa.replace(',', '.')) / 100);
                    document.getElementById('preco_venda_vista').value = valorVendaVista.toFixed(2).replace('.', ',');
                }
            </script>

            <!-- Link para voltar -->
            <div style="text-align: center; margin-top: 30px;"> <!-- Aumentar a margem -->
                <a href="../admin_home.php" class="back-link">Voltar</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
