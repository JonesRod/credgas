<?php
    var_dump($_POST);
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
} else {
    echo "Pedido não encontrado.";
    exit;
}
$formato_compra = $pedido['formato_compra'];
//echo $formato_compra;

// Fetch partner details from the database
$id_parceiro = $pedido['id_parceiro'];

$query_parceiro = "SELECT logo, nomeFantasia FROM meus_parceiros WHERE id = ?";
$stmt_parceiro = $mysqli->prepare($query_parceiro);
$stmt_parceiro->bind_param("i", $id_parceiro);
$stmt_parceiro->execute();
$result_parceiro = $stmt_parceiro->get_result();
$loja = $result_parceiro->fetch_assoc();
$logo = $loja['logo'];
$nomeFantasia = $loja['nomeFantasia'];
$stmt_parceiro->close();

// Calculate end time for countdown
$pedido_time = new DateTime($row['data']);
$pedido_time->modify('+15 minutes');
$end_time = $pedido_time->format('Y-m-d H:i:s');


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
</head>
<body>
    <h1>Detalhes do Pedido</h1>
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
    <p><strong>Total:</strong> R$ <?php echo number_format($pedido['valor'], 2, ',', '.'); ?></p>
    <hr>
    <h3>Produtos</h3>
    <table border="1" cellpadding="5" cellspacing="0">
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
            // Divide os produtos armazenados no formato "produto/quantidade/valor uni/total|proximo produto"
            $produtos = explode('|', $pedido['produtos']);
            foreach ($produtos as $produto) {
                // Divide os detalhes de cada produto
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

    <hr>
    <h3>Status de Pagamento</h3>
    <p>
        <?php  if ($formato_compra == 'crediario') {
            echo "Pagamento realizado Online.";
        } elseif ($formato_compra == 'online') {
            echo "Pagamento realizado Online.";
        } elseif ($formato_compra == 'retirar') {
            echo "receber na hora da entrega.";
        }?>
    </p>

    <hr>
    <h3>Forma de Entrega</h3>
    <p><strong>Tipo de Entrega:</strong>
        <?php
            if ($pedido['tipo_entrega'] == 'entregar') {
                echo "Entregar em casa.";
            } elseif ($pedido['tipo_entrega'] == 'buscar') {
                echo "Retirar no local.";
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
                echo $parceiro['endereco'];
            }
        ?>
    </p>
    <p>Nº: 
        <?php 
            if ($pedido['tipo_entrega'] == 'entregar') {
                echo $pedido['num_entrega'] != '' ? $pedido['num_entrega'] : $cliente['numero'];
            } elseif ($pedido['tipo_entrega'] == 'buscar') {
                echo $parceiro['numero'];
            }
        ?>
    </p>
    <p>BAIRRO: 
        <?php 
            if ($pedido['tipo_entrega'] == 'entregar') {
                echo $pedido['bairro_entrega'] != '' ? $pedido['bairro_entrega'] : $cliente['bairro'];
            } elseif ($pedido['tipo_entrega'] == 'buscar') {
                echo $parceiro['bairro'];
            }
        ?>
    </p>
    <p>CIDADE/UF: 
        <?php 
            if ($pedido['tipo_entrega'] == 'entregar') {
                echo $pedido['bairro_entrega'] != '' ? $cliente['cidade'].'/'.$cliente['uf'].', CEP: '. $cliente['cep'] : $cliente['cidade'].'/'.$cliente['uf'] . ', CEP: ' . $cliente['cep'];
            } elseif ($pedido['tipo_entrega'] == 'buscar') {
                echo $parceiro['cidade'].'/'.$parceiro['estado'].', CEP: '. $parceiro['cep'];
            }
        ?>
    </p>
    <p>CONTATO: 
        <?php 
            if ($pedido['tipo_entrega'] == 'entregar') {
                echo $pedido['contato_recebedor'] != '' ? $pedido['contato_recebedor'] : $cliente['celular1'];
            } elseif ($pedido['tipo_entrega'] == 'buscar') {
                echo $parceiro['telefoneComercial'];
            }
        ?>
    </p>
    <p>COMENTÁRIO: </p>
    <textarea name="" id="">
        <?php echo $pedido['comentario']; ?>
    </textarea>
    <hr>
    <p class="cancel-timer" style="color: red;"><strong>Tempo para cancelar:</strong> <span class="countdown" data-end-time="<?php echo $end_time; ?>"></span></p>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const countdownElements = document.querySelectorAll('.countdown');
            countdownElements.forEach(function (element) {
                const endTime = new Date(element.getAttribute('data-end-time')).getTime();

                function updateCountdown() {
                    const now = new Date().getTime();
                    const timeLeft = endTime - now;

                    if (timeLeft <= 0) {
                        element.textContent = "Tempo expirado.";
                        clearInterval(interval);
                    } else {
                        const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
                        const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);
                        element.textContent = `${hours}h ${minutes}m ${seconds}s`;
                    }
                }

                updateCountdown();
                const interval = setInterval(updateCountdown, 1000);
            });
        });
    </script>
    <p>
        <button onclick="javascript:history.back()">Voltar para os Pedidos</button>
    </p>
</body>

</html>
<?php

?>