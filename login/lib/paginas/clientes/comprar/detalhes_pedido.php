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
    $status_parceiro = $pedido['status_parceiro'];
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

// Fetch partner details from the database
$id_parceiro = $pedido['id_parceiro'];

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

// Consulta para buscar os dados do cliente
$query_cliente = "SELECT * FROM meus_clientes WHERE id = ?";
$stmt_cliente = $mysqli->prepare($query_cliente);
$stmt_cliente->bind_param("i", $id_cliente);
$stmt_cliente->execute();
$result_cliente = $stmt_cliente->get_result();

if ($result_cliente->num_rows > 0) {
    $cliente = $result_cliente->fetch_assoc();
} else {
    echo "Cliente não encontrado.";
    exit;
}

$stmt_cliente->close();
function formatDateTimeJS($dateString) {
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

        header, h1, h2, h3 {
            text-align: center;
            margin: 10px 0;
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

        table th, table td {
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

        .cancel-timer {
            text-align: center;
            margin: 20px 0;
        }

        textarea {
            width: 100%;
            height: 100px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: none;
        }

        #total {
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
        }

        @media (max-width: 600px) {
            .container {
                padding: 10px;
            }

            table th, table td {
                font-size: 14px;
                padding: 6px;
            }

            button {
                font-size: 14px;
                padding: 8px 15px;
            }

            img {
                width: 80px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Detalhes do Pedido</h1>
        <hr>
        <img src="<?php echo '../../parceiros/arquivos/'.$logo; ?>" alt="Logo" style="width: 100px; height: auto;">
        <h2><?php echo $nomeFantasia; ?></h2>
        <p>
            <?php echo $loja['endereco'] != '' ? $loja['endereco'] : 'Endereço não disponível'; ?>, 
            <?php echo $loja['numero'] != '' ? $loja['numero'] : 'Número não disponível'; ?>,
            <?php echo $loja['bairro'] != '' ? $loja['bairro'] : 'Bairro não disponível'; ?>.
        </p>
        <p>Contato: <?php echo $loja['telefoneComercial'] != '' ? $loja['telefoneComercial'] : 'Contato não disponível'; ?>.</p>
        <hr>
        <h2>Pedido #<?php echo $num_pedido; ?></h2>
        <p><strong>Data do pedido:</strong> <?php echo htmlspecialchars(formatDateTimeJS($pedido['data'])); ?></p>
        <p><strong>Status do Pedido:</strong> 
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
        <hr>
        <h3>Produtos</h3>
        <table>
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
                $produtos = explode('|', $pedido['produtos']);
                foreach ($produtos as $produto) {
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
        <p id="total"><strong>Total:</strong> R$ <?php echo number_format($pedido['valor'], 2, ',', '.'); ?></p>
        <hr>
        <h3>Status de Pagamento</h3>
        <p>
            <?php 
                if ($formato_compra == 'crediario') {
                    echo "Pagamento realizado Online.";
                } elseif ($formato_compra == 'online') {
                    echo "Pagamento realizado Online.";
                } elseif ($formato_compra == 'retirar') {
                    echo "Receber na hora da entrega.";
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
        <p>AV/RUA: 
            <?php 
                if ($pedido['tipo_entrega'] == 'entregar') {
                    echo $pedido['endereco_entrega'] != '' ? $pedido['endereco_entrega'] : $cliente['endereco'];
                } elseif ($pedido['tipo_entrega'] == 'buscar') {
                    echo $loja['endereco'];
                }
            ?>
        </p>
        <p>Nº: 
            <?php 
                if ($pedido['tipo_entrega'] == 'entregar') {
                    echo $pedido['num_entrega'] != '' ? $pedido['num_entrega'] : $cliente['numero'];
                } elseif ($pedido['tipo_entrega'] == 'buscar') {
                    echo $loja['numero'];
                }
            ?>
        </p>
        <p>BAIRRO: 
            <?php 
                if ($pedido['tipo_entrega'] == 'entregar') {
                    echo $pedido['bairro_entrega'] != '' ? $pedido['bairro_entrega'] : $cliente['bairro'];
                } elseif ($pedido['tipo_entrega'] == 'buscar') {
                    echo $loja['bairro'];
                }
            ?>
        </p>
        <p>CIDADE/UF: 
            <?php 
                if ($pedido['tipo_entrega'] == 'entregar') {
                    echo $pedido['bairro_entrega'] != '' ? $cliente['cidade'].'/'.$cliente['uf'].', CEP: '. $cliente['cep'] : $cliente['cidade'].'/'.$cliente['uf'] . ', CEP: ' . $cliente['cep'];
                } elseif ($pedido['tipo_entrega'] == 'buscar') {
                    echo $loja['cidade'].'/'.$loja['estado'].', CEP: '. $loja['cep'];
                }
            ?>
        </p>
        <p>CONTATO: 
            <?php 
                if ($pedido['tipo_entrega'] == 'entregar') {
                    echo $pedido['contato_recebedor'] != '' ? $pedido['contato_recebedor'] : $cliente['celular1'];
                } elseif ($pedido['tipo_entrega'] == 'buscar') {
                    echo $loja['telefoneComercial'];
                }
            ?>
        </p>
        <p>COMENTÁRIO: </p>
        <textarea name="" id=""><?php echo $pedido['comentario']; ?></textarea>
        <hr>
        <p id="tempo-cancelar" class="cancel-timer" style="color: red;">
            <strong>Tempo para cancelar:</strong> 
            <span class="countdown" data-end-time="<?php echo $end_time; ?>"></span>
        </p>
        <p id="text-cancelar" class="cancel-timer" style="color: red; display: none;">
            <strong>O tempo de resposta expirou. Você pode cancelar sua compra!</strong>
        </p>
        <div>
            <button onclick="javascript:history.back()">Voltar para os Pedidos</button>
            <button id="bt_cancelar_pedido" style="display: block;" onclick="">Cancelar pedido</button>
        </div>
    </div>
</body>
    <script>
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
                } else {
                    // Quando o tempo expira, para o intervalo e ajusta a exibição.
                    clearInterval(interval);
                    document.getElementById('tempo-cancelar').style.display = "none";

                    // Trata o valor de tempoEntrega como milissegundos.
                    const tempoEntrega = parseInt("<?php echo $tempo_entrega; ?>", 10);
                    if (isNaN(tempoEntrega)) {
                        console.error("Erro: tempoEntrega não é um número válido. Valor recebido:", "<?php echo $tempo_entrega; ?>");
                        return;
                    }

                    // Calcula o timestamp correto baseado no tempo de entrega.
                    const tempoEntregaTimestamp = new Date("<?php echo $pedido['data']; ?>").getTime() + tempoEntrega;
                    const tempoEntregaMais15Min = tempoEntregaTimestamp + 15 * 60 * 1000; // Soma 15 minutos ao tempo de entrega.
                    const statusParceiro = <?php echo $status_parceiro; ?>; // Status do parceiro.

                    // Calcula o tempo que já passou desde o término.
                    const timePassed = now - tempoEntregaMais15Min;
                    //console.log(`Tempo que já passou desde o término: ${Math.floor(timePassed / 1000)} segundos`);

                    // Verifica se o tempo de entrega + 15 minutos passou e o status do parceiro é 0.
                    if (now > tempoEntregaMais15Min && statusParceiro === 0) {
                        document.getElementById('bt_cancelar_pedido').style.display = "block"; // Mostra o botão de cancelar.
                        document.getElementById('text-cancelar').style.display = "block"; // Mostra o texto de cancelamento.
                    } else {
                        document.getElementById('bt_cancelar_pedido').style.display = "none"; // Oculta o botão de cancelar.
                        document.getElementById('text-cancelar').style.display = "none"; // Oculta o texto de cancelamento.
                    }
                }
            }

            updateCountdown(); // Atualiza a contagem imediatamente.
            interval = setInterval(updateCountdown, 1000); // Atualiza a cada segundo.
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Seleciona todos os elementos com a classe 'countdown'.
            const countdownElements = document.querySelectorAll('.countdown');
            countdownElements.forEach(function (element) {
                const endTime = new Date(element.getAttribute('data-end-time')).getTime(); // Obtém o timestamp de fim.
                startCountdown(element, endTime); // Inicia a contagem regressiva.
            });

            // Garante que os elementos estejam inicialmente ocultos.
            document.getElementById('text-cancelar').style.display = "none";
            document.getElementById('bt_cancelar_pedido').style.display = "none";
        });
    </script>
</html>
<?php

?>