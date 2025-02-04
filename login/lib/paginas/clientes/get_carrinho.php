<?php
    include('../../conexao.php');

    $id = $_GET['id'] ?? null;

    // Consulta para somar todas as quantidades de produtos no carrinho de um cliente específico
    $sql_query = "SELECT SUM(qt) AS total_carrinho FROM carrinho WHERE id_cliente = ?";
    $stmt = $mysqli->prepare($sql_query);
    $stmt->bind_param("i", $id); // Substituir $id_cliente pelo ID do cliente
    $stmt->execute();
    $stmt->bind_result($total_carrinho);
    $stmt->fetch();
    $stmt->close();

    // Se não houver produtos no carrinho, definir como 0 para evitar retorno null
    $total_carrinho = $total_carrinho ?? 0;

    // Retorna os dados em formato JSON
    header('Content-Type: application/json');
    echo json_encode(['total_carrinho' => $total_carrinho]);
    //die();    
?>
