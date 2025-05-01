<?php
session_start();
include('../../../conexao.php'); // Conexão com o banco

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($_SESSION['id'], $data['num_pedido'], $data['produtos']) || !is_array($data['produtos'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
    exit;
}

$id_parceiro = $_SESSION['id'];
$num_pedido = $data['num_pedido'];
$produtos_confirmados = [];
$valor_produtos_confirmados = 0;

// Processar os produtos confirmados
foreach ($data['produtos'] as $produto) {
    if (!isset($produto['nome'], $produto['quantidade'], $produto['valor_unitario'], $produto['total'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Dados do produto inválidos.']);
        exit;
    }

    $produtos_confirmados[] = implode('/', [
        $produto['nome'],
        $produto['quantidade'],
        number_format($produto['valor_unitario'], 2, '.', ''),
        number_format($produto['total'], 2, '.', '')
    ]);

    $valor_produtos_confirmados += $produto['total'];
}

$produtos_confirmados_str = implode('|', $produtos_confirmados);

// Atualiza o status do pedido e os produtos confirmados no banco de dados
$query = "UPDATE pedidos SET status_cliente = 1, status_parceiro = 1, produtos_confirmados = ?, valor_produtos_confirmados = ? WHERE id_parceiro = ? AND num_pedido = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("sdii", $produtos_confirmados_str, $valor_produtos_confirmados, $id_parceiro, $num_pedido);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Pedido confirmado com sucesso.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao confirmar o pedido.']);
}

$stmt->close();
$mysqli->close();
?>