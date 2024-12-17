<?php
    include('../../conexao.php');

    $id = $_GET['id'] ?? null;

    // Consulta para somar todas as notificações de todas as linhas
    $sql_query = "
    SELECT 
        id,
        id_cliente,
        cont_msg
    FROM contador_notificacoes_cliente
    WHERE id > $id";
    
    // Executar a consulta
    $result = $mysqli->query($sql_query);

    // Verificar se há resultados
    if ($result) {
    $row = $result->fetch_assoc();
    $total_notificacoes = 
        //($row['cont_msg'] ?? 0);
        $total_not = $row['cont_msg'] ?? 0;
    //echo "Total de notificações: $total_notificacoes";
    } else {
    //echo "Erro ao executar a consulta: " . $mysqli->error;
    }

    // Retorna os dados em formato JSON
    header('Content-Type: application/json');
    echo json_encode(['total_notificacoes' => $total_not]);
    //die();
?>

