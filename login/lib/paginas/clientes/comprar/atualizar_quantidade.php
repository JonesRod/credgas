<?php
include('../../../conexao.php'); // Arquivo de conexão com o banco

header('Content-Type: application/json');

$dados = json_decode(file_get_contents("php://input"), true);

if (!isset($dados['id_cliente']) || !isset($dados['id_produto']) || !isset($dados['quantidade'])) {
    echo json_encode(["status" => "erro", "mensagem" => "Dados incompletos"]);
    exit;
}

$id_cliente = intval($dados['id_cliente']);
$id_produto = intval($dados['id_produto']);
$quantidade = intval($dados['quantidade']);

if ($quantidade < 1) {
    echo json_encode(["status" => "erro", "mensagem" => "Quantidade inválida"]);
    exit;
}

// Atualiza a quantidade no banco de dados vinculando ao cliente correto
$stmt = $mysqli->prepare("UPDATE carrinho SET qt = ? WHERE id_cliente = ? AND id_produto = ?");
$stmt->bind_param("iii", $quantidade, $id_cliente, $id_produto);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(["status" => "sucesso", "mensagem" => "Quantidade atualizada"]);
} else {
    echo json_encode(["status" => "erro", "mensagem" => "Nenhum item atualizado"]);
}
?>
