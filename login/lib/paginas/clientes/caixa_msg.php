<?php
include('../../conexao.php');

$id_cliente = intval($_GET['id_cliente']); // Obtém o ID do cliente

// Consulta para obter notificações do cliente onde lida = 1
$sql_query_notificacoes = "SELECT * FROM contador_notificacoes_cliente WHERE id_cliente = ? ORDER BY data DESC";
$stmt = $mysqli->prepare($sql_query_notificacoes);
$stmt->bind_param("i", $id_cliente);
$stmt->execute();
$result = $stmt->get_result();

// Verificar se há notificações
if ($result->num_rows > 0) {
    echo "<h2>Mensagens</h2>";  // Título "Mensagens"
    echo "<ul id='lista-notificacoes'>";  // Abertura da lista
    // Iterar pelas notificações e renderizar no painel
    while ($notificacao = $result->fetch_assoc()) {
        $idNotificacao = htmlspecialchars($notificacao['id']);
        $dataOriginal = $notificacao['data'];
        $dataFormatada = (new DateTime($dataOriginal))->format('d/m/Y H:i:s');
        $mensagem = htmlspecialchars($notificacao['msg']);

        echo "<li class='notification-item'>";
        echo "<a href='mensagem.php?id_cliente=" . htmlspecialchars($id_cliente) . "&id_not=" . $idNotificacao . "' class='notification-link'>";
        echo "<div class='notification-header'>";
        echo "<strong class='notification-date'>$dataFormatada</strong>";
        echo "</div>";
        echo "<div class='notification-message'>";
        echo $mensagem;
        echo "</div>";
        echo "</a>";
        echo "</li>";
    }
    echo "</ul>";  // Fechamento da lista
} else {
    echo "<p class='no-notifications'>Sem notificações no momento.</p>"; // Modificado para <p> no lugar de <li>
}

$stmt->close();
?>

<!-- Botão Voltar -->
<div class="back-button">
    <button onclick="window.history.back()" class="btn-voltar">Voltar</button>
</div>

<style>
    /* Estilos gerais para o painel de notificações */
#lista-notificacoes {
    list-style-type: none;
    padding: 0;
    margin: 0;
}

.notification-item {
    background-color: #f8f8f8;
    border-radius: 8px;
    margin-bottom: 10px;
    padding: 15px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: background-color 0.3s ease;
}

.notification-item:hover {
    background-color: #e0f7fa;
}

.notification-link {
    text-decoration: none;
    color: #333;
    display: block;
}

.notification-header {
    font-size: 14px;
    color: #888;
    margin-bottom: 5px;
}

.notification-date {
    font-weight: bold;
}

.notification-message {
    font-size: 16px;
    line-height: 1.5;
}

.no-notifications {
    padding: 20px;
    text-align: center;
    color: #888;
    font-size: 16px;
}

/* Estilos para o botão Voltar */
.back-button {
    text-align: center;
    margin-top: 20px;
}

.btn-voltar {
    padding: 10px 20px;
    font-size: 16px;
    background-color: #3498db;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.btn-voltar:hover {
    background-color: #2980b9;
}

</style>