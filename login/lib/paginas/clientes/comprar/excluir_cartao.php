<?php
include('../../../conexao.php'); // Conexão com o banco

header('Content-Type: application/json');

if (!isset($_GET['id_cartao'])) {
    echo json_encode(['success' => false, 'message' => 'ID do cartão não fornecido.']);
    exit;
}

$id_cartao = intval($_GET['id_cartao']);

$stmt = $mysqli->prepare("DELETE FROM cartoes_clientes WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $id_cartao);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Cartão excluído com sucesso.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir o cartão.']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Erro na preparação da consulta.']);
}
?>