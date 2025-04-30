<?php
session_start();
include('../../../conexao.php'); // Conexão com o banco

// Verificação de sessão
if (!isset($_SESSION['id'])) {
    header("Location: ../../../../index.php");
    exit;
}

// Get user ID from session
$id = filter_var($_SESSION['id'], FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: ../../../../index.php");
    exit;
}

// Fetch filters from GET parameters
$num_pedido = isset($_GET['num_pedido']) ? $_GET['num_pedido'] : '';
$data = isset($_GET['data']) ? $_GET['data'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Build query with filters
$query = "SELECT * FROM pedidos WHERE id_parceiro = ?";
$params = [$id];
$types = "i";

if ($num_pedido) {
    $query .= " AND num_pedido = ?";
    $params[] = $num_pedido;
    $types .= "i";
}

if ($data) {
    $query .= " AND DATE(data) = ?";
    $params[] = $data;
    $types .= "s";
}

if ($status !== '') {
    $query .= " AND GREATEST(status_cliente, status_parceiro) = ?";
    $params[] = $status;
    $types .= "i";
}

$query .= " ORDER BY num_pedido DESC";
$stmt = $mysqli->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

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
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Pedidos</title>

    <style>
        .cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 10px;
            justify-content: center;
        }

        .card {
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
            cursor: pointer;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .card.status-0 {
            background-color: #ffcc80;
            /* Laranja */
        }

        .card.status-0:hover {
            background-color: #ffb74d;
            /* Laranja mais escuro */
        }

        .card.status-1 {
            background-color: #c8e6c9;
            /* Verde */
        }

        .card.status-1:hover {
            background-color: #a5d6a7;
            /* Verde mais escuro */
        }

        .card.status-2 {
            background-color: #90caf9;
            /* Azul */
        }

        .card.status-2:hover {
            background-color: #64b5f6;
            /* Azul mais escuro */
        }

        .card.status-3 {
            background-color: #ffcdd2;
            /* Vermelho */
        }

        .card.status-3:hover {
            background-color: #ef9a9a;
            /* Vermelho mais escuro */
        }

        .card.status-4 {
            background-color: #ffcdd2;
            /* Vermelho */
        }

        .card.status-4:hover {
            background-color: #ef9a9a;
            /* Vermelho mais escuro */
        }

        .card h2 {
            color: rgb(13, 69, 147);
        }

        .card .valor {
            font-weight: bold;
            color: rgb(13, 69, 147);
        }

        .card img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            vertical-align: middle;
        }

        .btn-voltar {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #007bff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .btn-voltar:hover {
            background-color: #0056b3;
        }

        .title {
            text-align: center;
            font-size: 2em;
            margin-bottom: 20px;
            color: #333;
        }

        .filters {
            margin-bottom: 20px;
            text-align: center;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .filters input,
        .filters select {
            padding: 10px;
            margin: 5px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .filters button {
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 5px;
            transition: background-color 0.3s ease;
        }

        .filters button:hover {
            background-color: #0056b3;
        }

        @media (max-width: 600px) {
            .filters {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 10px;
                /* Espaçamento entre os elementos */
            }

            .filters input,
            .filters select,
            .filters button,
            .btn-voltar {
                width: 100%;
                /* Ocupa toda a largura disponível */
                max-width: 600px;
                /* Limita a largura máxima */
                box-sizing: border-box;
                /* Inclui padding e borda no tamanho total */
            }

            .filters form {
                width: 100%;
                display: flex;
                flex-direction: column;
                align-items: center;
            }
        }

        @media (max-width: 380px) {
            .filters {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 10px;
                /* Espaçamento entre os elementos */
            }

            .filters input,
            .filters select,
            .filters button,
            .btn-voltar {
                width: 100%;
                /* Ocupa toda a largura disponível */
                max-width: 300px;
                /* Limita a largura máxima */
                box-sizing: border-box;
                /* Inclui padding e borda no tamanho total */
            }

            .filters form {
                width: 100%;
                display: flex;
                flex-direction: column;
                align-items: center;
            }
        }
    </style>

    <script>
        // Função para recarregar a página a cada 5 minutos
        function refreshPage() {
            setInterval(function () {
                location.reload(); // Recarrega a página
            }, 300000); // 300000 ms = 3 minutos
        }

        function atualizarStatusPedido(num_pedido, novoStatus) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'atualizar_status_pedido.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                if (xhr.status === 200) {
                    alert('Status do pedido atualizado com sucesso!');
                    const card = document.querySelector(`.card[data-num-pedido="${num_pedido}"]`);
                    if (card) {
                        card.className = `card status-${novoStatus}`; // Atualiza a classe do cartão
                        const statusSpan = card.querySelector('span');
                        if (statusSpan) {
                            // Atualiza o texto do status
                            switch (novoStatus) {
                                case '0':
                                    statusSpan.textContent = 'Aguardando confirmação';
                                    statusSpan.style.color = '#ff5722';
                                    break;
                                case '1':
                                    statusSpan.textContent = 'Pedido confirmado e já está em preparação.';
                                    statusSpan.style.color = 'green';
                                    break;
                                case '2':
                                    statusSpan.textContent = 'Pedido pronto para entrega';
                                    statusSpan.style.color = 'blue';
                                    break;
                                case '3':
                                    statusSpan.textContent = 'Pedido recusado pelo cliente';
                                    statusSpan.style.color = 'red';
                                    break;
                                case '4':
                                    statusSpan.textContent = 'Pedido Cancelado pelo cliente';
                                    statusSpan.style.color = 'red';
                                    break;
                            }
                        }
                    }
                } else {
                    alert('Erro ao atualizar o status do pedido.');
                }
            };
            xhr.send(`num_pedido=${num_pedido}&novo_status=${novoStatus}`);
        }
    </script>
</head>

<body onload="refreshPage()">
    <h1 class="title">Meus Pedidos</h1>
    <div class="filters">
        <form method="GET">
            <a href="../parceiro_home.php" class="btn-voltar">Voltar</a>
            <input type="date" name="data" value="<?php echo htmlspecialchars($data); ?>">
            <select name="status">
                <option value="">Todos os Status</option>
                <option value="0" <?php if ($status === '0')
                    echo 'selected'; ?>>Aguardando confirmação</option>
                <option value="1" <?php if ($status === '1')
                    echo 'selected'; ?>>Pedido confirmado</option>
                <option value="2" <?php if ($status === '2')
                    echo 'selected'; ?>>Pedido cancelado</option>
                <option value="3" <?php if ($status === '3')
                    echo 'selected'; ?>>Pedido recusado</option>
                <option value="3" <?php if ($status === '4')
                    echo 'selected'; ?>>Pedido Cancelado</option>
            </select>
            <input type="text" name="num_pedido" placeholder="Número do Pedido"
                value="<?php echo htmlspecialchars($num_pedido); ?>">
            <button type="submit">Filtrar</button>
            <button type="button" onclick="window.location.href='meus_pedidos.php'">Carregar Todos</button>
        </form>
    </div>
    <div class="cards-container">
        <?php while ($row = $result->fetch_assoc()): ?>
            <?php
            $valor = $row['valor_produtos_confirmados'] != "" ? $row['valor_produtos_confirmados'] : $row['valor_produtos'];
            $saldo_usado = $row['saldo_usado'];
            $taxa_crediario = $row['taxa_crediario'];
            $frete = $row['valor_frete'];
            $total = $valor + $frete - $saldo_usado + $taxa_crediario;

            // Calculate end time for countdown
            $pedido_time = new DateTime($row['data']);
            $pedido_time->modify('+15 minutes');
            $end_time = $pedido_time->format('Y-m-d H:i:s');
            ?>
            <div class="card status-<?php echo max($row['status_cliente'], $row['status_parceiro']); ?>"
                data-num-pedido="<?php echo htmlspecialchars($row['num_pedido']); ?>"
                onclick="redirectToDetails('<?php echo htmlspecialchars($row['num_pedido']); ?>', '<?php echo htmlspecialchars($row['id_parceiro']); ?>', '<?php echo htmlspecialchars($row['status_cliente']); ?>', '<?php echo htmlspecialchars($row['status_parceiro']); ?>', '<?php echo htmlspecialchars($row['data']); ?>', '<?php echo htmlspecialchars($row['valor_produtos']); ?>')">
                <h2>Pedido #<?php echo htmlspecialchars($row['num_pedido']); ?></h2>
                <h3 style="color:darkgreen;">Cód. para Retirada: <?php echo htmlspecialchars($row['codigo_retirada']); ?>
                </h3>
                <p><strong>Status do Pedido:</strong>
                    <span style="color: <?php
                    $status = max($row['status_cliente'], $row['status_parceiro']);
                    echo $status == 0 ? '#ff5722' : ($status == 1 ? 'green' : ($status == 2 ? 'blue' : 'red'));
                    ?>">
                        <?php
                        if ($status == 0) {
                            echo "Aguardando confirmação";
                        } else if ($status == 1) {
                            echo "Pedido confirmado e já está em preparação.";
                        } else if ($status == 2) {
                            echo "Pedido pronto para entrega";
                        } else if ($status == 3) {
                            echo "Pedido recusado";
                        } else if ($status == 4) {
                            echo "Pedido Cancelado";
                        }
                        ?>
                    </span>
                </p>
                <p><strong>Data:</strong> <?php echo htmlspecialchars(formatDateTimeJS($row['data'])); ?></p>
                <p class="valor"><strong>Valor da compra: R$ </strong>
                    <?php echo htmlspecialchars(number_format($total, 2, ',', '.')); ?></p>
                <hr>
                <?php if ($row['status_cliente'] == 4 || $row['status_parceiro'] == 4): ?>
                    <p style="color: red; text-align: center;"><strong>
                            <?php echo $row['status_cliente'] == 4 ? 'Cancelado pelo Cliente' : 'Cancelado pela Loja'; ?>.</strong>
                    </p>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>
    <br>

    <script>
        // Função para redirecionar para a página de detalhes do pedido
        function redirectToDetails(num_pedido, id_parceiro, status_cliente, status_parceiro, data, valor) {
            const form = document.createElement('form'); // Cria um formulário dinamicamente
            form.method = 'POST';

            const maiorStatus = Math.max(status_cliente, status_parceiro); // Determina o maior status

            if (maiorStatus === 1) {
                form.action = 'pedido_confirmado.php';
            } else if (maiorStatus === 3) {
                form.action = 'pedido_recusado.php';
            } else if (maiorStatus === 4) {
                form.action = 'pedido_cancelado.php';
            } else {
                form.action = 'detalhes_pedido.php';
            }

            // Campos a serem enviados no formulário
            const fields = {
                num_pedido: num_pedido,
                id_parceiro: id_parceiro,
                status: maiorStatus,
                data: data,
                valor: valor
            };

            // Adiciona os campos como inputs ocultos no formulário
            for (const key in fields) {
                if (fields.hasOwnProperty(key)) {
                    const hiddenField = document.createElement('input');
                    hiddenField.type = 'hidden';
                    hiddenField.name = key;
                    hiddenField.value = fields[key];
                    form.appendChild(hiddenField);
                }
            }

            document.body.appendChild(form); // Adiciona o formulário ao corpo do documento
            form.submit(); // Submete o formulário
        }

        // Função para iniciar a contagem regressiva
        function startCountdown(element, endTime, estimativaEntrega) {
            let interval;

            // Atualiza a contagem regressiva a cada segundo
            function updateCountdown() {
                const now = new Date().getTime(); // Obtém o timestamp atual
                const distance = endTime - now; // Calcula o tempo restante

                if (distance > 0) {
                    // Calcula minutos e segundos restantes
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                    element.innerHTML = minutes + ":" + (seconds < 10 ? "0" : "") + seconds + " min";

                    const tempoCancelar = element.closest('.tempo-cancelar');
                    if (tempoCancelar) {
                        tempoCancelar.style.display = "block"; // Mostra o "Tempo para cancelar"
                    }
                } else {
                    // Quando o tempo expira, para o intervalo e ajusta a exibição
                    clearInterval(interval);

                    const tempoCancelar = element.closest('.tempo-cancelar');
                    if (tempoCancelar) {
                        tempoCancelar.style.display = "none";
                    }

                    // Verifica se o tempo de cancelamento expirou
                    const tempoEntregaMais15Min = estimativaEntrega + 15 * 60 * 1000; // 15 minutos após a estimativa de entrega
                    const card = element.closest('.card');
                    if (now > tempoEntregaMais15Min && card) {
                        const textCancelar = card.querySelector('.text-cancelar');
                        if (textCancelar) {
                            textCancelar.style.display = "block"; // Mostra o texto de cancelamento
                        }
                    }
                }
            }

            updateCountdown(); // Atualiza a contagem imediatamente
            interval = setInterval(updateCountdown, 1000); // Atualiza a cada segundo
        }

        document.addEventListener('DOMContentLoaded', function () {
            const countdownElements = document.querySelectorAll('.countdown');

            // Converte a estimativa de entrega para milissegundos
            const estimativaEntrega = <?php echo json_encode(isset($estimativa_entrega) ? $estimativa_entrega : null); ?>;

            countdownElements.forEach(function (element) {
                const endTime = new Date(element.getAttribute('data-end-time')).getTime();
                if (!isNaN(endTime)) {
                    startCountdown(element, endTime, estimativaEntrega ? new Date(estimativaEntrega).getTime() : 0); // Chama a função startCountdown
                }
            });

            // Garante que os elementos estejam inicialmente ocultos
            document.querySelectorAll('.text-cancelar').forEach(function (element) {
                if (element) {
                    element.style.display = "none";
                }
            });
        });
    </script>
</body>

</html>

<?php
$stmt->close();
$mysqli->close();
?>