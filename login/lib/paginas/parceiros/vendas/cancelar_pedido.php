<?php
session_start();
include('../../../conexao.php'); // Conexão com o banco

// Verifica se os dados necessários foram enviados
if (!isset($_POST['num_pedido']) || !isset($_POST['motivo_cancelamento']) || !isset($_POST['data_hora_cliente']) || !isset($_POST['id_parceiro'])) {
    http_response_code(400); // Código de erro para requisição inválida
    echo json_encode(['success' => false, 'message' => 'Dados incompletos.']);
    exit;
}

$num_pedido = $_POST['num_pedido'];
$motivo_cancelamento = $_POST['motivo_cancelamento'];
$data_hora_cliente = $_POST['data_hora_cliente'];
$id_parceiro = $_POST['id_parceiro'];

// Validações adicionais
if (empty($num_pedido) || empty($motivo_cancelamento) || empty($data_hora_cliente) || empty($id_parceiro)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios.']);
    exit;
}

// Verifica se o pedido pertence ao parceiro
$query_verifica = "SELECT num_pedido FROM pedidos WHERE num_pedido = ? AND id_parceiro = ?";
$stmt_verifica = $mysqli->prepare($query_verifica);

if (!$stmt_verifica) {
    http_response_code(500); // Código de erro para erro interno do servidor
    echo json_encode(['success' => false, 'message' => 'Erro ao preparar a consulta de verificação.']);
    exit;
}

$stmt_verifica->bind_param("ii", $num_pedido, $id_parceiro);
$stmt_verifica->execute();
$result_verifica = $stmt_verifica->get_result();

if ($result_verifica->num_rows === 0) {
    http_response_code(403); // Código de erro para acesso proibido
    echo json_encode(['success' => false, 'message' => 'Pedido não pertence ao parceiro informado.']);
    exit;
}

$stmt_verifica->close();

// Atualiza o status do pedido para cancelado no banco de dados
$query = "UPDATE pedidos SET status_parceiro = 4, data_cancelamento = ?, motivo_cancelamento = ? WHERE num_pedido = ?";
$stmt = $mysqli->prepare($query);

if (!$stmt) {
    http_response_code(500); // Código de erro para erro interno do servidor
    echo json_encode(['success' => false, 'message' => 'Erro ao preparar a consulta.']);
    exit;
}

$stmt->bind_param("ssi", $data_hora_cliente, $motivo_cancelamento, $num_pedido);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Pedido cancelado com sucesso.']);
    } else {
        http_response_code(404); // Código de erro para recurso não encontrado
        echo json_encode(['success' => false, 'message' => 'Pedido não encontrado.']);
    }
} else {
    http_response_code(500); // Código de erro para erro interno do servidor
    echo json_encode(['success' => false, 'message' => 'Erro ao cancelar o pedido.']);
}

$stmt->close();
$mysqli->close();
?>