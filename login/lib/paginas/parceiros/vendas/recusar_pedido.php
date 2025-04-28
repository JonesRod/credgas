<?php
session_start();
include('../../../conexao.php'); // Conexão com o banco

if (!isset($_SESSION['id']) || !isset($_POST['num_pedido'])) {
    http_response_code(400);
    echo "Requisição inválida.";
    exit;
}

$id_parceiro = $_SESSION['id'];
$num_pedido = $_POST['num_pedido'];

// Atualiza o status do pedido no banco de dados
$query = "UPDATE pedidos SET status_cliente = 3, status_parceiro = 3 WHERE id_parceiro = ? AND num_pedido = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("ii", $id_parceiro, $num_pedido);

if ($stmt->execute()) {
    echo "Pedido recusado com sucesso.";
} else {
    http_response_code(500);
    echo "Erro ao recusar o pedido.";
}

$stmt->close();
$mysqli->close();
?>