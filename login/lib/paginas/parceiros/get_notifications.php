<?php
header('Content-Type: application/json');

include('../../conexao.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Consulta para somar todas as notificações por coluna
$sql_query_not_par = "
    SELECT 
        SUM(plataforma) AS total_plataforma, 
        SUM(not_novo_produto) AS total_not_novo_produto,
        SUM(not_adicao_produto) AS total_not_adicao_produto,
        SUM(pedidos) AS total_pedidos
    FROM contador_notificacoes_parceiro
    WHERE id_parceiro = $id";

$result = $mysqli->query($sql_query_not_par);

if ($result) {
    $row = $result->fetch_assoc();

    $total_plataforma = $row['total_plataforma'] ?? 0;
    $total_not_novo_produto = $row['total_not_novo_produto'] ?? 0;
    $total_not_adicao_produto = $row['total_not_adicao_produto'] ?? 0;
    $total_pedidos = $row['total_pedidos'] ?? 0;

    $total_notificacoes = $total_plataforma + $total_not_novo_produto + $total_not_adicao_produto + $total_pedidos;

    // Criar array de notificações
    $notificacoes = [];
    if ($total_plataforma > 0) {
        $notificacoes[] = ['id' => 1, 'mensagem' => "Plataforma: $total_plataforma"];
    }
    if ($total_not_novo_produto > 0) {
        $notificacoes[] = ['id' => 2, 'mensagem' => "Novo Produto: $total_not_novo_produto"];
    }
    if ($total_not_adicao_produto > 0) {
        $notificacoes[] = ['id' => 3, 'mensagem' => "Edição de Produtos: $total_not_adicao_produto"];
    }
    if ($total_pedidos > 0) {
        $notificacoes[] = ['id' => 4, 'mensagem' => "Pedidos: $total_pedidos"];
    }

    echo json_encode([
        'total_notificacoes' => $total_notificacoes,
        'notificacoes' => $notificacoes
    ]);
} else {
    echo json_encode([
        'erro' => 'Erro na consulta SQL'
    ]);
}

    
?>

