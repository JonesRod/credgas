<?php
include('../../conexao.php');

// Inicia a sessão
if (!isset($_SESSION)) {
    session_start(); 
}

// Verifica se o usuário está logado
if (isset($_SESSION['id'])) {
    $id = $_SESSION['id'];

    // Consulta para buscar o parceiro
    $sql_query = $mysqli->query("SELECT * FROM meus_parceiros WHERE id = '$id'") or die($mysqli->error);
    $parceiro = $sql_query->fetch_assoc();
} else {
    session_unset();
    session_destroy(); 
    header("Location: ../../../../index.php");
    exit();
}

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_parceiro = intval($_POST['id_parceiro']);
    $horarios = $_POST['horarios'] ?? [];
    $formas_recebimento = isset($_POST['formas_recebimento']) ? $_POST['formas_recebimento'] : [];
    $descricao_outros_forma = trim($_POST['descricao_outros_forma'] ?? '');

    // Adiciona "Outros" à lista de formas de recebimento se foi preenchido
    /*if (in_array('Outros', $formas_recebimento) && $descricao_outros_forma !== '') {
        $formas_recebimento[] = $descricao_outros_forma;
    }*/

    // Converte a lista de formas de recebimento em uma string separada por vírgulas
    $formas_recebimento_str = implode(',', $formas_recebimento);

    // Outros campos
    $valor_minimo_pedido = isset($_POST['valor_minimo_pedido']) ? floatval($_POST['valor_minimo_pedido']) : 0;
    $valor_min_entrega_gratis = isset($_POST['valor_min_entrega_gratis']) ? floatval($_POST['valor_min_entrega_gratis']) : 0;

    // Atualizar os dados no banco
    $sql = "UPDATE meus_parceiros 
            SET horarios_funcionamento = ?, formas_recebimento = ?, valor_minimo_pedido = ?, valor_min_entrega_gratis = ? 
            WHERE id = ?";
    $stmt = $mysqli->prepare($sql);

    if ($stmt) {
        $stmt->bind_param(
            'ssdii',
            json_encode($horarios),         // JSON dos horários de funcionamento
            $formas_recebimento_str,       // String das formas de recebimento
            $valor_minimo_pedido,          // Valor mínimo do pedido
            $valor_min_entrega_gratis,     // Valor mínimo para entrega grátis
            $id_parceiro                   // ID do parceiro
        );

        if ($stmt->execute()) {
            $msg = "Dados salvos com sucesso!";
        } else {
            $msg = "Erro ao salvar os dados: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $msg = "Erro na preparação da consulta: " . $mysqli->error;
    }
}

// Mensagem de resultado
if (isset($msg)) {
    echo "<script>alert('$msg');</script>";
    //echo "<script>window.location.href = 'configuracoes.php?id_parceiro=" . $id_parceiro . "';</script>";    // Altere para a página desejada
}
?>
