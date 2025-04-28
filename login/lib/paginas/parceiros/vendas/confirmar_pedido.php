<?php
session_start();
include('../../../conexao.php'); // Conexão com o banco

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($_SESSION['id'], $data['num_pedido'], $data['status_cliente'], $data['status_parceiro'], $data['produtos_confirmados'], $data['valor_produtos_confirmados'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
    exit;
}

$id_parceiro = $_SESSION['id'];
$num_pedido = $data['num_pedido'];
$status_cliente = $data['status_cliente'];
$status_parceiro = $data['status_parceiro'];
$produtos_confirmados = $data['produtos_confirmados'];
$valor_produtos_confirmados = $data['valor_produtos_confirmados'];

// Atualiza o status do pedido e os produtos confirmados no banco de dados
$query = "UPDATE pedidos SET status_cliente = ?, status_parceiro = ?, produtos_confirmados = ?, valor_produtos_confirmados = ? WHERE id_parceiro = ? AND num_pedido = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("iisdii", $status_cliente, $status_parceiro, $produtos_confirmados, $valor_produtos_confirmados, $id_parceiro, $num_pedido);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Pedido confirmado com sucesso.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao confirmar o pedido.']);
}

$stmt->close();
$mysqli->close();
?>