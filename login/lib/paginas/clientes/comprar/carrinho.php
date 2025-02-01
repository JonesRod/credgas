<?php
include('../../../conexao.php');

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_cliente = $_POST['id_cli'] ?? '';
    $id_produto = $_POST['id_produto_carrinho'] ?? '';
    $qt = $_POST['quantidade'] ?? '1';
    //var_dump($_POST);

    $sql_produtos = $mysqli->query("SELECT * FROM produtos WHERE id_produto = $id_produto") or die($mysqli->error);
    $dadosProduto = $sql_produtos->fetch_assoc();

    $id_parceiro = $dadosProduto['id_parceiro'] ?? '';
    $produto_nome = $dadosProduto['nome_produto'] ?? '';
    $promocao = $dadosProduto['promocao'] ?? '';

    if ($promocao == "sim") {
        $valor_produto = $dadosProduto['valor_promocao'] ?? '';

        if ($dadosProduto['frete_gratis_promocao'] == "sim") {
            $frete = '0';
        } else {
            $frete = $dadosProduto['valor_frete_promocao'] ?? '';
        }
    } else {
        $valor_produto = $dadosProduto['valor_produto'] ?? '';

        if ($dadosProduto['frete_gratis'] == "sim") {
            $frete = '0';
        } else {
            $frete = $dadosProduto['valor_frete'] ?? '';
        }
    }

    $taxa_padrao = floatval($dadosProduto['taxa_padrao'] ?? 0);
    $valor_base = floatval($valor_produto);
    $valor_produto = $valor_base + (($valor_base * $taxa_padrao) / 100);

    $total = $valor_produto * $qt;
    
    if (!empty($id_produto) && !empty($id_cliente)) {
        try {
            $sql = "INSERT INTO carrinho (data, id_parceiro, id_cliente, id_produto, valor_produto, taxa_padrao, frete, qt, total)
                VALUES (Now(), '$id_parceiro', '$id_cliente', '$id_produto', '$valor_produto', '$taxa_padrao', '$frete', '$qt', '$total')";

            $deu_certo = $mysqli->query(query: $sql) or die($mysqli->error);

            echo json_encode(["status" => "success", "message" => "Produto adicionado ao carrinho!"]);
        } catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => "Erro ao adicionar: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Dados incompletos!"]);
    }
    
} else {
    echo json_encode(["status" => "error", "message" => "Método inválido!"]);
}
?>
