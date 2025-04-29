<?php
session_start();
include('../../../conexao.php'); // Conexão com o banco

if (!isset($_SESSION['id']) || !isset($_POST['num_pedido']) || !isset($_POST['novo_status'])) {
    http_response_code(400);
    echo "Requisição inválida.";
    exit;
}

$id_parceiro = $_SESSION['id'];
$num_pedido = filter_var($_POST['num_pedido'], FILTER_VALIDATE_INT);
$novo_status = filter_var($_POST['novo_status'], FILTER_VALIDATE_INT);

if (!$num_pedido || $novo_status === null) {
    http_response_code(400);
    echo "Dados inválidos.";
    exit;
}

$query = "UPDATE pedidos SET status_cliente = ? WHERE id_parceiro = ? AND num_pedido = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("iii", $novo_status, $id_parceiro, $num_pedido);

if ($stmt->execute()) {
    echo "Status do pedido atualizado com sucesso.";
} else {
    http_response_code(500);
    echo "Erro ao atualizar o status do pedido.";
}

$stmt->close();
$mysqli->close();
?>