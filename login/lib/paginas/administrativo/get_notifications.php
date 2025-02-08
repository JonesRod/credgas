<?php
include('../../conexao.php');

// Consulta para somar todas as notificações de todas as linhas
$sql_query = "
    SELECT 
        COALESCE(SUM(not_novo_cliente), 0) AS total_not_novo_cliente,
        COALESCE(SUM(not_inscr_parceiro), 0) AS total_not_inscr_parceiro,
        COALESCE(SUM(not_crediario), 0) AS total_not_crediario,
        COALESCE(SUM(not_novos_produtos), 0) AS total_not_novos_produtos,
        COALESCE(SUM(not_atualizar_produto), 0) AS total_not_edicao_produtos,
        COALESCE(SUM(not_msg), 0) AS total_not_msg
    FROM contador_notificacoes_admin
    WHERE id > 0";

// Executar a consulta
$result = $mysqli->query($sql_query);

// Verificar se há resultados
if ($result) {
    $row = $result->fetch_assoc();

    // Recupera os valores
    $total_notificacoes = array_sum($row);

    // Retorna os dados em formato JSON
    header('Content-Type: application/json');
    echo json_encode([
        'total_notificacoes' => $total_notificacoes,
        'notificacoes' => [
            'Novo Cliente' => $row['total_not_novo_cliente'],
            'Solicitação de Cadastro de Parceiro' => $row['total_not_inscr_parceiro'],
            'Solicitação de Crediário' => $row['total_not_crediario'],
            'Novo Produto' => $row['total_not_novos_produtos'],
            'Edição de Produto' => $row['total_not_edicao_produtos'],
            'Nova Mensagem Recebida' => $row['total_not_msg']
        ]
    ]);
} else {
    // Retorna erro caso a consulta falhe
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Erro ao executar a consulta']);
}
?>
