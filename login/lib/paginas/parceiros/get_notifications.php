<?php
include('../../conexao.php');

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    // Consulta para obter o valor de not_inscr_parceiro da primeira linha
    $sql_query_not_par = "SELECT * FROM contador_notificacoes_parceiro WHERE id_parceiro = $id";
    $result = $mysqli->query(query: $sql_query_not_par);
    $row = $result->fetch_assoc();
    $platafoma= $row['plataforma'] ?? 0; // Define 0 se não houver resultado
    $not_adicao_produto= $row['not_adicao_produto'] ?? 0; // Define 0 se não houver resultado
    $pedidos = $row['pedidos'] ?? 0; // Define 0 se não houver resultado


    // Soma todos os valores de notificações
    $total_notificacoes = $not_adicao_produto + $pedidos;

    // Retorna os dados em formato JSON
    header('Content-Type: application/json');
    echo json_encode(['total_notificacoes' => $total_notificacoes]);
?>
