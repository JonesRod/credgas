<?php
include('../../conexao.php');

// Verifica se o ID da notificação foi fornecido
if (isset($_GET['id_notificacao'])) {
    $id_notificacao = intval($_GET['id_notificacao']);

    // Consulta para excluir a notificação
    $sql_delete = "DELETE FROM contador_notificacoes_parceiro WHERE id = ?";
    $stmt = $mysqli->prepare($sql_delete);

    if ($stmt) {
        $stmt->bind_param("i", $id_notificacao);
        if ($stmt->execute()) {
            $mensagem = "Notificação excluída com sucesso.";
            $tipo_mensagem = "sucesso"; // Define o tipo de mensagem (sucesso)
        } else {
            $mensagem = "Erro ao excluir a notificação: " . $stmt->error;
            $tipo_mensagem = "erro"; // Define o tipo de mensagem (erro)
        }
    } else {
        die("Erro na preparação da consulta: " . $mysqli->error);
    }
} else {
    $mensagem = "ID da notificação não fornecido.";
    $tipo_mensagem = "erro";
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exclusão de Notificação</title>
    <style>
        /* Estilo para centralizar a mensagem */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .mensagem {
            text-align: center;
            padding: 20px;
            border-radius: 8px;
            width: 300px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            font-size: 18px;
        }

        .sucesso {
            background-color: #28a745;
            color: white;
        }

        .erro {
            background-color: #dc3545;
            color: white;
        }

        .mensagem a {
            display: block;
            margin-top: 15px;
            color: white;
            text-decoration: none;
            font-weight: bold;
            text-align: center;
        }

    </style>
</head>
<body>
    <div class="mensagem <?php echo $tipo_mensagem; ?>">
        <p><?php echo $mensagem; ?></p>
        <a href="detalhes_notificacao_edi_prod.php">Voltar</a>
    </div>

    <script>
        // Função para redirecionar após um tempo
        setTimeout(function() {
            window.location.href = "detalhes_notificacao_edi_prod.php"; // Redireciona após 3 segundos
        }, 3000);
    </script>
</body>
</html>

