<?php
    include('../../../conexao.php');

    session_start();

    if (isset($_GET['id']) && isset($_GET['id_cliente'])) {
        $id = $_GET['id'];
        $idCliente = $_GET['id_cliente'];

        $sql_delete = "DELETE FROM carrinho WHERE id = ? AND id_cliente = ?";
        $stmt_delete = $mysqli->prepare($sql_delete);
        $stmt_delete->bind_param("ii", $id, $idCliente);
        //if ($stmt = $conexao->prepare($query)) {
            
        if ($stmt_delete->execute()) {
            echo "sucesso";
        } else {
            echo "erro";
        }

        $stmt_delete->close();
        /*} else {
            echo "Erro ao preparar a query: " . $conexao->error; // Log do erro na preparação
        }*/

    } else {
        echo "Erro: ID do produto ou cliente não fornecido.";
    }
?>