<?php
// Inicia a sessão se ainda não estiver iniciada
if (!isset($_SESSION)) {
    session_start();
}

// Verifica se existe uma sessão ativa
if (isset($_SESSION)) {
    // Destrói todas as variáveis de sessão
    session_unset();
    session_destroy();
    
    // Redireciona o usuário para a página inicial
    header("Location: ../../../../index.php");
    exit(); // É sempre bom usar exit() após redirecionar
} else {
    // Se não houver sessão, pode redirecionar também (opcional)
    header("Location: ../../../../index.php");
    exit();
}
?>
