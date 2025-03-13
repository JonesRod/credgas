<?php
include('../../../conexao.php'); // Conexão com o banco

$id_cliente = intval($_POST['id_cliente']);
$num_cartao = $_POST['num_cartao'];
$validade = $_POST['validade'];
$cod_seguranca = $_POST['cod_seguranca'];

$query = "INSERT INTO cartoes_clientes (id_cliente, num_cartao, validade, cod_seguranca) VALUES ($id_cliente, '$num_cartao', '$validade', '$cod_seguranca')";
$result = mysqli_query($conexao, $query);

$response = array();
if ($result) {
    $response['success'] = true;
} else {
    $response['success'] = false;
    $response['message'] = mysqli_error($conexao);
}

echo json_encode($response);
?>
