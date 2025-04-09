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
$query = "SELECT * FROM pedidos WHERE id_cliente = ?";
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
    $query .= " AND status_cliente = ?";
    $params[] = $status;
    $types .= "i";
}

$query .= " ORDER BY num_pedido DESC";
$stmt = $mysqli->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

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
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Meus Pedidos</title>
    <link rel="stylesheet" href="styles.css">
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
            background-color: rgb(227, 229, 132);
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
        .filters input, .filters select {
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
            transition: background-color 0.3s ease;
        }
        .filters button:hover {
            background-color: #0056b3;
        }
        @media (max-width: 768px) {
            .filters {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 10px; /* Espaçamento entre os elementos */
            }
            .filters input, .filters select, .filters button, .btn-voltar {
                width: 100%; /* Ocupa toda a largura disponível */
                max-width: 300px; /* Limita a largura máxima */
                box-sizing: border-box; /* Inclui padding e borda no tamanho total */
            }
            .filters form {
                width: 100%;
                display: flex;
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body onload="refreshPage()">
    <h1 class="title">Meus Pedidos</h1>
    <div class="filters">
        <form method="GET">
            <a href="../cliente_home.php" class="btn-voltar">Voltar</a>
            <input type="text" name="num_pedido" placeholder="Número do Pedido" value="<?php echo htmlspecialchars($num_pedido); ?>">
            <input type="date" name="data" value="<?php echo htmlspecialchars($data); ?>">
            <select name="status">
                <option value="">Todos os Status</option>
                <option value="0" <?php if ($status === '0') echo 'selected'; ?>>Aguardando confirmação</option>
                <option value="1" <?php if ($status === '1') echo 'selected'; ?>>Pedido confirmado</option>
                <option value="2" <?php if ($status === '2') echo 'selected'; ?>>Pedido cancelado</option>
            </select>
            <button type="submit">Filtrar</button>
            <button type="button" onclick="window.location.href='meus_pedidos.php'">Carregar Todos</button>
        </form>
    </div>
    <div class="cards-container">
        <?php while ($row = $result->fetch_assoc()): 
            $valor = $row['valor'];
            $taxa_crediario = $row['taxa_crediario'];
            $frete = $row['valor_frete'];
            $total = $valor + $taxa_crediario + $frete;

            // Calculate end time for countdown
            $pedido_time = new DateTime($row['data']);
            $pedido_time->modify('+15 minutes');
            $end_time = $pedido_time->format('Y-m-d H:i:s');
            //echo $end_time;

            ?>
            <div class="card" onclick="redirectToDetails('<?php echo htmlspecialchars($row['num_pedido']); ?>', '<?php echo htmlspecialchars($row['id_parceiro']); ?>', '<?php echo htmlspecialchars($row['status_cliente']); ?>', '<?php echo htmlspecialchars($row['data']); ?>', '<?php echo htmlspecialchars($row['valor']); ?>')">
                <h2>Pedido #<?php echo htmlspecialchars($row['num_pedido']); ?></h2>
                <h3 style="color:darkgreen;">Cód. para Retirada: <?php echo htmlspecialchars($row['codigo_retirada']); ?></h3>
                <?php
                    // Fetch partner details from the database
                    $id_parceiro = $row['id_parceiro'];

                    $query_parceiro = "SELECT * FROM meus_parceiros WHERE id = ?";
                    $stmt_parceiro = $mysqli->prepare($query_parceiro);
                    $stmt_parceiro->bind_param("i", $id_parceiro);
                    $stmt_parceiro->execute();
                    $result_parceiro = $stmt_parceiro->get_result();
                    $loja = $result_parceiro->fetch_assoc();
                    $logo = $loja['logo'];
                    $nomeFantasia = $loja['nomeFantasia'];
                    $estimativa_entrega = $loja['estimativa_entrega'];
                    $stmt_parceiro->close();
                ?>
                <p><strong>Status do Pedido:</strong>
                    <span style="color: <?php echo $row['status_cliente'] == 0 ? 'orange' : ($row['status_cliente'] == 1 ? 'green' : 'red'); ?>">
                        <?php 
                            $status = $row['status_cliente']; 
                            if ($status == 0) {
                                echo "Aguardando confirmação";
                            } else if ($status == 1) {
                                echo "Pedido confirmado";
                            } else if ($status == 2) {
                                echo "Pedido cancelado";
                            } else {
                                echo "Status desconhecido";
                            }
                        ?>
                    </span>
                </p>
                <p><img src="../../parceiros/arquivos/<?php echo $logo;?>" alt="Logo"> <?php echo $nomeFantasia; ?></p>
                <p><strong>Data:</strong> <?php echo htmlspecialchars(formatDateTimeJS($row['data'])); ?></p>
                <p class="valor"><strong>Valor da compra: R$ </strong> <?php echo htmlspecialchars(number_format($total, 2, ',', '.')); ?></p>
                <hr>
                <p class="tempo-cancelar" style="color: red; display: none;"><strong>Tempo para cancelar:</strong>
                <span class="countdown" data-end-time="<?php echo $end_time; ?>"></span></p>
                <p class="text-cancelar" style="color: red; display: none;">
                    <strong>O tempo de resposta expirou. Você pode cancelar sua compra!</strong>
                </p>
            </div>
        <?php endwhile; ?>
    </div>
    <br>

    <script>
        // Função para redirecionar para a página de detalhes do pedido
        function redirectToDetails(num_pedido, id_parceiro, status_cliente, data, valor) {
            const form = document.createElement('form'); // Cria um formulário dinamicamente
            form.method = 'POST';
            form.action = 'detalhes_pedido.php';

            // Campos a serem enviados no formulário
            const fields = {
                num_pedido: num_pedido,
                id_parceiro: id_parceiro,
                status_cliente: status_cliente,
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

        // Função para recarregar a página a cada 5 minutos
        function refreshPage() {
            setInterval(function() {
                location.reload(); // Recarrega a página
            }, 300000); // 300000 ms = 5 minutos
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
                    element.closest('.tempo-cancelar').style.display = "block"; // Mostra o "Tempo para cancelar"
                } else {
                    // Quando o tempo expira, para o intervalo e ajusta a exibição
                    clearInterval(interval);
                    element.closest('.tempo-cancelar').style.display = "none";

                    // Verifica se o tempo de cancelamento expirou
                    const tempoEntregaMais15Min = estimativaEntrega + 15 * 60 * 1000; // 15 minutos após a estimativa de entrega
                    if (now > tempoEntregaMais15Min) {
                        element.closest('.card').querySelector('.text-cancelar').style.display = "block"; // Mostra o texto de cancelamento
                    }
                }
            }

            updateCountdown(); // Atualiza a contagem imediatamente
            interval = setInterval(updateCountdown, 1000); // Atualiza a cada segundo
        }

        document.addEventListener('DOMContentLoaded', function () {
            const countdownElements = document.querySelectorAll('.countdown');

            // Converte a estimativa de entrega para milissegundos
            const estimativaEntrega = new Date(<?php echo json_encode($estimativa_entrega); ?>).getTime();

            countdownElements.forEach(function (element) {
                const endTime = new Date(element.getAttribute('data-end-time')).getTime();
                startCountdown(element, endTime, estimativaEntrega); // Chama a função startCountdown
            });

            // Garante que os elementos estejam inicialmente ocultos
            document.querySelectorAll('.text-cancelar').forEach(function (element) {
                element.style.display = "none";
            });
        });
    </script>
</body>
</html>

<?php
$stmt->close();
$mysqli->close();
?>
