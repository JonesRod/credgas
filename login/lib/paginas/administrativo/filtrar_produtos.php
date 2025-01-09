<?php
include('../../conexao.php');

// Obtém os filtros enviados
$categoria = $_POST['categoria'];
$status = json_decode($_POST['status'], true);

// Monta a consulta SQL
$sql = "SELECT * FROM produtos WHERE 1=1";

// Filtro por categoria
if (!empty($categoria)) {
    $sql .= " AND categoria = '" . $mysqli->real_escape_string($categoria) . "'";
}

// Filtro por status
$statusConditions = [];

if (!empty($status)) {
    // Filtra por status "ativo" ou "inativo"
    if (in_array("ativo", $status)) {
        $statusConditions[] = "produto_aprovado = 'sim'";
    }
    if (in_array("inativo", $status)) {
        $statusConditions[] = "produto_aprovado = 'nao'";
    }

    // Filtra por novidades (últimos 30 dias)
    if (in_array("novidades", $status)) {
        $statusConditions[] = "data >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND produto_aprovado = 'sim'";
    }

    // Filtra por promoção
    if (in_array("promocao", $status)) {
        $statusConditions[] = "promocao = 'sim'";
    }

    // Filtra por frete grátis
    if (in_array("frete-gratis", $status)) {
        $statusConditions[] = "frete_gratis = 'sim' OR (promocao = 'sim' AND frete_gratis_promocao = 'sim')";
    }

    // Filtra por mais vendidos
    if (in_array("mais-vendidos", $status)) {
        $statusConditions[] = "qt_vendido > 0";
    }
}

// Adiciona as condições de status ao SQL
if (!empty($statusConditions)) {
    $sql .= " AND (" . implode(' OR ', $statusConditions) . ")";
}

// Ordena por mais vendidos, se selecionado
if (in_array("mais-vendidos", $status)) {
    $sql .= " ORDER BY qt_vendido DESC";
} else {
    // Caso contrário, ordena por data
    $sql .= " ORDER BY data DESC";
}

// Executa a consulta
$result = $mysqli->query($sql);

// Verifica se a consulta foi executada com sucesso
if (!$result) {
    echo "Erro na consulta: " . $mysqli->error;
    exit;
}

// Conta o número de produtos
$totalProdutos = $result->num_rows;

// Exibe a tabela apenas se houver produtos
if ($totalProdutos > 0) {
    // Exibe os produtos
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

    echo '</tbody>';
    echo '</table>';
} else {
    echo "<p>Nenhum produto encontrado.</p>";
}

?>
