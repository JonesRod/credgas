<?php
session_start();
require 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cliente = $_SESSION['id_cliente'];
    $id_parceiro = intval($_POST['id_parceiro']);
    $total = floatval($_POST['total']);
    $forma_pagamento = $_POST['forma_pagamento'];

    $stmt = $pdo->prepare("INSERT INTO pedidos (id_cliente, id_parceiro, total, forma_pagamento, status) VALUES (?, ?, ?, ?, 'pendente')");
    $stmt->execute([$id_cliente, $id_parceiro, $total, $forma_pagamento]);

    // Limpar o carrinho após a compra
    $stmt = $pdo->prepare("DELETE FROM carrinho WHERE id_cliente = ?");
    $stmt->execute([$id_cliente]);

    header("Location: pedido_confirmado.php");
    exit;
}
?>
