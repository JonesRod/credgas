<?php
    include('../../conexao.php');

    $id = $_GET['id'] ?? null;

    // Consulta para somar todas as notificações de um cliente específico
    $sql_query = "SELECT COUNT(*) AS total_notificacoes FROM contador_notificacoes_cliente WHERE id_cliente = ?";
    $stmt = $mysqli->prepare($sql_query);
    $stmt->bind_param("i", $id); // Substituir $id pelo ID do cliente
    $stmt->execute();
    $stmt->bind_result($total_notificacoes);
    $stmt->fetch();
    $stmt->close();

    // Retorna os dados em formato JSON
    header('Content-Type: application/json');
    echo json_encode(['total_notificacoes' => $total_notificacoes]);
    //die();
?>

