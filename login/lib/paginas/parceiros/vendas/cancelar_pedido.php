<?php
session_start();
include('../../../conexao.php'); // Conexão com o banco

if (!isset($_SESSION['id']) || !isset($_POST['num_pedido']) || !isset($_POST['motivo_cancelamento'])) {
    http_response_code(400);
    echo "Requisição inválida.";
    exit;
}

$id_parceiro = $_SESSION['id'];
$num_pedido = filter_var($_POST['num_pedido'], FILTER_VALIDATE_INT);
$motivo_cancelamento = trim($_POST['motivo_cancelamento']);

if (!$num_pedido || empty($motivo_cancelamento)) {
    http_response_code(400);
    echo "Dados inválidos.";
    exit;
}

$query = "UPDATE pedidos SET status_cliente = 0, status_parceiro = 4, motivo_cancelamento = ? WHERE id_parceiro = ? AND num_pedido = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("sii", $motivo_cancelamento, $id_parceiro, $num_pedido);

if ($stmt->execute()) {
    echo "Pedido cancelado com sucesso.";
} else {
    http_response_code(500);
    echo "Erro ao cancelar o pedido.";
}

$stmt->close();
$mysqli->close();
?>