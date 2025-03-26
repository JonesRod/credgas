<?php
    var_dump($_POST);
    session_start();
include('../../../conexao.php'); // Conexão com o banco

// Verifica se o usuário está logado
if (!isset($_SESSION['id'])) {
    header("Location: ../../../../index.php");
    exit;
}

// Verifica se o ID do pedido foi enviado
if (!isset($_POST['num_pedido'])) {
    header("Location: ../../../../index.php");
    exit;
}

// Obtém o ID do cliente logado
$id_cliente = $_SESSION['id'];

// Obtém o ID do pedido enviado via POST
$num_pedido = $_POST['num_pedido'];

// Consulta para buscar os dados do pedido
$query = "SELECT * FROM pedidos WHERE id_cliente = ? AND num_pedido = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("ii", $id_cliente, $num_pedido);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $pedido = $result->fetch_assoc();
} else {
    echo "Pedido não encontrado.";
    exit;
}



?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Pedido</title>
</head>
<body>
    <h1>Detalhes do Pedido</h1>
    <h2>Pedido #<?php echo $num_pedido; ?></h2>
    <p><strong>Data do Pedido:</strong> <?php echo date('d/m/Y', strtotime($pedido['data'])); ?></p>
    <p><strong>Status:</strong> 
        <?php 
            if ($pedido['status_cliente'] == 0) {
                echo "Aguardando Confirmação.";
            } elseif ($pedido['status_cliente'] == 1) {
                echo "Pedido Confirmado.";
            } elseif ($pedido['status_cliente'] == 2) {
                echo "Pedido Recuzado!";
            } elseif ($pedido['status_cliente'] == 3) {
                echo "Pedido Entregue";
            } else {
                echo "Pedido Cancelado";
            }
        ?>
    </p>
    <p><strong>Total:</strong> R$ <?php echo number_format($pedido['valor'], 2, ',', '.'); ?></p>
    <h3>Produtos</h3>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Produto</th>
                <th>Quantidade</th>
                <th>Valor Unitário</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            // Divide os produtos armazenados no formato "produto/quantidade/valor uni/total|proximo produto"
            $produtos = explode('|', $pedido['produtos']);
            foreach ($produtos as $produto) {
                // Divide os detalhes de cada produto
                list($nome, $quantidade, $valor_unitario, $valor_total) = explode('/', $produto);
                echo "<tr>
                        <td>$nome</td>
                        <td>$quantidade</td>
                        <td>R$ " . number_format($valor_unitario, 2, ',', '.') . "</td>
                        <td>R$ " . number_format($valor_total, 2, ',', '.') . "</td>
                      </tr>";
            }
            ?>
        </tbody>
    </table>
    <h3>Endereço de Entrega</h3>
    <p><?php echo $pedido['endereco_entrega']; ?>, <?php echo $pedido['num_entrega']; ?> - <?php echo $pedido['bairro_entrega']; ?></p>
    <h3>Voltar</h3>
    <p><a href="javascript:history.back()">Voltar para a página anterior</a></p>
</body>
</html>
<?php

?>