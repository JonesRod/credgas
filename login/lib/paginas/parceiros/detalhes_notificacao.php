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
$sql_query = "SELECT * FROM notificacoes WHERE analize_inscricao = 1" or die($mysqli->$error);

// Execute a consulta SQL
$result = $mysqli->query($sql_query);

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parceiros em Análise</title>
    <link rel="stylesheet" href="detalhes_notificacao.css">
    <style>

    </style>
</head>
<body>
    <h2>Plataforma notificações</h2>

    <?php
        if ($result === false) {
            // Exibir uma mensagem de erro se a consulta falhar
            echo "<div class='center'><p><p>Nenhum notificação encontrada.</p></div>";
        } else {
            if ($result->num_rows > 0) {
                // Exibir os dados dos parceiros em uma tabela
                echo "<table>";
                echo "<tr>
                        <th>Data</th>
                        <th>Mensagem</th>
                        <th>CNPJ</th>
                        <th>Categoria</th>
                        <th>Detalhes</th>
                    </tr>";

                while ($parceiro = $result->fetch_assoc()) {
                    // Formatar a data de cadastro
                    $data = date("d/m/Y", strtotime($parceiro['data_cadastro']));

                    echo "<tr>";
                    echo "<td data-label='Data'>" . htmlspecialchars($data) . "</td>";
                    echo "<td data-label='Mensagem'>" . htmlspecialchars($parceiro['nomeFantasia']) . "</td>";
                    echo "<td data-label='CNPJ'>" . htmlspecialchars($parceiro['cnpj']) . "</td>";
                    echo "<td data-label='Categoria'>" . htmlspecialchars($parceiro['categoria']) . "</td>";
                    
                    // Adicionar link para página de detalhes, passando o ID do parceiro via URL
                    echo "<td data-label='Detalhes'><a href='detalhes_notificacoes.php?id=" . htmlspecialchars($parceiro['id']) . "'>Ver Detalhes</a></td>";
                    echo "</tr>";
                }

                echo "</table>";
            } else {
                // Exibir mensagem centralizada se não houver dados
                echo "<div class='center'><p>Nenhum notificação encontrada.</p></div>";
            }
        }
    ?>

    <!-- Link para voltar -->
    <div class="center">
        <a href="parceiro_home.php" class="back-link">Voltar</a>
    </div>

</body>
</html>
