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
if (!empty($status)) {
    $statusConditions = [];

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

    // Adiciona as condições de status ao SQL
    if (!empty($statusConditions)) {
        $sql .= " AND (" . implode(' OR ', $statusConditions) . ")";
    }
}

// Filtro para "mais vendidos"
if (in_array("mais-vendidos", $status)) {
    // Filtra produtos com quantidade vendida maior que 0 e ordena pelos mais vendidos
    $sql .= " AND qt_vendido > 0 ORDER BY qt_vendido DESC LIMIT 10";
} else {
    // Caso contrário, ordena por data para outros filtros
    $sql .= " ORDER BY data DESC";
}


// DEBUGGING: Exibe a consulta SQL gerada
//echo "<p><strong>Consulta SQL gerada:</strong> $sql</p>";

// Executa a consulta
$result = $mysqli->query($sql);

// Verifica se a consulta foi executada com sucesso
if (!$result) {
    // Se falhar, exibe um erro
    echo "Erro na consulta: " . $mysqli->error;
    exit;
}

// Conta o número de produtos
$totalProdutos = $result->num_rows;

// Exibe a tabela apenas se houver produtos
if ($result->num_rows > 0) {

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

// Exibe a quantidade de produtos no final da tabela
echo "<p><strong>Total de produtos: $totalProdutos</strong></p>";
?>
