<?php
include('../../conexao.php');

if (!isset($_SESSION)) {
    session_start();
}

// Verificar se a sessão está ativa
if (isset($_SESSION['id'])) {
    $id = $_SESSION['id'];
    $sql_query = $mysqli->query("SELECT * FROM meus_clientes WHERE id = '$id'") or die($mysqli->error);
    $usuario = $sql_query->fetch_assoc();
} else {
    // Redirecionar para a página de login caso a sessão não exista
    session_unset();
    session_destroy();
    header("Location: ../../../../index.php");
    exit();
}

// Obter os IDs da URL e validar
$id_parceiro = isset($_GET['id_parceiro']) ? intval($_GET['id_parceiro']) : null;
$id_produto = isset($_GET['id_produto']) ? intval($_GET['id_produto']) : null;

if ($id_parceiro && $id_produto) {
    // Buscar informações do produto usando prepared statements
    $stmt = $mysqli->prepare("SELECT * FROM produtos WHERE id_produto = ?");
    $stmt->bind_param("i", $id_produto);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $produto = $result->fetch_assoc();
    
        // Informações do produto
        $nome_produto = htmlspecialchars($produto['nome_produto'] ?? 'Produto sem nome');
        $descricao = htmlspecialchars($produto['descricao_produto'] ?? 'Sem descrição disponível');
        $preco = htmlspecialchars($produto['valor_produto'] ?? 'Preço não informado');
        $frete_gratis = htmlspecialchars($produto['frete_gratis'] ?? '??');

        if ($frete_gratis == 'sim') {
            $frete_gratis = 'SIM';  // Corrigido: alterando para 'SIM' caso seja 'sim'
        } else {
            $frete_gratis = 'NÃO';  // Corrigido: definindo como 'NÃO' caso contrário
        }        

        $frete = htmlspecialchars($produto['valor_frete'] ?? 'Frete não informado');

        // Carregar imagens (assumindo que são armazenadas como uma string separada por vírgulas)
        $imagens = isset($produto['imagens']) ? explode(',', $produto['imagens']) : [];
        $imagens = array_map('htmlspecialchars', $imagens); // Prevenir XSS
    
        // Exibir informações do produto
        echo "<h2>Detalhes do Produto</h2>";
        echo "<p><strong>Nome:</strong> $nome_produto</p>";
        echo "<p><strong>Descrição:</strong></p>";
        echo "<div class='descricao-box'>$descricao</div>";
        

        // Formatar o preço com 2 casas decimais e separador de milhar
        $preco_formatado = number_format($preco, 2, ',', '.'); 
        echo "<p><strong>Preço:</strong> R$ $preco_formatado</p>";

        echo "<p><strong>Frete Grátis:</strong>$frete_gratis</p>";

        $frete_formatado = number_format($frete, 2, ',', '.'); 
        echo "<p><strong>Preço:</strong> R$ $frete_formatado</p>";        

        // Exibir imagens do produto
        echo "<h3>Imagens do Produto</h3>";

        if (!empty($imagens)) {
            echo '<div class="image-slider">';

            // Exibir a imagem principal em destaque
            echo '<div class="main-image">';
            echo "<img class='active' src='../parceiros/produtos/img_produtos/{$imagens[0]}' alt='Imagem Principal do Produto'>";
            echo '</div>';

            // Exibir as miniaturas das imagens embaixo
            echo '<div class="thumbnail-container">';
            foreach ($imagens as $index => $imagem) {
                $activeClass = ($index === 0) ? 'active' : ''; // Marcar a primeira imagem como ativa
                echo "<img class='thumbnail $activeClass' src='../parceiros/produtos/img_produtos/$imagem' alt='Imagem do Produto' onclick='changeMainImage(this)'>";
            }
            echo '</div>';

            echo '</div>';
        } else {
            echo "<p>Sem imagens disponíveis para este produto.</p>";
        }
        // Exibir os botões de Aprovação/Reprovação
        echo '<div class="buttons-container">';
        echo '<form method="POST" action="">';
        echo '<button type="submit" name="aprovar" class="btn btn-success">Aprovar</button>';
        echo '<button type="submit" name="reprovar" class="btn btn-danger">Reprovar</button>';
        echo '</form>';
        echo '</div>';

    } else {
        // Mensagem de erro caso o produto não seja encontrado
        echo "<p>Produto não encontrado ou indisponível para análise.</p>";
    }
    
    $stmt->close();
} else {
    echo "<p>ID do parceiro ou produto inválido.</p>";
    exit();
}

// Verificar se o formulário foi enviado e processar a aprovação ou reprovação
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['aprovar'])) {
        // Aprovar o produto
        $id_produto = $_GET['id_produto']; // Garantir que o id_produto está sendo passado na URL
        $sql_aprovar = "UPDATE produtos SET produto_aprovado = 'sim' WHERE id_produto = '$id_produto'";


        if ($mysqli->query($sql_aprovar)) {
            //echo "<script>alert('Produto aprovado com sucesso!'); window.location.href='detalhes_produto.php?id_produto=$id_produto';</script>";
            $sql_contador_notificacoes_admin = "UPDATE contador_notificacoes_admin SET not_atualizar_produto = '0' WHERE id_produto = '$id_produto'";
            $mysqli->query(query: $sql_contador_notificacoes_admin) or die($mysqli->error);

            $sql_contador_notificacoes_parceiro ="INSERT INTO contador_notificacoes_parceiro (data, id_parceiro, not_edicao_produto, id_produto, msg) 
            VALUES (NOW(), ?, ?, ?, ?"; 
            $mysqli->query(query: $sql_contador_notificacoes_parceiro) or die($mysqli->error);   

            $sql_produtos = "UPDATE produtos SET produto_aprovado = 'nao' WHERE id_produto = '$id_produto'";
            $mysqli->query(query: $sql_produtos) or die($mysqli->error);       

        } else {
            echo "<script>alert('Erro ao aprovar o produto.');</script>";
            $sql_aprovar = "UPDATE produtos SET produto_aprovado = 'sim' WHERE id_produto = '$id_produto'";
        }
    }

    if (isset($_POST['reprovar'])) {
        // Reprovar o produto
        $id_produto = $_GET['id_produto']; // Garantir que o id_produto está sendo passado na URL
        $sql_reprovar = "UPDATE produtos SET produto_aprovado = 'nao' WHERE id_produto = '$id_produto'";

        if ($mysqli->query($sql_reprovar)) {
            echo "<script>alert('Produto reprovado com sucesso!'); window.location.href='detalhes_produto.php?id_produto=$id_produto';</script>";
        } else {
            echo "<script>alert('Erro ao reprovar o produto.');</script>";
        }
    }
}

?>
<style>
    .descricao-box {
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid #ddd;
    padding: 10px;
}

    .image-slider {
        text-align: center;
    }

    .main-image {
        margin-bottom: 20px;
    }

    .main-image img {
        width: 400px; /* Tamanho da imagem principal */
        height: auto;
        border: 3px solid #ddd;
        border-radius: 5px;
    }

    .thumbnail-container {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 10px;
        flex-wrap: wrap;
    }

    .thumbnail {
        width: 100px; /* Tamanho das miniaturas */
        height: auto;
        cursor: pointer;
        border: 2px solid transparent;
        transition: border 0.3s ease;
    }

    .thumbnail:hover {
        border-color: #007BFF;
    }

    .thumbnail.active {
        border-color: #007BFF; /* Destacar a miniatura ativa */
        border-width: 3px;
    }

    /* Responsividade */
    @media (max-width: 768px) {
        .main-image img {
            width: 80%;
        }

        .thumbnail {
            width: 80px;
        }
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
    transition: background-color 0.3s;
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

</style>

<script>
    function changeMainImage(thumbnail) {
        // Trocar a imagem principal quando uma miniatura for clicada
        const mainImage = document.querySelector('.main-image img');
        mainImage.src = thumbnail.src;

        // Remover a classe 'active' das miniaturas
        const thumbnails = document.querySelectorAll('.thumbnail');
        thumbnails.forEach(thumb => thumb.classList.remove('active'));

        // Adicionar a classe 'active' à miniatura clicada
        thumbnail.classList.add('active');
    }
</script>