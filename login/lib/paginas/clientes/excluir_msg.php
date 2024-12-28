<?php
include('../../conexao.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cliente = intval($_POST['id_cliente']);
    $id_not = intval($_POST['id_not']);

    // Inicializa a mensagem de feedback
    $mensagem = "";
    $classe = "";

    // Verifica se a notificação existe antes de excluir
    $sql_check = "SELECT * FROM contador_notificacoes_cliente WHERE id = ? AND id_cliente = ?";
    $stmt_check = $mysqli->prepare($sql_check);
    $stmt_check->bind_param("ii", $id_not, $id_cliente);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Exclui a notificação
        $sql_delete = "DELETE FROM contador_notificacoes_cliente WHERE id = ? AND id_cliente = ?";
        $stmt_delete = $mysqli->prepare($sql_delete);
        $stmt_delete->bind_param("ii", $id_not, $id_cliente);

        if ($stmt_delete->execute()) {
            $mensagem = "Mensagem excluída com sucesso!";
            $classe = "sucesso";
        } else {
            $mensagem = "Erro ao excluir a mensagem.";
            $classe = "erro";
        }

        $stmt_delete->close();
    } else {
        $mensagem = "Notificação não encontrada.";
        $classe = "erro";
    }

    $stmt_check->close();
    $mysqli->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensagem</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f9f9f9;
        }
        .mensagem-container {
            text-align: center;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            background-color: white;
        }
        .sucesso {
            color: #27ae60;
            font-weight: bold;
        }
        .erro {
            color: #e74c3c;
            font-weight: bold;
        }
        p {
            font-size: 16px;
            margin: 10px 0;
        }
        .redirect {
            font-size: 14px;
            color: #7f8c8d;
        }
        #contador {
            font-weight: bold;
            color: #3498db;
        }
    </style>
</head>
<body>
    <div class="mensagem-container">
        <p class="<?php echo htmlspecialchars($classe); ?>"><?php echo htmlspecialchars($mensagem); ?></p>
        <p class="redirect">Você será redirecionado para a página inicial em <span id="contador">3</span> segundos...</p>
    </div>

    <script>
        // Certifica-se de que o script é executado após o DOM estar carregado
        document.addEventListener('DOMContentLoaded', function () {
            let contador = 3; // Tempo em segundos
            const elementoContador = document.getElementById('contador');

            const interval = setInterval(function () {
                if (contador > 0) {
                    elementoContador.textContent = contador;
                    contador--;
                } else {
                    clearInterval(interval);
                    window.location.href = 'cliente_home.php';
                }
            }, 1000);
        });
    </script>
</body>
</html>
