<?php
header("Content-Type: application/json");
include('../../../conexao.php'); // Conexão com o banco

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["success" => false, "message" => "Dados inválidos."]);
    exit;
}

$id_cliente = $data['id_cliente'] ?? null;
$id_parceiro = $data['id_parceiro'] ?? null;
$valor_frete = $data['valor_frete'] ?? 0;
$valor_total = $data['valor_total'] ?? 0;
$entrada_saldo = $data['entrada_saldo'] ?? 0;
$detalhes_produtos = $data['detalhes_produtos'] ?? '';
$entrega = $data['entrega'] ?? '';
$rua = $data['rua'] ?? '';
$bairro = $data['bairro'] ?? '';
$numero = $data['numero'] ?? '';
$contato = $data['contato'] ?? '';
$comentario = $data['comentario'] ?? '';

// Inserir os dados no banco
$query = "INSERT INTO compras (id_cliente, id_parceiro, valor_frete, valor_total, entrada_saldo, detalhes_produtos, entrega, rua, bairro, numero, contato, comentario) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("iiddssssssss", $id_cliente, $id_parceiro, $valor_frete, $valor_total, $entrada_saldo, $detalhes_produtos, $entrega, $rua, $bairro, $numero, $contato, $comentario);

if ($stmt->execute()) {
    // manda a notificação para o parceiro
    $stmt = $mysqli->prepare("INSERT INTO contador_notificacoes_parceiro (data, id_parceiro, pedidos) VALUES (?, ?, 1)");
    $stmt->bind_param("si", $data_hora, $id_parceiro);
    $stmt->execute();
    $stmt->close();
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Erro ao salvar os dados no banco."]);
}
?>