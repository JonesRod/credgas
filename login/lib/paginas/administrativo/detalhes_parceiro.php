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

// Pega o ID do parceiro da URL
$parceiro_id = $_GET['id'];

// Construa a consulta SQL para buscar os dados do parceiro específico
$sql_query = "SELECT * FROM meus_parceiros WHERE id = ?" or die($mysqli->$error);

// Prepare e execute a consulta
$stmt = $mysqli->prepare($sql_query);
$stmt->bind_param("i", $parceiro_id);
$stmt->execute();
$result = $stmt->get_result();
$parceiro = $result->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Parceiro</title>
    <style>
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            padding: 8px;
            margin-bottom: 4px;
            border-bottom: 1px solid #ddd;
        }
        li strong {
            display: inline-block;
            width: 200px;
        }
    </style>
</head>
<body>

<h1>Detalhes do Parceiro</h1>

<?php if ($parceiro): ?>
    <ul>
        <li><strong>Data de Cadastro:</strong> <?php echo htmlspecialchars(date("d/m/Y", strtotime($parceiro['data_cadastro']))); ?></li>
        <li><strong>RAZÃO:</strong> <?php echo htmlspecialchars($parceiro['razao']); ?></li>
        <li><strong>Nome Fantasia:</strong> <?php echo htmlspecialchars($parceiro['nomeFantasia']); ?></li>
        <li><strong>CNPJ:</strong> <?php echo htmlspecialchars($parceiro['cnpj']); ?></li>
        <li><strong>Inscrição Estadual:</strong> <?php echo htmlspecialchars($parceiro['inscricaoEstadual']); ?></li>
        <li><strong>Categoria:</strong> <?php echo htmlspecialchars($parceiro['categoria']); ?></li>
        <li><strong>Anexo Comprovante:</strong>
            <?php 
            if (!empty($parceiro['anexo_comprovante'])) {
                // Exibir a imagem se o campo não estiver vazio
                echo '<img src="../arquivos/' . htmlspecialchars($parceiro['anexo_comprovante']) . '" alt="Comprovante" style="max-width: 200px; max-height: 200px;">';
            } else {
                echo 'Nenhum anexo disponível';
            }
            ?>
        </li>

        <li><strong>Telefone Comercial:</strong> <?php echo htmlspecialchars($parceiro['telefoneComercial']); ?></li>
        <li><strong>Telefone do Responsável:</strong> <?php echo htmlspecialchars($parceiro['telefoneResponsavel']); ?></li>
        <li><strong>Email:</strong> <?php echo htmlspecialchars($parceiro['email']); ?></li>
        <li><strong>CEP:</strong> <?php echo htmlspecialchars($parceiro['cep']); ?></li>
        <li><strong>Estado:</strong> <?php echo htmlspecialchars($parceiro['estado']); ?></li>
        <li><strong>Cidade:</strong> <?php echo htmlspecialchars($parceiro['cidade']); ?></li>
        <!-- Adicione mais campos conforme necessário -->
    </ul>
<?php else: ?>
    <p>Parceiro não encontrado.</p>
<?php endif; ?>

<!-- Link para voltar -->
<a href="javascript:history.back()">Voltar</a>

</body>
</html>
