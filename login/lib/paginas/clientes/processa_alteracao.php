<?php
include('../../conexao.php');
session_start();

// Verifica se o usuário está autenticado
if (!isset($_SESSION['id'])) {
    header("Location: ../../index.php");
    exit();
}

// Obtém o ID do usuário autenticado
$id = $_SESSION['id'];

// Verifica se os dados do formulário foram enviados
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Filtra e valida os dados recebidos
    $telefone1 = filter_input(INPUT_POST, 'telefone1', FILTER_SANITIZE_STRING);
    $telefone2 = filter_input(INPUT_POST, 'telefone2', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

    // Verifica se os campos obrigatórios foram preenchidos
    if (!$telefone1 || !$email) {
        $_SESSION['mensagem_erro'] = 'Telefone e E-mail são obrigatórios.';
        header("Location: alterar_contatos.php");
        exit();
    }

    // Prepara a consulta para atualizar os dados no banco
    $sql = $mysqli->prepare("UPDATE meus_clientes SET celular1 = ?, celular2 = ?, email = ? WHERE id = ?");
    $sql->bind_param("sssi", $telefone1, $telefone2, $email, $id);

    if ($sql->execute()) {
        // Sucesso: Redireciona com uma mensagem de sucesso
        $_SESSION['mensagem_sucesso'] = 'Contatos atualizados com sucesso!';
        header("Location: perfil_cliente.php");
    } else {
        // Falha: Redireciona com uma mensagem de erro
        $_SESSION['mensagem_erro'] = 'Erro ao atualizar os contatos. Tente novamente.';
        header("Location: alterar_contatos.php");
    }
    exit();
}

// Redireciona se o método não for POST
header("Location: alterar_contatos.php");
exit();
?>
