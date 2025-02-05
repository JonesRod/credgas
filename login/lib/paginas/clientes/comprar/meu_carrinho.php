<?php
include('../../../conexao.php');
session_start();

if (isset($_SESSION['id']) && isset($_GET['id_cliente'])) {
    $id_cliente = intval($_GET['id_cliente']);

    // Consulta para buscar os produtos do carrinho agrupados por parceiro
    $sql_produtos = $mysqli->query("SELECT c.*, p.nome_produto, pa.nomeFantasia 
                                    FROM carrinho c
                                    INNER JOIN produtos p ON c.id_produto = p.id_produto
                                    INNER JOIN meus_parceiros pa ON c.id_parceiro = pa.id
                                    WHERE c.id_cliente = $id_cliente
                                    ORDER BY c.id_parceiro") or die($mysqli->error);

    $carrinho = [];
    //echo $carrinho;
    while ($produto = $sql_produtos->fetch_assoc()) {
        $id_parceiro = $produto['id_parceiro'];
        
        // Agrupa os produtos por parceiro
        if (!isset($carrinho[$id_parceiro])) {
            $carrinho[$id_parceiro] = [
                'nomeFantasia' => $produto['nomeFantasia'],
                'produtos' => [],
                'total' => 0
            ];
        }

        // Adiciona o produto à lista do parceiro
        $carrinho[$id_parceiro]['produtos'][] = $produto;
        
        // Soma o total do parceiro
        $carrinho[$id_parceiro]['total'] += $produto['total'];
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Carrinho</title>
    <style>
        .carrinho-container {
            width: 90%;
            margin: 20px auto;
            font-family: Arial, sans-serif;
        }
        .parceiro {
            border: 1px solid #ddd;
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 8px;
            background: #f9f9f9;
        }
        .parceiro h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        .produto {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #ddd;
            font-size: 14px;
        }
        .produto:last-child {
            border-bottom: none;
        }
        .total {
            text-align: right;
            font-weight: bold;
            margin-top: 10px;
            font-size: 16px;
        }
        .produto span {
            flex: 1;
            text-align: center;
        }
        .header {
            font-weight: bold;
            background: #ddd;
            padding: 5px;
        }
    </style>
</head>
<body>

<div class="carrinho-container">
    <h2>Meu Carrinho</h2>

    <?php if (!empty($carrinho)): ?>
        <?php foreach ($carrinho as $id_parceiro => $dados): ?>
            <div class="parceiro">
                <h3>Loja: <?php echo htmlspecialchars($dados['nomeFantasia']); ?></h3>
                
                <div class="produto header">
                    <span>Produto</span>
                    <span>Valor Unitário</span>
                    <span>Taxa</span>
                    <span>Frete</span>
                    <span>Quantidade</span>
                    <span>Total</span>
                </div>

                <?php foreach ($dados['produtos'] as $produto): ?>
                    <div class="produto">
                        <span><?php echo htmlspecialchars($produto['nome_produto']); ?></span>
                        <span>R$ <?php echo number_format($produto['valor_produto'], 2, ',', '.'); ?></span>
                        <span><?php echo number_format($produto['taxa_padrao'], 2, ',', '.'); ?>%</span>
                        <span>R$ <?php echo number_format($produto['frete'], 2, ',', '.'); ?></span>
                        <span><?php echo $produto['qt']; ?></span>
                        <span>R$ <?php echo number_format($produto['total'], 2, ',', '.'); ?></span>
                    </div>
                <?php endforeach; ?>

                <div class="total">Total do Parceiro: R$ <?php echo number_format($dados['total'], 2, ',', '.'); ?></div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Seu carrinho está vazio.</p>
    <?php endif; ?>
</div>

</body>
</html>
