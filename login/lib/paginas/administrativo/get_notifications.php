<?php
include('../../conexao.php');

    // Consulta para somar todas as notificações de todas as linhas
    $sql_query = "
    SELECT 
        SUM(not_novo_cliente) AS total_not_novo_cliente,
        SUM(not_inscr_parceiro) AS total_not_inscr_parceiro,
        SUM(not_crediario) AS total_not_crediario,
        SUM(not_novos_produtos) AS total_not_novos_produtos,
        SUM(not_atualizar_produto) AS total_not_edicao_produtos,
        SUM(not_msg) AS total_not_msg
    FROM contador_notificacoes_admin
    WHERE id > '0'";

    // Executar a consulta
    $result = $mysqli->query($sql_query);

    // Verificar se há resultados
    if ($result) {
    $row = $result->fetch_assoc();
    $total_notificacoes = 
        ($row['total_not_novo_cliente'] ?? 0) + 
        ($row['total_not_inscr_parceiro'] ?? 0) + 
        ($row['total_not_crediario'] ?? 0) + 
        ($row['total_not_novos_produtos'] ?? 0) + 
        ($row['total_not_edicao_produtos'] ?? 0) + 
        ($row['total_not_msg'] ?? 0);

    //echo "Total de notificações: $total_notificacoes";
    } else {
    //echo "Erro ao executar a consulta: " . $mysqli->error;
    }

    $not_novo_cliente = $row['total_not_novo_cliente'] ?? 0;
    $not_inscr_parceiro = $row['total_not_inscr_parceiro'] ?? 0; // Define 0 se não houver resultado
    $not_crediario = $row['total_not_crediario'] ?? 0; // Define 0 se não houver resultado
    $not_novos_produtos = $row['total_not_novos_produtos'] ?? 0; // Define 0 se não houver resultado
    $not_edicao_produtos = $row['total_not_edicao_produtos'] ?? 0; // Define 0 se não houver resultado
    $not_msg = $row['total_not_msg'] ?? 0; // Define 0 se não houver resultado

    // Soma todos os valores de notificações
    $total_notificacoes = $not_novo_cliente + $not_inscr_parceiro + $not_crediario + $not_novos_produtos + $not_edicao_produtos + $not_msg;
    //echo $total_notificacoes; 

    // Retorna os dados em formato JSON
    header('Content-Type: application/json');
    echo json_encode(['total_notificacoes' => $total_notificacoes]);
    //die();
?>

