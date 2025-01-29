<?php
include('../../../conexao.php');

// Recupera os filtros enviados
$id = isset($_POST['id_cliente']) ? $mysqli->real_escape_string($_POST['id_cliente']) : '';
$nu_pedido = isset($_POST['nu_pedido']) ? $mysqli->real_escape_string($_POST['nu_pedido']) : '';
// Formata o número para ter exatamente 4 dígitos, preenchendo com zeros à esquerda
//$nu_pedido = str_pad($nu_pedido, 4, '0', STR_PAD_LEFT);

$dataInicioH = isset($_POST['data_inicioH']) ? $mysqli->real_escape_string($_POST['data_inicioH']) : '';
$dataFimH = isset($_POST['data_fimH']) ? $mysqli->real_escape_string($_POST['data_fimH']) : '';
$formaPagamentoH = isset($_POST['forma_pagamentoH']) ? $mysqli->real_escape_string($_POST['forma_pagamentoH']) : '';

// Monta a consulta SQL
$sql = "SELECT * FROM historico_crediario WHERE id_cliente = $id";

// Aplica os filtros
if (!empty($nu_pedido)) {
    $sql .= " AND nu_pedido = '$nu_pedido'";
}

if (!empty($dataInicioH) && !empty($dataFimH)) {
    $sql .= " AND DATE(data) BETWEEN '$dataInicioH' AND '$dataFimH'";
}

if (!empty($formaPagamentoH)) {
    $sql .= " AND forma_pagamento = '$formaPagamentoH'";
}

$sql .= " ORDER BY data DESC";

$result = $mysqli->query($sql);
// Verifica se a consulta foi executada com sucesso
if (!$result) {
    echo "Erro na consulta: " . $mysqli->error;
    exit;
}
// Conta o número de produtos
$totalComprasH = $result->num_rows;

// Retorna os resultados em formato de tabela
if ($totalComprasH > 0) {
    echo "<table>";
    echo "<tbody>";
        while ($compra = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . date('d/m/Y', strtotime($compra['data'])) . "</td>";
            echo "<td>" . htmlspecialchars(str_pad($compra['nu_pedido'], 4, '0', STR_PAD_LEFT)) . "</td>";
            echo "<td>" . htmlspecialchars($compra['produtos']) . "</td>";
            echo "<td>R$ " . htmlspecialchars(number_format($compra['valor_produtos'], 2, ',', '.')) . "</td>";
            echo "<td><a href='detalhes_compras_crediario.php?id=" . htmlspecialchars($compra['id']) . "&id_cliente=" . htmlspecialchars($compra['id_cliente']) . "' class='detalhes-link'>Ver Detalhes</a></td>";
            echo "</tr>";
        }
    echo '</tbody>';
    echo "</table>";
} else {
    $totalComprasH = 0;
    echo "<tr><td colspan='100%'><div class='msg'>Nenhum compra realizada ainda!</div></td></tr>";
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
