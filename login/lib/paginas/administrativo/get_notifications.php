<?php
include('../../conexao.php');

// Consulta para obter o valor de not_inscr_parceiro da primeira linha
$sql_query = "SELECT * FROM contador_notificacoes WHERE id = 1";
$result = $mysqli->query($sql_query);
$row = $result->fetch_assoc();

$not_inscr_parceiro = $row['not_inscr_parceiro'] ?? 0; // Define 0 se não houver resultado
$not_crediario = $row['not_crediario'] ?? 0; // Define 0 se não houver resultado
$not_novos_produtos = $row['not_novos_produtos'] ?? 0; // Define 0 se não houver resultado
$not_msg = $row['not_msg'] ?? 0; // Define 0 se não houver resultado

// Soma todos os valores de notificações
$total_notificacoes = $not_inscr_parceiro + $not_crediario + $not_novos_produtos + $not_msg;

// Retorna os dados em formato JSON
header('Content-Type: application/json');
echo json_encode(['total_notificacoes' => $total_notificacoes]);
?>

