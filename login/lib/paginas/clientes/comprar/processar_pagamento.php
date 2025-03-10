<?php
    session_start();
    include('../../../conexao.php'); // Conexão com o banco

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    var_dump($_POST);
    $id_cliente = intval($_POST['id_cliente']);
    $id_parceiro = intval($_POST['id_parceiro']);
    $total = floatval($_POST['valor_total']);
    //$forma_pagamento = $_POST['forma_pagamento'];

    /*try {
        // Usar MySQLi primeiro
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }*/

        $stmt = $mysqli->prepare("INSERT INTO pedidos (id_cliente, id_parceiro, valor, status) VALUES (?, ?, ?, 'pendente')");
        $stmt->bind_param("iid", $id_cliente, $id_parceiro, $total);
        $stmt->execute();

        // Limpar o carrinho após a compra
        $stmt = $mysqli->prepare("DELETE FROM carrinho WHERE id_cliente = ?");
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();

        $stmt->close();
        $mysqli->close();
    /*} catch (Exception $e) {
        // Caso ocorra um erro com MySQLi, usar PDO
        $stmt = $pdo->prepare("INSERT INTO pedidos (id_cliente, id_parceiro, total, forma_pagamento, status) VALUES (?, ?, ?, ?, 'pendente')");
        $stmt->execute([$id_cliente, $id_parceiro, $total, $forma_pagamento]);

        // Limpar o carrinho após a compra
        $stmt = $pdo->prepare("DELETE FROM carrinho WHERE id_cliente = ?");
        $stmt->execute([$id_cliente]);
    }

    header("Location: pedido_confirmado.php");*/
    exit;
}
?>
