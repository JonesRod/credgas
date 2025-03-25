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
// Obtém o ID do pedido enviado via POST
$num_pedido = $_POST['num_pedido'];
// Obtém o ID do cliente logado
$id_cliente = $_SESSION['id'];



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
    <h2>Pedido #<?php  ?></h2>
    <p><strong>Data do Pedido:</strong> <?php echo date('d/m/Y', strtotime($pedido['data'])); ?></p>
    <p><strong>Status:</strong> <?php echo $status['nome']; ?></p>
    <p><strong>Total:</strong> R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?></p>
    <h3>Produtos</h3>
    <ul>
        <?php foreach ($produtos as $produto): ?>
            <li><?php echo $produto['nome']; ?> - R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></li>
        <?php endforeach; ?>
    </ul>
    <h3>Endereço de Entrega</h3>
    <p><?php echo $endereco['rua']; ?>, <?php echo $endereco['numero']; ?> - <?php echo $endereco['bairro']; ?>, <?php echo $endereco['cidade']; ?> - <?php echo $endereco['estado']; ?>, <?php echo $endereco['cep']; ?></p>
    <h3>Pagamento</h3>
    <p><?php echo $pagamento['metodo']; ?> - R$ <?php echo number_format($pagamento['valor'], 2, ',', '.'); ?></p>
    <h3>Frete</h3>
    <p><?php echo $frete['tipo']; ?> - R$ <?php echo number_format($frete['valor'], 2, ',', '.'); ?></p>
    <h3>Cupom</h3>
    <p><?php if (!empty($cupom)) { echo $cupom['codigo'] . ' - R$ ' . number_format($cupom['desconto'], 2, ',', '.'); } else { echo 'Nenhum cupom aplicado'; } ?></p>
    <h3>Vendedor</h3>
    <ul>
        <?php foreach ($vendedor as $vend): ?>
            <li><?php echo $vend['nome']; ?></li>
        <?php endforeach; ?>
    </ul>
    <h3>Cliente</h3>
    <p><?php echo $cliente['nome']; ?> - <?php echo $cliente['email']; ?></p>
    <h3>Observações</h3>
    <p><?php echo $pedido['observacoes']; ?></p>
    <h3>Comentários</h3>
    <form action="adicionar_comentario.php" method="POST">
        <input type="hidden" name="id_pedido" value="<?php echo $pedido['id']; ?>">
        <textarea name="comentario" rows="4" cols="50" placeholder="Digite seu comentário..."></textarea>
        <br>
        <input type="submit" value="Adicionar Comentário">
    </form>
    <h3>Comentários Anteriores</h3>
    <ul>
        <?php
        // Obtém os comentários do pedido
        $sql = "SELECT * FROM comentarios WHERE id_pedido = '$id_pedido'";
        $result = mysqli_query($conexao, $sql);
        if (mysqli_num_rows($result) > 0) {
            while ($comentario = mysqli_fetch_assoc($result)) {
                echo '<li>' . $comentario['comentario'] . ' - ' . date('d/m/Y H:i:s', strtotime($comentario['data'])) . '</li>';
            }
        } else {
            echo '<li>Nenhum comentário encontrado.</li>';
        }
        ?>
    </ul>
    <h3>Alterar Status do Pedido</h3>
    <form action="alterar_status.php" method="POST">
        <input type="hidden" name="id_pedido" value="<?php echo $pedido['id']; ?>">
        <select name="status">
            <option value="1">Aguardando Pagamento</option>
            <option value="2">Em Processamento</option>
            <option value="3">Enviado</option>
            <option value="4">Entregue</option>
            <option value="5">Cancelado</option>
        </select>
        <br>
        <input type="submit" value="Alterar Status">
    </form>
    <h3>Cancelar Pedido</h3>
    <form action="cancelar_pedido.php" method="POST">
        <input type="hidden" name="id_pedido" value="<?php echo $pedido['id']; ?>">
        <input type="submit" value="Cancelar Pedido">
    </form>
    <h3>Voltar</h3>
    <form action="../../index.php" method="POST">
        <input type="submit" value="Voltar para a Página Inicial">
    </form>
</body>
</html>
<?php
// Fecha a conexão com o banco de dados
mysqli_close($conexao);
?>
<?php
// Adiciona o comentário ao banco de dados
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['comentario']) && !empty($_POST['comentario'])) {
        $id_pedido = $_POST['id_pedido'];
        $comentario = $_POST['comentario'];
        $sql = "INSERT INTO comentarios (id_pedido, comentario, data) VALUES ('$id_pedido', '$comentario', NOW())";
        mysqli_query($conexao, $sql);
    }
}
// Fecha a conexão com o banco de dados
mysqli_close($conexao);
?>
<?php
// Altera o status do pedido
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['status']) && !empty($_POST['status'])) {
        $id_pedido = $_POST['id_pedido'];
        $status = $_POST['status'];
        $sql = "UPDATE pedidos SET id_status = '$status' WHERE id = '$id_pedido'";
        mysqli_query($conexao, $sql);
    }
}
// Fecha a conexão com o banco de dados
mysqli_close($conexao);
?>
<?php
// Cancela o pedido
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['id_pedido']) && !empty($_POST['id_pedido'])) {
        $id_pedido = $_POST['id_pedido'];
        $sql = "UPDATE pedidos SET id_status = 5 WHERE id = '$id_pedido'";
        mysqli_query($conexao, $sql);
    }
}
// Fecha a conexão com o banco de dados
mysqli_close($conexao);
?>