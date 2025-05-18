<?php
session_start();
include('../../../conexao.php'); // Conexão com o banco

if (!isset($_SESSION['id']) || !isset($_POST['num_pedido']) || !isset($_POST['novo_status'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Requisição inválida.']);
    exit;
}

// Certifique-se de receber os parâmetros necessários:
$id_parceiro = isset($_POST['id_parceiro']) ? intval($_POST['id_parceiro']) : 0;
$num_pedido = isset($_POST['num_pedido']) ? intval($_POST['num_pedido']) : 0;
$novo_status = filter_var($_POST['novo_status'], FILTER_VALIDATE_INT);
$codigo_retirada = isset($_POST['codigo_retirada']) ? trim($_POST['codigo_retirada']) : null;

if (!$num_pedido || $novo_status === null) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
    exit;
}

// Busca do pedido
$query_pedido = "SELECT tipo_entrega, codigo_retirada FROM pedidos WHERE id_parceiro = ? AND num_pedido = ?";
$stmt_pedido = $mysqli->prepare($query_pedido);
$stmt_pedido->bind_param("ii", $id_parceiro, $num_pedido);
$stmt_pedido->execute();
$result_pedido = $stmt_pedido->get_result();

if ($result_pedido->num_rows === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Pedido não encontrado.']);
    exit;
}

$pedido = $result_pedido->fetch_assoc();
$tipo_entrega = $pedido['tipo_entrega'];
$codigo_retirada_pedido = $pedido['codigo_retirada'];

// para finalizar o pedido, o código de retirada deve ser fornecido
if ($novo_status === 7) {
    if (!$codigo_retirada || $codigo_retirada !== $codigo_retirada_pedido) {
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
}

$stmt_pedido->close();

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