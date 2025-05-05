<?php
session_start();
include('../../../conexao.php'); // Conexão com o banco

header('Content-Type: application/json'); // Garantir que a resposta seja JSON
ini_set('display_errors', 0); // Desativar exibição de erros no navegador
error_reporting(0); // Suprimir erros para evitar HTML na resposta

// Decodificar o corpo da requisição JSON
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id_parceiro']) || !isset($data['num_pedido']) || !isset($data['motivo_cancelamento']) || !isset($data['data_hora_cliente'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados incompletos.']);
    exit;
}

$id_parceiro = $data['id_parceiro'];
$num_pedido = $data['num_pedido'];
$motivo_cancelamento = $data['motivo_cancelamento'];
$data_finalizacao = $data['data_hora_cliente']; // Data e hora enviada pelo cliente
$status_parceiro = 3; // Status do parceiro ao recusar o pedido

// Atualiza o status do pedido no banco de dados
$query = "UPDATE pedidos SET status_parceiro = ?, data_finalizacao = ?, motivo_cancelamento = ? WHERE id_parceiro = ? AND num_pedido = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("issii", $status_parceiro, $data_finalizacao, $motivo_cancelamento, $id_parceiro, $num_pedido);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Pedido recusado com sucesso.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao recusar o pedido.']);
}

$stmt->close();
$mysqli->close();
?>