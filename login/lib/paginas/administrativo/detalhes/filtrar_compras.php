<?php
include('../../../conexao.php');

// Recupera os filtros enviados
$dataInicio = $mysqli->real_escape_string($_POST['data_inicio'] ?? '');
$dataFim = $mysqli->real_escape_string($_POST['data_fim'] ?? '');
$formaPagamento = $mysqli->real_escape_string($_POST['forma_pagamento'] ?? '');

// Monta a consulta SQL
$sql = "SELECT data, nu_pedido, produtos, valor_produtos, id_cliente FROM vendas WHERE 1=1";

// Aplica os filtros
if (!empty($dataInicio) && !empty($dataFim)) {
    $sql .= " AND DATE(data) BETWEEN '$dataInicio' AND '$dataFim'";
}

if (!empty($formaPagamento)) {
    $sql .= " AND forma_pagamento = '$formaPagamento'";
}

$sql .= " ORDER BY data DESC";

$result = $mysqli->query($sql);

// Conta o número de produtos
$totalCompras = $result->num_rows;

// Retorna os resultados em formato de tabela
if ($result && $result->num_rows > 0) {
    while ($compra = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . date('d/m/Y', strtotime($compra['data'])) . "</td>";
        echo "<td>" . htmlspecialchars($compra['nu_pedido']) . "</td>";
        echo "<td>" . htmlspecialchars($compra['produtos']) . "</td>";
        echo "<td>" . htmlspecialchars($compra['valor_produtos']) . "</td>";
        echo "<td><a href='detalhes_compras.php?id=" . htmlspecialchars($compra['id_cliente']) . "' class='detalhes-link'>Ver Detalhes</a></td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='5'>Nenhuma compra encontrada.</td></tr>";
}
?>
