<?php
session_start();
include('../../../conexao.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cliente = intval($_POST['id_cliente']);
    $id_parceiro = intval($_POST['id_parceiro']);
    $valor_frete = floatval($_POST['valor_frete']);
    $valor_total = floatval($_POST['valor_total']);
    $detalhes_produtos = $_POST['detalhes_produtos'];
    $entrega = $_POST['entrega'];
    $rua = $_POST['rua'];
    $bairro = $_POST['bairro'];
    $numero = $_POST['numero'];
    $contato = $_POST['contato'];
    $comentario = $_POST['comentario'];
    $entrada = floatval($_POST['entrada']);
    $restante = floatval($_POST['restante']);
    $tipo_entrada_crediario = $_POST['tipo_entrada_crediario'];
    $forma_pagamento = $_POST['forma_pagamento'];
    $forma_pagamento_entrada = $_POST['forma_pg_entrada'];
    $forma_pagamento_restante = $_POST['forma_pg_restante'];
    $qt_parcelas = intval($_POST['qt_parcelas']);

    $data_hora = date("Y-m-d H:i:s");

    $stmt = $mysqli->prepare("INSERT INTO pedidos (data, id_cliente, id_parceiro, produtos, valor_frete, valor, 
forma_pagamento, entrada, forma_pg_entrada, restante, forma_pg_restante, 
qt_parcelas, tipo_entrega, endereco_entrega, num_entrega, bairro_entrega, 
contato_recebedor, comentario, status_cliente, status_parceiro) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $status_cliente = '0';
        $status_parceiro = '0';
        $stmt->bind_param("siisssssssssssssssii", $data_hora, $id_cliente, $id_parceiro, $detalhes_produtos, $valor_frete, $valor_total, 
        $forma_pagamento, $entrada, $forma_pagamento_entrada, $restante, $forma_pagamento_restante, 
        $qt_parcelas, $entrega, $rua, $numero, $bairro, 
        $contato, $comentario, $status_cliente, $status_parceiro);
        if ($stmt->execute()) {
            echo json_encode(["sucesso" => true, "num_pedido" => $stmt->insert_id]);
        } else {
            echo json_encode(["sucesso" => false, "erro" => $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["sucesso" => false, "erro" => $mysqli->error]);
    }
}
?>