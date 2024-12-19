<?php
include('../../conexao.php');
session_start();

// Verifica se o usuário está autenticado
if (!isset($_SESSION['id'])) {
    header("Location: ../../index.php");
    exit();
}

// Obtém o ID do usuário autenticado
$id = $_SESSION['id'];

// Verifica se os dados do formulário foram enviados
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Filtra e valida os dados recebidos
    $cep = filter_input(INPUT_POST, 'cep', FILTER_SANITIZE_STRING);
    $uf = filter_input(INPUT_POST, 'uf', FILTER_SANITIZE_STRING);
    $cidade = filter_input(INPUT_POST, 'cidade', FILTER_SANITIZE_STRING);
    $rua = filter_input(INPUT_POST, 'rua', FILTER_SANITIZE_STRING);
    $numero = filter_input(INPUT_POST, 'numero', FILTER_SANITIZE_STRING);
    $bairro = filter_input(INPUT_POST, 'bairro', FILTER_SANITIZE_STRING);

    // Verifica se os campos obrigatórios foram preenchidos
    if (!$cep || !$uf || !$cidade || !$rua || !$numero || !$bairro) {
        $mensagem = "Todos os campos de endereço são obrigatórios.";
        $redirecionamento = "alterar_endereco.php";
        $status = "erro";
    } else {
        // Prepara a consulta para atualizar os dados no banco
        $sql = $mysqli->prepare("UPDATE meus_clientes SET cep = ?, uf = ?, cidade = ?, endereco = ?, numero = ?, bairro = ? WHERE id = ?");
        $sql->bind_param("ssssssi", $cep, $uf, $cidade, $rua, $numero, $bairro, $id);

        if ($sql->execute()) {
            $mensagem = "Endereço atualizado com sucesso!";
            $redirecionamento = "perfil_cliente.php";
            $status = "sucesso";
        } else {
            $mensagem = "Erro ao atualizar o endereço. Tente novamente.";
            $redirecionamento = "alterar_endereco.php";
            $status = "erro";
        }
    }

    // Exibe a mensagem e redireciona
    echo "<!DOCTYPE html>
    <html lang='pt-BR'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Mensagem</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                background-color: #f4f4f4;
            }
            .mensagem-container {
                text-align: center;
                padding: 20px;
                border-radius: 8px;
                background-color: #fff;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                max-width: 400px;
                width: 90%;
            }
            .mensagem-container.sucesso {
                border-left: 5px solid #4CAF50;
            }
            .mensagem-container.erro {
                border-left: 5px solid #F44336;
            }
            h1 {
                font-size: 1.5rem;
                margin-bottom: 10px;
            }
            p {
                margin: 0;
                font-size: 1rem;
                color: #555;
            }
        </style>
        <script>
            setTimeout(function() {
                window.location.href = '$redirecionamento';
            }, 5000);
        </script>
    </head>
    <body>
        <div class='mensagem-container $status'>
            <h1>$mensagem</h1>
            <p>Você será redirecionado em instantes...</p>
        </div>
    </body>
    </html>";
    exit();
}

// Redireciona se o método não for POST
header("Location: alterar_endereco.php");
exit();
?>
