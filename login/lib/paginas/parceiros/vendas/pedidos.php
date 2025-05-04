<?php
session_start();
include('../../../conexao.php'); // Conexão com o banco

// Verificação de sessão
if (!isset($_SESSION['id'])) {
    header("Location: ../../../../index.php");
    exit;
}

// Filtros
$num_pedido = $_GET['num_pedido'] ?? '';
$data = $_GET['data'] ?? '';
$status = $_GET['status'] ?? '';

// Query base
$query = "SELECT * FROM pedidos WHERE id_parceiro = ?";
$params = [$_SESSION['id']];
$types = "i";

// Filtros dinâmicos
if (!empty($num_pedido)) {
    $query .= " AND num_pedido = ?";
    $params[] = $num_pedido;
    $types .= "i";
}
if (!empty($data)) {
    $query .= " AND DATE(data) = ?";
    $params[] = $data;
    $types .= "s";
}
if ($status !== '') {
    $query .= " AND (status_cliente = ? OR status_parceiro = ?)";
    $params[] = $status;
    $params[] = $status;
    $types .= "ii";
}

// Executa a consulta
$stmt = $mysqli->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Adicionar consulta para buscar estimativa_entrega da tabela meus_parceiros
$parceiroQuery = "SELECT estimativa_entrega FROM meus_parceiros WHERE id = ?";
$parceiroStmt = $mysqli->prepare($parceiroQuery);
$parceiroStmt->bind_param("i", $_SESSION['id']);
$parceiroStmt->execute();
$parceiroResult = $parceiroStmt->get_result();
$parceiroData = $parceiroResult->fetch_assoc();
$estimativaEntrega = $parceiroData['estimativa_entrega'] ?? null;

// Agrupamento por data
$pedidosAgrupados = ['Hoje' => [], 'Ontem' => [], 'Outros' => []];
$hoje = new DateTime();
$ontem = (clone $hoje)->modify('-1 day');

while ($row = $result->fetch_assoc()) {
    $dataPedido = new DateTime($row['data']);
    $dataFormatada = $dataPedido->format('d/m/Y H:i');

    $valor = $row['valor_produtos_confirmados'] > 0 ? $row['valor_produtos_confirmados'] : $row['valor_produtos'];
    $frete = $row['valor_frete'] ?? 0;
    $saldo_usado = $row['saldo_usado'] ?? 0;
    $taxa_crediario = $row['taxa_crediario'] ?? 0;
    $total = $valor + $frete - $saldo_usado + $taxa_crediario;

    $status_cliente = $row['status_cliente'];
    $status_parceiro = $row['status_parceiro'];
    $status_final = max($status_cliente, $status_parceiro);

    // Mapeamento do status para descrição
    $status_descricao = [
        0 => 'Pendente',
        1 => 'Confirmado',
        2 => 'Em preparação',
        3 => 'Recusado',
        4 => 'Cancelado',
        5 => $row['tipo_entrega'] === 'buscar' ? 'Pronto para retirada' : 'Saiu para entrega'
    ];
    $descricao_status = $status_descricao[$status_final] ?? 'Desconhecido';

    $pedidoHTML = "
        <div class='card status-{$status_final}' data-num-pedido='{$row['num_pedido']}' 
            onclick='redirectToDetails(\"{$row['num_pedido']}\", \"{$status_final}\", \"{$row['id_parceiro']}\", \"{$row['status_cliente']}\", \"{$row['status_parceiro']}\", \"{$row['data']}\", \"{$row['valor_produtos']}\")'>
            <h2>Pedido #{$row['num_pedido']}</h2>
            <p>Status: <span class='status-label'>{$descricao_status}</span></p>
            <p>Data: {$dataFormatada}</p>
            <p>Valor Total: R$ " . number_format($total, 2, ',', '.') . "</p>
            <p>Código de Retirada: <strong>{$row['codigo_retirada']}</strong></p>
            <p>Tempo Restante para Cancelar: <span class='cancel-countdown' data-end-time='{$row['data_cancelamento']}'></span></p>
            <p>Tempo Restante para Entrega: <span class='countdown' data-end-time='{$estimativaEntrega}'></span></p>
        </div>";

    if ($dataPedido->format('Y-m-d') === $hoje->format('Y-m-d')) {
        $pedidosAgrupados['Hoje'][] = $pedidoHTML;
    } elseif ($dataPedido->format('Y-m-d') === $ontem->format('Y-m-d')) {
        $pedidosAgrupados['Ontem'][] = $pedidoHTML;
    } else {
        $dataChave = $dataPedido->format('d/m/Y');
        $pedidosAgrupados['Outros'][$dataChave][] = $pedidoHTML;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Meus Pedidos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
            background: #f4f4f4;
        }

        form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        form input,
        form select,
        form button {
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        form button {
            background-color: #2196F3;
            color: white;
            cursor: pointer;
            border: none;
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

        .card {
            background-color: #fff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
            cursor: pointer;
            transition: transform 0.2s, background-color 0.2s;
        }

        .card:hover {
            transform: scale(1.02);
        }

        .status-0 {
            background-color: orange;
        }

        .status-1 {
            background-color: green;
        }

        .status-2 {
            background-color: yellow;
        }

        .status-3 {
            background-color: purple;
        }

        .status-4 {
            background-color: red;
        }

        .status-5 {
            background-color: blue;
        }

        .status-0:hover,
        .status-1:hover,
        .status-2:hover,
        .status-3:hover,
        .status-4:hover,
        .status-5:hover {
            filter: brightness(0.9);
        }

        h2 {
            margin: 0 0 10px;
        }

        .status-label {
            font-weight: bold;
            color: #555;
        }

        .section-title {
            margin-top: 30px;
            font-size: 20px;
        }

        @media (max-width: 600px) {
            form {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>

    <h1>Meus Pedidos</h1>

    <form method="GET">
        <a href="../parceiro_home.php" class="btn-voltar">Voltar</a>
        <input type="date" name="data" value="<?= htmlspecialchars($data) ?>">
        <select name="status">
            <option value="">Todos</option>
            <option value="0" <?= $status === '0' ? 'selected' : '' ?>>Pendente</option>
            <option value="1" <?= $status === '1' ? 'selected' : '' ?>>Confirmado</option>
            <option value="2" <?= $status === '2' ? 'selected' : '' ?>>Em preparação</option>
            <option value="3" <?= $status === '3' ? 'selected' : '' ?>>Recusado</option>
            <option value="4" <?= $status === '4' ? 'selected' : '' ?>>Cancelado</option>
            <option value="5" <?= $status === '5' ? 'selected' : '' ?>>Pronto para retirada/Saiu para entrega</option>
        </select>
        <input type="text" name="num_pedido" placeholder="Número do Pedido"
            value="<?= htmlspecialchars($num_pedido) ?>">
        <button type="submit">Filtrar</button>
        <button type="button" onclick="window.location.href='pedidos.php'">Carregar Todos</button>
    </form>

    <?php
    // Renderiza os pedidos agrupados
    foreach ($pedidosAgrupados as $grupo => $pedidos) {
        if (empty($pedidos))
            continue;

        if ($grupo === 'Outros') {
            foreach ($pedidos as $dataEspecifica => $lista) {
                echo "<div class='section-title'>Pedidos de $dataEspecifica</div>";
                foreach ($lista as $pedidoHTML) {
                    echo $pedidoHTML;
                }
            }
        } else {
            echo "<div class='section-title'>$grupo</div>";
            foreach ($pedidos as $pedidoHTML) {
                echo $pedidoHTML;
            }
        }
    }
    ?>

    <script>
        // Função para redirecionar para a página de detalhes do pedido com base no status
        function redirectToDetails(num_pedido, status_final, id_parceiro, status_cliente, status_parceiro, data, valor) {
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

        // Atualiza a página a cada 5 minutos
        setInterval(() => {
            location.reload();
        }, 5 * 60 * 1000);

        // Função para iniciar o cronômetro
        function startCountdown(element, endTime) {
            const interval = setInterval(() => {
                const now = new Date().getTime();
                const distance = endTime - now;

                if (distance > 0) {
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                    element.textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds} min`;
                } else {
                    clearInterval(interval);
                    element.textContent = "Tempo expirado";
                }
            }, 1000);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const countdownElements = document.querySelectorAll('.countdown, .cancel-countdown');
            countdownElements.forEach(element => {
                const endTime = new Date(element.getAttribute('data-end-time')).getTime();
                if (!isNaN(endTime)) {
                    startCountdown(element, endTime);
                }
            });
        });
    </script>

</body>

</html>