<?php
    include('../../conexao.php');

    if (!isset($_SESSION)) {
        session_start();
    }

    if (isset($_SESSION['id'])) {
        $id = $_SESSION['id'];
        $sql_query = $mysqli->query("SELECT * FROM meus_clientes WHERE id = '$id'") or die($mysqli->error);
        $usuario = $sql_query->fetch_assoc();
    } else {
        // Se não houver uma sessão de usuário, redirecione para a página de login
        session_unset();
        session_destroy();
        header("Location: ../../../../index.php");
        exit();
    }

    // Pega o ID da notificação apenas se ele existir, senão define como null
    $id_not = isset($_GET['id']) ? $_GET['id'] : null;

    // Construa a consulta SQL para buscar os novos clientes
    $sql_query = "SELECT * FROM contador_notificacoes_admin WHERE not_novo_cliente = 1"; // Constrói a consulta

    // Execute a consulta SQL
    $result = $mysqli->query($sql_query) or die($mysqli->error); // Executa a consulta e armazena o resultado

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novos Clientes</title>
    <link rel="stylesheet" href="not_detalhes_cliente.css">
    <style>
    /* Estilo geral */
    body {
        font-family: Arial, sans-serif;
        line-height: 1.6;
        margin: 0;
        padding: 0;
        background-color: #f8f9fa;
        color: #333;
    }

    h1, h2 {
        text-align: center;
        margin-top: 20px;
        color: #0056b3;
    }

    .center {
        text-align: center;
        margin: 20px 0;
    }

    /* Estilo da tabela */
    table {
        width: 90%;
        margin: 20px auto;
        border-collapse: collapse;
        background-color: #fff;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        overflow: hidden;
    }

    th, td {
        padding: 15px;
        border-bottom: 1px solid #ddd;
        text-align: left;
    }

    th {
        background-color: #007bff;
        color: #fff;
        text-transform: uppercase;
        font-size: 14px;
    }

    td {
        font-size: 14px;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    tr:hover {
        background-color: #f1f1f1;
    }

    /* Links */
    a {
        text-decoration: none;
        color: #007bff;
    }

    a:hover {
        text-decoration: underline;
    }

    .back-link {
        display: inline-block;
        padding: 10px 15px;
        background-color: #0056b3;
        color: #fff;
        border-radius: 5px;
        font-size: 14px;
        text-align: center;
        transition: background-color 0.3s ease;
    }

    .back-link:hover {
        background-color: #003f8a;
    }

    /* Responsividade */
    @media (max-width: 768px) {
        table {
            width: 100%;
        }

        th, td {
            padding: 10px;
            font-size: 12px;
        }

        .back-link {
            font-size: 12px;
            padding: 8px 12px;
        }
    }

    @media (max-width: 480px) {
        h1, h2 {
            font-size: 18px;
        }

        th, td {
            padding: 8px;
            font-size: 10px;
        }

        .back-link {
            font-size: 10px;
            padding: 6px 10px;
        }
    }
</style>

</head>
<body>
    <h1>Notificação</h1>
    <h2>Novos Clientes</h2>

    <?php
        if ($result->num_rows > 0) {
            // Exibir os dados dos clientes em uma tabela
            echo "<table>";
            echo "<tr>
                    <th style='display: none;'>ID Cliente</th>
                    <th>Data de Cadastro</th>
                    <th>Nome</th>
                    <th>CPF</th>
                    <th>Idade</th>
                    <th>Detalhes</th>
                </tr>";

            while ($cliente = $result->fetch_assoc()) {
                // Pega o ID do cliente da notificação
                $id_cliente = $cliente['id_cliente'];

                // Busca os dados do cliente na tabela 'meus_clientes' com base no ID
                $sql_query_cliente = $mysqli->query("SELECT * FROM meus_clientes WHERE id = '$id_cliente'") or die($mysqli->error);
                $cliente_dados = $sql_query_cliente->fetch_assoc(); // Agora temos os dados do cliente na variável $cliente_dados

                // Formatar a data de cadastro
                $data_cadastro = date("d/m/Y", strtotime($cliente_dados['data_cadastro']));
            
                // Calcular a idade com base na data de nascimento
                $data_nascimento = new DateTime($cliente_dados['nascimento']); // Data de nascimento no formato YYYY-MM-DD
                $data_atual = new DateTime(); // Data atual
                $idade = $data_nascimento->diff($data_atual)->y; // Diferença em anos
            
                echo "<tr>";
                echo "<td style='display: none;' data-label='ID Cliente'>" . htmlspecialchars($cliente['id_cliente']) . "</td>"; // ID escondido
                echo "<td data-label='Data de Cadastro'>" . htmlspecialchars($data_cadastro) . "</td>";
                echo "<td data-label='Nome'>" . htmlspecialchars($cliente_dados['nome_completo']) . "</td>";
                echo "<td data-label='CPF'>" . htmlspecialchars($cliente_dados['cpf']) . "</td>";
                echo "<td data-label='Idade'>" . htmlspecialchars($idade) . "</td>";
                echo "<td data-label='Detalhes'><a href='detalhes_cliente.php?id=" . htmlspecialchars($cliente_dados['id']) . "'>Ver Detalhes</a></td>";
                echo "</tr>";
            }

            echo "</table>";
        } else {
            echo "<div class='center'><p>Nenhum novo cliente encontrado em análise.</p></div>"; // Mensagem centralizada
        }
    ?>

    <!-- Link para voltar -->
    <div class="center">
        <a href="admin_home.php" class="back-link">Voltar</a>
    </div>

</body>
</html>
