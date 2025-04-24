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
    //echo $status_parceiro;
} else {
    echo "Pedido não encontrado.";
    exit;
}
$formato_compra = $pedido['formato_compra'];
//echo $formato_compra;

// Calculate end time for countdown
$pedido_time = new DateTime($pedido['data']);
$pedido_time->modify('+15 minutes');
$end_time = $pedido_time->format('Y-m-d H:i:s');


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

// Fetch partner details from the database
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
            /* Cor vermelha */
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        #bt_recusar_pedido:hover {
            background-color: #c82333;
            /* Vermelho mais escuro ao passar o mouse */
        }

        #bt_confirmar_pedido {
            display: none;
            padding: 10px 20px;
            margin: 10px 5px;
            font-size: 16px;
            color: #fff;
            background-color: #28a745;
            /* Cor verde */
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        #bt_confirmar_pedido:hover {
            background-color: #218838;
            /* Verde mais escuro ao passar o mouse */
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
            margin-right: 30px;
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
        }

        .button-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            /* Espaçamento entre os botões */
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

        /* Esconde a coluna de confirmar produtos */
        .hide-column {
            display: none;
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
            <strong>
                Cód. para Retirada: <?php echo htmlspecialchars($pedido['codigo_retirada']); ?>
            </strong>
        </p>
        <p><strong>Data do pedido:</strong> <?php echo htmlspecialchars(formatDateTimeJS($pedido['data'])); ?></p>
        <p><strong>Status do Pedido:</strong>
            <span
                style="color: <?php echo $pedido['status_cliente'] === 0 ? '#ff5722' : ($pedido['status_cliente'] === 1 ? 'green' : ($pedido['status_cliente'] === 2 ? 'red' : ($pedido['status_cliente'] === 3 ? 'blue' : 'gray'))); ?>">
                <?php
                if ($pedido['status_cliente'] == 0) {
                    echo "Aguardando Confirmação da loja.";
                } elseif ($pedido['status_cliente'] == 1) {
                    echo "Pedido confirmado e ja está em preparação.";
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
                foreach ($produtos as $produto) {
                    list($nome, $quantidade, $valor_unitario, $valor_total) = explode('/', $produto);
                    echo "<tr>";
                    if ($pedido['status_cliente'] == 0) {
                        echo "<td><input type='checkbox' name='confirmar[]'></td>";
                    }
                    echo "<td>$nome</td>";
                    if ($pedido['status_cliente'] == 0) {
                        echo "<td><input type='number' value='$quantidade' data-max='$quantidade' data-unit-price='$valor_unitario'></td>";
                    } else {
                        echo "<td>$quantidade</td>";
                    }
                    echo "<td>R$ " . number_format($valor_unitario, 2, ',', '.') . "</td>";
                    echo "<td>R$ " . number_format($valor_total, 2, ',', '.') . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
        <p id="valor_vista" class="valores" data-total="<?php echo $valor_a_vista; ?>"><strong>Total:</strong> R$
            <?php echo number_format($valor_a_vista, 2, ',', '.'); ?>
        </p>
        <?php
        if ($frete != 0 && $tipo_entrega == 'entregar') {
            echo "<p id='frete' class='valores' data-frete='$frete'><strong>Frete:</strong> R$ " . number_format($frete, 2, ',', '.') . "</p>";
        } else {
            echo "<p id='frete' class='valores' data-frete='0'><strong></strong>Frete Grátis</p>";
        }
        ?>
        <?php
        if ($saldo_usado != 0) {
            echo "<p id='saldo_usado' class='valores' data-saldo='$saldo_usado'><strong>Saldo Utilzado:</strong> - R$ " . number_format($saldo_usado, 2, ',', '.') . "</p>";
        } else {
            echo "<p id='saldo_usado' class='valores' data-saldo='0' style='display: none;'><strong></strong>saldo_usado: 0,00</p>";
        }
        ?>
        <?php
        if ($taxa_crediario != 0 && $formato_compra == 'crediario') {
            echo "<p id='taxa_crediario' class='valores' data-taxa='$taxa_crediario'><strong>Taxa:</strong> R$ " . number_format($taxa_crediario, 2, ',', '.') . "</p>";
        } else {
            echo "<p id='taxa_crediario' class='valores' data-taxa='0' style='display: none;'><strong></strong>Taxa: Grátis</p>";
        }
        ?>
        <p id="valor_total" class="valores"><strong>Valor Total:</strong> R$
            <?php echo number_format($total, 2, ',', '.'); ?>
        </p>
        <hr>
        <h3>Status de Pagamento</h3>
        <p>
            <?php
            if ($formato_compra == 'crediario') {
                echo "<p><strong>Pagamento: <span>Oline.</span></p></strong></p>";
            } elseif ($formato_compra == 'online') {
                echo "<p><strong>Pagamento: <span>Oline.</span></p></strong></p>";
            } elseif ($formato_compra == 'retirar') {
                echo "<p><strong>Pagamento: <span>Na Retirada.</span></p></strong></p>";
            } else {
                echo "<p><strong>Pagamento: <span>Na Entrega.</span></p></strong></p>";
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
                echo "Retirar no loja.";
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
                echo $pedido['bairro_entrega'] != '' ? $cliente['cidade'] . '/' . $cliente['uf'] . ', CEP: ' . $cliente['cep'] : $cliente['cidade'] . '/' . $cliente['uf'] . ', CEP: ' . $cliente['cep'];
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
            style="display: <?php echo empty($pedido['comentario']) ? 'none' : 'block'; ?>;">
            <?php echo $pedido['comentario']; ?>
        </textarea>
        <hr>
        <p id="tempo-cancelar" class="cancel-timer" style="color: red; display: none;">
            <strong>Tempo para cancelar:</strong>
            <span id="countdown" data-end-time="<?php echo $end_time; ?>"></span>
        </p>
        <?php if ($pedido['status_cliente'] != 1): // Não mostrar se o pedido estiver confirmado ?>
            <p id="text-cancelar" class="cancel-timer" style="color: red; display: none;">
                <strong>O tempo de resposta expirou. Você pode cancelar sua compra!</strong>
            </p>
        <?php endif; ?>
        <div class="button-container">
            <button onclick="javascript:history.back()">Voltar para os Pedidos</button>
            <?php if ($pedido['status_cliente'] != 1): // Mostrar botão de cancelar apenas se o pedido não estiver confirmado ?>
                <button id="bt_recusar_pedido" style="display: none;" onclick="">Recusar pedido</button>
            <?php endif; ?>
            <button id="bt_confirmar_pedido">Confirmar Pedido</button>
        </div>
    </div>
</body>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const rows = document.querySelectorAll('table tbody tr');
        const totalElement = document.getElementById('valor_total');
        const valorVistaElement = document.getElementById('valor_vista');
        const freteElement = document.getElementById('frete');
        const saldoUsadoElement = document.getElementById('saldo_usado');
        const taxaCrediarioElement = document.getElementById('taxa_crediario');
        const confirmButton = document.getElementById('bt_confirmar_pedido');
        const recusarButton = document.getElementById('bt_recusar_pedido');
        const saindoEntregaButton = document.createElement('button');
        const statusPedidoElement = document.querySelector('p > span');

        saindoEntregaButton.id = 'bt_saindo_entrega';
        saindoEntregaButton.textContent = 'Saindo para Entrega';
        saindoEntregaButton.style.display = 'none';
        saindoEntregaButton.style.padding = '10px 20px';
        saindoEntregaButton.style.margin = '10px 5px';
        saindoEntregaButton.style.fontSize = '16px';
        saindoEntregaButton.style.color = '#fff';
        saindoEntregaButton.style.backgroundColor = '#ffc107'; // Cor amarela
        saindoEntregaButton.style.border = 'none';
        saindoEntregaButton.style.borderRadius = '5px';
        saindoEntregaButton.style.cursor = 'pointer';

        saindoEntregaButton.addEventListener('click', function () {
            alert('Pedido está saindo para entrega!');
        });

        document.querySelector('.button-container').appendChild(saindoEntregaButton);

        confirmButton.addEventListener('click', function () {
            const numPedido = <?php echo json_encode($num_pedido); ?>;

            fetch('atualizar_status_pedido.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ num_pedido: numPedido, status_cliente: 1 }),
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        confirmButton.style.display = 'none';
                        recusarButton.style.display = 'none';
                        saindoEntregaButton.style.display = 'block';
                        statusPedidoElement.textContent = 'Pedido Confirmado e já está em preparação.';
                        statusPedidoElement.style.color = 'green';

                        // Desabilitar os checkboxes e inputs de quantidade
                        rows.forEach(row => {
                            const checkbox = row.querySelector('input[type="checkbox"]');
                            const quantityInput = row.querySelector('input[type="number"]');
                            checkbox.disabled = true;
                            quantityInput.disabled = true;
                        });

                        alert('Pedido confirmado com sucesso!');
                    } else {
                        alert('Erro ao confirmar o pedido.');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao processar a solicitação.');
                });
        });

        /**
         * Atualiza os totais na tabela.
         */
        function updateTotals() {
            let total = 0;

            rows.forEach(row => {
                const checkbox = row.querySelector('input[type="checkbox"]');
                const quantityInput = row.querySelector('input[type="number"]');
                const unitPrice = parseFloat(quantityInput.getAttribute('data-unit-price'));
                const quantity = parseInt(quantityInput.value, 10);

                if (checkbox.checked && quantity > 0) {
                    total += quantity * unitPrice;
                }
            });

            const frete = parseFloat(freteElement.getAttribute('data-frete')) || 0;
            const saldoUsado = parseFloat(saldoUsadoElement.getAttribute('data-saldo')) || 0;
            const taxaCrediario = parseFloat(taxaCrediarioElement.getAttribute('data-taxa')) || 0;

            const valorFinal = total + frete + taxaCrediario - saldoUsado;

            valorVistaElement.textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;
            totalElement.textContent = `R$ ${valorFinal.toFixed(2).replace('.', ',')}`;
        }

        /**
         * Verifica se pelo menos um produto foi confirmado.
         */
        function checkConfirmation() {
            let atLeastOneChecked = false;

            rows.forEach(row => {
                const checkbox = row.querySelector('input[type="checkbox"]');
                if (checkbox.checked) {
                    atLeastOneChecked = true;
                }
            });

            // Exibe ou oculta o botão de confirmação
            confirmButton.style.display = atLeastOneChecked ? 'block' : 'none';
        }

        rows.forEach(row => {
            const checkbox = row.querySelector('input[type="checkbox"]');
            const quantityInput = row.querySelector('input[type="number"]');
            const totalCell = row.querySelector('td:last-child');

            // Carrega os valores iniciais
            const unitPrice = parseFloat(quantityInput.getAttribute('data-unit-price'));
            const quantity = parseInt(quantityInput.value, 10);
            totalCell.textContent = `R$ ${(quantity * unitPrice).toFixed(2).replace('.', ',')}`;

            checkbox.addEventListener('change', () => {
                if (!checkbox.checked) {
                    quantityInput.value = 0; // Reseta a quantidade se desmarcado
                    totalCell.textContent = 'R$ 0,00';
                }
                checkConfirmation();
                updateTotals();
            });

            quantityInput.addEventListener('input', () => {
                const maxQuantity = parseInt(quantityInput.getAttribute('data-max'), 10);
                let quantity = parseInt(quantityInput.value, 10);

                if (isNaN(quantity) || quantity <= 0) {
                    quantityInput.value = 0;
                    alert('A quantidade deve ser maior que 0.');
                    return;
                }

                if (quantity > maxQuantity) {
                    quantityInput.value = maxQuantity;
                    alert('A quantidade não pode ser maior que a solicitada.');
                    return;
                }

                if (checkbox.checked) {
                    const total = quantity * unitPrice;
                    totalCell.textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;
                } else {
                    totalCell.textContent = 'R$ 0,00';
                }
                checkConfirmation();
                updateTotals();
            });
        });

        // Atualiza os totais ao carregar a página
        updateTotals();

        // Seleciona o elemento com a classe 'countdown'.
        const countdownElement = document.querySelector('#countdown');
        if (countdownElement) {
            const endTime = new Date(countdownElement.getAttribute('data-end-time')).getTime(); // Obtém o timestamp de fim.
            startCountdown(countdownElement, endTime); // Inicia a contagem regressiva.
        }

        // Garante que os elementos estejam inicialmente ocultos, se existirem.
        const textCancelar = document.getElementById('text-cancelar');
        const btCancelarPedido = document.getElementById('bt_recusar_pedido');
        if (textCancelar) textCancelar.style.display = "none";
        if (btCancelarPedido) btCancelarPedido.style.display = "none";
    });

    /**
     * Inicia a contagem regressiva para o tempo de cancelamento.
     * @param {HTMLElement} element - O elemento onde a contagem será exibida.
     * @param {number} endTime - O timestamp do fim do tempo de cancelamento.
     */
    function startCountdown(element, endTime) {
        let interval;

        /**
         * Atualiza a contagem regressiva a cada segundo.
         */
        function updateCountdown() {
            const now = new Date().getTime(); // Obtém o timestamp atual.
            const distance = endTime - now; // Calcula o tempo restante.

            if (distance > 0) {
                // Calcula minutos e segundos restantes.
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                element.innerHTML = minutes + ":" + (seconds < 10 ? "0" : "") + seconds + " min";

                const tempoCancelar = document.getElementById('tempo-cancelar');
                const btCancelarPedido = document.getElementById('bt_recusar_pedido');
                if (tempoCancelar) tempoCancelar.style.display = "block"; // Mostra o "Tempo para cancelar".
                if (btCancelarPedido) btCancelarPedido.style.display = "block"; // Mostra o botão de cancelar.
            } else {
                // Quando o tempo expira, para o intervalo e ajusta a exibição.
                clearInterval(interval);

                const tempoCancelar = document.getElementById('tempo-cancelar');
                if (tempoCancelar) tempoCancelar.style.display = "none";

                // Calcula os timestamps para as condições.
                const pedidoTimestamp = new Date("<?php echo $pedido['data']; ?>").getTime();
                const quinzeMinutos = pedidoTimestamp + 15 * 60 * 1000; // 15 minutos após o pedido.

                // Verifica se já passaram 15 minutos.
                const now = new Date().getTime();
                const btCancelarPedido = document.getElementById('bt_recusar_pedido');
                const textCancelar = document.getElementById('text-cancelar');
                if (now >= quinzeMinutos) {
                    if (btCancelarPedido) btCancelarPedido.style.display = "block"; // Mostra o botão de cancelar.
                    if (textCancelar) textCancelar.style.display = "block"; // Mostra o texto de cancelamento.
                }
            }
        }

        updateCountdown(); // Atualiza a contagem imediatamente.
        interval = setInterval(updateCountdown, 1000); // Atualiza a cada segundo.
    }

</script>

</html>
<?php

?>