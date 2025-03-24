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

// Fetch orders from the database
$query = "SELECT * FROM pedidos WHERE id_cliente = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $id);
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
    </style>
    <script>
        function redirectToDetails(num_pedido) {
            window.location.href = 'detalhes_pedido.php?num_pedido=' + num_pedido;
        }
    </script>
</head>
<body>
    <h1 class="title">Meus Pedidos</h1>
    <div class="cards-container">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="card" onclick="redirectToDetails('<?php echo htmlspecialchars($row['num_pedido']); ?>')">
                <h2>Pedido #<?php echo htmlspecialchars($row['num_pedido']); ?></h2>
                <?php
                    // Fetch partner details from the database
                    $id_parceiro = $row['id_parceiro'];

                    $query_parceiro = "SELECT logo, nomeFantasia FROM meus_parceiros WHERE id = ?";
                    $stmt_parceiro = $mysqli->prepare($query_parceiro);
                    $stmt_parceiro->bind_param("i", $id_parceiro);
                    $stmt_parceiro->execute();
                    $result_parceiro = $stmt_parceiro->get_result();
                    $loja = $result_parceiro->fetch_assoc();
                    $logo = $loja['logo'];
                    $nomeFantasia = $loja['nomeFantasia'];
                    $stmt_parceiro->close();

                ?>
                <p><strong>Status do Pedido:</strong> 
                    <?php 
                        $status = $row['status_cliente']; 
                        if ($status = 'anilize') {
                            echo "Aguardando confirmação";
                        } else if ($status == 1) {
                            echo "Pedido confirmado";
                        } else if ($status == 2) {
                            echo "Pedido cancelado";
                        } else {
                            echo "Status desconhecido";
                        }
                    ?>
                </p>
                <p><img src="../../parceiros/arquivos/<?php echo $logo;?>" alt="Logo"> <?php echo $nomeFantasia; ?></p>
                
                <p><strong>Data:</strong> <?php echo htmlspecialchars(formatDateTimeJS($row['data'])); ?></p>
                <p class="valor"><strong>Valor da compra: R$ </strong> <?php echo htmlspecialchars(number_format($row['valor'], 2, ',', '.')); ?></p>
            </div>
        <?php endwhile; ?>
    </div>
    <br>
    <a href="../cliente_home.php" class="btn-voltar">Voltar</a>
</body>
</html>

<?php
$stmt->close();
$mysqli->close();
?>
