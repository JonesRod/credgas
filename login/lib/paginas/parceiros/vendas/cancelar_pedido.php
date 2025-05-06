<?php
session_start();
include('../../../conexao.php'); // Conexão com o banco

header('Content-Type: application/json'); // Força o retorno como JSON

try {
    // Verifica se os dados necessários foram enviados
    if (!isset($_POST['num_pedido'], $_POST['id_parceiro'], $_POST['motivo_cancelamento'], $_POST['data_hora_cliente'])) {
        throw new Exception('Dados incompletos.');
    }

    $num_pedido = $_POST['num_pedido'];
    $id_parceiro = $_POST['id_parceiro'];
    $motivo_cancelamento = $_POST['motivo_cancelamento'];
    $data_hora_cliente = $_POST['data_hora_cliente'];

    // Validações adicionais
    if (empty($num_pedido) || empty($id_parceiro) || empty($motivo_cancelamento) || empty($data_hora_cliente)) {
        throw new Exception('Todos os campos são obrigatórios.');
    }

    // Verifica se o pedido pertence ao parceiro
    $query_verifica = "SELECT num_pedido FROM pedidos WHERE num_pedido = ? AND id_parceiro = ?";
    $stmt_verifica = $mysqli->prepare($query_verifica);
    if (!$stmt_verifica) {
        throw new Exception('Erro ao preparar a consulta de verificação: ' . $mysqli->error);
    }
    $stmt_verifica->bind_param("ii", $num_pedido, $id_parceiro);
    $stmt_verifica->execute();
    $result_verifica = $stmt_verifica->get_result();

    if ($result_verifica->num_rows === 0) {
        throw new Exception('Pedido não pertence ao parceiro informado.');
    }
    $stmt_verifica->close();

    // Atualiza o status do pedido para cancelado no banco de dados
    $query = "UPDATE pedidos SET status_parceiro = 4, data_finalizacao = ?, motivo_cancelamento = ? WHERE num_pedido = ?";
    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        throw new Exception('Erro ao preparar a consulta: ' . $mysqli->error);
    }
    $stmt->bind_param("ssi", $data_hora_cliente, $motivo_cancelamento, $num_pedido);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Pedido cancelado com sucesso.']);
        } else {
            throw new Exception('Pedido não encontrado.');
        }
    } else {
        throw new Exception('Erro ao cancelar o pedido: ' . $stmt->error);
    }
    $stmt->close();
} catch (Exception $e) {
    http_response_code(400); // Código de erro para requisição inválida
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    $mysqli->close();
}
?>