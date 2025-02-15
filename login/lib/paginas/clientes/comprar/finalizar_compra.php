<?php
    session_start();
    include('../../../conexao.php'); // Conexão com o banco

    $id_cliente = isset($_GET['id_cliente']) ? intval($_GET['id_cliente']) : 0;
    $id_parceiro = isset($_GET['id_parceiro']) ? intval($_GET['id_parceiro']) : 0;

    // Buscar os produtos do carrinho
    $stmt = $mysqli->prepare("SELECT c.*, p.nome_produto, p.valor_produto, c.frete FROM carrinho c 
                            JOIN produtos p ON c.id_produto = p.id_produto 
                            WHERE c.id_cliente = ? AND p.id_parceiro = ?");
    $stmt->bind_param("ii", $id_cliente, $id_parceiro);
    $stmt->execute();
    $result = $stmt->get_result();
    $produtos = $result->fetch_all(MYSQLI_ASSOC);

    // Buscar se o parceiro aceita cartão de crédito
    $stmt = $mysqli->prepare("SELECT cartao_debito, cartao_credito, outras_formas FROM meus_parceiros WHERE id = ?");
    $stmt->bind_param("i", $id_parceiro);
    $stmt->execute();
    $result = $stmt->get_result();
    $parceiro = $result->fetch_assoc();

    $cartao_debito_ativo = !empty($parceiro['cartao_debito']); 
    $cartao_credito_ativo = !empty($parceiro['cartao_credito']); // Se estiver vazio, será falso
    $outros = !empty($parceiro['outras_formas']); 

    // Buscar se o parceiro aceita cartão de crédito
    $stmt = $mysqli->prepare("SELECT limite_cred FROM meus_clientes WHERE id = ?");
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $result = $stmt->get_result();
    $cliente = $result->fetch_assoc();

    $limite_cred = !empty($cliente['limite_cred']) ? $cliente['limite_cred'] : 0;

    //echo $limite_cred;
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
            $maiorFrete = 0;

            foreach ($produtos as $produto): 
                $total = $produto['valor_produto'] * $produto['qt'];
                $totalGeral += $total;

                // Verifica o maior frete no carrinho
                if ($produto['frete'] > $maiorFrete) {
                    $maiorFrete = $produto['frete'];
                }
            ?>
            <tr>
                <td><?php echo htmlspecialchars($produto['nome_produto']); ?></td>
                <td><?php echo $produto['qt']; ?></td>
                <td>R$ <?php echo number_format($produto['valor_produto'], 2, ',', '.'); ?></td>
                <td>R$ <?php echo number_format($total, 2, ',', '.'); ?></td>
            </tr>
            <?php endforeach; 
            
            // Soma o maior frete ao total da compra
            $totalComFrete = $totalGeral + $maiorFrete;
            $totalCrediario = $limite_cred - $totalComFrete;
            ?>
        </table>

        <h3>Frete: <?php echo ($maiorFrete > 0) ? 'R$ ' . number_format($maiorFrete, 2, ',', '.') : 'Frete Grátis'; ?></h3>
        <h3>Total da compra (com frete): R$ <?php echo number_format($totalComFrete, 2, ',', '.'); ?></h3>
        
        <form action="processar_pagamento.php" method="post">
            <input type="hidden" name="id_parceiro" value="<?php echo $id_parceiro; ?>">
            <input type="hidden" name="total" value="<?php echo $totalComFrete; ?>">

            <label>Escolha a forma de pagamento:</label>
            <select name="forma_pagamento">

                <?php if ($cartao_credito_ativo): ?>
                    <option value="cartaoCred">Cartão de Crédito</option>
                <?php endif; ?>

                <?php if ($cartao_debito_ativo): ?>
                    <option value="cartaoDeb">Cartão de Débito</option>
                <?php endif; ?>

                <option value="boleto">Boleto Bancário</option>
                <option value="pix">PIX</option>

                <?php if ($outros): ?>
                    <option value="outros">Outros</option>
                <?php endif; ?>
                <?php if ($totalCrediario >= 0): ?>
                    <option value="crediario">Crediário</option>
                <?php endif; ?>

            </select>

            <h3>Saldo de crédito: R$ <?php echo number_format($limite_cred, 2, ',', '.'); ?></h3>

            <button type="submit">Finalizar Compra</button>
        </form>
    <?php else: ?>
        <p>Erro: Nenhum produto encontrado.</p>
    <?php endif; ?>
</body>
</html>
