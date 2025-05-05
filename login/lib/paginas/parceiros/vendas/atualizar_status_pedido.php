<?php
session_start();
include('../../../conexao.php'); // Conexão com o banco

if (!isset($_SESSION['id']) || !isset($_POST['num_pedido']) || !isset($_POST['novo_status'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Requisição inválida.']);
    exit;
}

$id_parceiro = $_SESSION['id'];
$num_pedido = filter_var($_POST['num_pedido'], FILTER_VALIDATE_INT);
$novo_status = filter_var($_POST['novo_status'], FILTER_VALIDATE_INT);
$codigo_retirada = isset($_POST['codigo_retirada']) ? trim($_POST['codigo_retirada']) : null;

if (!$num_pedido || $novo_status === null) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
    exit;
}

// Verifica o código de retirada, se enviado
if ($codigo_retirada !== null) {
    $query_codigo = "SELECT codigo_retirada FROM pedidos WHERE id_parceiro = ? AND num_pedido = ?";
    $stmt_codigo = $mysqli->prepare($query_codigo);
    $stmt_codigo->bind_param("ii", $id_parceiro, $num_pedido);
    $stmt_codigo->execute();
    $result_codigo = $stmt_codigo->get_result();

    if ($result_codigo->num_rows === 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Pedido não encontrado.']);
        exit;
    }

    $pedido = $result_codigo->fetch_assoc();
    if ($pedido['codigo_retirada'] !== $codigo_retirada) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Código de retirada inválido.']);
        exit;
    }

    // Atualiza a data de finalização se o código for válido
    $data_finalizacao = date('Y-m-d H:i:s');
    $query_update_finalizacao = "UPDATE pedidos SET data_finalizacao = ? WHERE id_parceiro = ? AND num_pedido = ?";
    $stmt_finalizacao = $mysqli->prepare($query_update_finalizacao);
    $stmt_finalizacao->bind_param("sii", $data_finalizacao, $id_parceiro, $num_pedido);
    $stmt_finalizacao->execute();
    $stmt_finalizacao->close();

    $stmt_codigo->close();
}

// Atualiza o status do pedido
$query = "UPDATE pedidos SET status_parceiro = ? WHERE id_parceiro = ? AND num_pedido = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("iii", $novo_status, $id_parceiro, $num_pedido);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Status do pedido atualizado com sucesso.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar o status do pedido.']);
}

$stmt->close();
$mysqli->close();
?>