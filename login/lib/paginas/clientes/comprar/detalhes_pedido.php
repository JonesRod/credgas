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

$id_parceiro = $pedido['id_parceiro'];
// Consulta para buscar os dados do parceiro
$query_parceiro = "SELECT * FROM meus_parceiros WHERE id = ?";
$stmt_parceiro = $mysqli->prepare($query_parceiro);
$stmt_parceiro->bind_param("i", $id_parceiro);
$stmt_parceiro->execute();
$result_parceiro = $stmt_parceiro->get_result();
if ($result_parceiro->num_rows > 0) {
    $parceiro = $result_parceiro->fetch_assoc();
} else {
    echo "Parceiro não encontrado.";
    exit;
}
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
    <h2>Pedido #<?php echo $num_pedido; ?></h2>
    <p><strong>Data do Pedido:</strong> <?php echo date('d/m/Y', strtotime($pedido['data'])); ?></p>
    <p><strong>Status:</strong> 
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
    <h3>Forma de Entrega</h3>

    <input type="hidden" id="tipo_entrega" name="tipo_entrega" 
        value="<?php             
            if ($pedido['tipo_entrega'] == 'entregar') {
                echo "Entregar em casa.";
            } elseif ($pedido['tipo_entrega'] == 'buscar') {
                echo "Retirar no local.";
            } else {
                echo "Retirar na loja.";
            }?>" 
        readonly>

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
    <p>COMENTÁRIO: <?php echo $pedido['comentario']; ?></p>

    <h3>Voltar</h3>
    <p><a href="javascript:history.back()">Voltar para a página anterior</a></p>
</body>
</html>
<?php

?>