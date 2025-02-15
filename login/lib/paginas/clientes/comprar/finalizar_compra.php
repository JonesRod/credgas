<?php
session_start();
include('../../../conexao.php'); // Conexão com o banco

$id_cliente = isset($_GET['id_cliente']) ? intval($_GET['id_cliente']) : 0;
$id_parceiro = isset($_GET['id_parceiro']) ? intval($_GET['id_parceiro']) : 0;

$stmt = $mysqli->prepare("SELECT c.*, p.nome_produto, p.valor_produto FROM carrinho c 
                          JOIN produtos p ON c.id_produto = p.id_produto 
                          WHERE c.id_cliente = ? AND p.id_parceiro = ?");
$stmt->bind_param("ii", $id_cliente, $id_parceiro);
$stmt->execute();
$result = $stmt->get_result();
$produtos = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra</title>
</head>
<body>
    <h2>Finalizar Compra</h2>

    <?php if (!empty($produtos)): ?>
        <table border="1">
            <tr>
                <th>Produto</th>
                <th>Quantidade</th>
                <th>Valor Unitário</th>
                <th>Total</th>
            </tr>
            <?php 
            $totalGeral = 0;
            foreach ($produtos as $produto): 
                $total = $produto['valor_produto'] * $produto['qt'];
                $totalGeral += $total;
            ?>
            <tr>
                <td><?php echo htmlspecialchars($produto['nome_produto']); ?></td>
                <td><?php echo $produto['qt']; ?></td>
                <td>R$ <?php echo number_format($produto['valor_produto'], 2, ',', '.'); ?></td>
                <td>R$ <?php echo number_format($total, 2, ',', '.'); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <h3>Total da compra: R$ <?php echo number_format($totalGeral, 2, ',', '.'); ?></h3>

        <form action="processar_pagamento.php" method="post">
            <input type="hidden" name="id_parceiro" value="<?php echo $id_parceiro; ?>">
            <input type="hidden" name="total" value="<?php echo $totalGeral; ?>">

            <label>Escolha a forma de pagamento:</label>
            <select name="forma_pagamento">
                <option value="cartao">Cartão de Crédito</option>
                <option value="boleto">Boleto Bancário</option>
                <option value="pix">PIX</option>
            </select>

            <button type="submit">Finalizar Compra</button>
        </form>
    <?php else: ?>
        <p>Erro: Nenhum produto encontrado.</p>
    <?php endif; ?>
</body>
</html>
