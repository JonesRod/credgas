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

// desativar a notificação do pedido contador_notificacoes_parceiro
$query_notificacao = "DELETE FROM contador_notificacoes_parceiro WHERE id_parceiro = ? AND cod_num_pedido = ?";
$stmt = $mysqli->prepare($query_notificacao);
$stmt->bind_param("ii", $id_parceiro, $num_pedido);
$stmt->execute();


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

        table th:nth-child(3),
        /* Coluna "Qt" */
        table td:nth-child(3) {
            width: 50px;
            text-align: center;
        }

        table td:nth-child(3) input[type="number"] {
            width: 50px;
            text-align: center;
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

            table {
                font-size: 12px;
                width: 100%;
                border-collapse: collapse;
            }

            table th,
            table td {
                font-size: 10px;
                padding: 4px;
                text-align: center;
            }

            table td:nth-child(3) input[type="number"] {
                width: 40px;
            }

            button {
                font-size: 12px;
                padding: 6px 10px;
            }

            .container {
                padding: 10px;
            }

            textarea {
                font-size: 14px;
            }
        }

        @media (max-width: 380px) {
            body {
                font-size: 12px;
            }

            h1,
            h2,
            h3 {
                font-size: 16px;
            }

            p,
            .valores,
            .info p {
                font-size: 12px;
            }

            table {
                font-size: 10px;
                width: 100%;
                border-collapse: collapse;
            }

            table th,
            table td {
                font-size: 8px;
                padding: 2px;
                text-align: center;
            }

            table td:nth-child(3) input[type="number"] {
                width: 30px;
            }

            button {
                font-size: 10px;
                padding: 4px 8px;
            }

            .container {
                padding: 8px;
            }

            textarea {
                font-size: 12px;
            }
        }

        .button-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        button {
            font-size: 16px;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: #fff;
        }

        button#bt_recusar_pedido {
            background-color: #dc3545;
            /* Vermelho */
        }

        button#bt_recusar_pedido:hover {
            background-color: #c82333;
            /* Vermelho mais escuro */
        }

        button#bt_confirmar_pedido {
            background-color: #28a745;
            /* Verde */
        }

        button#bt_confirmar_pedido:hover {
            background-color: #218838;
            /* Verde mais escuro */
        }

        button.voltar {
            background-color: #007bff;
            /* Azul */
        }

        button.voltar:hover {
            background-color: #0056b3;
            /* Azul mais escuro */
        }

        @media (max-width: 600px) {
            button {
                font-size: 14px;
                padding: 8px 12px;
            }
        }

        @media (max-width: 380px) {
            button {
                font-size: 12px;
                padding: 6px 10px;
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
                } elseif ($pedido['status_cliente'] == 4) {
                    echo "Pedido recusado.";
                } elseif ($pedido['status_cliente'] == 5) {
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
                    <th>Qt</th>
                    <th>Vlr Uni</th>
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

                    echo "<tr>";
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
                    echo "<td class='total-cell'>R$ " . number_format($valor_total, 2, ',', '.') . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
        <p class="valores">
            Total: R$ <span id="total_inicial">0,00</span>
        </p>
        <?php
        if ($saldo_usado != 0) {
            echo "<p id='saldo_usado' class='valores' data-saldo='$saldo_usado' style='display: none;'><strong>Saldo Utilizado:</strong> - R$ " . number_format($saldo_usado, 2, ',', '.') . "</p>";
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
        <p id="frete" class="valores" data-frete="<?php echo $frete; ?>"
            style="display: none; color: <?php echo $frete == 0 ? 'green' : 'inherit'; ?>;">
            <strong><?php echo $frete == 0 ? 'Frete Grátis' : 'Frete:'; ?></strong>
            <?php echo $frete == 0 ? '' : 'R$ ' . number_format($frete, 2, ',', '.'); ?>
        </p>
        <p id="valor_total" class="valores" data-total="<?php echo $total; ?>" style="display: none;">
            <strong>Valor Total: R$</strong>
            <?php
            $valor_total_menos_saldo = $valor_a_vista + $frete + $taxa_crediario - $saldo_usado;
            echo number_format($valor_total_menos_saldo, 2, ',', '.');
            ?>
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
            <button class="voltar" onclick="javascript:history.back()">Voltar para os Pedidos</button>
            <?php if ($pedido['status_cliente'] != 1): ?>
                <button id="bt_recusar_pedido" onclick="">Recusar pedido</button>
            <?php endif; ?>
            <button id="bt_confirmar_pedido" style="display: none;">Confirmar Pedido</button>
        </div>
    </div>
</body>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const rows = document.querySelectorAll('table tbody tr');
        const totalElement = document.getElementById('total_inicial');
        const freteElement = document.getElementById('frete');
        const valorTotalElement = document.getElementById('valor_total');
        const saldoUsadoElement = document.getElementById('saldo_usado');
        const confirmButton = document.getElementById('bt_confirmar_pedido');
        const recusarButton = document.getElementById('bt_recusar_pedido');

        // Verifica se todos os elementos necessários estão presentes
        if (!rows.length || !totalElement || !freteElement || !valorTotalElement || !confirmButton || !recusarButton) {
            //console.warn('Elementos necessários não encontrados na página.');
            return;
        }

        function updateRowColor(row, checkbox, quantityInput, maxQuantity) {
            if (checkbox.checked) {
                const quantity = parseInt(quantityInput.value, 10);
                if (quantity < maxQuantity) {
                    row.style.color = 'orange'; // Quantidade menor que a escolhida pelo cliente
                } else if (quantity === maxQuantity) {
                    row.style.color = 'green'; // Quantidade correta confirmada
                } else {
                    row.style.color = 'red'; // Quantidade maior que a permitida (não deveria ocorrer)
                }
            } else {
                row.style.color = 'red'; // Produto desconfirmado
            }
        }

        function updateTotals() {
            let totalProdutos = 0;
            let atLeastOneChecked = false;

            rows.forEach(row => {
                const checkbox = row.querySelector('input[type="checkbox"]');
                const quantityInput = row.querySelector('input[type="number"]');
                const totalCell = row.querySelector('.total-cell');
                if (!checkbox || !quantityInput || !totalCell) return;

                const unitPrice = parseFloat(quantityInput.getAttribute('data-unit-price'));
                const maxQuantity = parseInt(quantityInput.getAttribute('data-max'), 10);
                let quantity = parseInt(quantityInput.value, 10);

                if (quantity < 1) {
                    quantityInput.value = 1;
                    alert('A quantidade não pode ser menor que 1.');
                    quantity = 1;
                } else if (quantity > maxQuantity) {
                    quantityInput.value = maxQuantity;
                    alert('A quantidade não pode ser maior que a escolhida pelo cliente.');
                    quantity = maxQuantity;
                }

                updateRowColor(row, checkbox, quantityInput, maxQuantity);

                if (checkbox.checked && quantity > 0) {
                    const total = quantity * unitPrice;
                    totalProdutos += total;
                    totalCell.textContent = `R$ ${total.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`;
                    atLeastOneChecked = true;
                } else {
                    totalCell.textContent = 'R$ 0,00';
                }
            });

            const frete = parseFloat(freteElement.getAttribute('data-frete')) || 0;
            const saldoUsado = parseFloat(saldoUsadoElement.getAttribute('data-saldo')) || 0;
            const totalComFrete = totalProdutos + frete - saldoUsado;

            totalElement.textContent = totalProdutos.toLocaleString('pt-BR', { minimumFractionDigits: 2 });

            if (atLeastOneChecked) {
                freteElement.style.display = 'block';
                freteElement.style.color = frete === 0 ? 'green' : 'inherit';
                freteElement.innerHTML = frete === 0
                    ? '<strong>Frete Grátis</strong>'
                    : `<strong>Frete:</strong> R$ ${frete.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`;
                valorTotalElement.style.display = 'block';
                valorTotalElement.innerHTML = `<strong>Valor Total: R$</strong> ${totalComFrete.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`;

                if (saldoUsado > 0) {
                    saldoUsadoElement.style.display = 'block';
                }

                confirmButton.style.display = totalComFrete >= 0 ? 'block' : 'none';


            } else {
                freteElement.style.display = 'none';
                valorTotalElement.style.display = 'none';
                saldoUsadoElement.style.display = 'none';
                confirmButton.style.display = 'none';
            }
        }

        function showPopup(message, onConfirm) {
            const popup = document.createElement('div');
            popup.style.position = 'fixed';
            popup.style.top = '0';
            popup.style.left = '0';
            popup.style.width = '100%';
            popup.style.height = '100%';
            popup.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
            popup.style.display = 'flex';
            popup.style.justifyContent = 'center';
            popup.style.alignItems = 'center';
            popup.style.zIndex = '1000';

            const popupContent = document.createElement('div');
            popupContent.style.backgroundColor = '#fff';
            popupContent.style.padding = '20px';
            popupContent.style.borderRadius = '8px';
            popupContent.style.boxShadow = '0 2px 4px rgba(0, 0, 0, 0.2)';
            popupContent.style.textAlign = 'center';

            const messageElement = document.createElement('p');
            messageElement.textContent = message;
            messageElement.style.marginBottom = '20px';

            const buttonContainer = document.createElement('div');
            buttonContainer.style.display = 'flex';
            buttonContainer.style.justifyContent = 'space-around';

            const cancelButton = document.createElement('button');
            cancelButton.textContent = 'Cancelar';
            cancelButton.style.backgroundColor = '#dc3545';
            cancelButton.style.color = '#fff';
            cancelButton.style.border = 'none';
            cancelButton.style.padding = '10px 20px';
            cancelButton.style.borderRadius = '5px';
            cancelButton.style.cursor = 'pointer';
            cancelButton.addEventListener('click', () => {
                document.body.removeChild(popup);
            });

            const confirmButton = document.createElement('button');
            confirmButton.textContent = 'Confirmar';
            confirmButton.style.backgroundColor = '#28a745';
            confirmButton.style.color = '#fff';
            confirmButton.style.border = 'none';
            confirmButton.style.padding = '10px 20px';
            confirmButton.style.borderRadius = '5px';
            confirmButton.style.cursor = 'pointer';
            confirmButton.addEventListener('click', () => {
                document.body.removeChild(popup);
                onConfirm();
            });

            buttonContainer.appendChild(cancelButton); // Botão de cancelar à esquerda
            buttonContainer.appendChild(confirmButton); // Botão de confirmar à direita
            popupContent.appendChild(messageElement);
            popupContent.appendChild(buttonContainer);
            popup.appendChild(popupContent);
            document.body.appendChild(popup);
        }

        confirmButton.addEventListener('click', function () {
            showPopup('Tem certeza de que deseja confirmar este pedido?', () => {
                const produtosConfirmados = [];
                rows.forEach(row => {
                    const checkbox = row.querySelector('input[type="checkbox"]');
                    const quantityInput = row.querySelector('input[type="number"]');
                    const unitPrice = parseFloat(quantityInput.getAttribute('data-unit-price'));
                    const productName = row.querySelector('td:nth-child(2)').textContent.trim();

                    if (checkbox.checked) {
                        const quantity = parseInt(quantityInput.value, 10);
                        const total = quantity * unitPrice;
                        produtosConfirmados.push({
                            nome: productName,
                            quantidade: quantity,
                            valor_unitario: unitPrice,
                            total: total
                        });
                    }
                });

                fetch('confirmar_pedido.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        num_pedido: <?php echo json_encode($num_pedido); ?>,
                        produtos: produtosConfirmados
                    }),
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showPopup('Pedido confirmado com sucesso!', () => {
                                // Redirecionar com ID do parceiro e número do pedido na URL
                                window.location.href = `pedido_confirmado.php?id_parceiro=${encodeURIComponent(<?php echo json_encode($id_parceiro); ?>)}&num_pedido=${encodeURIComponent(<?php echo json_encode($num_pedido); ?>)}`;
                            });
                        } else {
                            showPopup('Erro ao confirmar o pedido.', () => { });
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        showPopup('Erro ao processar a solicitação.', () => { });
                    });
            });
        });

        recusarButton.addEventListener('click', function () {
            const popup = document.createElement('div');
            popup.style.position = 'fixed';
            popup.style.top = '0';
            popup.style.left = '0';
            popup.style.width = '100%';
            popup.style.height = '100%';
            popup.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
            popup.style.display = 'flex';
            popup.style.justifyContent = 'center';
            popup.style.alignItems = 'center';
            popup.style.zIndex = '1000';

            const popupContent = document.createElement('div');
            popupContent.style.backgroundColor = '#fff';
            popupContent.style.padding = '20px';
            popupContent.style.borderRadius = '8px';
            popupContent.style.boxShadow = '0 2px 4px rgba(0, 0, 0, 0.2)';
            popupContent.style.textAlign = 'center';

            const messageElement = document.createElement('p');
            messageElement.textContent = 'Tem certeza de que deseja recusar este pedido?';
            messageElement.style.marginBottom = '20px';

            const errorMessage = document.createElement('p');
            errorMessage.style.color = 'red';
            errorMessage.style.display = 'none'; // Inicialmente oculto
            errorMessage.style.marginBottom = '10px';

            const textarea = document.createElement('textarea');
            textarea.placeholder = 'Justifique a recusa (obrigatório)';
            textarea.style.width = '100%';
            textarea.style.height = '80px';
            textarea.style.marginBottom = '20px';
            textarea.style.resize = 'none'; // Impedir redimensionamento, se necessário
            textarea.style.padding = '10px'; // Garantir espaço interno para digitação
            textarea.style.fontSize = '14px'; // Ajustar tamanho da fonte para melhor visibilidade
            textarea.style.boxSizing = 'border-box'; // Garantir que o padding não afete o tamanho total

            const buttonContainer = document.createElement('div');
            buttonContainer.style.display = 'flex';
            buttonContainer.style.justifyContent = 'space-around';

            const cancelButton = document.createElement('button');
            cancelButton.textContent = 'Cancelar';
            cancelButton.style.backgroundColor = '#dc3545';
            cancelButton.style.color = '#fff';
            cancelButton.style.border = 'none';
            cancelButton.style.padding = '10px 20px';
            cancelButton.style.borderRadius = '5px';
            cancelButton.style.cursor = 'pointer';
            cancelButton.addEventListener('click', () => {
                document.body.removeChild(popup);
            });

            const confirmButton = document.createElement('button');
            confirmButton.textContent = 'Confirmar';
            confirmButton.style.backgroundColor = '#28a745';
            confirmButton.style.color = '#fff';
            confirmButton.style.border = 'none';
            confirmButton.style.padding = '10px 20px';
            confirmButton.style.borderRadius = '5px';
            confirmButton.style.cursor = 'pointer';
            confirmButton.addEventListener('click', () => {
                const motivo = textarea.value.trim();
                if (!motivo) {
                    errorMessage.textContent = 'Por favor, justifique a recusa.';
                    errorMessage.style.display = 'block'; // Exibir a mensagem de erro
                    return;
                }

                // Captura a data e hora local do cliente
                const now = new Date();
                const dataHoraCliente = now.toLocaleString('sv-SE', { timeZoneName: 'short' }).replace('T', ' ');

                fetch('recusar_pedido.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id_parceiro: <?php echo json_encode($id_parceiro); ?>,
                        num_pedido: <?php echo json_encode($num_pedido); ?>,
                        motivo_cancelamento: motivo,
                        data_hora_cliente: dataHoraCliente
                    }),
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.text();
                    })
                    .then(text => {
                        try {
                            const data = JSON.parse(text);
                            if (data.success) {
                                const successPopup = document.createElement('div');
                                successPopup.style.position = 'fixed';
                                successPopup.style.top = '0';
                                successPopup.style.left = '0';
                                successPopup.style.width = '100%';
                                successPopup.style.height = '100%';
                                successPopup.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
                                successPopup.style.display = 'flex';
                                successPopup.style.justifyContent = 'center';
                                successPopup.style.alignItems = 'center';
                                successPopup.style.zIndex = '1000';

                                const successContent = document.createElement('div');
                                successContent.style.backgroundColor = '#fff';
                                successContent.style.padding = '20px';
                                successContent.style.borderRadius = '8px';
                                successContent.style.boxShadow = '0 2px 4px rgba(0, 0, 0, 0.2)';
                                successContent.style.textAlign = 'center';

                                const successMessage = document.createElement('p');
                                successMessage.textContent = 'Pedido recusado com sucesso!';
                                successMessage.style.marginBottom = '20px';

                                successContent.appendChild(successMessage);
                                successPopup.appendChild(successContent);
                                document.body.appendChild(successPopup);

                                setTimeout(() => {
                                    window.location.href = `pedido_recusado.php?id_parceiro=${encodeURIComponent(<?php echo json_encode($id_parceiro); ?>)}&num_pedido=${encodeURIComponent(<?php echo json_encode($num_pedido); ?>)}`;
                                }, 2000); // Redirecionar após 2 segundos
                            } else {
                                errorMessage.textContent = data.message || 'Erro ao recusar o pedido.';
                                errorMessage.style.display = 'block';
                            }
                        } catch (error) {
                            console.error('Erro ao processar JSON:', error);
                            errorMessage.textContent = 'Resposta inesperada do servidor.';
                            errorMessage.style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        errorMessage.textContent = 'Erro ao processar a solicitação.';
                        errorMessage.style.display = 'block';
                    });
            });

            buttonContainer.appendChild(cancelButton);
            buttonContainer.appendChild(confirmButton);
            popupContent.appendChild(messageElement);
            popupContent.appendChild(errorMessage); // Adicionar a mensagem de erro ao popup
            popupContent.appendChild(textarea);
            popupContent.appendChild(buttonContainer);
            popup.appendChild(popupContent);
            document.body.appendChild(popup);
        });

        rows.forEach(row => {
            const checkbox = row.querySelector('input[type="checkbox"]');
            const quantityInput = row.querySelector('input[type="number"]');

            if (!checkbox || !quantityInput) return;

            quantityInput.disabled = !checkbox.checked;

            checkbox.addEventListener('change', () => {
                quantityInput.disabled = !checkbox.checked;
                updateTotals(); // Garantir que updateTotals gerencie a visibilidade do botão
            });

            quantityInput.addEventListener('input', updateTotals);
        });

        updateTotals(); // Chamar updateTotals diretamente para inicializar corretamente
    });
</script>

</html>
<?php

?>