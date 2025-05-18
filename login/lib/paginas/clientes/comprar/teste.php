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
$query_pedido = "SELECT formato_compra, tipo_entrega, codigo_retirada FROM pedidos WHERE id_parceiro = ? AND num_pedido = ?";
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
$formato_compra = $pedido['formato_compra'];
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

    if ($formato_compra === 'crediario') {
        // Buscar todos os dados da linha do pedido
        $query_pedido_full = "SELECT * FROM pedidos WHERE id_parceiro = ? AND num_pedido = ?";
        $stmt_full = $mysqli->prepare($query_pedido_full);
        $stmt_full->bind_param("ii", $id_parceiro, $num_pedido);
        $stmt_full->execute();
        $result_full = $stmt_full->get_result();
        if ($result_full->num_rows > 0) {
            $pedido_full = $result_full->fetch_assoc();

            // Monta o insert para vendas_crediario
            $query_insert = "INSERT INTO vendas_crediario (
                num_pedido, data, codigo_retirada, id_cliente, id_parceiro, produtos, produtos_confirmados, valor_frete, valor_produtos, valor_produtos_confirmados, saldo_usado, taxa_crediario, formato_compra, entrada, forma_pg_entrada, qt_parcela_entrada, valor_parcela_entrada, valor_restante, forma_pg_restante, qt_parcelas, valor_parcela, tipo_entrega, endereco_entrega, num_entrega, bairro_entrega, contato_recebedor, comentario, status_cliente, status_parceiro, motivo_cancelamento, data_finalizacao
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )";

            $stmt_insert = $mysqli->prepare($query_insert);
            $stmt_insert->bind_param(
                "issiiissdddssssidsdssssssssiiiss",
                $pedido_full['num_pedido'],
                $pedido_full['data'],
                $pedido_full['codigo_retirada'],
                $pedido_full['id_cliente'],
                $pedido_full['id_parceiro'],
                $pedido_full['produtos'],
                $pedido_full['produtos_confirmados'],
                $pedido_full['valor_frete'],
                $pedido_full['valor_produtos'],
                $pedido_full['valor_produtos_confirmados'],
                $pedido_full['saldo_usado'],
                $pedido_full['taxa_crediario'],
                $pedido_full['formato_compra'],
                $pedido_full['entrada'],
                $pedido_full['forma_pg_entrada'],
                $pedido_full['qt_parcela_entrada'],
                $pedido_full['valor_parcela_entrada'],
                $pedido_full['valor_restante'],
                $pedido_full['forma_pg_restante'],
                $pedido_full['qt_parcelas'],
                $pedido_full['valor_parcela'],
                $pedido_full['tipo_entrega'],
                $pedido_full['endereco_entrega'],
                $pedido_full['num_entrega'],
                $pedido_full['bairro_entrega'],
                $pedido_full['contato_recebedor'],
                $pedido_full['comentario'],
                $pedido_full['status_cliente'],
                $pedido_full['status_parceiro'],
                $pedido_full['motivo_cancelamento'],
                $pedido_full['data_finalizacao']
            );
            $stmt_insert->execute();
            $stmt_insert->close();
        }
        
        $stmt_full->close();

        echo json_encode(['success' => true, 'message' => 'Pedido finalizado com sucesso.']);
    }
    echo json_encode(['success' => true, 'message' => 'Pedido finalizado com sucesso.']);

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