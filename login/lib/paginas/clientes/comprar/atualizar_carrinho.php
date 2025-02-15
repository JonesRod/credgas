<?php
try {
    header('Content-Type: application/json');
    $dados = json_decode(file_get_contents("php://input"), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Erro ao decodificar JSON: " . json_last_error_msg());
    }

    include('../../../conexao.php'); // Conexão com o banco

    session_start();

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Método inválido");
    }

    $id_cliente = $dados['id_cliente'] ?? null;
    $id_parceiro = $dados['id_parceiro'] ?? null;
    $produtos = $dados['produtos'] ?? [];

    if (!$id_cliente || !$id_parceiro || empty($produtos)) {
        throw new Exception("Dados incompletos");
    }

    foreach ($produtos as $produto) {
        $id_produto = $produto['id_produto'] ?? null;
        $quantidade = $produto['quantidade'] ?? null;

        if (!$id_produto || $quantidade === null) {
            throw new Exception("Produto inválido");
        }

        if ($quantidade <= 0) {
            $stmt = $mysqli->prepare("DELETE FROM carrinho WHERE id_cliente = ? AND id_produto = ?");
            $stmt->bind_param("ii", $id_cliente, $id_produto);
            $stmt->execute();
        } else {
            $stmt = $mysqli->prepare("UPDATE carrinho SET qt = ? WHERE id_cliente = ? AND id_produto = ?");
            if (!$stmt) {
                throw new Exception("Erro ao preparar a query: " . $mysqli->error);
            }
            $stmt->bind_param("iii", $quantidade, $id_cliente, $id_produto);
            $stmt->execute();

            /*if ($stmt->affected_rows === 0) {
                throw new Exception("Nenhum item atualizado. Verifique se o produto já está no carrinho.");
            }*/
        }
    }

    echo json_encode(["status" => "sucesso", "mensagem" => "Carrinho atualizado com sucesso"]);

} catch (Exception $e) {
    echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
}
?>