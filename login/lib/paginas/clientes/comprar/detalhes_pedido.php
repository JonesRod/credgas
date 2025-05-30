<?php
session_start();
include('../../../conexao.php'); // Conexão com o banco

// Verifica se o usuário está logado
if (!isset($_SESSION['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['erro' => 'Usuário não autenticado.']);
    exit;
}

// Adicione este bloco PHP no início do arquivo, antes de qualquer saída HTML:
if (isset($_GET['ajax']) && $_GET['ajax'] == 1 && isset($_GET['num_pedido'])) {
    header('Content-Type: application/json');
    $id_cliente = $_SESSION['id'];
    $num_pedido = (int) $_GET['num_pedido'];
    $query = "SELECT status_cliente, status_parceiro FROM pedidos WHERE id_cliente = ? AND num_pedido = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ii", $id_cliente, $num_pedido);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $pedido = $result->fetch_assoc();
        $status_final = max($pedido['status_cliente'], $pedido['status_parceiro']);
        echo json_encode(['status_final' => $status_final]);
    } else {
        echo json_encode(['status_final' => null]);
    }
    exit;
}

// Verifica se o ID do pedido foi enviado
if (!isset($_POST['num_pedido'])) {
    header('Content-Type: application/json');
    echo json_encode(['erro' => 'Número do pedido não fornecido.']);
    exit;
}

// Obtém o ID do cliente logado e o número do pedido
$id_cliente = $_SESSION['id'];
$num_pedido = $_POST['num_pedido'];

// Consulta para buscar os dados do pedido
$query = "SELECT * FROM pedidos WHERE id_cliente = ? AND num_pedido = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("ii", $id_cliente, $num_pedido);
$stmt->execute();
$result = $stmt->get_result();
$pedido = $result->fetch_assoc();


$status_parceiro = $pedido['status_parceiro'];
$valor_a_vista = $pedido['valor_produtos'];
$taxa_crediario = $pedido['taxa_crediario'];
$frete = $pedido['valor_frete'];
$saldo_usado = $pedido['saldo_usado'];
$total = $valor_a_vista + $frete + $taxa_crediario - $saldo_usado;
$tipo_entrega = $pedido['tipo_entrega'];
$formato_compra = $pedido['formato_compra'];

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

// Determina o status final com base no maior status entre cliente e parceiro
$status_final = max($pedido['status_cliente'], $pedido['status_parceiro']);

// Determina quem recusou ou cancelou o pedido e a justificativa
$quem_responsavel = '';
$justificativa = '';
if ($status_final == 3 || $status_final == 4) { // Status 3 = Recusado, Status 4 = Cancelado
    if ($status_final == 3) {
        $quem_responsavel = 'Parceiro';
        $justificativa = isset($pedido['motivo_recusa']) ? $pedido['motivo_recusa'] : 'Motivo não informado.';
    } elseif ($status_final == 4) {
        if ($pedido['status_cliente'] > $pedido['status_parceiro']) {
            $quem_responsavel = 'Cliente';
        } else {
            $quem_responsavel = 'Parceiro';
        }
        $justificativa = isset($pedido['motivo_cancelamento']) ? $pedido['motivo_cancelamento'] : 'Motivo não informado.';
    }
}

// Calcula o tempo que durou o processo, se aplicável
$tempo_duracao = '';
if (in_array($status_final, [3, 4, 7])) { // Status 3 = Recusado, 4 = Cancelado, 7 = Entregue
    $data_inicio = new DateTime($pedido['data']);
    $data_fim = new DateTime($pedido['data_finalizacao']);
    $intervalo = $data_inicio->diff($data_fim);

    $horas = ($intervalo->d * 24) + $intervalo->h; // Converte dias em horas e soma com as horas
    $minutos = $intervalo->i;

    $tempo_duracao = $horas . ' hora(s) e ' . $minutos . ' minuto(s)';
}

// Calcula o tempo de duração do pedido até expirar
$tempo_duracao_expiracao = '';
if ($status_final == 0) { // Status 0 = Aguardando Confirmação
    $data_inicio = new DateTime($pedido['data']);
    $data_expiracao = clone $data_inicio;
    $data_expiracao->modify('+15 minutes');
    $intervalo = $data_inicio->diff($data_expiracao);

    $horas = ($intervalo->d * 24) + $intervalo->h; // Converte dias em horas e soma com as horas
    $minutos = $intervalo->i;

    $tempo_duracao_expiracao = $horas . ' hora(s) e ' . $minutos . ' minuto(s)';
}

// Defina o valor do preenchimento com base no status_final
$percent = 0;

if ($status_final >= 7) {
    $percent = 100;
} elseif ($status_final >= 6) {
    $percent = 75;
} elseif ($status_final >= 5) {
    $percent = 50;
} elseif ($status_final >= 1) {
    $percent = 25;
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" width="device-width, initial-scale=1.0">
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

        #bt_cancelar_pedido {
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

        #bt_cancelar_pedido:hover {
            background-color: #c82333;
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

        .progress-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0;
            position: relative;
            padding: 40px 30px 20px 30px;
            border: 2px solid #ccc;
            border-radius: 8px;
            background-color: #f9f9f9;
        }

        .progress-line {
            position: absolute;
            top: 63%;
            left: 33px;
            right: 33px;
            height: 4px;
            background-color: #ddd;
            z-index: 1;
            transform: translateY(-50%);
            <?php if ($status_final == 3 || $status_final == 4): ?>
                background: linear-gradient(to right, #dc3545 100%, #dc3545 100%);
            <?php else: ?>
                background: linear-gradient(to right, #28a745 <?= $percent ?>%, #ddd <?= $percent ?>%);
            <?php endif; ?>
        }

        .progress-step {
            position: relative;
            z-index: 2;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
            color: #fff;
            background-color:
                <?php echo ($status_final == 3 || $status_final == 4) ? '#dc3545' : '#ddd'; ?>
            ;
        }

        .progress-step.active {
            background-color:
                <?php echo ($status_final == 3 || $status_final == 4) ? '#dc3545' : '#28a745'; ?>
            ;
        }

        .progress-label {
            position: absolute;
            top: -25px;
            font-size: 12px;
            color:
                <?php echo ($status_final == 3 || $status_final == 4) ? '#dc3545' : '#333'; ?>
            ;
            text-align: center;
        }

        .progress-label.active-label {
            font-weight: bold;
            color:
                <?php echo ($status_final == 3 || $status_final == 4) ? '#dc3545' : '#28a745'; ?>
            ;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Detalhes do Pedido</h1>
        <hr>
        <img src="<?php echo '../../parceiros/arquivos/' . $logo; ?>" alt="Logo" style="width: 100px; height: auto;">
        <h2><?php echo $nomeFantasia; ?></h2>
        <p class="end-parceiro">
            <?php echo $loja['endereco'] != '' ? $loja['endereco'] : 'Endereço não disponível'; ?>,
            <?php echo $loja['numero'] != '' ? $loja['numero'] : 'Número não disponível'; ?>,
            <?php echo $loja['bairro'] != '' ? $loja['bairro'] : 'Bairro não disponível'; ?>.
        </p>
        <p class="end-parceiro">Contato:
            <?php echo $loja['telefoneComercial'] != '' ? $loja['telefoneComercial'] : 'Contato não disponível'; ?>.
        </p>
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
                style="color: <?php echo $status_final === 0 ? '#ff5722' : ($status_final === 1 ? 'green' : ($status_final === 3 || $status_final === 4 ? 'red' : ($status_final === 5 ? 'orange' : ($status_final === 6 ? 'blue' : 'gray')))); ?>">
                <?php
                if ($status_final == 0) {
                    echo "Aguardando Confirmação.";
                } elseif ($status_final == 1) {
                    echo "Pedido Confirmado.";
                } elseif ($status_final == 3) {
                    echo "Pedido Recusado.";
                } elseif ($status_final == 4) {
                    echo "Pedido Cancelado!";
                } elseif ($status_final == 5) {
                    if ($tipo_entrega == 'entregar') {
                        echo "Pedido pronto para entregar.";
                    } else {
                        echo "Pedido pronto.";
                    }
                } elseif ($status_final == 6) {
                    if ($tipo_entrega == 'entregar') {
                        echo "Saiu para entregar.";
                    } else {
                        echo "Pedido pronto para retirar.";
                    }
                } elseif ($status_final == 7) {
                    echo "Pedido Entregue.";
                } else {
                    echo "Status Desconhecido.";
                }
                ?>
            </span>
        </p>
        <?php if (!empty($tempo_duracao_expiracao)): // Exibe o tempo de duração até expirar se aplicável ?>
            <p data-tempo-duracao><strong>Tempo de Duração até Expirar:</strong> <?php echo $tempo_duracao_expiracao; ?></p>
        <?php endif; ?>
        <?php if (!empty($tempo_duracao)): // Exibe o tempo de duração se aplicável ?>
            <p data-tempo-duracao><strong>Tempo de Duração:</strong> <?php echo $tempo_duracao; ?></p>
        <?php endif; ?>
        <?php if ($status_final == 3 || $status_final == 4): // Exibe quem recusou/cancelou e a justificativa ?>
            <p data-quem-responsavel>
                <strong><?php echo $status_final == 3 ? 'Recusado pelo(a):' : 'Cancelado pelo(a):'; ?></strong>
                <?php echo $quem_responsavel; ?>
            </p>
            <p data-justificativa><strong>Justificativa:</strong> <?php echo htmlspecialchars($justificativa); ?></p>
        <?php endif; ?>
        <hr>
        <h3 style="margin-bottom: 20px;">Andamento do Pedido</h3>
        <div class="progress-container">
            <div class="progress-line"></div>
            <div class="progress-step <?php echo $status_final >= 0 ? 'active' : ''; ?>">
                <span class="progress-label">Pendente</span>
            </div>
            <div class="progress-step <?php echo $status_final >= 1 ? 'active' : ''; ?>">
                <span class="progress-label">Confirmado</span>
            </div>
            <div class="progress-step <?php echo $status_final >= 5 ? 'active' : ''; ?>">
                <span class="progress-label">Pronto</span>
            </div>
            <div class="progress-step <?php echo $status_final >= 6 ? 'active' : ''; ?>">
                <span class="progress-label"><?php echo $tipo_entrega === 'entregar' ? 'Entregar' : 'Retirar'; ?></span>
            </div>
            <div class="progress-step <?php echo $status_final >= 7 ? 'active' : ''; ?>">
                <span class="progress-label">Entregou</span>
            </div>
        </div>
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
        <p id="valor_vista" class="valores"><strong>Valor Total dos Produtos:</strong> R$
            <?php echo number_format($valor_a_vista, 2, ',', '.'); ?>
        </p>
        <?php
        if ($frete != 0 && $tipo_entrega == 'entregar') {
            echo "<p id='frete' class='valores'><strong>Frete:</strong> R$ " . number_format($frete, 2, ',', '.') . "</p>";
        } else {
            echo "<p id='frete' class='valores' style='color: green;'><strong>Frete Grátis</strong></p>";
        }
        ?>
        <?php
        if ($saldo_usado != 0) {
            echo "<p id='saldo_usado' class='valores'><strong>Saldo Utilzado:</strong> - R$ " . number_format($saldo_usado, 2, ',', '.') . "</p>";
        } else {
            echo "<p id='saldo_usado' class='valores' style='display: none;'><strong></strong>saldo_usado: 0,00</p>";
        }
        ?>
        <?php
        if ($taxa_crediario != 0 && $formato_compra == 'crediario') {
            echo "<p id='taxa_crediario' class='valores'><strong>Taxa:</strong> R$ " . number_format($taxa_crediario, 2, ',', '.') . "</p>";
        } else {
            echo "<p id='taxa_crediario' class='valores' style='display: none;'><strong></strong>Taxa: Grátis</p>";
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
                echo "<p><strong>Pagamento: <span>Crediario.</span></p></strong></p>";
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
                echo $pedido['bairro_entrega'] != '' ? $cliente['cidade'] . '/' . $cliente['uf'] . ', CEP: ' . $cliente['cep'] : $cliente['cidade'] . '/' . $cliente['uf'] . ', CEP: ' . $cliente['cep'];
            } elseif ($pedido['tipo_entrega'] == 'buscar') {
                echo $loja['cidade'] . '/' . $loja['estado'] . ', CEP: ' . $loja['cep'];
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
        <textarea name="comentario" id="comentario"><?php echo $pedido['comentario']; ?></textarea>
        <hr>
        <p id="tempo-cancelar" class="cancel-timer" style="color: red; display: none;">
            <strong>Tempo para cancelar:</strong>
            <span id="countdown" data-end-time="<?php echo $end_time; ?>"></span>
        </p>
        <?php if ($pedido['status_cliente'] != 1): // Não mostrar se o pedido estiver confirmado ?>
            <p id="text-cancelar" class="cancel-timer" style="color: red; display: none;">
                <!-- Mensagem oculta -->
                <!-- <strong>O tempo de resposta expirou. Você pode cancelar sua compra!</strong> -->
            </p>
        <?php endif; ?>
        <div id="atraso-entrega" style="display: none; color: red; text-align: center; margin-top: 20px;">
            <strong>A entrega está atrasada. Você pode cancelar o pedido, se desejar.</strong>
        </div>
        <div class="button-container">
            <button onclick="javascript:history.back()">Voltar para os Pedidos</button>
            <?php if ($status_final !== 3 && $status_final !== 4): // Mostrar botão de cancelar apenas se o pedido não estiver recusado ou cancelado ?>
                <button id="bt_cancelar_pedido" style="display: none;" onclick="">Cancelar pedido</button>
            <?php endif; ?>
        </div>
    </div>
</body>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const countdownElement = document.querySelector('#countdown');
        if (countdownElement) {
            const endTime = new Date(countdownElement.getAttribute('data-end-time')).getTime();
            startCountdown(countdownElement, endTime);
        }
        // Exibe o botão de cancelar pedido se o status não for 3 (recusado), 4 (cancelado) ou 7 (entregue)
        const atrasoEntrega = document.getElementById('atraso-entrega');
        const btCancelarPedido = document.getElementById('bt_cancelar_pedido');
        //if (btCancelarPedido) btCancelarPedido.style.display = "block";

        // Esconde o botão de cancelar pedido se o status for 3 (recusado), 4 (cancelado) ou 7 (entregue)
        const statusFinal = <?php echo $status_final; ?>;
        if (statusFinal === 3 || statusFinal === 4 || statusFinal === 7) {
            //# Esconde o botão de cancelar pedido
            if (btCancelarPedido) btCancelarPedido.style.display = "none";
            // Esconde a mensagem de tempo para cancelar
            if (atrasoEntrega) atrasoEntrega.style.display = "none";
            console.log("ocultarCancelarPedido");
        } else {
            // Exibe o botão de cancelar pedido se o status não for 3 (recusado), 4 (cancelado) ou 7 (entregue)
            if (btCancelarPedido) btCancelarPedido.style.display = "block";
            // Exibe a mensagem de tempo para cancelar
            if (atrasoEntrega) atrasoEntrega.style.display = "block";
        }
    });

    /**
     * Inicia a contagem regressiva para o tempo de cancelamento.
     * @param {HTMLElement} element - O elemento onde a contagem será exibida.
     * @param {number} endTime - O timestamp do fim do tempo de cancelamento.
     */
    function startCountdown(element, endTime) {
        let interval;

        function updateCountdown() {
            const now = new Date().getTime();
            const distance = endTime - now;

            if (distance > 0) {
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                element.innerHTML = minutes + ":" + (seconds < 10 ? "0" : "") + seconds + " min";

                const tempoCancelar = document.getElementById('tempo-cancelar');
                const btCancelarPedido = document.getElementById('bt_cancelar_pedido');
                if (tempoCancelar) tempoCancelar.style.display = "block";
                if (btCancelarPedido) btCancelarPedido.style.display = "block";
            } else {
                clearInterval(interval);

                const tempoCancelar = document.getElementById('tempo-cancelar');
                if (tempoCancelar) tempoCancelar.style.display = "none";

                const textCancelar = document.getElementById('text-cancelar');
                if (textCancelar) textCancelar.style.display = "block";

                // Exibe mensagem de atraso na entrega apenas se o pedido não estiver cancelado ou recusado
                const statusFinal = <?php echo $status_final; ?>;
                if (statusFinal !== 3 && statusFinal !== 4 && statusFinal !== 7) {
                    const atrasoEntrega = document.getElementById('atraso-entrega');
                    if (atrasoEntrega) atrasoEntrega.style.display = "block";

                    const btCancelarPedido = document.getElementById('bt_cancelar_pedido');
                    if (btCancelarPedido) btCancelarPedido.style.display = "block"; // Permite cancelar após atraso
                }
            }
        }

        updateCountdown();
        interval = setInterval(updateCountdown, 1000);
    }

    // Verificação automática do status do pedido a cada 3 segundos
    let verificarStatusInterval;
    let ultimoStatusFinal = <?php echo $status_final; ?>;

    function verificarStatusPedido() {
        fetch('detalhes_pedido.php?ajax=1&num_pedido=<?php echo $num_pedido; ?>')
            .then(response => response.json())
            .then(data => {
                if (data && data.status_final !== undefined) {
                    // Se o status mudou, recarrega a página
                    if (data.status_final !== ultimoStatusFinal) {
                        clearInterval(verificarStatusInterval);
                        
                        location.reload();
                    }
                }
            })
            .catch(() => { });
    }

    document.addEventListener('DOMContentLoaded', function () {
        verificarStatusPedido();
        verificarStatusInterval = setInterval(verificarStatusPedido, 1500);
    });
</script>

</html>
<?php

?>