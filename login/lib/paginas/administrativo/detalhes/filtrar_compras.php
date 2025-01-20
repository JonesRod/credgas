<?php
include('../../../conexao.php');

// Recupera os filtros enviados
$id = isset($_POST['id_cliente']) ? $mysqli->real_escape_string($_POST['id_cliente']) : '';
$dataInicio = isset($_POST['data_inicio']) ? $mysqli->real_escape_string($_POST['data_inicio']) : '';
$dataFim = isset($_POST['data_fim']) ? $mysqli->real_escape_string($_POST['data_fim']) : '';
$formaPagamento = isset($_POST['forma_pagamento']) ? $mysqli->real_escape_string($_POST['forma_pagamento']) : '';

// Monta a consulta SQL
$sql = "SELECT * FROM vendas WHERE id_cliente = $id";

// Aplica os filtros
if (!empty($dataInicio) && !empty($dataFim)) {
    $sql .= " AND DATE(data) BETWEEN '$dataInicio' AND '$dataFim'";
}

if (!empty($formaPagamento)) {
    $sql .= " AND forma_pagamento = '$formaPagamento'";
}

$sql .= " ORDER BY data DESC";

$result = $mysqli->query($sql);
// Verifica se a consulta foi executada com sucesso
if (!$result) {
    echo "Erro na consulta: " . $mysqli->error;
    exit;
}
// Conta o número de produtos
$totalCompras = $result->num_rows;

// Retorna os resultados em formato de tabela
if ($totalCompras > 0) {
    echo "<table>";
    echo "<tbody>";
        while ($compra = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . date('d/m/Y', strtotime($compra['data'])) . "</td>";
            echo "<td>" . htmlspecialchars(str_pad($compra['nu_pedido'], 4, '0', STR_PAD_LEFT)) . "</td>";
            echo "<td>" . htmlspecialchars($compra['produtos']) . "</td>";
            echo "<td>R$ " . htmlspecialchars(number_format($compra['valor_produtos'], 2, ',', '.')) . "</td>";
            echo "<td><a href='detalhes_compras.php?id=" . htmlspecialchars($compra['id']) . "&id_cliente=" . htmlspecialchars($compra['id_cliente']) . "' class='detalhes-link'>Ver Detalhes</a></td>";
            echo "</tr>";
        }
    echo '</tbody>';
    echo "</table>";
} else {
    $totalCompras = 0;
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
