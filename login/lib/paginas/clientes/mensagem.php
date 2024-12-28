<?php
include('../../conexao.php');

// Verifica se os parâmetros necessários foram enviados
if (!isset($_GET['id_cliente']) || !isset($_GET['id_not'])) {
    echo "Parâmetros inválidos.";
    exit;
}

$id_cliente = intval($_GET['id_cliente']);
$id_not = intval($_GET['id_not']);

// Consulta para buscar a mensagem da notificação
$sql_notificacao = "SELECT * FROM contador_notificacoes_cliente WHERE id = ? AND id_cliente = ?";
$stmt = $mysqli->prepare($sql_notificacao);
$stmt->bind_param("ii", $id_not, $id_cliente);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Notificação não encontrada.";
    exit;
}

// Busca a notificação
$notificacao = $result->fetch_assoc();

// Atualiza a notificação para marcada como lida
$sql_update_lida = "UPDATE contador_notificacoes_cliente SET lida = 0 WHERE id = ? AND id_cliente = ?";
$stmt_update = $mysqli->prepare($sql_update_lida);
$stmt_update->bind_param("ii", $id_not, $id_cliente);
$stmt_update->execute();
$stmt_update->close();

// Fechar a consulta original
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caixa de Mensagem</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f9f9f9;
        }
        .container {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .msg-header {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .msg-content {
            margin-bottom: 20px;
        }
        .buttons {
            display: flex;
            justify-content: space-between;
        }
        .buttons button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .btn-delete {
            background-color: #e74c3c;
            color: white;
        }
        .btn-delete:hover {
            background-color: #c0392b;
        }
        .btn-back {
            background-color: #3498db;
            color: white;
        }
        .btn-back:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="msg-header">Notificação</div>
        <div class="msg-content">
            <p><?php echo htmlspecialchars((new DateTime($notificacao['data']))->format('d/m/Y H:i:s')); ?></p>
            <p><?php echo htmlspecialchars($notificacao['msg']); ?></p>
        </div>
        <div class="buttons">
            <form method="POST" action="excluir_msg.php" style="margin: 0;">
                <input type="hidden" name="id_cliente" value="<?php echo $id_cliente; ?>">
                <input type="hidden" name="id_not" value="<?php echo $id_not; ?>">
                <button type="submit" class="btn-delete">Excluir Mensagem</button>
            </form>
            <button class="btn-back" onclick="window.history.back()">Voltar</button>
        </div>
    </div>
</body>
</html>
