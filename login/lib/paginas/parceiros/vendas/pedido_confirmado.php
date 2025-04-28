<?php
//var_dump($_POST);
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
$id_parceiro = $_SESSION['id'];

// Obtém o ID do pedido enviado via POST
$num_pedido = $_POST['num_pedido'];

// Consulta para buscar os dados do pedido
$query = "SELECT * FROM pedidos WHERE id_parceiro = ? AND num_pedido = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("ii", $id_parceiro, $num_pedido);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $pedido = $result->fetch_assoc();
    $status_parceiro = $pedido['status_parceiro'];
    $valor_a_vista = $pedido['valor_produtos'];
    $taxa_crediario = $pedido['taxa_crediario'];
    $frete = $pedido['valor_frete'];
    $saldo_usado = $pedido['saldo_usado'];
    $total = $valor_a_vista + $frete + $taxa_crediario - $saldo_usado;
    $tipo_entrega = $pedido['tipo_entrega'];
    //echo $valor_a_vista;
} else {
    echo "Pedido não encontrado.";
    exit;
}

$formato_compra = $pedido['formato_compra']; // Corrigido: Removido código duplicado ou incorreto

// Calculate end time for countdown
$pedido_time = new DateTime($pedido['data']);
$pedido_time->modify('+15 minutes');
$end_time = $pedido_time->format('Y-m-d H:i:s');

// Consulta para buscar os produtos confirmados
$produtos_confirmados = [];
if (!empty($pedido['produtos_confirmados'])) {
    $produtos_confirmados = explode('|', $pedido['produtos_confirmados']);
}

// Fetch partner details from the database
$query_parceiro = "SELECT * FROM meus_parceiros WHERE id = ?";
$stmt_parceiro = $mysqli->prepare($query_parceiro);
$stmt_parceiro->bind_param("i", $id_parceiro);
$stmt_parceiro->execute();
$result_parceiro = $stmt_parceiro->get_result();
$loja = $result_parceiro->fetch_assoc();
$logo = $loja['logo'];
$nomeFantasia = $loja['nomeFantasia'];
$tempo_entrega = $loja['estimativa_entrega'];
$stmt_parceiro->close();

$id_cliente = $pedido['id_cliente'];

// Consulta para buscar os dados do cliente
$query_cliente = "SELECT * FROM meus_clientes WHERE id = ?";
$stmt_cliente = $mysqli->prepare($query_cliente);
$stmt_cliente->bind_param("i", $id_cliente);
$stmt_cliente->execute();
$result_cliente = $stmt_cliente->get_result();

if ($result_cliente->num_rows > 0) {
    $cliente = $result_cliente->fetch_assoc();
    $nome_completo = $cliente['nome_completo'];
    $primeiro_nome = explode(' ', $nome_completo)[0];
} else {
    echo "Cliente não encontrado.";
    exit;
}
$stmt_cliente->close();

function formatDateTimeJS($dateString)
{
    if (empty($dateString)) {
        return "Data não disponível";
    }
    try {
        $date = new DateTime($dateString);
        return $date->format('d/m/Y H:i');
    } catch (Exception $e) {
        return "Erro na data";
    }
}

if ($formato_compra == 'crediario') {
    $formato_compra = 'online';
} elseif ($formato_compra == 'online') {
    $formato_compra = 'online';
} elseif ($formato_compra == 'retirar') {
    $formato_compra = 'retirar';
} else {
    $formato_compra = 'entregar';
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Pedido</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            color: #333;
        }

        header,
        h1,
        h2,
        h3 {
            text-align: center;
            margin: 10px 0;
        }

        .end-parceiro {
            text-align: center;
            font-size: 14px;
            color: #555;
        }

        img {
            display: block;
            margin: 0 auto;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 15px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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

        button {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px 5px;
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

        #bt_recusar_pedido {
            display: block;
            padding: 10px 20px;
            margin: 10px 5px;
            font-size: 16px;
            color: #fff;
            background-color: #dc3545;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        #bt_recusar_pedido:hover {
            background-color: #c82333;
        }

        #bt_confirmar_pedido {
            display: none;
            padding: 10px 20px;
            margin: 10px 5px;
            font-size: 16px;
            color: #fff;
            background-color: #28a745;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        #bt_confirmar_pedido:hover {
            background-color: #218838;
        }

        .cancel-timer {
            text-align: center;
            margin: 20px 0;
        }

        textarea {
            width: 97.5%;
            height: 100px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: none;
        }

        .valores {
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
        }

        .button-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        @media (max-width: 600px) {
            .container {
                padding: 10px;
            }

            table th,
            table td {
                font-size: 12px;
                padding: 4px;
            }

            button {
                font-size: 14px;
                padding: 8px 15px;
            }

            img {
                width: 80px;
            }

            textarea {
                width: 91.5%;
                height: 100px;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 5px;
                resize: none;
            }

            .valores {
                font-size: 16px;
                margin-right: 10px;
            }

            .button-container {
                flex-direction: column;
                gap: 5px;
            }

            .cancel-timer {
                font-size: 14px;
            }

            h1,
            h2,
            h3 {
                font-size: 18px;
            }

            p {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Detalhes do Pedido</h1>
        <hr>
        <h3>Cliente: <span><?php echo $primeiro_nome; ?></span></h3>
        <hr>
        <h2>Pedido #<?php echo $num_pedido; ?></h2>
        <p style="color:darkgreen;">
            <strong>Cód. para Retirada: <?php echo htmlspecialchars($pedido['codigo_retirada']); ?></strong>
        </p>
        <p><strong>Data do pedido:</strong> <?php echo htmlspecialchars(formatDateTimeJS($pedido['data'])); ?></p>
        <p><strong>Status do Pedido:</strong>
            <span
                style="color: <?php echo $pedido['status_cliente'] === 0 ? '#ff5722' : ($pedido['status_cliente'] === 1 ? 'green' : ($pedido['status_cliente'] === 2 ? 'red' : ($pedido['status_cliente'] === 3 ? 'blue' : 'gray'))); ?>">
                <?php
                if ($pedido['status_cliente'] == 0) {
                    echo "Aguardando Confirmação da loja.";
                } elseif ($pedido['status_cliente'] == 1) {
                    echo "Pedido confirmado e já está em preparação.";
                } elseif ($pedido['status_cliente'] == 2) {
                    if ($pedido['tipo_entrega'] == 'entregar') {
                        echo "Saiu para entrega.";
                    } else {
                        echo "Pedido pronto para retirada.";
                    }
                } elseif ($pedido['status_cliente'] == 3) {
                    echo "Pedido Entregue.";
                } else {
                    echo "Pedido Cancelado.";
                }
                ?>
            </span>
        </p>
        <hr>
        <h3>Produtos</h3>
        <table>
            <thead>
                <tr>
                    <?php if ($pedido['status_cliente'] == 0): ?>
                        <th>Confirmar</th>
                    <?php endif; ?>
                    <th>Produto</th>
                    <th>Quantidade</th>
                    <th>Valor Unitário</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $produtos = explode('|', $pedido['produtos']);
                $produtos_confirmados_map = [];

                // Mapeia os produtos confirmados para facilitar a verificação
                foreach ($produtos_confirmados as $produto_confirmado) {
                    list($nome, $quantidade, $valor_unitario, $valor_total) = explode('/', $produto_confirmado);
                    $produtos_confirmados_map[$nome] = [
                        'quantidade' => $quantidade,
                        'valor_unitario' => $valor_unitario,
                        'valor_total' => $valor_total,
                    ];
                }

                foreach ($produtos as $produto) {
                    list($nome, $quantidade, $valor_unitario, $valor_total) = explode('/', $produto);
                    $is_confirmed = isset($produtos_confirmados_map[$nome]);
                    $confirmed_quantity = $is_confirmed ? $produtos_confirmados_map[$nome]['quantidade'] : $quantidade;
                    $row_color = $is_confirmed ? ($confirmed_quantity == $quantidade ? 'green' : 'orange') : 'red';

                    echo "<tr style='color: $row_color;'>";
                    if ($pedido['status_cliente'] == 0) {
                        echo "<td><input type='checkbox' name='confirmar[]' " . ($is_confirmed ? 'checked disabled' : '') . "></td>";
                    }
                    echo "<td>$nome</td>";
                    if ($pedido['status_cliente'] == 0) {
                        echo "<td><input type='number' value='$confirmed_quantity' data-max='$quantidade' data-unit-price='$valor_unitario' " . ($is_confirmed ? 'disabled' : '') . "></td>";
                    } else {
                        echo "<td>$confirmed_quantity</td>";
                    }
                    echo "<td>R$ " . number_format($valor_unitario, 2, ',', '.') . "</td>";
                    echo "<td>R$ " . number_format($valor_total, 2, ',', '.') . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
        <p class="valores">
            Total: R$ <span id="total_inicial"><?php echo number_format($valor_a_vista, 2, ',', '.'); ?></span>
        </p>
        <?php
        if ($frete != 0 && $tipo_entrega == 'entregar') {
            echo "<p id='frete' class='valores' data-frete='$frete'><strong>Frete:</strong> R$ " . number_format($frete, 2, ',', '.') . "</p>";
        } else {
            echo "<p id='frete' class='valores' data-frete='0'><strong>Frete Grátis</strong></p>";
        }
        ?>
        <?php
        if ($saldo_usado != 0 && $formato_compra != 'online') {
            echo "<p id='saldo_usado' class='valores' data-saldo='$saldo_usado'><strong>Saldo Utilizado:</strong> - R$ " . number_format($saldo_usado, 2, ',', '.') . "</p>";
        } else {
            echo "<p id='saldo_usado' class='valores' data-saldo='0' style='display: none;'><strong>Saldo Utilizado: 0,00</strong></p>";
        }
        ?>
        <?php
        if ($taxa_crediario != 0 && $formato_compra == 'online') {
            echo "<p id='taxa_crediario' class='valores' data-taxa='$taxa_crediario'><strong>Taxa:</strong> R$ " . number_format($taxa_crediario, 2, ',', '.') . "</p>";
        } else {
            echo "<p id='taxa_crediario' class='valores' data-taxa='0' style='display: none;'><strong>Taxa: Grátis</strong></p>";
        }
        ?>
        <p id="valor_total" class="valores" data-total="<?php echo $total; ?>"><strong>Valor Total: R$</strong>
            <?php echo number_format($total, 2, ',', '.'); ?>
        </p>
        <hr>
        <h3>Status de Pagamento</h3>
        <p>
            <?php
            if ($formato_compra == 'crediario') {
                echo "<p><strong>Pagamento: <span>Online.</span></strong></p>";
            } elseif ($formato_compra == 'online') {
                echo "<p><strong>Pagamento: <span>Online.</span></strong></p>";
            } elseif ($formato_compra == 'retirar') {
                echo "<p><strong>Pagamento: <span>Na Retirada.</span></strong></p>";
            } else {
                echo "<p><strong>Pagamento: <span>Na Entrega.</span></strong></p>";
            }
            ?>
        </p>
        <hr>
        <h3>Forma de Entrega</h3>
        <p><strong>Tipo de Entrega:</strong>
            <?php
            if ($pedido['tipo_entrega'] == 'entregar') {
                echo "Entregar em casa.";
            } elseif ($pedido['tipo_entrega'] == 'buscar') {
                echo "Retirar na loja.";
            } else {
                echo "Retirar na loja.";
            }
            ?>
        </p>
        <p><strong>AV/RUA:</strong>
            <?php
            if ($pedido['tipo_entrega'] == 'entregar') {
                echo $pedido['endereco_entrega'] != '' ? $pedido['endereco_entrega'] : $cliente['endereco'];
            } elseif ($pedido['tipo_entrega'] == 'buscar') {
                echo $loja['endereco'];
            }
            ?>
        </p>
        <p><strong>Nº:</strong>
            <?php
            if ($pedido['tipo_entrega'] == 'entregar') {
                echo $pedido['num_entrega'] != '' ? $pedido['num_entrega'] : $cliente['numero'];
            } elseif ($pedido['tipo_entrega'] == 'buscar') {
                echo $loja['numero'];
            }
            ?>
        </p>
        <p><strong>BAIRRO:</strong>
            <?php
            if ($pedido['tipo_entrega'] == 'entregar') {
                echo $pedido['bairro_entrega'] != '' ? $pedido['bairro_entrega'] : $cliente['bairro'];
            } elseif ($pedido['tipo_entrega'] == 'buscar') {
                echo $loja['bairro'];
            }
            ?>
        </p>
        <p style="display: none;"><strong>CIDADE/UF:</strong>
            <?php
            if ($pedido['tipo_entrega'] == 'entregar') {
                echo $cliente['cidade'] . '/' . $cliente['uf'] . ', CEP: ' . $cliente['cep'];
            } elseif ($pedido['tipo_entrega'] == 'buscar') {
                echo $loja['cidade'] . '/' . $loja['estado'] . ', CEP: ' . $loja['cep'];
            }
            ?>
        </p>
        <p><strong>CONTATO:</strong>
            <?php
            if ($pedido['tipo_entrega'] == 'entregar') {
                echo $pedido['contato_recebedor'] != '' ? $pedido['contato_recebedor'] : $cliente['celular1'];
            } elseif ($pedido['tipo_entrega'] == 'buscar') {
                echo $loja['telefoneComercial'];
            }
            ?>
        </p>
        <p id="comentario_container" style="display: <?php echo empty($pedido['comentario']) ? 'none' : 'block'; ?>;">
            <strong>COMENTÁRIO:</strong>
        </p>
        <textarea name="comentario" id="comentario"
            style="display: <?php echo empty($pedido['comentario']) ? 'none' : 'block'; ?>;"><?php echo $pedido['comentario']; ?></textarea>
        <hr>
        <p id="tempo-cancelar" class="cancel-timer" style="color: red; display: none;">
            <strong>Tempo para cancelar:</strong>
            <span id="countdown" data-end-time="<?php echo $end_time; ?>"></span>
        </p>
        <?php if ($pedido['status_cliente'] != 1): ?>
            <p id="text-cancelar" class="cancel-timer" style="color: red; display: none;">
                <strong>O tempo de resposta expirou. Você pode cancelar sua compra!</strong>
            </p>
        <?php endif; ?>
        <div class="button-container">
            <button onclick="javascript:history.back()">Voltar para os Pedidos</button>
            <?php if ($pedido['status_cliente'] != 1): ?>
                <button id="bt_recusar_pedido" style="display: none;" onclick="">Recusar pedido</button>
            <?php endif; ?>
            <button id="bt_confirmar_pedido">Confirmar Pedido</button>
        </div>
    </div>
</body>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const rows = document.querySelectorAll('table tbody tr');
        const totalElement = document.getElementById('total_inicial');
        const freteElement = document.getElementById('frete');
        const confirmButton = document.getElementById('bt_confirmar_pedido');

        if (!rows.length || !totalElement || !confirmButton) {
            console.error('Elementos necessários não encontrados na página.');
            return;
        }

        function updateTotals() {
            let totalProdutos = 0;
            let atLeastOneChecked = false;

            rows.forEach(row => {
                const checkbox = row.querySelector('input[type="checkbox"]');
                const quantityInput = row.querySelector('input[type="number"]');
                if (!checkbox || !quantityInput) return;

                const unitPrice = parseFloat(quantityInput.getAttribute('data-unit-price'));
                const quantity = parseInt(quantityInput.value, 10);

                if (checkbox.checked && quantity > 0) {
                    totalProdutos += quantity * unitPrice;
                    atLeastOneChecked = true;
                }
            });

            const frete = freteElement ? parseFloat(freteElement.getAttribute('data-frete')) || 0 : 0;
            const totalComFrete = totalProdutos + (atLeastOneChecked ? frete : 0);

            totalElement.textContent = `${totalProdutos.toFixed(2).replace('.', ',')}`;
            if (freteElement) {
                freteElement.style.display = atLeastOneChecked ? 'block' : 'none';
            }
            const totalComFreteElement = document.getElementById('total_com_frete');
            if (totalComFreteElement) {
                
                totalComFreteElement.textContent = `R$ ${totalComFrete.toFixed(2).replace('.', ',')}`;
            }

            confirmButton.style.display = atLeastOneChecked ? 'block' : 'none';
        }

        function initializeTotals() {
            totalElement.textContent = 'R$ 0,00';
            if (freteElement) {
                freteElement.style.display = 'none';
            }
            const totalComFreteElement = document.getElementById('total_com_frete');
            if (totalComFreteElement) {
                totalComFreteElement.style.display = 'none';
            }
            confirmButton.style.display = 'none';
        }

        initializeTotals();

        rows.forEach(row => {
            const checkbox = row.querySelector('input[type="checkbox"]');
            const quantityInput = row.querySelector('input[type="number"]');
            const totalCell = row.querySelector('td:last-child');
            const rowCells = row.querySelectorAll('td');

            if (!checkbox || !quantityInput || !totalCell || !rowCells.length) return;

            updateRowColor(rowCells, 'red');

            checkbox.addEventListener('change', () => {
                if (checkbox.checked) {
                    quantityInput.disabled = false;
                    const maxQuantity = parseInt(quantityInput.getAttribute('data-max'), 10);
                    const quantity = parseInt(quantityInput.value, 10);

                    if (quantity === maxQuantity) {
                        updateRowColor(rowCells, 'green');
                    } else if (quantity < maxQuantity) {
                        updateRowColor(rowCells, 'orange');
                    }
                } else {
                    quantityInput.disabled = true;
                    updateRowColor(rowCells, 'red');
                }
                updateTotals();
            });

            quantityInput.addEventListener('input', () => {
                const unitPrice = parseFloat(quantityInput.getAttribute('data-unit-price'));
                const maxQuantity = parseInt(quantityInput.getAttribute('data-max'), 10);
                let quantity = parseInt(quantityInput.value, 10);

                if (isNaN(quantity) || quantity <= 0) {
                    quantityInput.value = 1;
                    alert('A quantidade deve ser maior que 0.');
                    return;
                }

                if (quantity > maxQuantity) {
                    quantityInput.value = maxQuantity;
                    alert('A quantidade não pode ser maior que a escolhida pelo cliente.');
                    return;
                }

                const total = quantity * unitPrice;
                totalCell.textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;

                if (checkbox.checked) {
                    if (quantity === maxQuantity) {
                        updateRowColor(rowCells, 'green');
                    } else if (quantity < maxQuantity) {
                        updateRowColor(rowCells, 'orange');
                    }
                }
                updateTotals();
            });

            quantityInput.disabled = !checkbox.checked;
        });

        function updateRowColor(rowCells, color) {
            rowCells.forEach(cell => {
                cell.style.color = color;
            });
        }

        updateTotals();

        confirmButton.addEventListener('click', function () {
            const numPedido = <?php echo json_encode($num_pedido); ?>;
            const produtosConfirmados = [];
            let totalProdutosConfirmados = 0;

            rows.forEach(row => {
                const checkbox = row.querySelector('input[type="checkbox"]');
                const quantityInput = row.querySelector('input[type="number"]');
                const unitPrice = parseFloat(quantityInput.getAttribute('data-unit-price'));
                const totalCell = row.querySelector('td:last-child');
                const productName = row.querySelector('td:nth-child(2)').textContent.trim();

                if (checkbox.checked) {
                    const quantity = parseInt(quantityInput.value, 10);
                    const total = parseFloat(totalCell.textContent.replace('R$', '').replace(',', '.').trim());
                    produtosConfirmados.push(`${productName}/${quantity}/${unitPrice}/${total}`);
                    totalProdutosConfirmados += total; // Soma o total dos produtos confirmados
                }
            });

            fetch('atualizar_status_pedido.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    num_pedido: numPedido,
                    status_cliente: 1,
                    produtos_confirmados: produtosConfirmados.join('|'), // Formata os produtos confirmados
                    valor_produtos_confirmados: totalProdutosConfirmados // Salva o total dos produtos confirmados
                }),
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Pedido confirmado com sucesso!');
                        location.reload(); // Recarrega a página para refletir as alterações
                    } else {
                        alert('Erro ao confirmar o pedido.');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao processar a solicitação.');
                });
        });
    });
</script>

</html>
<?php

?>