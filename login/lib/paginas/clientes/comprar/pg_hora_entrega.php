<?php
session_start();
include('../../../conexao.php'); // Conexão com o banco

// Verificação de sessão
if (!isset($_SESSION['id'])) {
    header("Location: ../../../../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Captura os valores do array $_POST em variáveis
    $id_cliente = $_POST['id_cliente'];
    $id_parceiro = $_POST['id_parceiro'];
    $valor_frete = $_POST['valor_frete'];
    $valor_total = $_POST['valor_total'];
    $entrada_saldo = $_POST['entrada_saldo'];
    $detalhes_produtos = $_POST['detalhes_produtos'];
    $entrega = $_POST['entrega'];
    $rua = $_POST['rua'];
    $bairro = $_POST['bairro'];
    $numero = $_POST['numero'];
    $contato = $_POST['contato'];
    $comentario = $_POST['comentario'];
    $bandeiras_outros_aceitos = $_POST['bandeiras_outros_aceitos'];

    // Debug para verificar os valores capturados
    var_dump([
        'id_cliente' => $id_cliente,
        'id_parceiro' => $id_parceiro,
        'valor_frete' => $valor_frete,
        'valor_total' => $valor_total,
        'entrada_saldo' => $entrada_saldo,
        'detalhes_produtos' => $detalhes_produtos,
        'entrega' => $entrega,
        'rua' => $rua,
        'bairro' => $bairro,
        'numero' => $numero,
        'contato' => $contato,
        'comentario' => $comentario,
        'bandeiras_outros_aceitos' => $bandeiras_outros_aceitos,
    ]);

    echo 'hora entrega';
}
?>