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

// Adicionar ordenação no final da consulta
$query .= " ORDER BY num_pedido DESC";

// Executa a consulta
$stmt = $mysqli->prepare($query);
if (!$stmt) {
    die("Erro na preparação da consulta: " . $mysqli->error); // Exibe o erro da consulta
}
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Adicionar consulta para buscar estimativa_entrega da tabela meus_parceiros
$parceiroQuery = "SELECT estimativa_entrega FROM meus_parceiros WHERE id = ?";
$parceiroStmt = $mysqli->prepare($parceiroQuery);
if (!$parceiroStmt) {
    die("Erro na preparação da consulta de parceiro: " . $mysqli->error); // Exibe o erro da consulta
}
$parceiroStmt->bind_param("i", $_SESSION['id']);
$parceiroStmt->execute();
$parceiroResult = $parceiroStmt->get_result();
$parceiroData = $parceiroResult->fetch_assoc();
$estimativaEntrega = $parceiroData['estimativa_entrega'] ?? null;

// Agrupamento por data
$pedidosAgrupados = ['Hoje' => [], 'Ontem' => [], 'Outros' => []];
$hoje = new DateTime(); // Data atual
$ontem = (clone $hoje)->modify('-1 day');

while ($row = $result->fetch_assoc()) {
    // Ignorar pedidos com o código de retirada 983678
    if ($row['codigo_retirada'] === '983678') {
        continue;
    }

    $dataPedido = new DateTime($row['data']);
    $dataFormatada = $dataPedido->format('d/m/Y H:i:s'); // Formato brasileiro para exibição
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
        5 => 'Pedido pronto',
        6 => $row['tipo_entrega'] === 'buscar' ? 'Pronto para retirada' : 'Saiu para entrega',
        7 => 'Finalizado',
    ];
    $descricao_status = $status_descricao[$status_final] ?? 'Desconhecido';

    // Calcular o tempo de recusa e a estimativa de entrega
    $tempoCancelamento = (new DateTime($dataPedido->format('Y-m-d H:i:s')))->modify('+15 minutes');
    $tempoEntrega = (clone $tempoCancelamento)->modify('+' . ($estimativaEntrega / 1000 / 60) . ' minutes')->format('Y-m-d H:i:s');

    // Calcular o tempo total do processo para pedidos finalizados, recusados e cancelados
    $tempoDuracao = '';
    if (in_array($status_final, [3, 4, 7]) && !empty($row['data_finalizacao'])) {
        $dataFinalizacao = new DateTime($row['data_finalizacao']);
        $intervalo = $dataPedido->diff($dataFinalizacao);
        $tempoDuracao = sprintf('%02dh %02dm %02ds', $intervalo->h + ($intervalo->days * 24), $intervalo->i, $intervalo->s);
    }

    $currentDateTime = new DateTime();
    $pedidoHTML = "
        <div class='card status-{$status_final}' data-num-pedido='{$row['num_pedido']}' onclick=\"redirectToDetails(
            '{$row['num_pedido']}', 
            '{$status_final}', 
            '{$_SESSION['id']}', 
            '{$status_cliente}', 
            '{$status_parceiro}', 
            '{$dataFormatada}',  
            '{$total}'
        )\">
            <h2>Pedido #{$row['num_pedido']}</h2>
            <p>Status do Pedido: <span class='status-label'>{$descricao_status}</span></p>
            <p>Data: {$dataFormatada}</p>
            <p>Valor Total: R$ " . number_format($total, 2, ',', '.') . "</p>";

    if ($status_final !== 7 && $status_final !== 3 && $status_final !== 4) { // Exibe o tempo de recusa e entrega apenas para pedidos não finalizados, recusados ou cancelados
        if ($currentDateTime < $tempoCancelamento) { // Exibe o tempo de recusa apenas se não expirou
            $pedidoHTML .= "
                <p>Tempo Restante para Recusar: <span class='cancel-countdown' data-end-time='{$tempoCancelamento->format('Y-m-d H:i:s')}'></span></p>";
        }
        $pedidoHTML .= "
            <p>Tempo Restante para Entrega: <span class='countdown' data-end-time='{$tempoEntrega}'></span></p>";
    } elseif (in_array($status_final, [3, 4, 7])) { // Exibe o tempo total do processo para pedidos finalizados, recusados ou cancelados
        $pedidoHTML .= "
            <p>Tempo Total do Processo: <span>{$tempoDuracao}</span></p>";
    }

    $pedidoHTML .= "
        </div>";

    // Agrupamento dos pedidos
    if ($dataPedido->format('Y-m-d') === $hoje->format('Y-m-d')) {
        $pedidosAgrupados['Hoje'][] = $pedidoHTML;
    } elseif ($dataPedido->format('Y-m-d') === $ontem->format('Y-m-d')) {
        $pedidosAgrupados['Ontem'][] = $pedidoHTML;
    } else {
        $dataChave = $dataPedido->format('d/m/Y');
        if (!isset($pedidosAgrupados['Outros'][$dataChave])) {
            $pedidosAgrupados['Outros'][$dataChave] = [];
        }
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
            background: #f9f9f9;
        }

        .pedidos-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .card {
            background-color: #ffffff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: transform 0.2s, background-color 0.2s;
        }

        .card:hover {
            transform: scale(1.02);
        }

        .status-0 {
            background-color: #ffe5b4;
        }

        .status-1,
        .status-5,
        .status-6,
        .status-7 {
            background-color: #d4edda;
        }

        .status-2 {
            background-color: #fff3cd;
        }

        .status-3 {
            background-color: #f8c6d8;
        }

        .status-4 {
            background-color: #f8d7da;
        }

        .section-title {
            margin-bottom: 10px;
            font-size: 18px;
            font-weight: bold;
        }

        h2 {
            margin: 0 0 10px;
            font-size: 18px;
        }

        .status-label {
            font-weight: bold;
            color: #555;
            font-size: 14px;
        }

        .form-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            margin-bottom: 20px;
        }

        .form-container input,
        .form-container select,
        .form-container button {
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        .form-container button[type="submit"] {
            background-color: #28a745;
            color: #fff;
            border: none;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .form-container button[type="submit"]:hover {
            background-color: #218838;
            transform: scale(1.05);
        }

        .form-container button[type="button"] {
            background-color: #007bff;
            color: #fff;
            border: none;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .form-container button[type="button"]:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }

        .form-container .btn-voltar {
            background-color: #6c757d;
            color: #fff;
            text-decoration: none;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .form-container .btn-voltar:hover {
            background-color: #5a6268;
            transform: scale(1.05);
        }

        @media (max-width: 768px) {
            h2 {
                font-size: 14px;
            }

            .status-label {
                font-size: 12px;
            }

            .form-container {
                flex-direction: column;
                align-items: stretch;
            }

            .form-container input,
            .form-container select,
            .form-container button,
            .form-container .btn-voltar {
                width: 100%;
                font-size: 14px;
                padding: 8px;
            }

            .pedidos-container {
                grid-template-columns: 1fr;
            }

            .card {
                padding: 10px;
                font-size: 14px;
            }

            .section-title {
                font-size: 16px;
                text-align: center;
            }

            p {
                font-size: 12px;
            }
        }
    </style>
</head>

<body>
    <h1>Meus Pedidos</h1>

    <form method="GET" class="form-container">
        <a href="../parceiro_home.php" class="btn-voltar">Voltar</a>
        <input type="date" name="data" value="<?= htmlspecialchars($data) ?>">
        <select name="status">
            <option value="">Todos</option>
            <option value="0" <?= $status === '0' ? 'selected' : '' ?>>Pendente</option>
            <option value="1" <?= $status === '1' ? 'selected' : '' ?>>Confirmado</option>
            <option value="2" <?= $status === '2' ? 'selected' : '' ?>>Em preparação</option>
            <option value="3" <?= $status === '3' ? 'selected' : '' ?>>Recusado</option>
            <option value="4" <?= $status === '4' ? 'selected' : '' ?>>Cancelado</option>
            <option value="5" <?= $status === '5' ? 'selected' : '' ?>>Pedido pronto</option>
            <option value="6" <?= $status === '6' ? 'selected' : '' ?>>Pronto para retirada/Saiu para entrega</option>
            <option value="7" <?= $status === '7' ? 'selected' : '' ?>>Finalizado</option>
        </select>
        <input type="text" name="num_pedido" placeholder="Número do Pedido" value="<?= htmlspecialchars($num_pedido) ?>"
            oninput="this.value = this.value.replace(/[^0-9]/g, '')">
        <button type="submit">Filtrar</button>
        <button type="button" onclick="window.location.href='pedidos.php'">Carregar Todos</button>
    </form>
    <hr> <!-- Linha horizontal para separar os filtros dos pedidos -->

    <?php
    // Renderiza os pedidos agrupados
    $nenhumPedidoEncontrado = true; // Variável para verificar se há pedidos
    foreach ($pedidosAgrupados as $grupo => $pedidos) {
        if (empty($pedidos)) {
            continue;
        }
        $nenhumPedidoEncontrado = false; // Há pelo menos um pedido
    
        if ($grupo === 'Outros') {
            // Agrupamento por data
            foreach ($pedidos as $dataEspecifica => $lista) {
                echo "<hr>"; // Linha horizontal para separar os pedidos por data
                echo "<div class='section-title'>Pedidos de $dataEspecifica</div>";
                echo "<div class='pedidos-container'>"; // Container flexível para pedidos lado a lado
                foreach ($lista as $pedidoHTML) {
                    echo $pedidoHTML;
                }
                echo "</div>"; // Fecha o container flexível
            }
        } else {
            echo "<hr>"; // Linha horizontal para separar os pedidos por grupo
            echo "<div class='section-title'>$grupo</div>";
            echo "<div class='pedidos-container'>"; // Container flexível para pedidos lado a lado
            foreach ($pedidos as $pedidoHTML) {
                echo $pedidoHTML;
            }
            echo "</div>"; // Fecha o container flexível
        }
    }
    // Exibe mensagem se nenhum pedido for encontrado
    if ($nenhumPedidoEncontrado) {
        echo "<p style='text-align: center; font-size: 18px; color: #555;'>Nenhum pedido encontrado.</p>";
    }
    ?>
    <script>
        // Função para redirecionar para a página de detalhes do pedido com base no status
        function redirectToDetails(num_pedido, status_final, id_parceiro, status_cliente, status_parceiro, data, valor) {
            const form = document.createElement('form'); // Cria um formulário dinamicamente
            form.method = 'POST';
            const maiorStatus = Math.max(status_cliente, status_parceiro); // Determina o maior status

            if (maiorStatus === 1 || maiorStatus === 5 || maiorStatus === 6 || maiorStatus === 7) {
                form.action = `pedido_confirmado.php?id_parceiro=${encodeURIComponent(id_parceiro)}&num_pedido=${encodeURIComponent(num_pedido)}`;
            } else if (maiorStatus === 3) {
                form.action = `pedido_recusado.php?id_parceiro=${encodeURIComponent(id_parceiro)}&num_pedido=${encodeURIComponent(num_pedido)}`;
            } else if (maiorStatus === 4) {
                form.action = `pedido_cancelado.php?id_parceiro=${encodeURIComponent(id_parceiro)}&num_pedido=${encodeURIComponent(num_pedido)}`;
            } else {
                form.action = `detalhes_pedido.php?id_parceiro=${encodeURIComponent(id_parceiro)}&num_pedido=${encodeURIComponent(num_pedido)}`;
            }

            // Campos a serem enviados no formulário
            const fields = {
                num_pedido: num_pedido,
                id_parceiro: id_parceiro,
                status: maiorStatus,
                data: data,
                valor: valor,
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
                const distance = new Date(endTime).getTime() - now;

                if (distance > 0) {
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                    element.textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds} min`;
                } else {
                    clearInterval(interval);
                    element.textContent = "Tempo expirado"; // Continua mostrando "Tempo Restante para Entrega" com "Tempo expirado"
                }
            }, 1000);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const countdownElements = document.querySelectorAll('.countdown');
            countdownElements.forEach(element => {
                const endTime = element.getAttribute('data-end-time');
                if (endTime) {
                    startCountdown(element, endTime);
                }
            });

            const cancelCountdownElements = document.querySelectorAll('.cancel-countdown');
            cancelCountdownElements.forEach(element => {
                const endTime = element.getAttribute('data-end-time');
                if (endTime) {
                    startCountdown(element, endTime);
                }
            });
        });
    </script>
</body>

</html>