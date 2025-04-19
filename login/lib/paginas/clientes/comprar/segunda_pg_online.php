<?php
session_start();
include('../../../conexao.php'); // Conexão com o banco

// Verificação de sessão
if (!isset($_SESSION['id'])) {
    header("Location: ../../../../index.php");
    exit;
}

$id_session = $_SESSION['id'];
//var_dump($_POST);
//echo 'crediario';

if (isset($_POST['id_cliente'])) {
    $id_cliente = $_POST['id_cliente'];
} else {
    die("ID do cliente não fornecido.");
}

$id_parceiro = $_POST['id_parceiro'];
$id_cliente = $_POST['id_cliente'];

$momen_pagamento = $_POST['momen_pagamento'];
$data_hora = $_POST['data_hora'];
$entrega = $_POST['entrega'];

$valor_total = $_POST['valor_total'];
$valor_frete = $_POST['valor_frete'];
$valor_produtos = $valor_total - $valor_frete;
$restante = $_POST['restante'];
$entrada = $valor_total - $restante;
$saldo_usado = $_POST['saldo_usado'];
$detalhes_produtos = $_POST['detalhes_produtos'];

$rua = $_POST['rua'];
$bairro = $_POST['bairro'];
$numero = $_POST['numero'];
$contato = $_POST['contato'];

$segunda_forma_pg = $_POST['segunda_forma_pg'];

if (isset($_POST['tipo_pagamento_principal']) && $_POST['tipo_pagamento_principal'] === '1') {
    $primeiro_pagamento = $_POST['tipo_pagamento_principal'];
    //echo $primeiro_pagamento;

    $nome_tipo_pagamento = 'pix';

} else if (isset($_POST['tipo_pagamento_principal']) && $_POST['tipo_pagamento_principal'] === '2') {
    $primeiro_pagamento = $_POST['tipo_pagamento_principal'];
    //echo $primeiro_pagamento;

    $nome_tipo_pagamento = 'credito';
    $bandeiras_aceitas = $_POST['bandeiras_aceitas'];
    $num_cartao = $_POST['num_cartao'];
    $nome_cartao = $_POST['nome_cartao'];
    $validade = $_POST['validade'];
    $cod_seguranca = $_POST['cod_seguranca'];
    $salvar_cartao = $_POST['salvar_cartao'];

    $valor_parcela_cartao_selecionado = $_POST['valor_parcela_cartao_selecionado'];
    $parcelas_cartaoCred_entrada_selecionado = $_POST['parcelas_cartaoCred_entrada_selecionado'];

} else {
    $primeiro_pagamento = $_POST['tipo_pagamento_principal'];
    //echo $primeiro_pagamento;

    $nome_tipo_pagamento = 'debito';
    $bandeiras_aceitas = $_POST['bandeiras_aceitas'];
    $num_cartao = $_POST['num_cartao'];
    $nome_cartao = $_POST['nome_cartao'];
    $validade = $_POST['validade'];
    $cod_seguranca = $_POST['cod_seguranca'];
    $salvar_cartao = $_POST['salvar_cartao'];

    $valor_parcela_cartao_selecionado = $_POST['valor_parcela_cartao_selecionado'];
    $parcelas_cartaoCred_entrada_selecionado = $_POST['parcelas_cartaoCred_entrada_selecionado'];
}

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

// salvar o cartão de crédito ou débito se necessário
if (isset($salvar_cartao) && $salvar_cartao == '1') {
    // Criptografar o código de segurança
    $cod_seguranca_criptografado = password_hash($cod_seguranca, PASSWORD_DEFAULT);

    // Verificar se o cartão já está cadastrado
    $stmt = $mysqli->prepare("SELECT id FROM cartoes_clientes WHERE id_cliente = ? AND num_cartao = ? AND tipo = ?");

    if ($stmt) {
        $stmt->bind_param("iss", $id_cliente, $num_cartao, $tipo_cartao);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->close();
            //$mensagem_erro = "Este cartão já está cadastrado.";
        } else {
            $stmt->close();

            // Verificar se o limite de cartões foi atingido
            if (($tipo_cartao === 'credito' && $cartoes_credito >= 5) || ($tipo_cartao === 'debito' && $cartoes_debito >= 5)) {
                //$mensagem_erro = "Você atingiu o limite de 5 cartões de $tipo_cartao.";
            } else {
                // Salvar o novo cartão no banco de dados
                $stmt = $mysqli->prepare("INSERT INTO cartoes_clientes (id_cliente, num_cartao, validade, cod_seguranca, tipo, nome) VALUES (?, ?, ?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("isssss", $id_cliente, $num_cartao, $validade, $cod_seguranca_criptografado, $tipo_cartao, $nome_cartao);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    die("Erro ao salvar o cartão: " . $mysqli->error);
                }
            }
        }
    } else {
        die("Erro na preparação da consulta: " . $mysqli->error);
    }
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

if ($segunda_forma_pg == '2') {
    $bandeiras_aceitas = $admin_cartoes_credito;
} else if ($segunda_forma_pg == '3') {
    $bandeiras_aceitas = $admin_cartoes_debito;
} else {
    $bandeiras_aceitas = '';
}

// Inicializar variáveis para evitar erros
//$entrada_formatado = isset($entrada) ? number_format($entrada, 2, ',', '.') : '0,00';
$nome_cartao = isset($nome_cartao) ? $nome_cartao : '';
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>2ª Formas de pagamento</title>
    <style>
        /* Estilo para o fundo escuro do popup */
        body {
            text-align: center;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            width: 100vw;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        #popup-background {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 998;
        }

        /* Estilo para o popup */
        #popup_novo_cartao,
        #popup-confirmacao,
        #popup-pix,
        #popup_cartaoCred,
        #popup_cartaoDeb {

            display: none;
            /*position: fixed;*/
            top: 50%;
            left: 50%;
            /*transform: translate(-50%, -50%);*/
            width: 90%;
            max-width: 500px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 999;
            padding: 20px;
        }

        /* Botão de fechar */
        .close {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 20px;
            cursor: pointer;
            color: #333;
        }

        /* Botões dentro do popup */
        .popup-buttons {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-top: 20px;
        }

        .popup-buttons button {
            flex: 1 1 calc(48% - 10px);
            margin: 5px;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
            border: none;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .popup-buttons .btn_proximo {
            background-color: #007bff;
            color: #fff;
        }

        .popup-buttons .btn_proximo:hover {
            background-color: #0056b3;
        }

        .popup-buttons .cancelar {
            background-color: #dc3545;
            color: #fff;
        }

        .popup-buttons .cancelar:hover {
            background-color: #a71d2a;
        }

        .popup-buttons .continuar {
            background-color: #28a745;
            color: #fff;
        }

        .popup-buttons .continuar:hover {
            background-color: #218838;
        }

        /* Tabelas */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table th,
        table td {
            border: none;
            /* Remove as linhas da tabela */
            padding: 8px;
            text-align: left;
        }

        table th {
            background-color: #f2f2f2;
        }

        /* Responsividade */
        @media (max-width: 768px) {

            #popup_novo_cartao,
            #popup-confirmacao,
            #popup-pix,
            #popup_cartaoCred,
            #popup_cartaoDeb {
                width: 90%;
                padding: 15px;
            }

            .popup-buttons .btn_proximo,
            .popup-buttons .cancelar {
                flex: 1 1 90%;
                margin: 5px 0;
            }

            table th,
            table td {
                font-size: 14px;
            }
        }

        @media (max-width: 480px) {

            table th,
            table td {
                font-size: 12px;
            }

            .popup-buttons .btn_proximo,
            .popup-buttons .cancelar {
                flex: 1 1 90%;
                font-size: 14px;
            }
        }

        #popup_novo_cartao {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 50%;
            max-width: 450px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            padding: 20px;
            font-family: Arial, sans-serif;
            overflow-y: auto;
            /* Adiciona rolagem vertical se o conteúdo exceder a altura */
            max-height: 90vh;
            /* Limita a altura máxima do popup a 90% da altura da tela */
        }

        #popup_novo_cartao h3 {
            font-size: 22px;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
            word-wrap: break-word;
            /* Quebra o texto se for muito longo */
            margin-top: 10px;
            /* Adiciona margem superior para evitar sobreposição */
        }

        #popup_novo_cartao label {
            display: block;
            font-size: 14px;
            color: #555;
            margin-bottom: 8px;
        }

        #popup_novo_cartao input[type="text"],
        #popup_novo_cartao select {
            width: 70%;
            padding: 5px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
        }

        #popup_novo_cartao p {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }

        #popup_novo_cartao .popup-buttons {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        #popup_novo_cartao .popup-buttons button {
            flex: 1;
            padding: 12px;
            font-size: 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        #popup_novo_cartao .popup-buttons .cancelar {
            background-color: #dc3545;
            color: #fff;
        }

        #popup_novo_cartao .popup-buttons .cancelar:hover {
            background-color: #a71d2a;
        }

        #popup_novo_cartao .popup-buttons .btn_proximo {
            background-color: #007bff;
            color: #fff;
        }

        #popup_novo_cartao .popup-buttons .btn_proximo:hover {
            background-color: #0056b3;
        }

        @media (max-width: 480px) {
            #popup_novo_cartao {
                width: 95%;
                padding: 15px;
            }

            #popup_novo_cartao h3 {
                font-size: 18px;
            }

            #popup_novo_cartao .popup-buttons button {
                font-size: 12px;
                padding: 10px;
            }
        }

        #popup-confirmacao .btn_cancelar {
            background-color: #dc3545;
            /* Vermelho */
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        #popup-confirmacao .btn_cancelar:hover {
            background-color: #a71d2a;
            /* Vermelho mais escuro */
        }

        #popup-confirmacao .btn_continuar {
            background-color: #28a745;
            /* Verde */
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        #popup-confirmacao .btn_continuar:hover {
            background-color: #218838;
            /* Verde mais escuro */
        }
    </style>
</head>

<body>
    <div id="popup-background" class="popup-background"></div>
    <h3>2ª Formas de pagamento</h3>
    <p>Pagar com:
        <select name="tipo_pagamento" id="tipo_pagamento" onchange="exibirPagamentoSelecionado()">
            <option value="pix" <?php echo (isset($segunda_forma_pg) && $segunda_forma_pg == '1') ? 'selected' : ''; ?>>
                Pix</option>
            <option value="credito" <?php echo (isset($segunda_forma_pg) && $segunda_forma_pg == '2') ? 'selected' : ''; ?>>Cartão de Crédito</option>
            <option value="debito" <?php echo (isset($segunda_forma_pg) && $segunda_forma_pg == '3') ? 'selected' : ''; ?>>Cartão de Débito</option>
        </select>
    </p>

    <div id="dados" style="display: none;">
        <p>Momento do Pagamento: <input type="text" id="momen_pagamento" name="momen_pagamento"
                value="<?= htmlspecialchars($_POST['momen_pagamento'] ?? '') ?>" readonly></p>
        <p>Tipo de Pagamento Principal: <input type="text" id="tipo_pagamento_principal" name="tipo_pagamento_principal"
                value="<?= htmlspecialchars($_POST['tipo_pagamento_principal'] ?? '') ?>" readonly></p>
        <p>ID Cliente: <input type="text" id="id_cliente" name="id_cliente"
                value="<?= htmlspecialchars($_POST['id_cliente'] ?? '') ?>" readonly></p>
        <p>ID Parceiro: <input type="text" id="id_parceiro" name="id_parceiro"
                value="<?= htmlspecialchars($_POST['id_parceiro'] ?? '') ?>" readonly></p>
        <p>Detalhes dos Produtos: <input type="text" id="detalhes_produtos" name="detalhes_produtos"
                value="<?= htmlspecialchars($_POST['detalhes_produtos'] ?? '') ?>" readonly></p>
        <p>Entrega: <input type="text" id="entrega" name="entrega"
                value="<?= htmlspecialchars($_POST['entrega'] ?? '') ?>" readonly></p>
        <hr>

        <p>Rua: <input type="text" id="rua" name="rua" value="<?= htmlspecialchars($_POST['rua'] ?? '') ?>" readonly>
        </p>
        <p>Bairro: <input type="text" id="bairro" name="bairro" value="<?= htmlspecialchars($_POST['bairro'] ?? '') ?>"
                readonly></p>
        <p>Número: <input type="text" id="numero" name="numero" value="<?= htmlspecialchars($_POST['numero'] ?? '') ?>"
                readonly></p>
        <p>Contato: <input type="text" id="contato" name="contato"
                value="<?= htmlspecialchars($_POST['contato'] ?? '') ?>" readonly></p>

        <p>Comentário: <input type="text" id="comentario" name="comentario"
                value="<?= htmlspecialchars($_POST['comentario'] ?? '') ?>" readonly></p>
        <hr>

        <p>Valor Total: <input type="text" id="valor_pedido" name="valor_pedido" value="<?= $valor_total; ?>" readonly>
        </p>
        <p>Valor do Frete: <input type="text" id="valor_frete" name="valor_frete"
                value="<?= htmlspecialchars($_POST['valor_frete'] ?? '') ?>" readonly></p>
        <p>Entrada: <input type="text" id="entrada_valor" name="entrada_valor" value="<?= $entrada; ?>" readonly></p>
        <p>Saldo Usado: <input type="text" id="saldo_usado" name="saldo_usado"
                value="<?= htmlspecialchars($_POST['saldo_usado'] ?? '') ?>" readonly></p>
        <p>Restante: <input type="text" id="restante" name="restante"
                value="<?= htmlspecialchars($_POST['restante'] ?? '0') ?>" readonly></p>
        <hr>
        <p>Tipo Cartão: <input type="text" id="tipo_cartao_valor" name="tipo_cartao_valor"
                value="<?= $nome_tipo_pagamento; ?>" readonly></p>

        <p>Bandeiras Aceitas: <input type="text" id="bandeiras_aceitas_valor" name="bandeiras_aceitas_valor"
                value="<?= htmlspecialchars($_POST['bandeiras_aceitas'] ?? '') ?>" readonly></p>

        <p>Data e Hora: <input type="text" id="data_hora_valor" name="data_hora_valor"
                value="<?= htmlspecialchars($_POST['data_hora'] ?? '') ?>" readonly></p>

        <p>Número Cartão Selecionado: <input type="text" id="num_cartao_selecionado_valor"
                name="num_cartao_selecionado_valor"
                value="<?= htmlspecialchars($_POST['num_cartao_selecionado'] ?? '') ?>" readonly></p>
        <p>Nome Cartão Selecionado: <input type="text" id="nome_cartao_selecionado_valor"
                name="nome_cartao_selecionado_valor"
                value="<?= htmlspecialchars($_POST['nome_cartao_selecionado'] ?? '') ?>" readonly></p>
        <p>Validade Selecionado: <input type="text" id="validade_selecionado_valor" name="validade_selecionado_valor"
                value="<?= htmlspecialchars($_POST['validade_selecionado'] ?? '') ?>" readonly></p>
        <p>Código de Segurança Selecionado: <input type="text" id="cod_seguranca_selecionado_valor"
                name="cod_seguranca_selecionado_valor"
                value="<?= htmlspecialchars($_POST['cod_seguranca_selecionado'] ?? '') ?>" readonly></p>
        <p>Valor Parcela Cartão Selecionado: <input type="text" id="valor_parcela_cartao_primeiro_pg"
                name="valor_parcela_cartao_primeiro_pg"
                value="<?= htmlspecialchars($_POST['valor_parcela_cartao_selecionado'] ?? '') ?>" readonly></p>
        <p>Parcelas Cartão Crédito Entrada Selecionado: <input type="text" id="qt_parcela_cartao_primeiro_pg"
                name="qt_parcela_cartao_primeiro_pg"
                value="<?= htmlspecialchars($_POST['parcelas_cartaoCred_entrada_selecionado'] ?? '') ?>" readonly></p>
        <p>Salvar Cartão: <input type="text" id="salvar_cartao_valor" name="salvar_cartao_valor"
                value="<?= htmlspecialchars($_POST['salvar_cartao'] ?? '') ?>" readonly></p>

        <p>Segunda Forma de Pagamento: <input type="text" id="segunda_forma_pg_valor" name="segunda_forma_pg_valor"
                value="<?= htmlspecialchars($_POST['segunda_forma_pg'] ?? '') ?>" readonly></p>
    </div>

    <div id="popup-pix" style="display: none;">
        <h3>Pagar entrada com PIX</h3>
        <p>Valor do pagamento: R$ <span><?php echo number_format($valor_total, 2, ',', '.'); ?></span></p>
        <p id="p_testo_orientacao" style="display: none;">Abra o aplicativo do seu banco e faça a leitura do QR
            Code abaixo para efetuar o pagamento.</p>

        <img id="qr_code_pix" src="" alt="QR Code PIX" style="display: none;">
        <br>
        <p id="link_pix" style="display: none;">Link de cópia e cola do PIX: <a href="#" id="pix_link">Copiar</a></p>
        <div class="popup-buttons">
            <button type="button" id="id_gr_qrCode" class="btn_proximo" onclick="validarValorPix()">Gerar QR
                Code</button>
            <button type="button" id="btn_continuar" class="continuar" onclick="confirmar_pix()"
                style="display: none;">Continuar</button>
        </div>
    </div>

    <div id="popup_cartaoCred" style="display: none;">
        <p>Valor Restante: R$ <span
                id="restante_cred_inicio"><?php echo number_format($restante, 2, ',', '.'); ?></span></p>
        <hr>
        <h3>Selecione o cartão de Crédito a ser usado</h3>
        <p>Bandeiras Aceitas: <span><?= htmlspecialchars($_POST['bandeiras_aceitas'] ?? '') ?></span></p>
        <table id="cartoes_credito_cadastrados">
            <thead>
                <tr>
                    <th>Selecionar</th>
                    <th>Número do Cartão</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($cartoes) || count(array_filter($cartoes, fn($cartao) => $cartao['tipo'] === 'debito')) === 0): ?>
                    <tr>
                        <td colspan="3" id="mensagem_sem_cartao_credito">Nenhum cartão salvo!</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($cartoes as $cartao): ?>
                        <?php if ($cartao['tipo'] === 'credito'): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="cartao_credito_selecionado" value="<?php echo $cartao['id']; ?>"
                                        data-num-cartao="<?php echo $cartao['num_cartao']; ?>"
                                        data-validade="<?php echo $cartao['validade']; ?>"
                                        data-cod-seguranca="<?php echo $cartao['cod_seguranca']; ?>"
                                        data-nome-cartao="<?php echo $cartao['nome']; ?>" onchange="verificarCartaoSelecionado()">
                                </td>
                                <td>**** **** **** <?php echo substr($cartao['num_cartao'], -4); ?></td>
                                <td>
                                    <button type="button" onclick="confirmarExclusaoCartao(<?php echo $cartao['id']; ?>)"
                                        style="background: none; border: none; cursor: pointer;">
                                        <i class="fas fa-trash-alt trash-icon" style="color: red;"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div id="div_cred_principal" style="display: none;">
            <hr>
            <label for="parcelas_cartaoCred_entrada_principal">Quantidade de parcelas:</label>
            <input id="parcelas_cartaoCred_entrada_principal" name="parcelas_cartaoCred_entrada_principal" type="number"
                min="1" max="12" value="1" onchange="calcularValorParcelaCred()">
            <p>Valor da Parcela: R$ <span
                    id="valor_parcela_cartaoCred_entrada"><?php echo number_format($restante, 2, ',', '.'); ?></span>
            </p>
            <hr>
        </div>
        <div class="popup-buttons">
            <button type="button" class="btn_proximo" onclick="abrirPopupNovoCartao()">Usar outro cartão</button>
            <button type="button" id="btn_continuar_cartaoCred" class="continuar"
                onclick="abrirPopupConfirmacaoCompra()" style="display: none;">Continuar</button>
        </div>
    </div>

    <div id="popup_cartaoDeb" style="display: none;">
        <div id="div_deb_principal" style="display: block;">
            <p>Valor Restante: R$ <span
                    id="restante_deb_principal"><?php echo number_format($restante, 2, ',', '.'); ?></span></p>
            <hr>
        </div>
        <h3>Selecione o Cartão de Débito a ser usado.</h3>
        <p>Bandeiras Aceitas: <span><?= htmlspecialchars($_POST['bandeiras_aceitas'] ?? '') ?></span></p>
        <table id="cartoes_debito_cadastrados">
            <thead>
                <tr>
                    <th>Selecionar</th>
                    <th>Número do Cartão</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($cartoes) || count(array_filter($cartoes, fn($cartao) => $cartao['tipo'] === 'debito')) === 0): ?>
                    <tr>
                        <td colspan="3" id="mensagem_sem_cartao_debito">Nenhum cartão salvo!</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($cartoes as $cartao): ?>
                        <?php if ($cartao['tipo'] === 'debito'): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="cartao_debito_selecionado" value="<?php echo $cartao['id']; ?>"
                                        data-num-cartao="<?php echo $cartao['num_cartao']; ?>"
                                        data-validade="<?php echo $cartao['validade']; ?>"
                                        data-cod-seguranca="<?php echo $cartao['cod_seguranca']; ?>"
                                        data-nome-cartao="<?php echo $cartao['nome']; ?>"
                                        data-valor-entreda="<?php echo $entrada; ?>" data-valor-parcela=""
                                        onchange="verificarCartaoSelecionado()">
                                </td>
                                <td>**** **** **** <?php echo substr($cartao['num_cartao'], -4); ?></td>
                                <td>
                                    <button type="button" onclick="confirmarExclusaoCartao(<?php echo $cartao['id']; ?>)"
                                        style="background: none; border: none; cursor: pointer;">
                                        <i class="fas fa-trash-alt trash-icon" style="color: red;"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="popup-buttons">
            <button type="button" class="btn_proximo" onclick="abrirPopupNovoCartao()">Usar outro cartão</button>
            <button type="button" id="btn_continuar_cartaoDeb" class="continuar" onclick="abrirPopupConfirmacaoCompra()"
                style="display: none;">Continuar</button>
        </div>
    </div>

    <div id="popup_novo_cartao" class="popup-content-cartoes" style="display: none;">
        <span class="close" onclick="fecharPopup('popup_novo_cartao')">&times;</span>
        <h3>Adicionar Novo Cartão</h3>
        <?php if (isset($mensagem_erro)): ?>
            <p style="color: red;"><?php echo $mensagem_erro; ?></p>
        <?php endif; ?>
        <h3>Valor da compra: R$ <?php echo number_format($restante, 2, ',', '.'); ?></h3>

        <div id="dados_cartao">
            <p>
                <label for="tipo_cartao">Tipo de Cartão:</label>
                <select id="tipo_cartao" name="tipo_cartao" onchange="atualizarBandeiras()">
                    <option value="Crédito" <?php if ($segunda_forma_pg == 2)
                        echo 'selected'; ?>>Crédito</option>
                    <option value="Débito" <?php if ($segunda_forma_pg == 3)
                        echo 'selected'; ?>>Débito</option>
                </select>
            </p>
            <p>Bandeiras aceitas: <span
                    id="bandeiras_aceitas_texto"><?php echo $segunda_forma_pg == 2 ? $admin_cartoes_credito : $admin_cartoes_debito; ?></span>
            </p>
            <p>
                <label for="nome_cartao">Nome descrito no Cartão:</label>
                <input type="text" id="nome_cartao" name="nome_cartao"
                    value="<?php echo htmlspecialchars($nome_cartao); ?>">
            </p>
            <p>
                <label for="num_cartao">Número do Cartão:</label>
                <input type="text" id="num_cartao" name="num_cartao" oninput="formatarNumeroCartao(this)" value="">
            </p>
            <p>
                <label for="validade">Validade:</label>
                <input type="text" id="validade" name="validade" oninput="formatarValidadeCartao(this)" value="">
            </p>
            <p>
                <label for="cod_seguranca">Código de Segurança:</label>
                <input type="text" id="cod_seguranca" name="cod_seguranca" oninput="formatarCodSeguranca(this)"
                    value="">
            </p>
            <p>Valor a pagar: R$ <span id="vl_novo"><?php echo number_format($valor_total, 2, ',', '.'); ?></span></p>
            <div id="div_parcelas_cartaoCred_entrada_novo"
                style="<?php echo $segunda_forma_pg == 2 ? 'display: block;' : 'display: none;'; ?>">
                <label for="parcelas_cartaoCred_entrada_novo">Quantidade de parcelas:</label>
                <input id="parcelas_cartaoCred_entrada_novo" name="parcelas_cartaoCred_entrada_novo" type="number"
                    min="1" value="1" max="12" onchange="calcularValorParcelaNovo()">
                <p>Valor da Parcela: R$ <span
                        id="valor_parcelas_cartaoCred_entrada_novo"><?php echo number_format($valor_total, 2, ',', '.'); ?></span>
                </p>
            </div>
        </div>
        <div class="popup-buttons">
            <button type="button" class="cancelar" onclick="fecharPopup('popup_novo_cartao')">Cancelar</button>
            <button type="button" class="btn_proximo" onclick="adicionarNovoCartao(1)">Usar e Salvar</button>
            <button type="button" class="btn_proximo" onclick="adicionarNovoCartao(0)">Usar só dessa vez</button>
        </div>
    </div>

    <div id="popup-confirmacao" class="popup-content" style="display: none;">
        <h3>Confirmação de Pagamento</h3>
        <p>Ao clicar em "Finalizar", você concorda com os termos e condições de compra.</p>
        <p id="msg_erro" style="color: red; display: none;"></p>
        <p id="msg_sucesso" style="color: green; display: none;"></p>
        <div class="popup-buttons">
            <button type="button" id="btn_cancelar" class="btn_cancelar"
                onclick="fecharPopupConfirmar()">Cancelar</button>
            <button type="button" id="btn_finalizar" class="btn_continuar"
                onclick="finalizarCompra()">Finalizar</button>
        </div>
    </div>

    <div id="detalhes_cartao" style="display: none;">
        <input type="text" id="segunda_forma_pg" name="segunda_forma_pg"
            value="<?= htmlspecialchars($_POST['segunda_forma_pg'] ?? '') ?>" readonly>
        <input type="text" id="nome_cartao_selecionado" name="nome_cartao_selecionado" readonly>
        <input type="text" id="num_cartao_selecionado" name="num_cartao_selecionado" readonly>
        <input type="text" id="validade_selecionado" name="validade_selecionado" readonly>

        <input type="text" id="cod_seguranca_selecionado" name="cod_seguranca_selecionado" readonly>
        <input type="text" id="valor_parcela_cartao_selecionado" name="valor_parcela_cartao_selecionado" readonly>
        <input type="text" id="parcelas_cartaoCred_entrada_selecionado" name="parcelas_cartaoCred_entrada_selecionado"
            readonly>
        <input type="text" id="salvar_cartao" name="salvar_cartao" readonly>
    </div>
</body>
<script>
    function validarValorPix() {
        const valor = document.getElementById('restante');
        const valorPagamento = parseFloat(valor.value.replace(/\./g, '').replace(',', '.')) || 0;
        const vl_pix_arredondado = Math.round(valorPagamento * 100) / 100; // Arredondar para 2 casas decimais

        if (vl_pix_arredondado > valorPagamento) {
            alert('O valor a pagar não pode ser maior que o valor total.');
            return;
        } else if (vl_pix_arredondado <= 0) {
            alert('O valor a pagar deve ser maior que zero.');
            return;
        }
        gerarQRCode();
    }

    window.gerarQRCode = function () {
        const p_testo_orientacao = document.getElementById("p_testo_orientacao");
        const qrCodePix = document.getElementById("qr_code_pix");
        const linkPix = document.getElementById("link_pix");
        const btnContinuar = document.getElementById("btn_continuar");

        p_testo_orientacao.style.display = "block";
        qrCodePix.style.display = "block";
        linkPix.style.display = "block";
        btnContinuar.style.display = "inline-block";
        document.getElementById('segunda_forma_pg').value = 1;
    };

    function confirmar_pix() {
        // Limpar os detalhes do cartão, se necessário
        limparDetalhesCartao();

        // Finalizar a compra
        finalizarCompra();
    }

    function abrirPopupNovoCartao() {
        const popupNovoCartao = document.getElementById('popup_novo_cartao');
        const popupBackground = document.getElementById('popup-background');
        const popupCartaoCred = document.getElementById('popup_cartaoCred');
        const popupCartaoDeb = document.getElementById('popup_cartaoDeb');

        if (!popupNovoCartao || !popupBackground) {
            console.error('Elementos necessários não encontrados.');
            return;
        }

        if (popupCartaoCred) {
            popupCartaoCred.style.display = 'block';
            popupCartaoCred.style.zIndex = '998';

            // Exibir o background cobrindo tudo
            popupBackground.style.display = 'block';
            popupBackground.style.zIndex = '999';

            // Exibir o popup de novo cartão por cima do background
            popupNovoCartao.style.display = 'block';
            popupNovoCartao.style.position = 'fixed';
            popupNovoCartao.style.top = '50%';
            popupNovoCartao.style.left = '50%';
            popupNovoCartao.style.transform = 'translate(-50%, -50%)';
            popupNovoCartao.style.zIndex = '1000';
        }
        if (popupCartaoDeb) {
            popupCartaoDeb.style.display = 'block';
            popupCartaoDeb.style.zIndex = '998';
            // Exibir o background cobrindo tudo
            popupBackground.style.display = 'block';
            popupBackground.style.zIndex = '999';
            // Exibir o popup de novo cartão por cima do background
            popupNovoCartao.style.display = 'block';
            popupNovoCartao.style.position = 'fixed';
            popupNovoCartao.style.top = '50%';
            popupNovoCartao.style.left = '50%';
            popupNovoCartao.style.transform = 'translate(-50%, -50%)';
            popupNovoCartao.style.zIndex = '1000';
        }
    }

    function fecharPopup(popupId) {
        const segunda_forma_pg = document.getElementById('segunda_forma_pg').value;
        const popup_cartaoCred = document.getElementById('popup_cartaoCred').style.display;
        const popup_cartaoDeb = document.getElementById('popup_cartaoDeb').style.display;

        if (segunda_forma_pg === '2') {
            document.getElementById('popup_cartaoCred').style.display = 'block';
            document.getElementById('popup_cartaoDeb').style.display = 'none';
        } else if (segunda_forma_pg === '3') {
            document.getElementById('popup_cartaoCred').style.display = 'none';
            document.getElementById('popup_cartaoDeb').style.display = 'block';
        }

        document.getElementById(popupId).style.display = 'none';
        document.getElementById('popup-background').style.display = 'none'
    }

    function fecharPopupConfirmar() {
        if (document.getElementById('popup-confirmacao').style.display === 'block') {
            //console.log('Popup de novo cartão está aberto.');
            document.getElementById('popup-confirmacao').style.display = 'none';
        }

        if (document.getElementById('popup-background').style.display === 'block') {
            //console.log('Popup de novo cartão está aberto.');
            document.getElementById('popup-background').style.display = 'none';
        }

        if (document.getElementById('popup_novo_cartao').style.display === 'block') {
            //console.log('Popup de novo cartão está aberto.');
            document.getElementById('popup-background').style.display = 'block';
            document.getElementById('popup-background').style.zIndex = "999"; // Garantir que o background fique abaixo dos popups
            document.getElementById('popup_novo_cartao').style.position = "fixed"; // Garantir que o popup fique acima do background
            document.getElementById('popup_novo_cartao').style.zIndex = "1000"; // Garantir que o popup fique acima do background
        }
    }

    function verificarCartaoSelecionado() {
        const checkboxes = document.querySelectorAll('input[name="cartao_credito_selecionado"], input[name="cartao_debito_selecionado"]');

        checkboxes.forEach((checkbox) => {
            checkbox.addEventListener('change', function () {
                if (this.checked) {
                    // Desmarcar todos os outros cartões
                    checkboxes.forEach((cb) => {
                        if (cb !== this) cb.checked = false;
                    });

                    // Preencher os detalhes do cartão selecionado
                    carregarDetalhesCartao(this);
                } else {
                    // Limpar os detalhes do cartão se nenhum for selecionado
                    limparDetalhesCartao();
                }
            });
        });

        // Verificar se algum cartão já está selecionado ao carregar a página
        const selecionado = Array.from(checkboxes).find(cb => cb.checked);
        if (selecionado) {
            carregarDetalhesCartao(selecionado);
        }
    }

    function atualizarBandeiras() {
        const tipoCartao = document.getElementById('tipo_cartao').value;
        const bandeirasTexto = document.getElementById('bandeiras_aceitas_texto');
        const div_parcelas_cartaoCred_entrada_novo = document.getElementById('div_parcelas_cartaoCred_entrada_novo');

        if (tipoCartao === 'Crédito') {
            document.getElementById("segunda_forma_pg").value = 2;
            console.log('Tipo de pagamento: ' + document.getElementById("segunda_forma_pg").value);
            bandeirasTexto.textContent = "<?php echo $admin_cartoes_credito; ?>";
            div_parcelas_cartaoCred_entrada_novo.style.display = 'block';
        } else if (tipoCartao === 'Débito') {
            document.getElementById("segunda_forma_pg").value = 3;
            console.log('Tipo de pagamento: ' + document.getElementById("segunda_forma_pg").value);
            bandeirasTexto.textContent = "<?php echo $admin_cartoes_debito; ?>";
            div_parcelas_cartaoCred_entrada_novo.style.display = 'none';
        }
    }

    function seg_pg() {
        const tipo_pagamento = document.getElementById('tipo_pagamento').value;
        if (tipo_pagamento === 'pix') {
            document.getElementById('segunda_forma_pg').value = 1;
        } else if (tipo_pagamento === 'credito') {
            document.getElementById('segunda_forma_pg').value = 2;
        } else if (tipo_pagamento === 'debito') {
            document.getElementById('segunda_forma_pg').value = 3;
        }
    }

    function carregarDetalhesCartao(cartao) {
        const nomeCartao = cartao.dataset.nomeCartao;
        const numCartao = cartao.dataset.numCartao;
        const validade = cartao.dataset.validade;
        const codSeguranca = cartao.dataset.codSeguranca;

        document.getElementById('nome_cartao_selecionado').value = nomeCartao;
        document.getElementById('num_cartao_selecionado').value = numCartao;
        document.getElementById('validade_selecionado').value = validade;
        document.getElementById('cod_seguranca_selecionado').value = codSeguranca;

        if (cartao.name === "cartao_debito_selecionado") {
            document.getElementById('div_deb_principal').style.display = 'block';
            document.getElementById('btn_continuar_cartaoDeb').style.display = 'block';
        } else if (cartao.name === "cartao_credito_selecionado") {
            document.getElementById('div_cred_principal').style.display = 'block';
            document.getElementById('btn_continuar_cartaoCred').style.display = 'block';
            calcularValorParcelaCred();
        }
        seg_pg();
    }

    function limparDetalhesCartao() {
        document.getElementById('div_deb_principal').style.display = 'none';
        document.getElementById('div_cred_principal').style.display = 'none';

        document.getElementById('nome_cartao_selecionado').value = '';
        document.getElementById('num_cartao_selecionado').value = '';
        document.getElementById('validade_selecionado').value = '';
        document.getElementById('cod_seguranca_selecionado').value = '';

        document.getElementById('btn_continuar_cartaoDeb').style.display = 'none';
        document.getElementById('btn_continuar_cartaoCred').style.display = 'none';
    }

    function calcularValorParcelaCred() {
        const restante = document.getElementById('restante_cred_inicio').textContent;
        const restante_cred_inicio = parseFloat(restante.replace(/\./g, '').replace(',', '.')) || 0;
        const numParcelas = parseInt(document.getElementById('parcelas_cartaoCred_entrada_principal').value) || 1;

        if (numParcelas <= 0) {
            alert('O número de parcelas deve ser maior que zero.');
            return;
        }

        let valorParcela;
        if (numParcelas > 3) {
            const taxaJuros = 0.0299; // 2.99% ao mês
            valorParcela = Math.round(restante_cred_inicio * Math.pow(1 + taxaJuros, numParcelas)) / numParcelas;

        } else {
            valorParcela = restante_cred_inicio / numParcelas; // Sem juros para até 3 parcelas
            valorParcela = Math.round((restante_cred_inicio / numParcelas) * 100) / 100; // Arredondar para 2 casas decimais
        }

        const valorParcelaFormatado = valorParcela.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        document.getElementById('valor_parcela_cartaoCred_entrada').textContent = valorParcela.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('valor_parcela_cartao_selecionado').value = valorParcela.toFixed(2);;
        document.getElementById('parcelas_cartaoCred_entrada_selecionado').value = numParcelas;
    }

    function confirmarExclusaoCartao(idCartao) {
        if (confirm("Tem certeza de que deseja excluir este cartão?")) {
            fetch(`excluir_cartao.php?id_cartao=${idCartao}`, {
                method: "GET"
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error("Erro na resposta do servidor.");
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert("Cartão excluído com sucesso!");
                        location.reload(); // Recarregar a página para atualizar a lista de cartões
                    } else {
                        alert("Erro ao excluir o cartão: " + data.message);
                    }
                })
                .catch(error => {
                    console.error("Erro ao excluir o cartão:", error);
                    alert("Erro ao processar a solicitação.");
                });
        }
    }

    function calcularValorParcelaNovo() {
        const valorTotalCompra = document.getElementById('valor_pedido').value;
        let valor = document.getElementById('vl_novo').textContent.replace(/\./g, '').replace(',', '.'); // Remove pontos de milhar e troca vírgula por ponto
        const valorTotal = valor;
        const numParcelas = parseInt(document.getElementById('parcelas_cartaoCred_entrada_novo').value) || 1;

        if (numParcelas <= 0) {
            alert('O número de parcelas deve ser maior que zero.');
            return;
        }

        let valorParcela;
        if (numParcelas > 3) {
            const taxaJuros = 0.0299; // 2.99% ao mês
            valorParcela = (valorTotal * Math.pow(1 + taxaJuros, numParcelas)) / numParcelas;
        } else {
            valorParcela = valorTotal / numParcelas; // Sem juros para até 3 parcelas
        }

        const valorParcelaFormatado = valorParcela.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('valor_parcelas_cartaoCred_entrada_novo').textContent = valorParcelaFormatado;
    }

    function formatarNumeroCartao(input) {
        input.value = input.value.replace(/\D/g, '').replace(/(\d{4})(?=\d)/g, '$1 ').slice(0, 19);
    }
    function formatarValidadeCartao(input) {
        input.value = input.value.replace(/\D/g, '').replace(/(\d{2})(\d)/, '$1/$2').slice(0, 5);
    }
    function formatarCodSeguranca(input) {
        input.value = input.value.replace(/\D/g, '').slice(0, 3);
    }
    function validarCartao() {
        const numCartao = document.getElementById('num_cartao').value.replace(/\s/g, '');
        const validade = document.getElementById('validade').value;
        const codSeguranca = document.getElementById('cod_seguranca').value;
        const nomeCartao = document.getElementById('nome_cartao').value;// Verificar se o nome do cartão não está vazio

        if ((nomeCartao.trim() === '') || nomeCartao.length < 5) {
            alert('O nome do cartão precisa ser preenchido corretamente.');
            document.getElementById('nome_cartao').focus();
            return false;
        }
        if (numCartao.length !== 16) {
            alert('O número do cartão deve ter 16 dígitos.');
            document.getElementById('num_cartao').focus();
            return false;
        }

        if (validade.length !== 5 || !/^\d{2}\/\d{2}$/.test(validade)) {
            alert('A validade deve estar no formato MM/AA.');
            document.getElementById('validade').focus();
            return false;
        }

        if (codSeguranca.length !== 3) {
            alert('O código de segurança deve ter 3 dígitos.');
            document.getElementById('cod_seguranca').focus();
            return false;
        }
        return true;
    }

    function adicionarNovoCartao(salvar) {
        if (validarCartao()) {
            document.getElementById('salvar_cartao').value = salvar === 1 ? 1 : 0;
            document.getElementById('num_cartao_selecionado').value = document.getElementById('num_cartao').value.replace(/\s/g, '');
            document.getElementById('validade_selecionado').value = document.getElementById('validade').value;
            document.getElementById('cod_seguranca_selecionado').value = document.getElementById('cod_seguranca').value;
            document.getElementById('nome_cartao_selecionado').value = document.getElementById('nome_cartao').value;
            document.getElementById('parcelas_cartaoCred_entrada_selecionado').value = document.getElementById('parcelas_cartaoCred_entrada_novo').value;
            document.getElementById('valor_parcela_cartao_selecionado').value = parseFloat(document.getElementById('valor_parcelas_cartaoCred_entrada_novo').textContent.replace('.', '').replace(',', '.'));

            let vl_a_pg_novo = parseFloat(document.getElementById('vl_novo').textContent.replace(/\./g, '').replace(',', '.'));
            const valor_total = <?php echo $valor_total; ?>;

            vl_a_pg_novo = Math.round(vl_a_pg_novo * 100) / 100; // Arredondar para 2 casas decimais
            const valor_total_arredondado = Math.round(valor_total * 100) / 100; // Arredondar para 2 casas decimais

            if (vl_a_pg_novo > valor_total_arredondado) {
                alert('O valor a pagar não pode ser maior que o valor total.');
                document.getElementById('vl_novo').value = "<?php echo number_format($valor_total, 2, ',', '.'); ?>";
                calcularValorParcelaNovo();
                return;
            } else if (vl_a_pg_novo <= 0) {
                alert('O valor a pagar deve ser maior que zero.');
                document.getElementById('vl_novo').value = "<?php echo number_format($valor_total, 2, ',', '.'); ?>";
                calcularValorParcelaNovo();
                return;
            } else {
                if (vl_a_pg_novo === valor_total_arredondado) {
                    const popupConfirmacao = document.getElementById("popup-confirmacao");
                    const popup_background = document.getElementById('popup-background');
                    const popup_novo_cartao = document.getElementById('popup_novo_cartao');

                    popup_novo_cartao.style.display = 'block';
                    popup_novo_cartao.style.zIndex = "999";
                    popup_background.style.display = "block";
                    popup_background.style.zIndex = "1000";
                    popupConfirmacao.style.display = "block";
                    popupConfirmacao.style.position = "fixed";
                    popupConfirmacao.style.top = "50%";
                    popupConfirmacao.style.left = "50%";
                    popupConfirmacao.style.transform = "translate(-50%, -50%)";
                    popupConfirmacao.style.zIndex = "1001";
                    popupConfirmacao.style.backgroundColor = "#fff";
                    popupConfirmacao.style.padding = "20px";
                    popupConfirmacao.style.boxShadow = "0 4px 8px rgba(0, 0, 0, 0.2)";
                } else {
                    alert('O valor a pagar deve ser igual ao valor total.');
                }
            }
        }
    }

    function abrirPopupConfirmacaoCompra() {
        const popupConfirmacao = document.getElementById("popup-confirmacao");
        const popup_background = document.getElementById('popup-background');

        // Esconder os outros popups
        popup_background.style.display = "block";
        popup_background.style.zIndex = "1000"; // Garantir que o background fique acima dos popups

        popupConfirmacao.style.display = "block";
        popupConfirmacao.style.position = "fixed";
        popupConfirmacao.style.top = "50%";
        popupConfirmacao.style.left = "50%";
        popupConfirmacao.style.transform = "translate(-50%, -50%)";
        popupConfirmacao.style.zIndex = "1001"; // Garantir que o popupConfirmacao fique acima de todos
        popupConfirmacao.style.backgroundColor = "#fff";
        popupConfirmacao.style.padding = "20px";
        popupConfirmacao.style.boxShadow = "0 4px 8px rgba(0, 0, 0, 0.2)";
    }

    function obterHorarioLocal() {
        const agora = new Date();

        // Obtém os componentes da data e hora
        const ano = agora.getFullYear();
        const mes = String(agora.getMonth() + 1).padStart(2, '0'); // Mês começa do 0, então +1
        const dia = String(agora.getDate()).padStart(2, '0');
        const hora = String(agora.getHours()).padStart(2, '0');
        const minuto = String(agora.getMinutes()).padStart(2, '0');
        const segundo = String(agora.getSeconds()).padStart(2, '0');

        // Formata a data e hora como YYYY-MM-DD HH:MM:SS
        const dataFormatada = `${ano}-${mes}-${dia} ${hora}:${minuto}:${segundo}`;

        //console.log("Horário do dispositivo:", dataFormatada);
        document.getElementById('data_hora_valor').value = dataFormatada;
    }

    // Função para enviar os dados via JavaScript em formato JSON
    function finalizarCompra() {
        obterHorarioLocal();

        const dataFormatada = document.getElementById('data_hora_valor').value;
        const popup_confirmacao = document.getElementById("popup-confirmacao");
        // Calcular o valor total da compra no cliente (se necessário)

        const valorTotal = parseFloat(document.getElementById("valor_pedido").value) || 0;
        const totalCompra = valorTotal;

        //console.log("Valor total da compra:", totalCompra);
        //console.log(dataFormatada);

        //return;
        if (totalCompra === 0) {
            console.error("Erro: O valor total da compra não foi calculado corretamente.");
            document.getElementById("msg_erro").textContent = "Erro ao calcular o valor total da compra.";
            document.getElementById("msg_erro").style.display = "block";
            return;
        }

        // Criar o overlay escuro
        const overlay = document.createElement("div");
        overlay.id = "overlay";
        overlay.style.position = "fixed";
        overlay.style.top = "0";
        overlay.style.left = "0";
        overlay.style.width = "100%";
        overlay.style.height = "100%";
        overlay.style.backgroundColor = "rgba(0, 0, 0, 0.5)";
        overlay.style.zIndex = "999";
        overlay.style.display = "none";
        document.body.appendChild(overlay);

        // Criar o popup de sucesso
        const popupSucesso = document.createElement("div");
        popupSucesso.id = "popup-sucesso";
        popupSucesso.style.display = "none";
        popupSucesso.style.position = "fixed";
        popupSucesso.style.top = "50%";
        popupSucesso.style.left = "50%";
        popupSucesso.style.transform = "translate(-50%, -50%)";
        popupSucesso.style.zIndex = "1000";
        popupSucesso.style.backgroundColor = "#fff";
        popupSucesso.style.padding = "20px";
        popupSucesso.style.borderRadius = "8px";
        popupSucesso.style.boxShadow = "0 4px 8px rgba(0, 0, 0, 0.2)";
        popupSucesso.innerHTML = `
                    <h3>Compra Finalizada</h3>
                    <p>Sua compra foi finalizada com sucesso!</p>
                    <p>Você será redirecionado em <span id="contador">5</span> segundos...</p>
                `;
        document.body.appendChild(popupSucesso);

        // Mostrar o popup de sucesso e o overlay
        function mostrarPopupSucesso() {
            popup_confirmacao.style.display = "none";
            overlay.style.display = "block";
            popupSucesso.style.display = "block";
            document.body.style.overflow = "hidden"; // Travar a rolagem da tela
        }

        // Ocultar o popup de sucesso e o overlay
        function ocultarPopupSucesso() {
            overlay.style.display = "none";
            popupSucesso.style.display = "none";
            document.body.style.overflow = "auto"; // Liberar a rolagem da tela
        }
        //console.log(document.getElementById("tipo_pagamento_principal").value);
        //console.log(document.getElementById("salvar_cartao").value);
        //return;
        // Enviar os dados via fetch
        const formData = {
            data_hora: dataFormatada,
            momen_pagamento: document.getElementById("momen_pagamento").value,
            tipo_pagamento_principal: document.getElementById("tipo_pagamento_principal").value,
            id_cliente: document.getElementById("id_cliente").value,
            id_parceiro: document.getElementById("id_parceiro").value,
            detalhes_produtos: document.getElementById("detalhes_produtos").value,
            tipo_entrega: document.getElementById("entrega").value,

            rua: document.getElementById("rua").value,
            bairro: document.getElementById("bairro").value,
            numero: document.getElementById("numero").value,
            contato: document.getElementById("contato").value,
            comentario: document.getElementById("comentario").value,

            total_compra: document.getElementById("valor_pedido").value,
            valor_frete: document.getElementById("valor_frete").value,
            saldo_usado: document.getElementById("saldo_usado").value,

            valor_entrada: document.getElementById("entrada_valor").value,
            qt_parcelas_entrada: document.getElementById("qt_parcela_cartao_primeiro_pg").value,
            valor_parcela_entrada: document.getElementById("valor_parcela_cartao_primeiro_pg").value, // Valor da parcela                

            restante: document.getElementById("restante").value,

            segunda_forma_pg: document.getElementById("segunda_forma_pg").value,
            nome_cartao: document.getElementById("nome_cartao_selecionado").value,
            num_cartao: document.getElementById("num_cartao_selecionado").value,
            validade: document.getElementById("validade_selecionado").value,
            cod_seguranca: document.getElementById("cod_seguranca_selecionado").value,

            qt_parcelas_restante: document.getElementById("parcelas_cartaoCred_entrada_selecionado").value,
            valor_parcelas_restante: document.getElementById("valor_parcela_cartao_selecionado").value,

            salvar_cartao: document.getElementById("salvar_cartao").value
        };
        //return;
        fetch("finalizar_compra_segundo_pg_online.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(formData)
        })

            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarPopupSucesso(); // Mostrar o popup de sucesso e o overlay
                    let contador = 5;
                    const intervalo = setInterval(() => {
                        contador--;
                        document.getElementById("contador").textContent = contador;
                        if (contador === 0) {
                            clearInterval(intervalo);
                            ocultarPopupSucesso(); // Ocultar o popup e o overlay antes de redirecionar
                            window.location.href = "meus_pedidos.php"; // Redirecionar após 5 segundos
                        }
                    }, 1000);
                } else {
                    document.getElementById("msg_erro").textContent = data.message || "Erro ao finalizar a compra.";
                    document.getElementById("msg_erro").style.display = "block";
                    ocultarPopupSucesso(); // Garantir que o overlay seja removido em caso de erro
                }
            })
            .catch(error => {
                console.error("Erro:", error);
                document.getElementById("msg_erro").textContent = "Erro ao processar a solicitação.";
                document.getElementById("msg_erro").style.display = "block";
                ocultarPopupSucesso(); // Garantir que o overlay seja removido em caso de erro
            });

    };

    function exibirPagamentoSelecionado() {
        const tipoPagamento = document.getElementById('tipo_pagamento').value;

        // Ocultar todos os popups
        document.getElementById('popup-pix').style.display = 'none';
        document.getElementById('popup_cartaoCred').style.display = 'none';
        document.getElementById('popup_cartaoDeb').style.display = 'none';

        // Desmarcar todos os cartões e esconder botões "Continuar"
        document.querySelectorAll('input[name="cartao_credito_selecionado"], input[name="cartao_debito_selecionado"]').forEach(checkbox => {
            checkbox.checked = false;
        });
        document.getElementById('btn_continuar_cartaoCred').style.display = 'none';
        document.getElementById('btn_continuar_cartaoDeb').style.display = 'none';

        // Esconder o div_cred_principal
        document.getElementById('div_cred_principal').style.display = 'none';

        // Exibir o popup correspondente ao tipo de pagamento selecionado
        if (tipoPagamento === 'pix') {
            document.getElementById('popup-pix').style.display = 'block';
            document.getElementById('qr_code_pix').style.display = 'none';
            document.getElementById('btn_continuar').style.display = 'none';
        } else if (tipoPagamento === 'credito') {
            document.getElementById('popup_cartaoCred').style.display = 'block';
        } else if (tipoPagamento === 'debito') {
            document.getElementById('popup_cartaoDeb').style.display = 'block';
        }
        seg_pg();
    }

    // Chamar a função ao carregar a página para exibir o tipo de pagamento selecionado inicialmente
    document.addEventListener('DOMContentLoaded', exibirPagamentoSelecionado);
</script>

</html>