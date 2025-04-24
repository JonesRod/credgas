<?php
include('../../../conexao.php'); // Conexão com o banco

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['num_pedido']) || !isset($data['status_cliente'])) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
    exit;
}

$num_pedido = $data['num_pedido'];
$status_cliente = $data['status_cliente'];

$query = "UPDATE pedidos SET status_cliente = ? WHERE num_pedido = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("ii", $status_cliente, $num_pedido);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar o pedido.']);
}

$stmt->close();
$mysqli->close();
?>
