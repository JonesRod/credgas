<?php
    include('../../conexao.php');

    // Inicia a sessão
    if (!isset($_SESSION)) {
        session_start(); 
    }

// Recebe os dados via POST (dados JSON)
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['cartao'])) {
    $id_cartao = $data['cartao'];
    
    // Consulta para excluir o cartão
    $sql = "DELETE FROM cartoes WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $id_cartao);
    
    // Executa a query
    if ($stmt->execute()) {
        echo json_encode(['sucesso' => true]);
    } else {
        echo json_encode(['sucesso' => false]);
    }
    $stmt->close();
}

    // Atualiza a página
    header("Refresh:0; url=lista_cartoes.php"); // Substitua 'pagina_cartoes.php' pelo nome da página.
    exit;
?>
