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

// Pega o ID da notificação e o ID da sessão da URL
$id = $_GET['id'];
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
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<h1>Solicitação de Análise de novos Parceiros</h1>

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
        echo "<td>" . htmlspecialchars($data_cadastro) . "</td>";
        echo "<td>" . htmlspecialchars($parceiro['nomeFantasia']) . "</td>";
        echo "<td>" . htmlspecialchars($parceiro['cnpj']) . "</td>";
        echo "<td>" . htmlspecialchars($parceiro['categoria']) . "</td>";
        
        // Adicionar link para página de detalhes, passando o ID do parceiro via URL
        echo "<td><a href='detalhes_parceiro.php?id=" . htmlspecialchars($parceiro['id']) . "'>Ver Detalhes</a></td>";
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "<p>Nenhum parceiro encontrado em análise.</p>";
}
?>

<!-- Link para voltar -->
<a href="admin_home.php">Voltar</a>
</body>
</html>
