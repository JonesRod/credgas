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

// Inicia o buffer de saída para evitar saída acidental
ob_start();

// Certifique-se de que não há saída HTML antes de processar o conteúdo
if (headers_sent()) {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['erro' => 'Saída inesperada detectada.']);
    exit;
}

// Função para formatar datas no formato "d/m/Y H:i"
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

// Fetch filters from GET parameters
$num_pedido = isset($_GET['num_pedido']) ? filter_var($_GET['num_pedido'], FILTER_SANITIZE_NUMBER_INT) : '';
$data = isset($_GET['data']) ? filter_var($_GET['data'], FILTER_SANITIZE_STRING) : '';
$status = isset($_GET['status']) ? filter_var($_GET['status'], FILTER_SANITIZE_NUMBER_INT) : '';

// Construção da consulta SQL corrigida para incluir a logo e o nome da loja
$query = "SELECT p.*, GREATEST(p.status_cliente, p.status_parceiro) AS status_final, mp.logo, mp.nomeFantasia 
          FROM pedidos p 
          JOIN meus_parceiros mp ON p.id_parceiro = mp.id 
          WHERE p.id_cliente = ?";
$params = [$id];
$types = "i";

if (!empty($num_pedido)) {
    $query .= " AND p.num_pedido = ?";
    $params[] = $num_pedido;
    $types .= "i";
}

if (!empty($data)) {
    $query .= " AND DATE(p.data) = ?";
    $params[] = $data;
    $types .= "s";
}

if ($status !== '' && is_numeric($status)) {
    $query .= " AND GREATEST(p.status_cliente, p.status_parceiro) = ?";
    $params[] = $status;
    $types .= "i";
}

$query .= " ORDER BY p.num_pedido DESC";
$stmt = $mysqli->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Certifique-se de que não há saída HTML antes de processar o conteúdo
if (headers_sent()) {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['erro' => 'Saída inesperada detectada.']);
    exit;
}

// Agrupamento por data
$hoje = date('Y-m-d');
$ontem = date('Y-m-d', strtotime('-1 day'));

$pedidosAgrupados = [
    'Hoje' => [],
    'Ontem' => [],
    'Outros' => [],
];

while ($row = $result->fetch_assoc()) {
    $dataPedido = date('Y-m-d', strtotime($row['data']));
    if ($dataPedido == $hoje) {
        $pedidosAgrupados['Hoje'][] = $row;
    } elseif ($dataPedido == $ontem) {
        $pedidosAgrupados['Ontem'][] = $row;
    } else {
        $dataFormatada = date('d/m/Y', strtotime($row['data']));
        if (!isset($pedidosAgrupados['Outros'][$dataFormatada])) {
            $pedidosAgrupados['Outros'][$dataFormatada] = [];
        }
        $pedidosAgrupados['Outros'][$dataFormatada][] = $row;
    }
}

$stmt->close();
$mysqli->close();

// Limpa qualquer saída acidental antes de renderizar o HTML
if (ob_get_length()) {
    ob_end_clean();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Meus Pedidos</title>
    <style>
        /* Container principal dos cards */
        .cards-container {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            justify-content: center;
        }

        /* Estilo dos cards */
        .card {
            flex: 1 1 calc(25% - 10px);
            max-width: auto;
            min-width: 180px;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
            cursor: pointer;
            margin: 5px;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        /* Cores dos cards por status */
        .card.status-0 {
            background-color: #ffcc80;
        }

        .card.status-0:hover {
            background-color: #ffb74d;
        }

        .card.status-1 {
            background-color: #c8e6c9;
        }

        .card.status-1:hover {
            background-color: #a5d6a7;
        }

        .card.status-2,
        .card.status-3 {
            background-color: #ffcdd2;
        }

        .card.status-2:hover,
        .card.status-3:hover {
            background-color: #ef9a9a;
        }

        /* Estilo do texto nos cards */
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

        /* Botão voltar */
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

        /* Título principal */
        .title {
            text-align: center;
            font-size: 2em;
            margin-bottom: 20px;
            color: #333;
        }

        /* Filtros */
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

        /* Responsividade */
        @media (max-width: 768px) {
            .card {
                flex: 1 1 calc(50% - 10px);
            }
        }

        @media (max-width: 480px) {
            .card {
                flex: 1 1 100%;
            }
        }

        @media (max-width: 600px) {
            .filters {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 10px;
            }

            .filters input,
            .filters select,
            .filters button,
            .btn-voltar {
                width: 100%;
                max-width: 600px;
                box-sizing: border-box;
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
            }

            .filters input,
            .filters select,
            .filters button,
            .btn-voltar {
                width: 100%;
                max-width: 300px;
                box-sizing: border-box;
            }

            .filters form {
                width: 100%;
                display: flex;
                flex-direction: column;
                align-items: center;
            }
        }

        hr {
            border: 1px solid #ccc;
            width: 100%;
            margin: 5px 0;
            /* Reduz a margem superior e inferior */
        }

        h3 {
            text-align: left;
            margin: 5px 10px;
            /* Reduz a margem superior e lateral */
            font-size: 1.2em;
            /* Ajusta o tamanho da fonte */
        }
    </style>
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

        document.addEventListener('DOMContentLoaded', function () {
            const countdownElements = document.querySelectorAll('.countdown');

            // Limpa o conteúdo dos elementos countdown
            countdownElements.forEach(function (element) {
                element.innerHTML = "";
            });

            // Garante que os elementos estejam inicialmente ocultos
            document.querySelectorAll('.text-cancelar').forEach(function (element) {
                if (element) {
                    element.style.display = "none";
                }
            });
        });
    </script>
</head>

<body>
    <h1 class="title">Meus Pedidos</h1>
    <div class="filters">
        <form method="GET">
            <a href="../cliente_home.php" class="btn-voltar">Voltar</a>
            <input type="date" name="data" value="<?php echo htmlspecialchars($data); ?>">
            <select name="status">
                <option value="">Todos os Status</option>
                <option value="0" <?php if ($status === 0 || $status === "0")
                    echo 'selected'; ?>>Aguardando confirmação
                </option>
                <option value="1" <?php if ($status === 1 || $status === "1")
                    echo 'selected'; ?>>Pedido confirmado</option>
                <option value="3" <?php if ($status === 3 || $status === "3")
                    echo 'selected'; ?>>Pedido recusado</option>
                <option value="4" <?php if ($status === 4 || $status === "4")
                    echo 'selected'; ?>>Pedido cancelado</option>
                <option value="5" <?php if ($status === 5 || $status === "5")
                    echo 'selected'; ?>>Pedido Pronto</option>
                <option value="6" <?php if ($status === 6 || $status === "6")
                    echo 'selected'; ?>>Saiu para entrega</option>
                <option value="7" <?php if ($status === 7 || $status === "7")
                    echo 'selected'; ?>>Finalizado</option>
            </select>
            <input type="text" name="num_pedido" placeholder="Número do Pedido"
                value="<?php echo htmlspecialchars($num_pedido); ?>">
            <button type="submit">Filtrar</button>
            <button type="button" onclick="window.location.href='meus_pedidos.php'">Carregar Todos</button>
        </form>
    </div>
    <div class="cards-container" style="flex-direction: column; align-items: flex-start;">
        <?php
        $temPedidos = false;
        // Renderiza os pedidos agrupados
        foreach (['Hoje', 'Ontem'] as $grupo) {
            if (!empty($pedidosAgrupados[$grupo])) {
                $temPedidos = true;
                echo "<hr style='border: 1px solid #ccc; width: 100%;'>"; // Linha horizontal para separar os grupos
                echo "<div style='margin-bottom: 20px; width: 100%;'>"; // Adiciona margem entre os grupos
                echo "<h3 style='text-align: left; margin-left: 10px;'>$grupo</h3>"; // Alinha o título do grupo à esquerda
                echo "<div class='cards-container' style='justify-content: flex-start;'>"; // Container para os pedidos
                foreach ($pedidosAgrupados[$grupo] as $pedido) {
                    $status_final = $pedido['status_final'];
                    $total = $pedido['valor_produtos'] + $pedido['valor_frete'] - $pedido['saldo_usado'] + $pedido['taxa_crediario'];
                    ?>
                    <div class="card status-<?php echo $status_final; ?>"
                        onclick="redirectToDetails('<?php echo $pedido['num_pedido']; ?>', '<?php echo $pedido['id_parceiro']; ?>', '<?php echo $status_final; ?>', '<?php echo $pedido['data']; ?>', '<?php echo $total; ?>')"
                        style="background-color: <?php
                        echo $status_final === 0 ? '#ffcc80' : // Laranja para Aguardando confirmação
                            (in_array($status_final, [1, 5, 6, 7]) ? '#c8e6c9' : // Verde para Pedido confirmado, Pronto, Saiu para entrega e Finalizado
                                (in_array($status_final, [3, 4]) ? '#ffcdd2' : 'inherit')); // Vermelho para Recusado e Cancelado
                        ?>;">
                        <div class="store-info">
                            <img src="<?php echo '../../parceiros/arquivos/' . htmlspecialchars($pedido['logo']); ?>"
                                alt="Logo da Loja">
                            <span><?php echo htmlspecialchars($pedido['nomeFantasia']); ?></span>
                        </div>
                        <h2>Pedido #<?php echo htmlspecialchars($pedido['num_pedido']); ?></h2>
                        <p><strong>Status:</strong>
                            <span style="color: <?php
                            echo $status_final === 0 ? '#ff5722' : // Laranja para Aguardando confirmação
                                (in_array($status_final, [1, 5, 6, 7]) ? '#c8e6c9' : // Verde para Pedido confirmado, Pronto, Saiu para entrega e Finalizado
                                    (in_array($status_final, [3, 4]) ? 'red' : 'black')); // Vermelho para Recusado e Cancelado
                            ?>">
                                <?php
                                if ($status_final == 0) {
                                    echo "Aguardando confirmação";
                                } else if ($status_final == 1) {
                                    echo "Pedido confirmado";
                                } else if ($status_final == 3) {
                                    echo "Pedido recusado";
                                } else if ($status_final == 4) {
                                    echo "Pedido Cancelado";
                                } else if ($status_final == 5) {
                                    echo "Pedido Pronto";
                                } else if ($status_final == 6) {
                                    echo "Saiu para entrega";
                                } else if ($status_final == 7) {
                                    echo "Finalizado";
                                } else {
                                    echo "Status desconhecido";
                                }
                                ?>
                            </span>
                        </p>
                        <p><strong>Data:</strong> <?php echo htmlspecialchars(formatDateTimeJS($pedido['data'])); ?></p>
                        <p class="valor"><strong>Valor da compra: R$ </strong>
                            <?php echo htmlspecialchars(number_format($total, 2, ',', '.')); ?></p>
                    </div>
                    <?php
                }
                echo "</div>"; // Fecha o container dos pedidos
                echo "</div>"; // Fecha o grupo
            }
        }

        // Renderiza os pedidos agrupados por data no grupo "Outros"
        if (!empty($pedidosAgrupados['Outros'])) {
            foreach ($pedidosAgrupados['Outros'] as $dataEspecifica => $listaPedidos) {
                $temPedidos = true;
                echo "<hr style='border: 1px solid #ccc; width: 100%;'>"; // Linha horizontal para separar as datas
                echo "<div style='margin-bottom: 20px; width: 100%;'>"; // Adiciona margem entre os grupos
                echo "<h3 style='text-align: left; margin-left: 10px;'>$dataEspecifica</h3>"; // Alinha a data à esquerda
                echo "<div class='cards-container' style='justify-content: flex-start;'>"; // Container para os pedidos
                foreach ($listaPedidos as $pedido) {
                    $status_final = $pedido['status_final'];
                    $total = $pedido['valor_produtos'] + $pedido['valor_frete'] - $pedido['saldo_usado'] + $pedido['taxa_crediario'];
                    ?>
                    <div class="card status-<?php echo $status_final; ?>"
                        onclick="redirectToDetails('<?php echo $pedido['num_pedido']; ?>', '<?php echo $pedido['id_parceiro']; ?>', '<?php echo $status_final; ?>', '<?php echo $pedido['data']; ?>', '<?php echo $total; ?>')"
                        style="background-color: <?php
                        echo $status_final === 0 ? '#ffcc80' : // Laranja para Aguardando confirmação
                            (in_array($status_final, [1, 5, 6, 7]) ? '#c8e6c9' : // Verde para Pedido confirmado, Pronto, Saiu para entrega e Finalizado
                                (in_array($status_final, [3, 4]) ? '#ffcdd2' : 'inherit')); // Vermelho para Recusado e Cancelado
                        ?>;">
                        <div class="store-info">
                            <img src="<?php echo '../../parceiros/arquivos/' . htmlspecialchars($pedido['logo']); ?>"
                                alt="Logo da Loja">
                            <span><?php echo htmlspecialchars($pedido['nomeFantasia']); ?></span>
                        </div>
                        <h2>Pedido #<?php echo htmlspecialchars($pedido['num_pedido']); ?></h2>
                        <p><strong>Status:</strong>
                            <span style="color: <?php
                            echo $status_final === 0 ? '#ff5722' : // Laranja para Aguardando confirmação
                                (in_array($status_final, [1, 5, 6, 7]) ? '#c8e6c9' : // Verde para Pedido confirmado, Pronto, Saiu para entrega e Finalizado
                                    (in_array($status_final, [3, 4]) ? 'red' : 'black')); // Vermelho para Recusado e Cancelado
                            ?>">
                                <?php
                                if ($status_final == 0) {
                                    echo "Aguardando confirmação";
                                } else if ($status_final == 1) {
                                    echo "Pedido confirmado";
                                } else if ($status_final == 3) {
                                    echo "Pedido recusado";
                                } else if ($status_final == 4) {
                                    echo "Pedido Cancelado";
                                } else if ($status_final == 5) {
                                    echo "Pedido Pronto";
                                } else if ($status_final == 6) {
                                    echo "Saiu para entrega";
                                } else if ($status_final == 7) {
                                    echo "Finalizado";
                                } else {
                                    echo "Status desconhecido";
                                }
                                ?>
                            </span>
                        </p>
                        <p><strong>Data:</strong> <?php echo htmlspecialchars(formatDateTimeJS($pedido['data'])); ?></p>
                        <p class="valor"><strong>Valor da compra: R$ </strong>
                            <?php echo htmlspecialchars(number_format($total, 2, ',', '.')); ?></p>
                    </div>
                    <?php
                }
                echo "</div>"; // Fecha o container dos pedidos
                echo "</div>"; // Fecha o grupo
            }
        }

        if (!$temPedidos) {
            echo "<div style='display: flex; justify-content: center; align-items: center; height: calc(100vh - 400px); margin-top: -50px; width: 100%; color: #555; font-size: 1.2em; text-align: center;'>
                    Nenhum pedido encontrado para os filtros aplicados.
                  </div>";
        }
        ?>
    </div>
    <br>
</body>
<script>
    window.addEventListener('load', () => {
        const scrollY = localStorage.getItem('scrollY');
        if (scrollY !== null) {
            window.scrollTo(0, parseInt(scrollY));
            localStorage.removeItem('scrollY'); // limpa depois de usar
        }
    });

    setInterval(() => {
        const scrollPosition = window.scrollY;
        localStorage.setItem('scrollY', scrollPosition);
        location.reload();
    }, 3000); // 3000 milissegundos = 3 segundos
</script>

</html>