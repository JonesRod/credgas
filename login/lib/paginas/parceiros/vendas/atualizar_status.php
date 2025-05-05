<?php
include('../../../conexao.php'); // Conexão com o banco

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $num_pedido = $_POST['num_pedido'] ?? null;
    $status = $_POST['status'] ?? null;

    if ($num_pedido && $status) {
        $query = "UPDATE pedidos SET status_parceiro = ? WHERE num_pedido = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ii", $status, $num_pedido);

        if ($stmt->execute()) {
            echo "Status atualizado com sucesso.";
        } else {
            http_response_code(500);
            echo "Erro ao atualizar o status.";
        }

        $stmt->close();
    } else {
        http_response_code(400);
        echo "Dados inválidos.";
    }
} else {
    http_response_code(405);
    echo "Método não permitido.";
}
?>