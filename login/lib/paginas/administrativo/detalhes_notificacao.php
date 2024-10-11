<?php
include('../../conexao.php');

if (!isset($_SESSION)) {
    session_start();
}

if (isset($_SESSION['id'])) {
    $id = $_SESSION['id'];
    //$id = $_SESSION['usuario'];
    $sql_query = $mysqli->query("SELECT * FROM meus_clientes WHERE id = '$id'") or die($mysqli->$error);
    $usuario = $sql_query->fetch_assoc();
} else {
    // Se não houver uma sessão de usuário, redirecione para a página de login
    session_unset();
    session_destroy();
    header("Location: ../../../../index.php");
    exit(); // Importante adicionar exit() após o redirecionamento
}

// Pega o ID da notificação e o ID da sessão da URL
$id = $_GET['id'];
$session_id = $_GET['session_id'];

// Construa a consulta SQL para buscar os parceiros com analize_inscricao = 0
$sql_query = "SELECT * FROM meus_parceiros WHERE analize_inscricao = 0" or die($mysqli->$error);

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

<h1>Parceiros em Análise</h1>

<?php
if ($result->num_rows > 0) {
    // Exibir os dados dos parceiros em uma tabela
    echo "<table>";
    echo "<tr>
            <th>Data de Cadastro</th>
            <th>Razão</th>
            <th>Nome Fantasia</th>
            <th>CNPJ</th>
            <th>Inscrição Estadual</th>
            <th>Categoria</th>
            <th>Anexo</th>
            <th>Telefone Comercial</th>
            <th>Telefone do Responsável</th>
            <th>E-MAIL</th>
            <th>CEP</th>
            <th>Estado</th>
            <th>Cidade</th>
          </tr>";

    while ($parceiro = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($parceiro['data_cadastro']) . "</td>";
        echo "<td>" . htmlspecialchars($parceiro['razao']) . "</td>";
        echo "<td>" . htmlspecialchars($parceiro['nomeFantasia']) . "</td>";
        echo "<td>" . htmlspecialchars($parceiro['cnpj']) . "</td>";
        echo "<td>" . htmlspecialchars($parceiro['inscricaoEstadual']) . "</td>";
        echo "<td>" . htmlspecialchars($parceiro['categoria']) . "</td>";
        echo "<td>" . htmlspecialchars($parceiro['anexo_comprovante']) . "</td>";
        echo "<td>" . htmlspecialchars($parceiro['telefoneComercial']) . "</td>";
        echo "<td>" . htmlspecialchars($parceiro['telefoneResponsavel']) . "</td>";
        echo "<td>" . htmlspecialchars($parceiro['email']) . "</td>";
        echo "<td>" . htmlspecialchars($parceiro['cep']) . "</td>";
        echo "<td>" . htmlspecialchars($parceiro['estado']) . "</td>";
        echo "<td>" . htmlspecialchars($parceiro['cidade']) . "</td>";
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
