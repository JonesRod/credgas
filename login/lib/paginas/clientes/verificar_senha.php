<?php
include('../../conexao.php');
session_start();

// Verifica se a senha foi enviada
if (!isset($_POST['senha']) || empty($_POST['senha'])) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Senha não fornecida.']);
    exit();
}

$senha = $_POST['senha'];
$id = $_SESSION['id'] ?? null;

// Verifica se o ID do usuário está na sessão
if (!$id) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Usuário não autenticado.']);
    exit();
}

// Consulta a senha no banco de dados
$sql = $mysqli->prepare("SELECT senha_login FROM meus_clientes WHERE id = ?");
$sql->bind_param('i', $id);
$sql->execute();
$result = $sql->get_result();
$usuario = $result->fetch_assoc();

// Verifica a senha
if ($usuario && password_verify($senha, $usuario['senha_login'])) {
    echo json_encode(['sucesso' => true]);
} else {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Senha incorreta.']);
}

