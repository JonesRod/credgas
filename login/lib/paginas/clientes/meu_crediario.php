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

// Consulta para verificar se o cliente já possui crediário e buscar seus detalhes
$sql_query = $mysqli->prepare("SELECT * FROM meus_clientes WHERE id = ?");
$sql_query->bind_param('i', $id);
$sql_query->execute();
$result = $sql_query->get_result();
$crediario = $result->fetch_assoc();

$status = $crediario['status_crediario'];
if ($status == 'Aprovado')
    $status = 'ATIVO';

    $limite = $crediario['limite_cred'];

?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Crediário</title>
</head>
<body>
    <div>
        <h1>Meu Crediário</h1>
        <p>Status: <?php echo $status;?></p>
        <p>Limite de crédito: <?php echo $limite;?></p>
        <p>Total ultilizado: <?php echo 'valor1';?></p>
        <p>Total disponivel: <?php echo 'valor2';?></p>        
    </div>

    <a href="">Minhas dividas</a> |
    <a href="">Conta pagas</a>

    <div>
        <a href="cliente_home.php">Voltar</a>
    </div>

</body>
</html>