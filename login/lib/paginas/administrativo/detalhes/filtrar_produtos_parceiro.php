<?php
include('../../../conexao.php');

// Obtém os filtros enviados
$categoria = $_POST['categoria'];
$status = json_decode($_POST['statusPro'], true);
$id_parceiro = $_POST['id'];

// Monta a consulta SQL
$sql = "SELECT * FROM produtos WHERE id_parceiro = " . intval($id_parceiro);

// Filtro por categoria
if (!empty($categoria)) {
    $sql .= " AND categoria = '" . $mysqli->real_escape_string($categoria) . "'";
}

// Filtro por status
$statusConditions = [];

if (!empty($status)) {
    // Filtra por status "ativo" ou "inativo"
    if (in_array("ativoPro", $status)) {
        $statusConditions[] = "produto_aprovado = '1'";
    }
    if (in_array("inativoPro", $status)) {
        $statusConditions[] = "produto_aprovado = '0'";
    }
    if (in_array("crediarioVende", $status)) {
        $statusConditions[] = "vende_crediario = '1'";
    }
    if (in_array("oculto", $status)) {
        $statusConditions[] = "oculto = '1'";
    }

    // Filtra por novidades (últimos 30 dias)
    if (in_array("novidades", $status)) {
        $statusConditions[] = "data >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND produto_aprovado = '1'";
    }

    // Filtra por promoção
    if (in_array("promocao", $status)) {
        $statusConditions[] = "promocao = '1'";
    }

    // Filtra por frete grátis
    if (in_array("frete-gratis", $status)) {
        $statusConditions[] = "frete_gratis = '1' OR (promocao = '1' AND frete_gratis_promocao = '1')";
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
    echo "<table>";
    /*echo "<thead><tr><th>Nome Fantasia</th><th>Cidade/Estado</th><th>Categoria</th><th>Status</th><th>Ações</th></tr></thead>";*/
    echo "<tbody>";

    // Exibe os produtos
    while ($produto = $result->fetch_assoc()) {
        // Obtém a primeira imagem
        $imagens = explode(',', $produto['imagens']);
        $primeiraImagem = $imagens[0];

        echo "<tr>";
        echo "<td>" . date('d/m/Y', strtotime($produto['data'])) . "</td>";
        echo "<td><img src='../../parceiros/produtos/img_produtos/" . $primeiraImagem . "' alt='Imagem do Produto' class='imagem'></td>";
        echo "<td>" . htmlspecialchars($produto['nome_produto']) . "</td>";
        echo "<td>" . htmlspecialchars($produto['categoria']) . "</td>";
        echo "<td><a href='detalhes_produto.php?id=" . $produto['id_produto'] . "' class='detalhes-link'>Ver Detalhes</a></td>";
        echo "</tr>";
    }

    echo '</tbody>';
    echo "</table>";

} else {
    echo "<tr><td colspan='100%'><div class='msg'>Nenhum produto encontrado.</div></td></tr>";
}

?>
<style>
.msg {
    text-align: center;
    font-weight: bold;
    width: 100%;
    padding: 15px 0;
    background-color: #f8f8f8;
    border: 0px solid #ddd;
    color: dimgray;
}

</style>
