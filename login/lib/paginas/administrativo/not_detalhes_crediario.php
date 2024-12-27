<?php
include('../../conexao.php');

if (!isset($_SESSION)) {
    session_start();
}

if (isset($_SESSION['id'])) {
    $id = $_SESSION['id'];
    $sql_query = $mysqli->query("SELECT * FROM meus_clientes WHERE id = '$id'") or die($mysqli->$error);
    $usuario = $sql_query->fetch_assoc();
    $cliente = $usuario;

} else {
    // Se não houver uma sessão de usuário, redirecione para a página de login
    session_unset();
    session_destroy();
    header("Location: ../../../../index.php");
    exit();
}

// Pega o ID da notificação apenas se ele existir, senão define como null
$id = isset($_GET['id']) ? $_GET['id'] : null;
$session_id = $_GET['id'];

// Construa a consulta SQL para buscar os parceiros com analize_inscricao = 0
$sql_query = "SELECT * FROM contador_notificacoes_admin WHERE not_crediario > 0" or die($mysqli->$error);

// Execute a consulta SQL
$result = $mysqli->query($sql_query);

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificações</title>
    <link rel="stylesheet" href="not_detalhes_parceiro.css">
    <style>

    </style>
</head>
<body>
    <h1>Notificação</h1>
    <h2>Solicitação de Crediário</h2>

    <?php
    if ($result->num_rows > 0) {
        // Exibir os dados dos parceiros em uma tabela
        echo "<table>";
        echo "<tr>
                <th>Data</th>
                <th>Nome</th>
                <th>CPF</th>
                <th>Data Nasc.</th>
                <th>Idade</th>
                <th>Detalhes</th>
            </tr>";

        while ($not = $result->fetch_assoc()) {
            // Formatar a data de cadastro
            $data = date("d/m/Y", strtotime($not['data']));
            
            $data_nasc = date("d/m/Y", strtotime($cliente['nascimento']));

            // Calcula a idade
            $hoje = new DateTime(); // Data atual
            $dataNascimento = new DateTime($cliente['nascimento']); // Converte a data de nascimento
            $idade = $hoje->diff($dataNascimento)->y; // Calcula a diferença em anos

            echo "<tr>";
            echo "<td data-label='Data de Cadastro'>" . htmlspecialchars($data) . "</td>";
            echo "<td data-label='Nome Fantasia'>" . htmlspecialchars($cliente['nome_completo']) . "</td>";
            echo "<td data-label='CNPJ'>" . htmlspecialchars($cliente['cpf']) . "</td>";
            echo "<td data-label='Categoria'>" . htmlspecialchars($data_nasc) . "</td>";
            echo "<td data-label='Categoria'>" . htmlspecialchars($idade) . "</td>";

            // Adicionar link para página de detalhes, passando o ID do parceiro via URL
            echo "<td data-label='Detalhes'><a href='detalhes_cliente_cred.php?id=" . htmlspecialchars($cliente['id']) . "'>Ver Detalhes</a></td>";
            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "<div class='center'><p>Nenhum solicitaçõa de crediário.</p></div>"; // Mensagem centralizada
    }
    ?>

    <!-- Link para voltar -->
    <div class="center">
        <a href="admin_home.php" class="back-link">Voltar</a>
    </div>

</body>
</html>
