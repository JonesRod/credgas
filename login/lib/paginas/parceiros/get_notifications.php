<?php
include('../../conexao.php');

// Consulta para obter o valor de not_inscr_parceiro da primeira linha
$sql_query = "SELECT * FROM contador_notificacoes_parceiro WHERE id = 1";
$result = $mysqli->query($sql_query);
$row = $result->fetch_assoc();

$plataforma = $row['plataforma'] ?? 0; // Define 0 se não houver resultado
$pedidos = $row['pedidos'] ?? 0; // Define 0 se não houver resultado

// Soma todos os valores de notificações
$total_notificacoes = $plataforma + $pedidos;

// Retorna os dados em formato JSON
header('Content-Type: application/json');
echo json_encode(['total_notificacoes' => $total_notificacoes]);
?>

