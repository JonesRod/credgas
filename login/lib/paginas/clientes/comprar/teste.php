<?php
session_start();
include('../../../conexao.php'); // Conexão com o banco

// Verificação de sessão
if (!isset($_SESSION['id'])) {
    header("Location: ../../../../index.php");
    exit;
}

$id_session = $_SESSION['id'];
var_dump($_POST);
//echo 'crediario';

$tipo_pagamento = isset($_POST['tipo_pagamento']) ? $_POST['tipo_pagamento'] : '';
//echo $tipo_pagamneto;
// Verificação e sanitização dos dados recebidos
if ($tipo_pagamento == 'pix') {
    $tipo_pagamento = '1';
} else if ($tipo_pagamento == 'cartaoCred') {
    $tipo_pagamento = '2';
} else if ($tipo_pagamento == 'cartaoDeb') {
    $tipo_pagamento = '3';
}
//echo $tipo_pagamento;
$momen_pagamento = isset($_POST['momen_pagamento']) ? $_POST['momen_pagamento'] : 'online';

$id_cliente = isset($_POST['id_cliente']) ? intval($_POST['id_cliente']) : 0;
$id_parceiro = isset($_POST['id_parceiro']) ? intval($_POST['id_parceiro']) : 0;
$valor_frete = isset($_POST['valor_frete']) ? floatval(str_replace(',', '.', $_POST['valor_frete'])) : 0.0;
$valor_total = isset($_POST['valor_total']) ? floatval(str_replace(',', '.', $_POST['valor_total'])) : 0.0;
$detalhes_produtos = isset($_POST['detalhes_produtos']) ? $_POST['detalhes_produtos'] : '';
$entrega = isset($_POST['entrega']) ? $_POST['entrega'] : '';
$rua = isset($_POST['rua']) ? $_POST['rua'] : '';
$bairro = isset($_POST['bairro']) ? $_POST['bairro'] : '';
$numero = isset($_POST['numero']) ? $_POST['numero'] : '';
$contato = isset($_POST['contato']) ? $_POST['contato'] : '';
$entrada = isset($_POST['entrada']) ? floatval(str_replace(',', '.', $_POST['entrada'])) : 0.0;
$restante = isset($_POST['restante']) ? floatval(str_replace(',', '.', $_POST['restante'])) : 0.0;
$saldo_usado = isset($_POST['entrada_saldo']) ? floatval($_POST['entrada_saldo']) : 0.0;
$tipo_entrada_crediario = isset($_POST['tipo_entrada_crediario']) ? $_POST['tipo_entrada_crediario'] : '';
$bandeiras_aceitas = isset($_POST['bandeiras']) ? $_POST['bandeiras'] : '';
$comentario = isset($_POST['comentario']) ? $_POST['comentario'] : '';
$maior_parcelas = isset($_POST['maiorParcelas']) ? intval($_POST['maiorParcelas']) : 1;

$vl_pix = isset($_POST['vl_pix']) ? floatval(str_replace(',', '.', $_POST['vl_pix'])) : 0.0;
$parcelas_cartaoCred_entrada_principal = isset($_POST['parcelas_cartaoCred_entrada_principal']) ? intval($_POST['parcelas_cartaoCred_entrada_principal']) : 1;
$tipo_cartao = isset($_POST['tipo_cartao']) ? $_POST['tipo_cartao'] : '';
$nome_cartao = isset($_POST['nome_cartao']) ? $_POST['nome_cartao'] : '';
$num_cartao = isset($_POST['num_cartao']) ? $_POST['num_cartao'] : '';
$validade = isset($_POST['validade']) ? $_POST['validade'] : '';
$cod_seguranca = isset($_POST['cod_seguranca']) ? $_POST['cod_seguranca'] : '';
$parcelas_cartaoCred_entrada_novo = isset($_POST['parcelas_cartaoCred_entrada_novo']) ? intval($_POST['parcelas_cartaoCred_entrada_novo']) : 1;
$num_cartao_selecionado = isset($_POST['num_cartao_selecionado']) ? $_POST['num_cartao_selecionado'] : '';
$nome_cartao_selecionado = isset($_POST['nome_cartao_selecionado']) ? $_POST['nome_cartao_selecionado'] : '';
$validade_selecionado = isset($_POST['validade_selecionado']) ? $_POST['validade_selecionado'] : '';
$cod_seguranca_selecionado = isset($_POST['cod_seguranca_selecionado']) ? $_POST['cod_seguranca_selecionado'] : '';
$valor_parcela_cartao_selecionado = isset($_POST['valor_parcela_cartao_selecionado']) ? floatval(str_replace(',', '.', $_POST['valor_parcela_cartao_selecionado'])) : 0.0;
$parcelas_cartaoCred_entrada_selecionado = isset($_POST['parcelas_cartaoCred_entrada_selecionado']) ? intval($_POST['parcelas_cartaoCred_entrada_selecionado']) : 1;
$salvar_cartao = isset($_POST['salvar_cartao']) ? $_POST['salvar_cartao'] : '0';
$segunda_forma_pg = isset($_POST['segunda_forma_pg']) ? $_POST['segunda_forma_pg'] : '';
$btn_continuar_pg = isset($_POST['btn_continuar_pg']) ? $_POST['btn_continuar_pg'] : '';

$bd_cliente = $mysqli->query("SELECT senha_login FROM meus_clientes WHERE id = $id_session") or die($mysqli->error);
$dados = $bd_cliente->fetch_assoc();
$senha_compra = $dados['senha_login'];

//echo $tipo_entrada_crediario;

// Buscar cartões do cliente usando prepared statements
$stmt = $mysqli->prepare("SELECT * FROM cartoes_clientes WHERE id_cliente = ?");
if ($stmt) {
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $result = $stmt->get_result();

    $cartoes = array();
    $cartoes_credito = 0;
    $cartoes_debito = 0;
    while ($row = $result->fetch_assoc()) {
        $cartoes[] = $row;
        if ($row['tipo'] === 'credito') {
            $cartoes_credito++;
        } elseif ($row['tipo'] === 'debito') {
            $cartoes_debito++;
        }
    }

    $stmt->close();
} else {
    die("Erro na preparação da consulta: " . $mysqli->error);
}

// Buscar os cartões de crédito e débito aceitos online com a última data de alteração onde formas_pagamento não está vazio
$stmt = $mysqli->prepare("
        SELECT cartoes_debito, cartoes_credito, data_alteracao 
        FROM config_admin 
        WHERE formas_recebimento <> '' 
        ORDER BY data_alteracao DESC 
        LIMIT 1
    ");
$stmt->execute();
$result = $stmt->get_result();
$admin_cartoes = $result->fetch_assoc();

$admin_cartoes_debito = str_replace(',', ', ', $admin_cartoes['cartoes_debito']);
$admin_cartoes_credito = str_replace(',', ', ', $admin_cartoes['cartoes_credito']);

if ($tipo_pagamento == '2') {
    $bandeiras_aceitas = $admin_cartoes_credito;
} else if ($tipo_pagamento == '3') {
    $bandeiras_aceitas = $admin_cartoes_debito;
} else {
    $bandeiras_aceitas = '';
}

// Inicializar variáveis para evitar erros
$entrada_formatado = isset($entrada) ? number_format($entrada, 2, ',', '.') : '0,00';
$nome_cartao = isset($nome_cartao) ? $nome_cartao : '';
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>Formas de pagamento</title>

</head>

<body>
    <h1>Dados Recebidos</h1>
    <div>
        <p>Momento do Pagamento: <input type="text" id="momen_pagamento" name="momen_pagamento"
                value="<?= htmlspecialchars($_POST['momen_pagamento'] ?? '') ?>" readonly></p>
        <p>Valor Total: <input type="text" id="valor_total" name="valor_total"
                value="<?= htmlspecialchars($_POST['valor_total'] ?? '') ?>" readonly></p>
        <p>Tipo de Pagamento Principal: <input type="text" id="tipo_pagamento_principal" name="tipo_pagamento_principal"
                value="<?= htmlspecialchars($_POST['tipo_pagamento_principal'] ?? '') ?>" readonly></p>
        <p>ID Cliente: <input type="text" id="id_cliente" name="id_cliente"
                value="<?= htmlspecialchars($_POST['id_cliente'] ?? '') ?>" readonly></p>
        <p>ID Parceiro: <input type="text" id="id_parceiro" name="id_parceiro"
                value="<?= htmlspecialchars($_POST['id_parceiro'] ?? '') ?>" readonly></p>
        <p>Valor do Frete: <input type="text" id="valor_frete" name="valor_frete"
                value="<?= htmlspecialchars($_POST['valor_frete'] ?? '') ?>" readonly></p>
        <p>Detalhes dos Produtos: <input type="text" id="detalhes_produtos" name="detalhes_produtos"
                value="<?= htmlspecialchars($_POST['detalhes_produtos'] ?? '') ?>" readonly></p>
        <p>Entrega: <input type="text" id="entrega" name="entrega"
                value="<?= htmlspecialchars($_POST['entrega'] ?? '') ?>" readonly></p>
        <p>Rua: <input type="text" id="rua" name="rua" value="<?= htmlspecialchars($_POST['rua'] ?? '') ?>" readonly>
        </p>
        <p>Bairro: <input type="text" id="bairro" name="bairro" value="<?= htmlspecialchars($_POST['bairro'] ?? '') ?>"
                readonly></p>
        <p>Número: <input type="text" id="numero" name="numero" value="<?= htmlspecialchars($_POST['numero'] ?? '') ?>"
                readonly></p>
        <p>Contato: <input type="text" id="contato" name="contato"
                value="<?= htmlspecialchars($_POST['contato'] ?? '') ?>" readonly></p>
        <p>Entrada: <input type="text" id="entrada" name="entrada"
                value="<?= htmlspecialchars($_POST['entrada'] ?? '') ?>" readonly></p>
        <p>Saldo Usado: <input type="text" id="saldo_usado" name="saldo_usado"
                value="<?= htmlspecialchars($_POST['saldo_usado'] ?? '') ?>" readonly></p>
        <p>Bandeiras Aceitas: <input type="text" id="bandeiras_aceitas" name="bandeiras_aceitas"
                value="<?= htmlspecialchars($_POST['bandeiras_aceitas'] ?? '') ?>" readonly></p>
        <p>Comentário: <input type="text" id="comentario" name="comentario"
                value="<?= htmlspecialchars($_POST['comentario'] ?? '') ?>" readonly></p>
        <p>Data e Hora: <input type="text" id="data_hora" name="data_hora"
                value="<?= htmlspecialchars($_POST['data_hora'] ?? '') ?>" readonly></p>
        <p>Tipo Entrada Crediário: <input type="text" id="tipo_entrada_crediario" name="tipo_entrada_crediario"
                value="<?= htmlspecialchars($_POST['tipo_entrada_crediario'] ?? '') ?>" readonly></p>
        <p>Valor PIX: <input type="text" id="vl_pix" name="vl_pix"
                value="<?= htmlspecialchars($_POST['vl_pix'] ?? '') ?>" readonly></p>
        <p>Parcelas Cartão Crédito Entrada Principal: <input type="text" id="parcelas_cartaoCred_entrada_principal"
                name="parcelas_cartaoCred_entrada_principal"
                value="<?= htmlspecialchars($_POST['parcelas_cartaoCred_entrada_principal'] ?? '') ?>" readonly></p>
        <p>Tipo Cartão: <input type="text" id="tipo_cartao" name="tipo_cartao"
                value="<?= htmlspecialchars($_POST['tipo_cartao'] ?? '') ?>" readonly></p>
        <p>Nome Cartão: <input type="text" id="nome_cartao" name="nome_cartao"
                value="<?= htmlspecialchars($_POST['nome_cartao'] ?? '') ?>" readonly></p>
        <p>Número Cartão: <input type="text" id="num_cartao" name="num_cartao"
                value="<?= htmlspecialchars($_POST['num_cartao'] ?? '') ?>" readonly></p>
        <p>Validade: <input type="text" id="validade" name="validade"
                value="<?= htmlspecialchars($_POST['validade'] ?? '') ?>" readonly></p>
        <p>Código de Segurança: <input type="text" id="cod_seguranca" name="cod_seguranca"
                value="<?= htmlspecialchars($_POST['cod_seguranca'] ?? '') ?>" readonly></p>
        <p>Parcelas Cartão Crédito Entrada Novo: <input type="text" id="parcelas_cartaoCred_entrada_novo"
                name="parcelas_cartaoCred_entrada_novo"
                value="<?= htmlspecialchars($_POST['parcelas_cartaoCred_entrada_novo'] ?? '') ?>" readonly></p>
        <p>Número Cartão Selecionado: <input type="text" id="num_cartao_selecionado" name="num_cartao_selecionado"
                value="<?= htmlspecialchars($_POST['num_cartao_selecionado'] ?? '') ?>" readonly></p>
        <p>Nome Cartão Selecionado: <input type="text" id="nome_cartao_selecionado" name="nome_cartao_selecionado"
                value="<?= htmlspecialchars($_POST['nome_cartao_selecionado'] ?? '') ?>" readonly></p>
        <p>Validade Selecionado: <input type="text" id="validade_selecionado" name="validade_selecionado"
                value="<?= htmlspecialchars($_POST['validade_selecionado'] ?? '') ?>" readonly></p>
        <p>Código de Segurança Selecionado: <input type="text" id="cod_seguranca_selecionado"
                name="cod_seguranca_selecionado"
                value="<?= htmlspecialchars($_POST['cod_seguranca_selecionado'] ?? '') ?>" readonly></p>
        <p>Valor Parcela Cartão Selecionado: <input type="text" id="valor_parcela_cartao_selecionado"
                name="valor_parcela_cartao_selecionado"
                value="<?= htmlspecialchars($_POST['valor_parcela_cartao_selecionado'] ?? '') ?>" readonly></p>
        <p>Parcelas Cartão Crédito Entrada Selecionado: <input type="text" id="parcelas_cartaoCred_entrada_selecionado"
                name="parcelas_cartaoCred_entrada_selecionado"
                value="<?= htmlspecialchars($_POST['parcelas_cartaoCred_entrada_selecionado'] ?? '') ?>" readonly></p>
        <p>Salvar Cartão: <input type="text" id="salvar_cartao" name="salvar_cartao"
                value="<?= htmlspecialchars($_POST['salvar_cartao'] ?? '') ?>" readonly></p>
        <p>Restante: <input type="text" id="restante" name="restante"
                value="<?= htmlspecialchars($_POST['restante'] ?? '0') ?>" readonly></p>
        <p>Segunda Forma de Pagamento: <input type="text" id="segunda_forma_pg" name="segunda_forma_pg"
                value="<?= htmlspecialchars($_POST['segunda_forma_pg'] ?? '') ?>" readonly></p>
        <p>Botão Continuar Pagamento: <input type="text" id="btn_continuar_pg" name="btn_continuar_pg"
                value="<?= htmlspecialchars($_POST['btn_continuar_pg'] ?? '') ?>" readonly></p>
    </div>
</body>

</html>