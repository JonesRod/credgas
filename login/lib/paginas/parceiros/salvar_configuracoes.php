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
    
    // Campos para cartões de débito e crédito
    $cartoes_debito = isset($_POST['cartoes_debito']) ? implode(',', $_POST['cartoes_debito']) : '';
    $cartoes_credito = isset($_POST['cartoes_credito']) ? implode(',', $_POST['cartoes_credito']) : '';

    // Verificação de valores (somente para debug; remova em produção)
    // echo "<pre>"; var_dump($cartoes_debito, $cartoes_credito); echo "</pre>";

    // Converte a lista de formas de recebimento em uma string separada por vírgulas
    $formas_recebimento_str = implode(',', $formas_recebimento);

    // Outros campos
    $valor_minimo_pedido = isset($_POST['valor_minimo_pedido']) 
        ? floatval(str_replace(['.', ','], ['', '.'], $_POST['valor_minimo_pedido'])) 
        : 0;

    $valor_min_entrega_gratis = isset($_POST['valor_min_entrega_gratis']) 
        ? floatval(str_replace(['.', ','], ['', '.'], $_POST['valor_min_entrega_gratis'])) 
        : 0;

    $estimativa_entrega = isset($_POST['estimativa_entrega']) ? trim($_POST['estimativa_entrega']) : '00:00';


    // Criação de variáveis para o bind_param
    $horarios_json = json_encode($horarios); // JSON dos horários de funcionamento

    // Atualizar os dados no banco
    $sql = "UPDATE meus_parceiros 
            SET horarios_funcionamento = ?, formas_recebimento = ?, valor_minimo_pedido = ?, valor_min_entrega_gratis = ?, cartao_debito = ?, cartao_credito = ?, outras_formas = ?, estimativa_entrega = ?
            WHERE id = ?";
    $stmt = $mysqli->prepare($sql);

    if ($stmt) {
        // Bind de parâmetros
        $stmt->bind_param(
            'ssdsssssi',
            $horarios_json,             // JSON dos horários de funcionamento
            $formas_recebimento_str,    // String das formas de recebimento
            $valor_minimo_pedido,       // Valor mínimo do pedido
            $valor_min_entrega_gratis,  // Valor mínimo para entrega grátis
            $cartoes_debito,            // String de cartões de débito
            $cartoes_credito,           // String de cartões de crédito
            $descricao_outros_forma,    // Outras formas de recebimento
            $estimativa_entrega,
            $id_parceiro                // ID do parceiro
        );

        // Execução e verificação
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
    echo "<div class='mensagem-centralizada'>$msg</div>";
    echo "<script>
        setTimeout(function() {
            window.location.href = 'configuracoes.php?id_parceiro=" . $id_parceiro . "';
        }, 3000); // 3000 milissegundos = 3 segundos
    </script>";
}
?>
<style>
.mensagem-centralizada {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: #f0f0f0;
    color: #333;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    font-size: 18px;
    text-align: center;
    z-index: 1000;
}
</style>

