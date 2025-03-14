<?php
    session_start();
    include('../../../conexao.php'); // Conexão com o banco

    // Verificação de sessão
    if (!isset($_SESSION['id'])) {
        header("Location: ../../../../index.php");
        exit;
    }

    // Get user ID from session
    $id = $_SESSION['id'];

    // Fetch orders from the database
    $query = "SELECT * FROM pedidos WHERE id_cliente = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Meus Pedidos</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Meus Pedidos</h1>
    <table>
        <thead>
            <tr>
                <th>ID do Pedido</th>
                <th>Data</th>
                <th>Produtos</th>
                <th>Entrada</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['num_pedido']); ?></td>
                    <td><?php echo htmlspecialchars($row['data']); ?></td>
                    <td><?php echo htmlspecialchars($row['produtos']); ?></td>
                    <td><?php echo htmlspecialchars($row['entrada']); ?></td>
                    <td><?php echo htmlspecialchars($row['valor']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>

<?php
// Close the statement and connection
$stmt->close();
$mysqli->close();
?>