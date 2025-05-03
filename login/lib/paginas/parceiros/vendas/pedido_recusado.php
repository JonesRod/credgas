<?php
session_start();
include('../../../conexao.php'); // Conexão com o banco

// Verifica se o usuário está logado
if (!isset($_SESSION['id'])) {
    header("Location: ../../../../index.php");
    exit;
}

// Verifica se o ID do pedido foi enviado
if (!isset($_POST['num_pedido'])) {
    echo "Número do pedido não fornecido.";
    exit;
}

// Obtém o ID do cliente logado e o número do pedido
$id_parceiro = $_SESSION['id'];
$num_pedido = $_POST['num_pedido'];

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
    $motivo_recusa = $pedido['motivo_cancelamento'];
    $produtos = explode('|', $pedido['produtos']);
    $produtos_confirmados = !empty($pedido['produtos_confirmados']) ? explode('|', $pedido['produtos_confirmados']) : [];
    $frete = $pedido['valor_frete'];
    $saldo_usado = $pedido['saldo_usado'];

    // Mapeia os produtos confirmados
    $produtos_confirmados_map = [];
    foreach ($produtos_confirmados as $produto_confirmado) {
        list($nome, $quantidade, $valor_unitario, $valor_total) = explode('/', $produto_confirmado);
        $produtos_confirmados_map[$nome] = [
            'quantidade' => (float) $quantidade,
            'valor_unitario' => (float) $valor_unitario,
            'valor_total' => (float) $valor_total,
        ];
    }

    // Calcula o total apenas dos produtos confirmados
    $valor_total_produtos_confirmados = array_reduce($produtos_confirmados_map, function ($carry, $produto) {
        return $carry + $produto['valor_total'];
    }, 0.0);

    // Se nenhum produto foi confirmado, o valor total será 0
    if ($valor_total_produtos_confirmados == 0) {
        $valor_total = 0;
        $exibir_frete_saldo = false;
    } else {
        $valor_total = $valor_total_produtos_confirmados + $frete - $saldo_usado;
        $exibir_frete_saldo = true;
    }
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
    <title>Pedido Recusado</title>
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

            textarea {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Pedido Recusado</h1>
        <h2>Pedido #<?php echo htmlspecialchars($num_pedido); ?></h2>
        <p class="motivo">Motivo da Recusa: <?php echo htmlspecialchars($motivo_recusa); ?></p>
        <div class="info">
            <p><strong>Data do Pedido:</strong> <?php echo date('d/m/Y H:i', strtotime($pedido['data'])); ?></p>
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
                    $is_confirmed = isset($produtos_confirmados_map[$nome]);
                    $confirmed_quantity = $is_confirmed ? $produtos_confirmados_map[$nome]['quantidade'] : $quantidade_listada;
                    $confirmed_total = $is_confirmed ? $produtos_confirmados_map[$nome]['valor_total'] : $total_listado;

                    // Define a cor da linha com base na confirmação e quantidade
                    if (!$is_confirmed) {
                        $row_color = 'red'; // Produto não confirmado
                    } elseif ($confirmed_quantity < $quantidade_listada) {
                        $row_color = 'orange'; // Quantidade confirmada menor que a escolhida pelo cliente
                    } else {
                        $row_color = 'green'; // Quantidade confirmada igual à escolhida pelo cliente
                    }
                    ?>
                    <tr style="color: <?php echo $row_color; ?>;">
                        <td><?php echo htmlspecialchars($nome); ?></td>
                        <td><?php echo htmlspecialchars($confirmed_quantity); ?></td>
                        <td>R$ <?php echo number_format($valor_unitario, 2, ',', '.'); ?></td>
                        <td>R$ <?php echo number_format($confirmed_total, 2, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
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
        <p class="valores">Valor Total: R$ <?php echo number_format($valor_total, 2, ',', '.'); ?></p>
        <button onclick="window.location.href='pedidos.php';">Voltar</button>
    </div>
</body>

</html>