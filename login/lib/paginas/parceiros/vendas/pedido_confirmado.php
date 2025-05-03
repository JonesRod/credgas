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
    header("Location: ../../../../index.php");
    exit;
}

$id_parceiro = $_SESSION['id'];
$num_pedido = $_POST['num_pedido'];

// Consulta para buscar os dados do pedido
$query = "SELECT * FROM pedidos WHERE id_parceiro = ? AND num_pedido = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("ii", $id_parceiro, $num_pedido);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Pedido não encontrado.";
    exit;
}

$pedido = $result->fetch_assoc();
$frete = $pedido['valor_frete'];
$saldo_usado = $pedido['saldo_usado'];
$taxa_crediario = $pedido['taxa_crediario'];
$tipo_entrega = $pedido['tipo_entrega'];
$formato_compra = $pedido['formato_compra'];
$produtos = explode('|', $pedido['produtos']);
$produtos_confirmados = !empty($pedido['produtos_confirmados']) ? explode('|', $pedido['produtos_confirmados']) : [];

// Recalcula o total considerando apenas os produtos confirmados e suas quantidades
$total_confirmado = 0;
$produtos_confirmados_map = [];
foreach ($produtos_confirmados as $produto_confirmado) {
    list($nome, $quantidade, $valor_unitario, $valor_total) = explode('/', $produto_confirmado);
    $produtos_confirmados_map[$nome] = [
        'quantidade' => (int) $quantidade,
        'valor_unitario' => (float) $valor_unitario,
        'valor_total' => (float) $valor_total
    ];
    $total_confirmado += (float) $valor_total;
}

// Atualiza o total com base nos produtos confirmados, saldo usado e frete
$total = $total_confirmado;
if ($frete > 0 && $tipo_entrega == 'entregar')
    $total += $frete;
if ($saldo_usado > 0)
    $total -= $saldo_usado;

// Consulta para buscar os dados do cliente
$query_cliente = "SELECT * FROM meus_clientes WHERE id = ?";
$stmt_cliente = $mysqli->prepare($query_cliente);
$stmt_cliente->bind_param("i", $pedido['id_cliente']);
$stmt_cliente->execute();
$result_cliente = $stmt_cliente->get_result();

if ($result_cliente->num_rows === 0) {
    echo "Cliente não encontrado.";
    exit;
}

$cliente = $result_cliente->fetch_assoc();
$primeiro_nome = explode(' ', $cliente['nome_completo'])[0];

// Calculate end time for countdown
$pedido_time = new DateTime($pedido['data']);
$pedido_time->modify('+15 minutes');
$end_time = $pedido_time->format('Y-m-d H:i:s');

// Consulta para buscar os dados do parceiro
$query_parceiro = "SELECT * FROM meus_parceiros WHERE id = ?";
$stmt_parceiro = $mysqli->prepare($query_parceiro);
$stmt_parceiro->bind_param("i", $id_parceiro);
$stmt_parceiro->execute();
$result_parceiro = $stmt_parceiro->get_result();

if ($result_parceiro->num_rows === 0) {
    echo "Parceiro não encontrado.";
    exit;
}

$loja = $result_parceiro->fetch_assoc();
$tempo_entrega = round($loja['estimativa_entrega'] / 60000); // Converte milissegundos para minutos
$stmt_parceiro->close();

// Calculate remaining time for delivery
$pedido_time = new DateTime($pedido['data']);
$pedido_time->modify("+{$tempo_entrega} minutes");
$current_time = new DateTime();
$interval = $current_time->diff($pedido_time);

if ($current_time > $pedido_time) {
    $tempo_entrega_restante = "Tempo expirado";
} else {
    $tempo_entrega_restante = $interval->format('%Hh %Im %Ss');
}

function formatDateTimeJS($dateString)
{
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
            background-color: #f9f9f9;
            color: #333;
            margin: 0;
            padding: 0;
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
            color: #444;
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

        .button-container {
            margin-top: 20px;
            text-align: center;
        }

        .button-container button {
            margin: 5px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            background-color: #007bff;
            color: #fff;
        }

        .button-container button:hover {
            background-color: #0056b3;
        }

        .button-container button.cancel {
            background-color: #dc3545;
        }

        .button-container button.cancel:hover {
            background-color: #c82333;
        }

        .button-container button.confirm {
            background-color: #28a745;
        }

        .button-container button.confirm:hover {
            background-color: #218838;
        }

        #cancelPopup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        #cancelPopup div {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            width: 300px;
        }

        #cancelPopup button {
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
        }

        #cancelPopup button:first-of-type {
            background-color: #6c757d;
            /* Cinza para o botão "Voltar" */
            color: #fff;
        }

        #cancelPopup button:first-of-type:hover {
            background-color: #5a6268;
            /* Cinza mais escuro ao passar o mouse */
        }

        #cancelPopup button:last-of-type {
            background-color: #dc3545;
            /* Vermelho para o botão "Confirmar Cancelamento" */
            color: #fff;
        }

        #cancelPopup button:last-of-type:hover {
            background-color: #c82333;
            /* Vermelho mais escuro ao passar o mouse */
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
            .valores {
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

            .button-container button {
                font-size: 12px;
                padding: 6px 10px;
            }

            .container {
                padding: 10px;
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
            .valores {
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

            .button-container button {
                font-size: 10px;
                padding: 4px 8px;
            }

            .container {
                padding: 8px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Detalhes do Pedido</h1>
        <hr>
        <h3>Cliente: <span><?php echo $primeiro_nome; ?></span></h3>
        <h2>Pedido #<?php echo $num_pedido; ?></h2>
        <p><strong>Data do pedido:</strong> <?php echo formatDateTimeJS($pedido['data']); ?></p>
        <p><strong>Status do Pedido:</strong>
            <span style="color: <?php echo $pedido['status_parceiro'] == 1 ? 'green' : 'red'; ?>;">
                <?php echo $pedido['status_parceiro'] == 1 ? 'Confirmado.' : 'Pendente'; ?>
            </span>
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
                <?php foreach ($produtos as $produto): ?>
                    <?php
                    list($nome, $quantidade, $valor_unitario, $valor_total) = explode('/', $produto);
                    $is_confirmed = isset($produtos_confirmados_map[$nome]);
                    $confirmed_quantity = $is_confirmed ? $produtos_confirmados_map[$nome]['quantidade'] : 0;
                    $row_color = $is_confirmed ? 'green' : 'red';
                    $text_decoration = $is_confirmed ? 'none' : 'line-through';
                    ?>
                    <tr style="color: <?php echo $row_color; ?>; text-decoration: <?php echo $text_decoration; ?>;">
                        <td><?php echo htmlspecialchars($nome); ?></td>
                        <td><?php echo $is_confirmed ? $confirmed_quantity : htmlspecialchars($quantidade); ?></td>
                        <td>R$ <?php echo number_format($valor_unitario, 2, ',', '.'); ?></td>
                        <td>R$
                            <?php echo $is_confirmed ? number_format($produtos_confirmados_map[$nome]['valor_total'], 2, ',', '.') : number_format($valor_total, 2, ',', '.'); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($frete > 0): ?>
            <p class="valores">Frete: R$ <?php echo number_format($frete, 2, ',', '.'); ?></p>
        <?php else: ?>
            <p class="valores" style="color: green;">Frete Grátis</p>
        <?php endif; ?>

        <?php if ($saldo_usado > 0): ?>
            <p class="valores">Saldo Usado: -R$ <?php echo number_format($saldo_usado, 2, ',', '.'); ?></p>
        <?php endif; ?>

        <p class="valores">Valor Total: R$ <?php echo number_format($total, 2, ',', '.'); ?></p>
        <hr>
        <h3>Forma de Entrega</h3>
        <p><strong>Tipo de Entrega:</strong>
            <?php echo $tipo_entrega === 'entregar' ? 'Entregar em casa' : 'Retirar na loja'; ?></p>
        <p><strong>Endereço:</strong>
            <?php echo $tipo_entrega === 'entregar' ? $cliente['endereco'] . ', ' . $cliente['numero'] . ' - ' . $cliente['bairro'] . ', ' . $cliente['cidade'] . '/' . $cliente['uf'] . ', CEP: ' . $cliente['cep'] : 'Loja'; ?>
        </p>
        <p><strong>Contato:</strong> <?php echo $cliente['celular1']; ?></p>
        <?php if (!empty($pedido['comentario'])): ?>
            <p><strong>Comentário:</strong> <?php echo htmlspecialchars($pedido['comentario']); ?></p>
        <?php endif; ?>
        <p><strong>Tempo de Entrega:</strong> <span style="color: red;"><?php echo $tempo_entrega_restante; ?></span>
        </p>
        <hr>
        <div class="button-container">
            <button onclick="javascript:history.back()">Voltar</button>
            <button class="cancel" onclick="cancelarPedido()">Cancelar Pedido</button>
            <button class="confirm"
                onclick="<?php echo $tipo_entrega === 'entregar' ? 'sairParaEntregar()' : 'prontoParaRetirada()'; ?>">
                <?php echo $tipo_entrega === 'entregar' ? 'Sair para Entregar' : 'Pronto para Retirada'; ?>
            </button>
        </div>
    </div>

    <div id="cancelPopup">
        <div>
            <h3>Confirmar Cancelamento</h3>
            <p>Tem certeza de que deseja cancelar este pedido?</p>
            <textarea id="motivoCancelamento" placeholder="Informe o motivo do cancelamento"
                style="width: 100%; height: 80px; margin-bottom: 10px;" required></textarea>
            <button onclick="fecharPopup()">Voltar</button>
            <button onclick="confirmarCancelamento()">Confirmar Cancelamento</button>
        </div>
    </div>
</body>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tempoEntregaElement = document.getElementById('tempo-entrega');

        // Verifica se o elemento existe antes de acessar suas propriedades
        if (tempoEntregaElement) {
            let tempoRestante = parseInt(tempoEntregaElement.getAttribute('data-tempo-entrega'), 10);

            function atualizarTempoEntrega() {
                if (tempoRestante <= 0) {
                    tempoEntregaElement.innerHTML = "Tempo expirado";
                    return;
                }

                const minutos = Math.floor(tempoRestante / 60);
                const segundos = tempoRestante % 60;
                tempoEntregaElement.innerHTML = `${minutos}m ${segundos}s`;

                tempoRestante--;
            }

            atualizarTempoEntrega();
            setInterval(atualizarTempoEntrega, 1000);
        }
    });

    function confirmarPedido() {
        if (confirm("Deseja confirmar este pedido?")) {
            alert("Pedido confirmado com sucesso!");
        }
    }

    function cancelarPedido() {
        const popup = document.getElementById('cancelPopup');
        popup.style.display = 'flex';
    }

    function fecharPopup() {
        const popup = document.getElementById('cancelPopup');
        popup.style.display = 'none';
    }

    function confirmarCancelamento() {
        const motivo = document.getElementById('motivoCancelamento').value.trim();
        if (!motivo) {
            alert('Por favor, informe o motivo do cancelamento.');
            return;
        }

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'cancelar_pedido.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            if (xhr.status === 200) {
                alert('Pedido cancelado com sucesso!');
                window.location.href = 'pedido_recusado.php'; // Redireciona para pedido_recusado.php
            } else {
                alert('Erro ao cancelar o pedido.');
            }
        };
        xhr.send('num_pedido=<?php echo $num_pedido; ?>&motivo_cancelamento=' + encodeURIComponent(motivo));
    }

    function sairParaEntregar() {
        if (confirm("Confirma que o pedido está saindo para entrega?")) {
            alert("Pedido marcado como 'Saiu para Entregar'.");
        }
    }

    function prontoParaRetirada() {
        if (confirm("Confirma que o pedido está pronto para retirada?")) {
            alert("Pedido marcado como 'Pronto para Retirada'.");
        }
    }
</script>

</html>