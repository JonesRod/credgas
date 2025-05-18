<?php
session_start();
include('../../../conexao.php'); // Conexão com o banco

// Verifica se o usuário está logado
if (!isset($_GET['id_parceiro'])) {
    header("Location: ../../../../index.php");
    exit;
}

// Verifica se o ID do pedido foi enviado
if (!isset($_GET['num_pedido'])) {
    header("Location: ../../../../index.php");
    exit;
}

$id_parceiro = $_GET['id_parceiro'];
$num_pedido = $_GET['num_pedido'];

if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    header('Content-Type: application/json');

    try {
        // Consulta para buscar o status do pedido
        $query_status = "SELECT status_parceiro, status_cliente FROM pedidos WHERE id_parceiro = ? AND num_pedido = ?";
        $stmt_status = $mysqli->prepare($query_status);
        if (!$stmt_status) {
            throw new Exception("Erro ao preparar a consulta: " . $mysqli->error);
        }
        $stmt_status->bind_param("ii", $id_parceiro, $num_pedido);
        $stmt_status->execute();
        $result_status = $stmt_status->get_result();

        if ($result_status->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Pedido não encontrado.']);
            exit;
        }

        $status = $result_status->fetch_assoc();
        $status_final = max($status['status_parceiro'], $status['status_cliente']);

        echo json_encode(['success' => true, 'status_final' => $status_final]);
        exit;
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

// Consulta para buscar os dados do pedido
$query = "SELECT * FROM pedidos WHERE id_parceiro = ? AND num_pedido = ?";
$stmt = $mysqli->prepare($query);
if (!$stmt) {
    die("Erro ao preparar a consulta: " . $mysqli->error . " | Query: " . $query); // Exibe o erro do MySQL e a consulta
}
$stmt->bind_param("ii", $id_parceiro, $num_pedido);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Pedido não encontrado.";
    exit;
}

$pedido = $result->fetch_assoc();
$stmt->close();

// Atualiza o total com base nos produtos confirmados, saldo usado e frete
$total_confirmado = 0;
$produtos_confirmados = isset($pedido['produtos_confirmados']) ? explode('|', $pedido['produtos_confirmados']) : [];
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

$total = $total_confirmado;
$frete = (float) $pedido['valor_frete'];
$saldo_usado = (float) $pedido['saldo_usado'];
$tipo_entrega = $pedido['tipo_entrega'];

if ($frete > 0 && $tipo_entrega == 'entregar') {
    $total += $frete;
}
if ($saldo_usado > 0) {
    $total -= $saldo_usado;
}

// Consulta para buscar os dados do cliente
$query_cliente = "SELECT * FROM meus_clientes WHERE id = ?";
$stmt_cliente = $mysqli->prepare($query_cliente);
if (!$stmt_cliente) {
    die("Erro ao preparar a consulta (cliente): " . $mysqli->error . " | Query: " . $query_cliente); // Exibe o erro do MySQL e a consulta
}
$stmt_cliente->bind_param("i", $pedido['id_cliente']);
$stmt_cliente->execute();
$result_cliente = $stmt_cliente->get_result();

if ($result_cliente->num_rows === 0) {
    echo "Cliente não encontrado.";
    exit;
}

$cliente = $result_cliente->fetch_assoc();
$primeiro_nome = explode(' ', $cliente['nome_completo'])[0];
$stmt_cliente->close();

// Consulta para buscar os dados do parceiro
$query_parceiro = "SELECT * FROM meus_parceiros WHERE id = ?";
$stmt_parceiro = $mysqli->prepare($query_parceiro);
if (!$stmt_parceiro) {
    die("Erro ao preparar a consulta (parceiro): " . $mysqli->error . " | Query: " . $query_parceiro); // Exibe o erro do MySQL e a consulta
}
$stmt_parceiro->bind_param("i", $id_parceiro);
$stmt_parceiro->execute();
$result_parceiro = $stmt_parceiro->get_result();

if ($result_parceiro->num_rows === 0) {
    echo "Parceiro não encontrado.";
    exit;
}

$loja = $result_parceiro->fetch_assoc();
$tempo_entrega = round($loja['estimativa_entrega'] / 60000) + 15; // Converte milissegundos para minutos e adiciona 15 minutos
$stmt_parceiro->close();

// Inicializa a variável $tempo_entrega_restante
$tempo_entrega_restante = null;

// Determina o maior status entre cliente e parceiro
$maior_status = max($pedido['status_parceiro'], $pedido['status_cliente']);

// Define os rótulos e cores para cada etapa do progresso
$progresso_etapas = [
    0 => ['label' => 'Pendente', 'color' => 'orange'],
    1 => ['label' => 'Confirmado', 'color' => 'green'],
    5 => ['label' => 'Pronto', 'color' => 'blue'],
    6 => ['label' => 'Saiu para entrega', 'color' => 'purple'],
    7 => ['label' => 'Finalizado', 'color' => 'green'],
    3 => ['label' => 'Cancelado', 'color' => 'red'],
    4 => ['label' => 'Recusado', 'color' => 'red']
];

// Calcula o tempo com base no status do pedido
if (in_array($maior_status, [3, 4, 7])) {
    // Pedido recusado, cancelado ou finalizado
    if (!empty($pedido['data']) && !empty($pedido['data_finalizacao'])) {
        $inicio = new DateTime($pedido['data']);
        $fim = new DateTime($pedido['data_finalizacao']);
        $duracao = $inicio->diff($fim);
        // Calcula o total de horas com base em dias + horas
        $horas_totais = ($duracao->days * 24) + $duracao->h;
        $tempo_entrega_restante = sprintf('%02d:%02d:%02d', $horas_totais, $duracao->i, $duracao->s);
    } else {
        $tempo_entrega_restante = "Dados de tempo indisponíveis.";
    }
} else {
    // Pedido em andamento
    if (!empty($pedido['data']) && $tempo_entrega > 0) {
        $pedido_time = new DateTime($pedido['data']);
        $pedido_time->modify("+{$tempo_entrega} minutes");
        $current_time = new DateTime();
        if ($current_time > $pedido_time) {
            $tempo_entrega_restante = "Tempo expirado";
        } else {
            $interval = $current_time->diff($pedido_time);
            $tempo_entrega_restante = $interval->format('%Hh %Im %Ss');
        }
    } else {
        $tempo_entrega_restante = "Dados de tempo indisponíveis.";
    }
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
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .button-container button {
            margin: 0;
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

        #cancelPopup textarea {
            width: 100%;
            height: 100px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 10px;
            resize: none;
            font-size: 14px;
            box-sizing: border-box;
            outline: none;
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
            color: #fff;
        }

        #cancelPopup button:first-of-type:hover {
            background-color: #5a6268;
        }

        #cancelPopup button:last-of-type {
            background-color: #dc3545;
            color: #fff;
        }

        #cancelPopup button:last-of-type:hover {
            background-color: #c82333;
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
        }

        .progress-container.active .progress-line {
            background: linear-gradient(to right, #28a745
                    <?php echo $maior_status == 0 ? '0%' : ($maior_status == 1 ? '25%' : ($maior_status == 5 ? '50%' : ($maior_status == 6 ? '75%' : '100%'))); ?>
                    ,
                    #ddd
                    <?php echo $maior_status == 0 ? '0%' : ($maior_status == 1 ? '25%' : ($maior_status == 5 ? '50%' : ($maior_status == 6 ? '75%' : '100%'))); ?>
                );
        }

        .progress-step {
            position: relative;
            z-index: 2;
            width: 15px;
            height: 15px;
            background-color: #ddd;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
            color: #fff;
        }

        .progress-step.active {
            background-color: #28a745;
        }

        .progress-label {
            position: absolute;
            top: -25px;
            font-size: 12px;
            color: #333;
            text-align: center;
        }

        .progress-label.active-label {
            font-weight: bold;
            color: #28a745;
        }

        /* Popup de confirmação de andamento */
        #popupConfirmar {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }

        #popupConfirmar .cancel {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
            background-color: #dc3545;
            color: #fff;
        }

        #popupConfirmar .cancel:hover {
            background-color: #c82333;
        }

        #popupConfirmar .confirm {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
            background-color: #28a745;
            color: #fff;
        }

        #popupConfirmar .confirm:hover {
            background-color: #218838;
        }

        #popupConfirmar>div {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            text-align: center;
            max-width: 350px;
            margin: auto;
        }

        #codigo-retirada-div {
            display: none;
        }

        #codigo-retirada-input {
            width: 100%;
            margin: 10px 0;
            padding: 8px;
            font-size: 16px;
            text-align: center;
        }

        /* Popup de mensagem */
        #mensagem-popup {
            display: none;
            position: fixed;
            top: 30%;
            left: 50%;
            transform: translateX(-50%);
            background: #222;
            color: #fff;
            padding: 18px 30px;
            border-radius: 8px;
            z-index: 3000;
            font-size: 18px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            min-width: 200px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Detalhes do Pedido</h1>
        <hr>
        <h3>Cliente: <span><?php echo htmlspecialchars($primeiro_nome); ?></span></h3>
        <h2>Pedido #<?php echo htmlspecialchars($num_pedido); ?></h2>
        <p><strong>Data do pedido:</strong> <?php echo formatDateTimeJS($pedido['data']); ?></p>
        <p><strong>Status do Pedido:</strong>
            <span style="color: <?php echo $maior_status == 0 ? 'orange' :
                ($maior_status == 1 ? 'green' :
                    ($maior_status == 5 ? 'green' :
                        ($maior_status == 6 ? 'green' :
                            ($maior_status == 7 ? 'green' : 'red')))); ?>;">
                <?php echo $maior_status == 0 ? 'Pendente' :
                    ($maior_status == 1 ? 'Confirmado e vai para preparação.' :
                        ($maior_status == 5 ? 'Pronto para entrega.' :
                            ($maior_status == 6 ? 'Saiu para entrega.' :
                                ($maior_status == 7 ? 'Finalizado.' : 'Cancelado.')))); ?>
            </span>
        </p>
        <hr>

        <h3 style="margin-bottom: 20px;">Andamento do Pedido</h3>
        <div class="progress-container <?php
        echo $maior_status == 0 ? 'pending' :
            ($maior_status == 3 || $maior_status == 4 ? 'cancelled' : 'active'); ?>">
            <div class="progress-line"></div>
            <div class="progress-step <?php echo $maior_status >= 0 ? 'active' : 'pending'; ?>">
                <span class="progress-label <?php echo $maior_status >= 0 ? 'active-label' : ''; ?>">Pendente</span>
            </div>
            <div class="progress-step <?php echo $maior_status >= 1 ? 'active' : ''; ?>">
                <span class="progress-label <?php echo $maior_status >= 1 ? 'active-label' : ''; ?>">Confirmado</span>
            </div>
            <div class="progress-step <?php echo $maior_status >= 5 ? 'active' : ''; ?>">
                <span class="progress-label <?php echo $maior_status >= 5 ? 'active-label' : ''; ?>">Pronto</span>
            </div>
            <div class="progress-step <?php echo $maior_status >= 6 ? 'active' : ''; ?>">
                <span class="progress-label <?php echo $maior_status >= 6 ? 'active-label' : ''; ?>">
                    <?php echo $tipo_entrega === 'entregar' ? 'Entregar' : 'Retirar'; ?>
                </span>
            </div>
            <div class="progress-step <?php echo $maior_status >= 7 ? 'active' : ''; ?>">
                <span class="progress-label <?php echo $maior_status >= 7 ? 'active-label' : ''; ?>">Entregou</span>
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
                <?php foreach ($produtos_confirmados_map as $nome => $produto): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($nome); ?></td>
                        <td><?php echo $produto['quantidade']; ?></td>
                        <td>R$ <?php echo number_format($produto['valor_unitario'], 2, ',', '.'); ?></td>
                        <td>R$ <?php echo number_format($produto['valor_total'], 2, ',', '.'); ?></td>
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
            <?php echo $tipo_entrega === 'entregar' ? htmlspecialchars($cliente['endereco'] . ', ' . $cliente['numero'] . ' - ' . $cliente['bairro'] . ', ' . $cliente['cidade'] . '/' . $cliente['uf'] . ', CEP: ' . $cliente['cep']) : 'Loja'; ?>
        </p>
        <p><strong>Contato:</strong> <?php echo htmlspecialchars($cliente['celular1']); ?></p>
        <?php if (!empty($pedido['comentario'])): ?>
            <p><strong>Comentário:</strong> <?php echo htmlspecialchars($pedido['comentario']); ?></p>
        <?php endif; ?>
        <p><strong>Tempo de Entrega:</strong>
            <span id="tempo-entrega" style="color: red;">
                <?php echo htmlspecialchars($tempo_entrega_restante); ?>
            </span>
        </p>
        <hr>
        <div class="button-container">
            <button onclick="javascript:history.back()">Voltar</button>
            <?php if (!in_array($maior_status, [3, 4, 7])): ?>
                <button class="confirm" onclick="abrirPopupConfirmar()">Continuar</button>
                <button class="cancel" onclick="cancelarPedido()">Cancelar Pedido</button>
            <?php endif; ?>
        </div>

        <!-- Popup de confirmação de andamento -->
        <div id="popupConfirmar">
            <div>
                <h3>Confirmação</h3>
                <p>Deseja realmente avançar o andamento do pedido?</p>
                <div id="codigo-retirada-div">
                    <label for="codigo-retirada-input"><strong>Código de Retirada do Cliente:</strong></label>
                    <input type="text" id="codigo-retirada-input" maxlength="6" placeholder="Informe o código" value="921943">
                </div>
                <div style="display: flex; justify-content: center; gap: 10px; margin-top: 15px;">
                    <button class="cancel" onclick="fecharPopupConfirmar()">Cancelar</button>
                    <button class="confirm" onclick="confirmarAndamento()">Confirmar</button>
                </div>
            </div>
        </div>

        <!-- Popup de mensagem -->
        <div id="mensagem-popup"></div>
    </div>
</body>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        atualizarTempoEntregaPedido(); // Chama ao carregar a página

        // Inicia a verificação automática do status do pedido a cada 3 segundos
        verificarStatusPedido();
        verificarStatusInterval = setInterval(verificarStatusPedido, 3000);

        // Chame também ao carregar a página para garantir consistência inicial
        atualizarBotoesStatus(<?php echo $maior_status; ?>);
    });

    function atualizarTempoEntregaPedido(status_final = null) {
        const tempoEntregaElement = document.getElementById('tempo-entrega');
        if (!tempoEntregaElement) return;

        // Usa status_final se passado, senão usa o inicial do PHP
        let statusPedido = status_final !== null ? status_final : <?php echo $maior_status; ?>;

        if ([3, 4, 7].includes(statusPedido)) {
            // Calcula o tempo decorrido para pedidos cancelados, recusados ou finalizados
            const dataInicio = new Date("<?php echo $pedido['data']; ?>");
            const dataFim = new Date("<?php echo $pedido['data_finalizacao'] ?? $pedido['data_cancelamento'] ?? $pedido['data_recusa']; ?>");

            if (!isNaN(dataInicio) && !isNaN(dataFim)) {
                const diffMs = Math.abs(dataFim - dataInicio);
                const horas = Math.floor(diffMs / (1000 * 60 * 60));
                const minutos = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
                const segundos = Math.floor((diffMs % (1000 * 60)) / 1000);

                tempoEntregaElement.innerHTML = `${horas.toString().padStart(2, '0')}:${minutos.toString().padStart(2, '0')}:${segundos.toString().padStart(2, '0')}`;
            } else {
                tempoEntregaElement.innerHTML = "Dados de tempo indisponíveis.";
            }
            return;
        }

        // Calcula o tempo restante para pedidos em andamento
        const dataPedido = new Date("<?php echo $pedido['data']; ?>");
        const tempoEntregaMinutos = <?php echo $tempo_entrega; ?>;
        const dataLimite = new Date(dataPedido.getTime() + tempoEntregaMinutos * 60000);
        const agora = new Date();

        if (isNaN(dataPedido) || isNaN(dataLimite)) {
            tempoEntregaElement.innerHTML = "Dados de tempo indisponíveis.";
            return;
        }

        let tempoRestante = Math.floor((dataLimite - agora) / 1000);
        let intervalId;

        function atualizarTempoEntrega() {
            if (tempoRestante <= 0) {
                tempoEntregaElement.innerHTML = "Tempo expirado";
                clearInterval(intervalId);
                return;
            }

            const horas = Math.floor(tempoRestante / 3600);
            const minutos = Math.floor((tempoRestante % 3600) / 60);
            const segundos = tempoRestante % 60;

            tempoEntregaElement.innerHTML = `${horas.toString().padStart(2, '0')}:${minutos.toString().padStart(2, '0')}:${segundos.toString().padStart(2, '0')}`;
            tempoRestante--;
        }

        atualizarTempoEntrega();
        intervalId = setInterval(atualizarTempoEntrega, 1000);
    }

    // Verificação automática do status do pedido a cada 3 segundos
    let verificarStatusInterval;

    function verificarStatusPedido() {
        fetch(`pedido_confirmado.php?ajax=1&id_parceiro=<?php echo $id_parceiro; ?>&num_pedido=<?php echo $num_pedido; ?>`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Se o pedido for cancelado pelo cliente ou parceiro (status 3 ou 4), redireciona
                    if (data.status_final === 3 || data.status_final === 4) {
                        clearInterval(verificarStatusInterval);
                        window.location.href = `pedido_cancelado.php?id_parceiro=<?php echo $id_parceiro; ?>&num_pedido=<?php echo $num_pedido; ?>`;
                    } else {
                        // Atualiza a barra de progresso e o status do pedido sem recarregar a página
                        atualizarBarraProgressoEStatus(data.status_final);
                        atualizarBotoesStatus(data.status_final);
                    }
                }
            })
            .catch(error => {
                // Opcional: pode exibir erro no console
                console.error('Erro ao verificar status do pedido:', error);
            });
    }

    function atualizarBarraProgressoEStatus(status_final) {
        // Atualiza classes dos steps
        const steps = document.querySelectorAll('.progress-step');
        const labels = document.querySelectorAll('.progress-label');
        steps.forEach((step, idx) => {
            // idx: 0=Pendente, 1=Confirmado, 2=Pronto, 3=Entregar/Retirar, 4=Entregou
            let ativo = false;
            if (idx === 0 && status_final >= 0) ativo = true;
            if (idx === 1 && status_final >= 1) ativo = true;
            if (idx === 2 && status_final >= 5) ativo = true;
            if (idx === 3 && status_final >= 6) ativo = true;
            if (idx === 4 && status_final >= 7) ativo = true;
            if (ativo) {
                step.classList.add('active');
                if (labels[idx]) labels[idx].classList.add('active-label');
            } else {
                step.classList.remove('active');
                if (labels[idx]) labels[idx].classList.remove('active-label');
            }
        });

        // Atualiza a barra de progresso (linha)
        const progressLine = document.querySelector('.progress-line');
        let percent = '0%';
        if (status_final >= 7) percent = '100%';
        else if (status_final >= 6) percent = '75%';
        else if (status_final >= 5) percent = '50%';
        else if (status_final >= 1) percent = '25%';
        progressLine.style.background = `linear-gradient(to right, #28a745 ${percent}, #ddd ${percent})`;

        // Atualiza o texto do status
        const statusSpan = document.querySelector('span[style*="color"]');
        if (statusSpan) {
            let cor = 'orange', texto = 'Pendente';
            if (status_final == 0) { cor = 'orange'; texto = 'Pendente'; }
            else if (status_final == 1) { cor = 'green'; texto = 'Confirmado e vai para preparação.'; }
            else if (status_final == 5) { cor = 'green'; texto = 'Pronto para entrega.'; }
            else if (status_final == 6) { cor = 'green'; texto = 'Saiu para entrega.'; }
            else if (status_final == 7) { cor = 'green'; texto = 'Finalizado.'; }
            else { cor = 'red'; texto = 'Cancelado.'; }
            statusSpan.style.color = cor;
            statusSpan.textContent = texto;
        }

        atualizarTempoEntregaPedido(status_final); // Atualiza o tempo de entrega conforme status
    }

    // Função para mostrar/ocultar botões conforme status
    function atualizarBotoesStatus(status_final) {
        const btnConfirm = document.querySelector('.button-container .confirm');
        const btnCancel = document.querySelector('.button-container .cancel');
        //console.log('Atualizando botões para status:', status_final);
        if (btnConfirm) {
            if ([3, 4, 7].includes(status_final)) {
                btnConfirm.style.display = 'none';
                btnCancel.style.display = 'none';
            } else {
                btnConfirm.style.display = 'block';
                btnCancel.style.display = 'block';
            }
        }
        if (btnCancel) {
            if ([3, 4, 7].includes(status_final)) {
                btnCancel.style.display = 'none';
                btnConfirm.style.display = 'none';
            } else {
                btnCancel.style.display = 'block';
                btnConfirm.style.display = 'block';
            }
        }
    }

    function abrirPopupConfirmar() {
        const popup = document.getElementById('popupConfirmar');
        if (popup) {
            popup.style.display = 'flex';
            // Exibe o campo do código apenas se for finalizar (novo_status == 7)
            var statusAtual = <?php echo (int) max($pedido['status_parceiro'], $pedido['status_cliente']); ?>;
            var novo_status = statusAtual;
            if (novo_status == 0) {
                novo_status = 1;
            } else if (novo_status == 1) {
                novo_status = 5;
            } else if (novo_status == 5) {
                novo_status = 6;
            } else if (novo_status == 6) {
                novo_status = 7;
            }
            if ((<?php echo json_encode($tipo_entrega); ?> === 'buscar' || <?php echo json_encode($tipo_entrega); ?> === 'entregar') && novo_status == 7) {
                document.getElementById('codigo-retirada-div').style.display = 'block';
            } else {
                document.getElementById('codigo-retirada-div').style.display = 'none';
            }
        }
    }

    function fecharPopupConfirmar() {
        const popup = document.getElementById('popupConfirmar');
        if (popup) popup.style.display = 'none';
        document.getElementById('codigo-retirada-div').style.display = 'none';
        document.getElementById('codigo-retirada-input').value = '';
    }

    function confirmarAndamento() {
        // Para retirada ou entrega, exige o código do cliente ao finalizar
        var statusAtual = <?php echo (int) max($pedido['status_parceiro'], $pedido['status_cliente']); ?>;
        var novo_status = statusAtual;
        if (novo_status == 0) {
            novo_status = 1;
        } else if (novo_status == 1) {
            novo_status = 5;
        } else if (novo_status == 5) {
            novo_status = 6;
        } else if (novo_status == 6) {
            novo_status = 7;
        }
        if (novo_status == 7) {
            const codigoInput = document.getElementById('codigo-retirada-input');
            const codigo = codigoInput.value.trim();
            if (!codigo || codigo.length < 4) {
                alert('Informe o código de retirada fornecido pelo cliente.');
                codigoInput.focus();
                return;
            }
        }

        // Envia requisição AJAX para avançar o status do pedido
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'atualizar_status_pedido.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                try {
                    var resp = JSON.parse(xhr.responseText);
                    if (xhr.status === 200 && resp.success) {
                        mostrarMensagemPopup(resp.message || 'Pedido atualizado com sucesso!', true);
                        fecharPopupConfirmar();
                        setTimeout(function () {
                            location.reload();
                        }, 3000);
                    } else {
                        mostrarMensagemPopup(resp.message || 'Erro ao atualizar pedido.', false);
                    }
                } catch (e) {
                    alert('Erro inesperado ao processar resposta.');
                }
            }
        };
        var params = 'id_parceiro=<?php echo $id_parceiro; ?>&num_pedido=<?php echo $num_pedido; ?>&novo_status=' + novo_status;
        if (novo_status == 7) {
            params += '&codigo_retirada=' + encodeURIComponent(document.getElementById('codigo-retirada-input').value.trim());
        }
        xhr.send(params);
    }

    function mostrarMensagemPopup(msg, sucesso) {
        var popup = document.getElementById('mensagem-popup');
        if (!popup) return;
        popup.textContent = msg;
        popup.style.background = sucesso ? '#28a745' : '#dc3545';
        popup.style.display = 'block';
        clearTimeout(popup._timeout);
        popup._timeout = setTimeout(function () {
            popup.style.display = 'none';
        }, 1500);
    }
</script>

</html>