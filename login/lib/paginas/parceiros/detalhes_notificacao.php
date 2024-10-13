<?php
include('../../conexao.php');

if (!isset($_SESSION)) {
    session_start();
}

if (isset($_SESSION['id'])) {
    $id = $_SESSION['id'];
    $sql_query = $mysqli->query("SELECT * FROM meus_clientes WHERE id = '$id'") or die($mysqli->$error);
    $usuario = $sql_query->fetch_assoc();
} else {
    // Se não houver uma sessão de usuário, redirecione para a página de login
    session_unset();
    session_destroy();
    header("Location: ../../../../index.php");
    exit();
}

// Pega o ID da notificação apenas se ele existir, senão define como null
$id = isset($_GET['id']) ? $_GET['id'] : null;
$session_id = $_GET['session_id'];

// Construa a consulta SQL para buscar os parceiros com analize_inscricao = 0
$sql_query = "SELECT * FROM meus_parceiros WHERE analize_inscricao = 1" or die($mysqli->$error);

// Execute a consulta SQL
$result = $mysqli->query($sql_query);

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parceiros em Análise</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f9f9f9;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        a {
            text-decoration: none;
            color: #007BFF;
        }

        a:hover {
            text-decoration: underline;
        }

        /* Estilização do botão "Voltar" */
        .back-link {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            border-radius: 5px;
            text-align: center;
            margin: 20px 0;
        }

        .back-link:hover {
            background-color: #0056b3;
        }

        /* Centralização */
        .center {
            text-align: center; /* Centraliza o conteúdo */
        }

        /* Responsividade */
        @media (max-width: 768px) {
            th, td {
                padding: 10px;
                font-size: 14px;
            }

            h1 {
                font-size: 20px;
            }
            h2 {
                font-size: 15px;
            }
            .back-link {
                padding: 8px 16px;
                font-size: 14px;
            }
        }

        @media (max-width: 480px) {
            table, th, td {
                display: block;
                width: 100%;
            }

            th {
                display: none; /* Esconde os cabeçalhos em telas pequenas */
            }

            td {
                display: flex;
                justify-content: space-between;
                padding: 10px;
                border: 1px solid #ddd;
                border-bottom: none;
            }

            td:before {
                content: attr(data-label); /* Usa o conteúdo dos cabeçalhos como labels */
                flex-basis: 40%;
                text-align: left;
                font-weight: bold;
                color: #333;
            }

            h1 {
                font-size: 18px;
            }
            h2 {
                font-size: 14px;
            }
            .back-link {
                font-size: 12px;
                padding: 8px 12px;
            }
        }
    </style>
</head>
<body>
    <h1>Notificação</h1>
    <h2>Solicitação de Análise de novos Parceiros</h2>

    <?php
    if ($result->num_rows > 0) {
        // Exibir os dados dos parceiros em uma tabela
        echo "<table>";
        echo "<tr>
                <th>Data de Cadastro</th>
                <th>Nome Fantasia</th>
                <th>CNPJ</th>
                <th>Categoria</th>
                <th>Detalhes</th>
            </tr>";

        while ($parceiro = $result->fetch_assoc()) {
            // Formatar a data de cadastro
            $data_cadastro = date("d/m/Y", strtotime($parceiro['data_cadastro']));
            
            echo "<tr>";
            echo "<td data-label='Data de Cadastro'>" . htmlspecialchars($data_cadastro) . "</td>";
            echo "<td data-label='Nome Fantasia'>" . htmlspecialchars($parceiro['nomeFantasia']) . "</td>";
            echo "<td data-label='CNPJ'>" . htmlspecialchars($parceiro['cnpj']) . "</td>";
            echo "<td data-label='Categoria'>" . htmlspecialchars($parceiro['categoria']) . "</td>";
            
            // Adicionar link para página de detalhes, passando o ID do parceiro via URL
            echo "<td data-label='Detalhes'><a href='detalhes_parceiro.php?id=" . htmlspecialchars($parceiro['id']) . "'>Ver Detalhes</a></td>";
            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "<div class='center'><p>Nenhum parceiro encontrado em análise.</p></div>"; // Mensagem centralizada
    }
    ?>

    <!-- Link para voltar -->
    <div class="center">
        <a href="admin_home.php" class="back-link">Voltar</a>
    </div>

</body>
</html>
