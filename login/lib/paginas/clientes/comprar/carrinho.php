<?php
include('../../../conexao.php');

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_cliente = $_POST['id_cliente'] ?? ''; // O formulário deve ter um campo com name="id_cliente" ou id="id_cliente"
    $id_produto = $_POST['id_produto_carrinho'] ?? ''; // O formulário deve ter um campo com name="id_produto_carrinho" ou id="id_produto_carrinho"
    $qt = intval($_POST['quantidade'] ?? '1'); // O formulário deve ter um campo com name="quantidade" ou id="quantidade"

    if (empty($id_cliente) || empty($id_produto) || $qt < 1) {
        echo json_encode(["status" => "error", "message" => "Dados inválidos!"]);
        exit;
    }

    $sql_produtos = $mysqli->prepare("SELECT * FROM produtos WHERE id_produto = ?");
    $sql_produtos->bind_param("i", $id_produto);
    $sql_produtos->execute();
    $dadosProduto = $sql_produtos->get_result()->fetch_assoc();

    if (!$dadosProduto) {
        echo json_encode(["status" => "error", "message" => "Produto não encontrado!"]);
        exit;
    }

    $id_parceiro = $dadosProduto['id_parceiro'] ?? '';
    $produto_nome = $dadosProduto['nome_produto'] ?? '';
    $promocao = $dadosProduto['promocao'] ?? '';

    if ($promocao == "1") {
        $valor_produto = $dadosProduto['valor_promocao'] ?? 0;

        $frete = ($dadosProduto['frete_gratis_promocao'] == "1") ? 0 : ($dadosProduto['valor_frete_promocao'] ?? 0);
    } else {
        $valor_produto = $dadosProduto['valor_produto'] ?? 0;

        $frete = ($dadosProduto['frete_gratis'] == "1") ? 0 : ($dadosProduto['valor_frete'] ?? 0);
    }

    $taxa_padrao = floatval($dadosProduto['taxa_padrao'] ?? 0);
    $valor_base = floatval($valor_produto);
    $valor_produto = $valor_base + (($valor_base * $taxa_padrao) / 100);

    $total = ($valor_produto * $qt) + $frete;

    // Verifica se o produto já está no carrinho do cliente
    $sql_check = $mysqli->prepare("SELECT * FROM carrinho WHERE id_cliente = ? AND id_produto = ?");
    $sql_check->bind_param("ii", $id_cliente, $id_produto);
    $sql_check->execute();
    $result_check = $sql_check->get_result();

    if ($result_check->num_rows > 0) {
        // Produto já está no carrinho, atualiza a quantidade
        $row = $result_check->fetch_assoc();
        $new_qt = $row['qt'] + $qt;
        $new_total = ($valor_produto * $new_qt) + $frete;

        $sql_update = $mysqli->prepare("UPDATE carrinho SET qt = ?, total = ? WHERE id_cliente = ? AND id_produto = ?");
        $sql_update->bind_param("idii", $new_qt, $new_total, $id_cliente, $id_produto);
        $sql_update->execute();

        echo json_encode(["status" => "success", "message" => "Quantidade atualizada no carrinho!"]);
    } else {
        // Produto não está no carrinho, insere um novo
        $sql_insert = $mysqli->prepare("INSERT INTO carrinho (data, id_parceiro, id_cliente, id_produto, valor_produto, taxa_padrao, frete, qt, total) VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?)");
        $sql_insert->bind_param("iiidddid", $id_parceiro, $id_cliente, $id_produto, $valor_produto, $taxa_padrao, $frete, $qt, $total);
        $sql_insert->execute();

        echo json_encode(["status" => "success", "message" => "Produto adicionado ao carrinho!"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Método inválido!"]);
}
?>