<?php
session_start();
include('../../../conexao.php'); // Conexão com o banco

// Verifica se o usuário está logado
if (!isset($_GET['id_parceiro']) || $_GET['id_parceiro'] != $_SESSION['id']) {
    header("Location: ../../../../index.php");
    exit;
}

// Verifica se o ID do pedido foi enviado
if (!isset($_GET['num_pedido'])) {
    echo "Número do pedido não fornecido.";
    exit;
}

// Obtém o ID do cliente logado e o número do pedido
$id_parceiro = $_GET['id_parceiro'];
$num_pedido = $_GET['num_pedido'];

// Consulta para buscar os dados do pedido
$query = "SELECT p.*, c.nome_completo, c.endereco, c.numero, c.bairro, c.cidade, c.uf, c.cep, c.celular1 
          FROM pedidos p 
          JOIN meus_clientes c ON p.id_cliente = c.id 
          WHERE p.id_parceiro = ? AND p.num_pedido = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("ii", $id_parceiro, $num_pedido);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $pedido = $result->fetch_assoc();
    $motivo_cancelamento = $pedido['motivo_cancelamento'];
    $status_cliente = $pedido['status_cliente'];
    $status_parceiro = $pedido['status_parceiro'];

    // Determina quem cancelou com base no maior status
    if ($status_cliente > $status_parceiro) {
        $quem_cancelou = 'Cliente';
    } else {
        $quem_cancelou = 'Parceiro';
    }

    $produtos = explode('|', $pedido['produtos']);
    $frete = $pedido['valor_frete'];
    $saldo_usado = $pedido['saldo_usado'];

    // Calcula o total dos produtos listados
    $valor_total_produtos = array_reduce($produtos, function ($carry, $produto) {
        list($nome, $quantidade, $valor_unitario, $valor_total) = explode('/', $produto);
        return $carry + (float) $valor_total;
    }, 0.0);

    // Calcula o valor total geral
    $valor_total = $valor_total_produtos + $frete - $saldo_usado;
    $exibir_frete_saldo = $valor_total_produtos > 0 || $frete > 0 || $saldo_usado > 0;

    // Calcula o tempo que durou para cancelar o pedido usando data_finalizacao
    $dataPedido = new DateTime($pedido['data']);
    $dataFinalizacao = new DateTime($pedido['data_finalizacao']);
    $intervalo = $dataPedido->diff($dataFinalizacao);
    $tempoCancelamento = $intervalo->format('%d dias, %h horas, %i minutos');
} else {
    echo "Pedido não encontrado.";
    exit;
}
$stmt->close();
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Cancelado</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 15px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h1,
        h2,
        h3 {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        table th {
            background-color: #f4f4f4;
        }

        .valores {
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
        }

        .motivo {
            margin-top: 20px;
            font-size: 16px;
            color: red;
            font-weight: bold;
        }

        button {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .info {
            margin: 20px 0;
            font-size: 16px;
        }

        .info p {
            margin: 5px 0;
        }

        @media (max-width: 600px) {
            body {
                font-size: 14px;
            }

            h1,
            h2,
            h3 {
                font-size: 18px;
            }

            p,
            .valores,
            .info p {
                font-size: 14px;
            }

            table th,
            table td {
                font-size: 12px;
                padding: 6px;
            }

            button {
                font-size: 14px;
                padding: 8px 12px;
            }

            .container {
                padding: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Pedido Cancelado</h1>
        <h2>Pedido #<?php echo htmlspecialchars($num_pedido); ?></h2>
        <p class="motivo">Motivo do Cancelamento: <?php echo htmlspecialchars($motivo_cancelamento); ?></p>
        <p><strong>Cancelado pelo:</strong> <?php echo $quem_cancelou; ?></p>
        <div class="info">
            <p><strong>Data do Pedido:</strong> <?php echo date('d/m/Y H:i', strtotime($pedido['data'])); ?></p>
            <p><strong>Data do Cancelamento:</strong>
                <?php echo date('d/m/Y H:i', strtotime($pedido['data_finalizacao'])); ?></p>
            <p><strong>Tempo para Cancelar:</strong> <?php echo $tempoCancelamento; ?></p>
            <hr>
            <p><strong>Cliente:</strong> <?php echo htmlspecialchars($pedido['nome_completo']); ?></p>
            <hr>
            <p><strong>Endereço:</strong>
                <?php echo htmlspecialchars($pedido['endereco'] . ', ' . $pedido['numero'] . ' - ' . $pedido['bairro'] . ', ' . $pedido['cidade'] . '/' . $pedido['uf'] . ' - CEP: ' . $pedido['cep']); ?>
            </p>
            <p><strong>Contato:</strong> <?php echo htmlspecialchars($pedido['celular1']); ?></p>
            <hr>
            <p><strong>Tipo de Entrega:</strong>
                <?php echo $pedido['tipo_entrega'] == 'entregar' ? 'Entrega em casa' : 'Retirada na loja'; ?></p>
            <p><strong>Forma de Pagamento:</strong> <?php echo ucfirst($pedido['formato_compra']); ?></p>
        </div>
        <hr>
        <h3>Detalhes do Pedido</h3>
        <table>
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Qt</th>
                    <th>Vlr Uni</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos as $produto): ?>
                    <?php
                    list($nome, $quantidade_listada, $valor_unitario, $total_listado) = explode('/', $produto);
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($nome); ?></td>
                        <td><?php echo htmlspecialchars($quantidade_listada); ?></td>
                        <td>R$ <?php echo number_format($valor_unitario, 2, ',', '.'); ?></td>
                        <td>R$ <?php echo number_format($total_listado, 2, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p class="valores"><strong>Valor Total dos Produtos:</strong> R$
            <?php echo number_format($valor_total_produtos, 2, ',', '.'); ?></p>
        <?php if ($exibir_frete_saldo): ?>
            <?php if ($frete == 0): ?>
                <p class="valores" style="color: green;"><strong>Frete:</strong> Frete Grátis</p>
            <?php else: ?>
                <p class="valores"><strong>Frete:</strong> R$ <?php echo number_format($frete, 2, ',', '.'); ?></p>
            <?php endif; ?>
            <?php if ($saldo_usado > 0): ?>
                <p class="valores"><strong>Saldo Utilizado:</strong> - R$
                    <?php echo number_format($saldo_usado, 2, ',', '.'); ?>
                </p>
            <?php endif; ?>
        <?php endif; ?>
        <p class="valores"><strong>Valor Total Geral:</strong> R$
            <?php echo number_format($valor_total, 2, ',', '.'); ?></p>
        <button onclick="window.location.href='pedidos.php';">Voltar</button>
    </div>
</body>

</html>