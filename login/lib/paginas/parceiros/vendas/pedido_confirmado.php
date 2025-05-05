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

// Calculate end time for countdown
$pedido_time = new DateTime($pedido['data']);
$pedido_time->modify('+15 minutes');
$end_time = $pedido_time->format('Y-m-d H:i:s');

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
$tempo_entrega = round($loja['estimativa_entrega'] / 60000); // Converte milissegundos para minutos
$stmt_parceiro->close();

// Inicializa a variável $tempo_entrega_restante
$tempo_entrega_restante = null;

// Determina o maior status entre cliente e parceiro
$maior_status = max($pedido['status_parceiro'], $pedido['status_cliente']);

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
            /* Alinha os botões em linha */
            justify-content: center;
            /* Centraliza os botões */
            flex-wrap: wrap;
            /* Permite quebra de linha se necessário */
            gap: 10px;
            /* Espaçamento entre os botões */
        }

        .button-container button {
            margin: 0;
            /* Remove margens extras */
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
            /* Aumentar a altura para melhor visibilidade */
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 10px;
            resize: none;
            /* Impedir redimensionamento */
            font-size: 14px;
            /* Ajustar o tamanho da fonte */
            box-sizing: border-box;
            /* Garantir que padding não afete o tamanho total */
            outline: none;
            /* Remover borda azul ao focar */
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
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0;
            padding: 40px 30px 20px 30px;
            border: 2px solid #ccc;
            /* Adiciona uma borda */
            border-radius: 8px;
            /* Bordas arredondadas */
            background-color: #f9f9f9;
            /* Fundo claro */
        }

        .progress-line {
            position: absolute;
            top: 63%;
            left: 33px;
            /* Alinha com o primeiro ponto */
            right: 33px;
            /* Alinha com o último ponto */
            height: 4px;
            background-color: #ddd;
            z-index: 1;
            transform: translateY(-50%);
        }

        .progress-container.active .progress-line {
            background: linear-gradient(to right, #28a745
                    <?php echo $pedido['status_parceiro'] == 0 ? '0%' : ($pedido['status_parceiro'] == 1 ? '25%' : ($pedido['status_parceiro'] == 5 ? '50%' : ($pedido['status_parceiro'] == 6 ? '75%' : '100%'))); ?>
                    ,
                    #ddd
                    <?php echo $pedido['status_parceiro'] == 0 ? '0%' : ($pedido['status_parceiro'] == 1 ? '25%' : ($pedido['status_parceiro'] == 5 ? '50%' : ($pedido['status_parceiro'] == 6 ? '75%' : '100%'))); ?>
                );
        }

        .progress-container.pending::before {
            background-color: #ffc107;
            /* Laranja */
        }

        .progress-container.cancelled::before {
            background-color: #dc3545;
            /* Vermelho */
        }

        .progress-step {
            position: relative;
            z-index: 2;
            width: 15px;
            height: 15px;
            background-color: #ddd;
            /* Cor padrão */
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
            color: #fff;
        }

        .progress-step.active {
            background-color: #28a745;
            /* Verde para etapas concluídas */
        }

        .progress-step.pending {
            background-color: #ffc107;
            /* Laranja para pendente */
        }

        .progress-step.cancelled {
            background-color: #dc3545;
            /* Vermelho para cancelado */
        }

        .progress-label {
            position: absolute;
            top: -25px;
            font-size: 12px;
            color: #333;
            /* Cor padrão */
            text-align: center;
        }

        .progress-label.active-label {
            font-weight: bold;
            color: #28a745;
            /* Verde para rótulos ativos */
        }

        #popupCodigoRetirada {
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

        #popupCodigoRetirada div {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            width: 300px;
        }

        #popupCodigoRetirada input {
            width: 90%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            autocomplete: off;
        }

        #popupCodigoRetirada span {
            display: block;
            margin-bottom: 10px;
            color: red;
        }

        #popupCodigoRetirada button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        #popupCodigoRetirada button:first-of-type {
            background-color: #6c757d;
            color: #fff;
        }

        #popupCodigoRetirada button:first-of-type:hover {
            background-color: #5a6268;
        }

        #popupCodigoRetirada button:last-of-type {
            background-color: #28a745;
            color: #fff;
        }

        #popupCodigoRetirada button:last-of-type:hover {
            background-color: #218838;
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
                font-size: 14px;
                /* Reduz o tamanho da fonte */
                padding: 8px 16px;
                /* Ajusta o padding */
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
                font-size: 12px;
                /* Reduz ainda mais o tamanho da fonte */
                padding: 6px 12px;
                /* Ajusta o padding para telas muito pequenas */
            }

            .container {
                padding: 8px;
            }

            .progress-label {
                font-size: 8px;
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
            <span style="color: <?php
            $maior_status = max($pedido['status_parceiro'], $pedido['status_cliente']);
            echo $maior_status == 0 ? 'orange' :
                ($maior_status == 1 ? 'green' :
                    ($maior_status == 5 ? 'blue' :
                        ($maior_status == 6 ? 'purple' :
                            ($maior_status == 7 ? 'green' : 'red')))); ?>;">
                <?php
                echo $maior_status == 0 ? 'Pendente' :
                    ($maior_status == 1 ? 'Confirmado e vai para preparação.' :
                        ($maior_status == 5 ? 'Pronto para entrega.' :
                            ($maior_status == 6 ? 'Saiu para entrega.' :
                                ($maior_status == 7 ? 'Finalizado.' : 'Cancelado.')))); ?>
            </span>
        </p>
        <hr>

        <h3 style="margin-bottom: 20px;">Andamento do Pedido</h3>
        <div class="progress-container <?php
        $maior_status = max($pedido['status_parceiro'], $pedido['status_cliente']);
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
        <p><strong>Tempo de Entrega:</strong>
            <span id="tempo-entrega" style="color: red;">
                <?php echo htmlspecialchars($tempo_entrega_restante); ?>
            </span>
        </p>

        <hr>
        <div class="button-container">
            <button onclick="javascript:history.back()">Voltar</button>
            <?php if (!in_array($maior_status, [3, 4, 7])): ?>
                <button class="cancel" onclick="cancelarPedido()">Cancelar Pedido</button>
            <?php endif; ?>
            <?php if ($maior_status == 1): ?>
                <button class="pronto" id="btnProntoParaEntregar" onclick="marcarProntoParaEntregar()">Pronto</button>
            <?php endif; ?>
            <?php if ($maior_status == 5): ?>
                <button class="confirm" onclick="atualizarStatusPedido(6)">
                    <?php echo $tipo_entrega === 'entregar' ? 'Sair para Entregar' : 'Pronto para Retirada'; ?>
                </button>
            <?php endif; ?>
            <?php if ($maior_status == 6): ?>
                <button class="finalizar" onclick="abrirPopupCodigoRetirada()">Finalizar Entrega</button>
            <?php endif; ?>
        </div>
    </div>

    <div id="cancelPopup">
        <div>
            <h3>Confirmar Cancelamento</h3>
            <p>Tem certeza de que deseja cancelar este pedido?</p>
            <textarea id="motivoCancelamento" placeholder="Informe o motivo do cancelamento" required></textarea>
            <button onclick="fecharPopup()">Voltar</button>
            <button onclick="confirmarCancelamento()">Confirmar Cancelamento</button>
        </div>
    </div>

    <div id="popupCodigoRetirada">
        <div>
            <h3>Confirmar Entrega</h3>
            <p>Digite o código de retirada fornecido pelo cliente:</p>
            <input type="text" id="codigoRetirada" placeholder="Código de Retirada" autocomplete="new-password">
            <span id="popupMensagem"></span>
            <button onclick="fecharPopupCodigoRetirada()">Cancelar</button>
            <button onclick="confirmarEntrega()">Confirmar</button>
        </div>
    </div>
</body>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tempoEntregaElement = document.getElementById('tempo-entrega');

        // Verifica se o elemento existe antes de acessar suas propriedades
        if (tempoEntregaElement) {
            const statusPedido = <?php echo $maior_status; ?>; // Status do pedido

            if ([3, 4, 7].includes(statusPedido)) {
                // Calcula o tempo decorrido para pedidos cancelados, recusados ou finalizados
                const dataInicio = new Date("<?php echo $pedido['data']; ?>");
                const dataFim = new Date("<?php echo $pedido['data_finalizacao'] ?? $pedido['data_cancelamento'] ?? $pedido['data_recusa']; ?>");

                if (!isNaN(dataInicio) && !isNaN(dataFim)) {
                    const diffMs = Math.abs(dataFim - dataInicio); // Diferença em milissegundos
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

            let tempoRestante = Math.floor((dataLimite - agora) / 1000); // Tempo restante em segundos
            let intervalId; // Declaração da variável intervalId

            function atualizarTempoEntrega() {
                if (tempoRestante <= 0) {
                    tempoEntregaElement.innerHTML = "Tempo expirado";
                    clearInterval(intervalId); // Para o cronômetro quando o tempo expira
                    return;
                }

                const horas = Math.floor(tempoRestante / 3600);
                const minutos = Math.floor((tempoRestante % 3600) / 60);
                const segundos = tempoRestante % 60;

                tempoEntregaElement.innerHTML = `${horas.toString().padStart(2, '0')}:${minutos.toString().padStart(2, '0')}:${segundos.toString().padStart(2, '0')}`;

                tempoRestante--;
            }

            atualizarTempoEntrega();
            intervalId = setInterval(atualizarTempoEntrega, 1000); // Inicializa intervalId após a declaração
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
        const popupContent = document.querySelector('#cancelPopup div');
        const botaoVoltar = popupContent.querySelector('button:first-of-type');
        const mensagem = document.createElement('p');
        mensagem.style.marginBottom = '10px';
        mensagem.style.fontSize = '14px';
        mensagem.style.fontWeight = 'bold';
        mensagem.style.color = 'red';

        // Captura a data e hora local do cliente no formato correto para MySQL
        const now = new Date();
        const dataHoraCliente = now.toISOString().slice(0, 19).replace('T', ' '); // Exemplo: "2025-05-05 02:49:22"

        // Remove mensagens anteriores, se existirem
        const mensagensExistentes = popupContent.querySelectorAll('p.mensagem');
        mensagensExistentes.forEach(msg => msg.remove());

        if (!motivo) {
            mensagem.textContent = 'Por favor, informe o motivo do cancelamento.';
            mensagem.classList.add('mensagem');
            popupContent.insertBefore(mensagem, botaoVoltar);
            return;
        }

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'cancelar_pedido.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        // Log dos dados enviados
        const dadosEnviados = `num_pedido=<?php echo $num_pedido; ?>&id_parceiro=<?php echo $id_parceiro; ?>&motivo_cancelamento=${encodeURIComponent(motivo)}&data_hora_cliente=${encodeURIComponent(dataHoraCliente)}`;
        console.log("Dados enviados:", dadosEnviados);

        xhr.onload = function () {
            console.log("Resposta do servidor:", xhr.responseText); // Log da resposta do servidor
            try {
                const response = JSON.parse(xhr.responseText);
                if (xhr.status === 200 && response.success) {
                    mensagem.textContent = 'Pedido cancelado com sucesso!';
                    mensagem.style.color = 'green';
                    mensagem.classList.add('mensagem');
                    popupContent.insertBefore(mensagem, botaoVoltar);

                    setTimeout(() => {
                        window.location.href = 'pedido_cancelado.php';
                    }, 2000);
                } else {
                    mensagem.textContent = response.message || 'Erro ao cancelar o pedido.';
                    mensagem.classList.add('mensagem');
                    popupContent.insertBefore(mensagem, botaoVoltar);
                }
            } catch (e) {
                console.error("Erro ao processar a resposta do servidor:", e); // Log do erro no console
                mensagem.textContent = 'Erro inesperado: resposta inválida do servidor.';
                mensagem.classList.add('mensagem');
                popupContent.insertBefore(mensagem, botaoVoltar);
            }
        };
        xhr.onerror = function () {
            mensagem.textContent = 'Erro de conexão com o servidor.';
            mensagem.classList.add('mensagem');
            popupContent.insertBefore(mensagem, botaoVoltar);
        };
        xhr.send(dadosEnviados);
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

    function marcarProntoParaEntregar() {
        if (confirm("Confirma que o pedido está pronto para entrega?")) {
            const btn = document.getElementById('btnProntoParaEntregar');
            btn.style.display = 'none'; // Esconde o botão imediatamente

            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'atualizar_status_pedido.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                if (xhr.status === 200) {
                    alert('Status atualizado para "Pronto para entrega".');
                    location.reload(); // Atualiza a página
                } else {
                    alert('Erro ao atualizar o status.');
                    btn.style.display = 'inline-block'; // Mostra o botão novamente em caso de erro
                }
            };
            xhr.send('num_pedido=<?php echo $num_pedido; ?>&novo_status=5');
        }
    }

    function atualizarStatusPedido(novoStatus) {
        if (confirm("Confirma a atualização do status do pedido?")) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'atualizar_status_pedido.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                if (xhr.status === 200) {
                    alert('Status atualizado com sucesso.');
                    location.reload(); // Atualiza a página
                } else {
                    alert('Erro ao atualizar o status.');
                }
            };
            xhr.send('num_pedido=<?php echo $num_pedido; ?>&novo_status=' + novoStatus);
        }
    }

    function finalizarEntrega() {
        if (confirm("Confirma que a entrega foi finalizada?")) {
            // Captura a data e hora local do cliente
            const now = new Date();
            const dataHoraCliente = now.toISOString().slice(0, 19); // Exemplo: "2025-05-04T22:47:27"

            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'atualizar_status_pedido.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                if (xhr.status === 200) {
                    alert('Entrega finalizada com sucesso.');
                    location.reload();
                } else {
                    alert('Erro ao finalizar a entrega.');
                }
            };
            xhr.send('num_pedido=<?php echo $num_pedido; ?>&novo_status=7&data_hora_cliente=' + encodeURIComponent(dataHoraCliente));
        }
    }

    function abrirPopupCodigoRetirada() {
        document.getElementById('popupCodigoRetirada').style.display = 'flex';
        document.getElementById('codigoRetirada').value = ''; // Limpa o campo de entrada
        document.getElementById('popupMensagem').textContent = ''; // Limpa a mensagem
    }

    function fecharPopupCodigoRetirada() {
        document.getElementById('popupCodigoRetirada').style.display = 'none';
    }

    function confirmarEntrega() {
        const codigo = document.getElementById('codigoRetirada').value.trim();
        const mensagem = document.getElementById('popupMensagem');
        mensagem.style.color = 'red'; // Define a cor padrão como vermelho

        if (!codigo) {
            mensagem.textContent = 'Por favor, insira o código de retirada.';
            return;
        }

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'atualizar_status_pedido.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            try {
                // Verifica se a resposta é um JSON válido
                const response = JSON.parse(xhr.responseText);
                if (xhr.status === 200 && response.success) {
                    mensagem.style.color = 'green';
                    mensagem.textContent = 'Entrega finalizada com sucesso.';
                    setTimeout(() => location.reload(), 2000); // Atualiza a página após 2 segundos
                } else {
                    mensagem.textContent = response.message || 'Erro ao finalizar a entrega.';
                }
            } catch (e) {
                // Exibe uma mensagem de erro caso o JSON seja inválido
                mensagem.textContent = 'Erro inesperado: resposta inválida do servidor.';
            }
        };
        xhr.onerror = function () {
            mensagem.textContent = 'Erro de conexão com o servidor.';
        };
        xhr.send('num_pedido=<?php echo $num_pedido; ?>&novo_status=7&codigo_retirada=' + encodeURIComponent(codigo));
    }
</script>

</html>