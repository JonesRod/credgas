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
//$bandeiras_aceitas = isset($_POST['bandeiras']) ? $_POST['bandeiras'] : '';
$comentario = isset($_POST['comentario']) ? $_POST['comentario'] : '';
$maior_parcelas = isset($_POST['maiorParcelas']) ? intval($_POST['maiorParcelas']) : 1;

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
    <title>Formas de pagamento</title>
    <style>
        /* Estilos gerais */
        body {
            font-family: 'Roboto', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            color: #333;
        }

        h1,
        h3 {
            text-align: center;
            color: #495057;
        }

        .form-container {
            max-width: 900px;
            margin: 30px auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        p {
            margin: 10px 0;
            color: #6c757d;
        }

        /* Botões gerais */
        button {
            display: inline-block;
            padding: 12px 25px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease, opacity 0.3s ease;
            text-align: center;
            width: 100%;
            /* Melhorar responsividade */
        }

        /* Botões de ação positiva (Continuar, Finalizar, Usar outro cartão) */
        button#btn_continuar_cartaoCred,
        button#btn_continuar_cartaoDeb,
        button.usar-outro-cartao-credito,
        button.usar-outro-cartao-debito {
            background-color: #28a745;
            /* Verde */
            color: #fff;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease, opacity 0.3s ease;
            text-align: center;
            width: 100%;
        }

        button#btn_continuar_cartaoCred:hover,
        button#btn_continuar_cartaoDeb:hover,
        button.usar-outro-cartao-credito:hover,
        button.usar-outro-cartao-debito:hover {
            background-color: #218838;
            /* Verde mais escuro */
        }

        /* Botão de ação negativa (Cancelar) */
        button#btn_cancelar,
        .popup-buttons button.cancelar {
            background-color: #dc3545;
            /* Vermelho */
            color: #fff;
        }

        button#btn_cancelar:hover,
        .popup-buttons button.cancelar:hover {
            background-color: #c82333;
            /* Vermelho mais escuro */
        }

        /* Botões de ação neutra (Voltar, Gerar QR Code) */
        button#btn_voltar,
        .btn_proximo,
        button#id_gr_qrCode {
            background-color: #007bff;
            /* Azul */
            color: #fff;
        }

        button#btn_voltar:hover,
        .btn_proximo:hover,
        button#id_gr_qrCode:hover {
            background-color: #0056b3;
            /* Azul mais escuro */
        }

        .popup-content {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            background-color: #0056b3;
            /* Azul mais escuro */
        }

        .continuar {
            background-color: rgb(13, 255, 0);
            color: #fff;
        }

        .continuar:hover {
            background-color: rgb(62, 187, 51);
        }

        #id_gr_qrCode {
            background-color: #007bff;
            /* Azul */
            color: #fff;
        }

        #id_gr_qrCode:hover {
            background-color: #0056b3;
            /* Azul mais escuro */
        }

        .popup-content {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            max-width: 90%;
        }

        .popup-content h3 {
            margin-top: 0;
            color: #333;
        }

        .popup-content button {
            width: 100%;
        }

        /* Estilo aprimorado para o popup de novo cartão */
        .popup-content-cartoes {
            display: none;
            position: absolute;
            /* Alterado para permitir movimento com rolagem */
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            transition: top 0.3s ease;
            /* Suavizar o movimento */
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            width: 90%;
            max-width: 500px;
            border: 1px solid #ccc;
            text-align: center;
            overflow-y: auto;
            /* Adicionado para permitir rolagem interna */
            max-height: 90vh;
            /* Limitar a altura máxima para evitar que o conteúdo ultrapasse a tela */
        }

        .popup-content-cartoes h3 {
            margin-top: 0;
            color: #333;
            font-size: 20px;
        }

        .popup-content-cartoes p {
            color: #555;
            margin: 10px 0;
        }

        .popup-content-cartoes #dados_cartao {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 15px;
        }

        .popup-content-cartoes #dados_cartao div {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .popup-content-cartoes #dados_cartao label {
            font-size: 14px;
            margin-bottom: 5px;
            color: #333;
        }

        .popup-content-cartoes #dados_cartao input,
        .popup-content-cartoes #dados_cartao select {
            width: 95%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        .popup-content-cartoes .popup-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .popup-content-cartoes .popup-buttons button {
            flex: 1;
            margin: 0 5px;
            padding: 10px;
            font-size: 14px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, opacity 0.3s ease;
        }

        .popup-content-cartoes .popup-buttons button.cancelar {
            background-color: #dc3545;
            /* Vermelho */
            color: #fff;
        }

        .popup-content-cartoes .popup-buttons button.cancelar:hover {
            background-color: #c82333;
            /* Vermelho mais escuro */
        }

        .popup-content-cartoes .popup-buttons button.btn_proximo {
            background-color: #007bff;
            /* Azul */
            color: #fff;
        }

        .popup-content-cartoes .popup-buttons button.btn_proximo:hover {
            background-color: #0056b3;
            /* Azul mais escuro */
        }

        /*Adicionar estilo para escurecer o fundo*/
        .popup-background {
            display: none;
            position: fixed;
            z-index: 999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
        }

        #dados_cartao {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        #dados_cartao div {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        #dados_cartao label {
            flex: 1;
            text-align: left;
        }

        #dados_cartao input {
            flex: 2;
            padding: 5px;
        }

        .popup-buttons {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
        }

        .popup-buttons button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .popup-buttons button.cancelar {
            background-color: #dc3545;
        }

        .popup-buttons button:hover {
            opacity: 0.9;
        }

        /* Estilos responsivos */
        @media (max-width: 768px) {
            .form-container {
                padding: 15px;
            }

            h1 {
                font-size: 24px;
            }

            button {
                font-size: 14px;
                padding: 10px 20px;
            }

            .popup-content {
                max-width: 95%;
            }

            #dados_cartao div {
                flex-direction: column;
                align-items: flex-start;
            }

            #dados_cartao label {
                margin-bottom: 5px;
            }

            #dados_cartao input {
                width: 100%;
            }

            .popup-content-cartoes {
                width: 95%;
                padding: 15px;
            }

            .popup-content-cartoes h3 {
                font-size: 18px;
            }

            .popup-content-cartoes #dados_cartao input,
            .popup-content-cartoes #dados_cartao select {
                font-size: 13px;
            }

            .popup-content-cartoes .popup-buttons button {
                font-size: 13px;
                padding: 8px;
            }
        }

        @media (max-width: 480px) {
            .form-container {
                padding: 10px;
            }

            h1 {
                font-size: 20px;
            }

            button {
                font-size: 12px;
                padding: 8px 16px;
            }

            .popup-content {
                padding: 15px;
            }

            #dados_cartao div {
                flex-direction: column;
                align-items: flex-start;
            }

            #dados_cartao label {
                margin-bottom: 5px;
            }

            #dados_cartao input {
                width: 100%;
            }

            .popup-content-cartoes {
                padding: 10px;
            }

            .popup-content-cartoes h3 {
                font-size: 16px;
            }

            .popup-content-cartoes #dados_cartao input,
            .popup-content-cartoes #dados_cartao select {
                font-size: 12px;
            }

            .popup-content-cartoes .popup-buttons button {
                font-size: 12px;
                padding: 6px;
            }
        }

        .popup-content-cartoes .close {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 20px;
            font-weight: bold;
            color: #333;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .popup-content-cartoes .close:hover {
            color: #dc3545;
            /* Vermelho */
        }
    </style>
</head>

<body>

    <div id="popup-background" class="popup-background"></div>
    <div class="form-container">

        <form action="segunda_pg_online.php" method="POST">
            <input type="text" id="momen_pagamento" name="momen_pagamento" value="<?php echo $momen_pagamento; ?>"
                hidden>
            <input type="text" id="valor_total" name="valor_total" value="<?php echo $valor_total; ?>" hidden>
            <input type="text" id="tipo_pagamento_principal" name="tipo_pagamento_principal"
                value="<?php echo $tipo_pagamento; ?>" hidden>
            <input type="text" id="id_cliente" name="id_cliente" value="<?php echo $id_cliente; ?>" hidden>
            <input type="text" id="id_parceiro" name="id_parceiro" value="<?php echo $id_parceiro; ?>" hidden>
            <input type="text" id="valor_frete" name="valor_frete" value="<?php echo $valor_frete; ?>" hidden>
            <input type="text" id="detalhes_produtos" name="detalhes_produtos" value="<?php echo $detalhes_produtos; ?>"
                hidden>
            <input type="text" id="entrega" name="entrega" value="<?php echo $entrega; ?>" hidden>
            <input type="text" id="rua" name="rua" value="<?php echo $rua; ?>" hidden>
            <input type="text" id="bairro" name="bairro" value="<?php echo $bairro; ?>" hidden>
            <input type="text" id="numero" name="numero" value="<?php echo $numero; ?>" hidden>
            <input type="text" id="contato" name="contato" value="<?php echo $contato; ?>" hidden>
            <input type="text" id="entrada" name="entrada" value="<?php echo $entrada; ?>" hidden>
            <input type="text" id="saldo_usado" name="saldo_usado" value="<?php echo $saldo_usado; ?>" hidden>
            <input type="text" id="bandeiras_aceitas" name="bandeiras_aceitas" value="<?php echo $bandeiras_aceitas; ?>"
                hidden>
            <input type="text" id="comentario" name="comentario" value="<?php echo $comentario; ?>" hidden>
            <input type="text" id="data_hora" name="data_hora" hidden>

            <h1>Formas de Pagamento</h1>
            <h3>Valor total da Compra: R$ <?php echo number_format($valor_total, 2, ',', '.'); ?></h3>

            <div style="<?php if ($tipo_pagamento == 1) {
                echo 'display: none;';
            } else {
                echo 'display: block;';
            } ?>">
                <p style="display: block;"><span><?php echo 'Bandeiras aceitas: ' . $bandeiras_aceitas; ?></span></p>
                <input id="tipo_entrada_crediario" name="tipo_entrada_crediario" style="display: none;"
                    value="<?php echo $tipo_entrada_crediario; ?>" readonly>
                <input type="text" id="bandeiras" name="bandeiras" style="display: none;"
                    value="<?php echo $bandeiras_aceitas; ?>" readonly>
            </div>

            <hr style="border: 1px solid #ccc; margin: 10px 0;">

            <div id="popup-pix" style="<?php if ($tipo_pagamento == 1) {
                echo 'display: block;';
            } else {
                echo 'display: none;';
            } ?>">
                <h3>Pagar entrada com PIX</h3>
                <p>Valor do pagamento: R$ <input type="text"
                        value="<?php echo number_format($valor_total, 2, ',', '.'); ?>" id="vl_pix" name="vl_pix"
                        oninput="formatarValorPagamentoPix(this)">
                </p>
                <p id="restante_pix">Valor do restante: R$ <?php echo number_format('0', 2, ',', '.'); ?></p>
                <p id="p_testo_orientacao" style="display: none;">Abra o aplicativo do seu banco e faça a leitura do QR
                    Code abaixo para efetuar o pagamento.</p>

                <img id="qr_code_pix" src="" alt="QR Code PIX" style="display: none;">
                <br>
                <p id="link_pix" style="display: none;">Link de cópia e cola do PIX: <a href="#"
                        id="pix_link">Copiar</a></p>
                <button type="button" id="id_gr_qrCode" onclick="validarValorPix()">Gerar QR Code</button>
                <button type="button" id="btn_continuar" onclick="confirmar_pix()"
                    style="display: none;">Continuar</button>
            </div>

            <div id="popup_cartaoCred" style="<?php if ($tipo_pagamento == 2) {
                echo 'display: block;';
            } else {
                echo 'display: none;';
            } ?>">
                <h3>Selecione o cartão de Crédito a ser usado</h3>
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
                                            <input type="checkbox" name="cartao_credito_selecionado"
                                                value="<?php echo $cartao['id']; ?>"
                                                data-num-cartao="<?php echo $cartao['num_cartao']; ?>"
                                                data-validade="<?php echo $cartao['validade']; ?>"
                                                data-cod-seguranca="<?php echo $cartao['cod_seguranca']; ?>"
                                                data-nome-cartao="<?php echo $cartao['nome']; ?>"
                                                onchange="verificarCartaoSelecionado()">
                                        </td>
                                        <td>**** **** **** <?php echo substr($cartao['num_cartao'], -4); ?></td>
                                        <td>
                                            <button type="button"
                                                onclick="confirmarExclusaoCartao(<?php echo $cartao['id']; ?>)">Excluir</button>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div id="div_cred_principal" style="display: none;">
                    <hr>
                    <p>Valor do pagamento: R$ <input type="text" id="vl_cred_principal"
                            value="<?php echo number_format($valor_total, 2, ',', '.'); ?>"
                            oninput="formatarValorPagamentoCred()"></p>
                    <label for="parcelas_cartaoCred_entrada_principal">Quantidade de parcelas:</label>
                    <input id="parcelas_cartaoCred_entrada_principal" name="parcelas_cartaoCred_entrada_principal"
                        type="number" min="1" max="12" value="1" onchange="calcularValorParcelaCred()">
                    <p>Valor da Parcela: R$ <span
                            id="valor_parcela_cartaoCred_entrada"><?php echo number_format($valor_total, 2, ',', '.'); ?></span>
                    </p>
                    <p>Restante: R$ <span id="restante_cred_inicio">0,00</span></p>
                    <hr>
                </div>
                <div class="div_bt_principal">
                    <button type="button" class="btn_proximo" onclick="abrirPopupNovoCartao()">Usar outro
                        cartão</button>
                    <button type="button" id="btn_continuar_cartaoCred" class="continuar"
                        onclick="validarValorPagamentoCred()" style="display: none;">Continuar</button>
                </div>
            </div>

            <div id="popup_cartaoDeb" style="<?php if ($tipo_pagamento == 3) {
                echo 'display: block;';
            } else {
                echo 'display: none;';
            } ?>">
                <h3>Selecione o cartão de débito a ser usado</h3>
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
                                            <input type="checkbox" name="cartao_debito_selecionado"
                                                value="<?php echo $cartao['id']; ?>"
                                                data-num-cartao="<?php echo $cartao['num_cartao']; ?>"
                                                data-validade="<?php echo $cartao['validade']; ?>"
                                                data-cod-seguranca="<?php echo $cartao['cod_seguranca']; ?>"
                                                data-nome-cartao="<?php echo $cartao['nome']; ?>"
                                                data-valor-entreda="<?php echo $entrada; ?>" data-valor-parcela=""
                                                onchange="verificarCartaoSelecionado()">
                                        </td>
                                        <td>**** **** **** <?php echo substr($cartao['num_cartao'], -4); ?></td>
                                        <td>
                                            <button type="button"
                                                onclick="confirmarExclusaoCartao(<?php echo $cartao['id']; ?>)">Excluir</button>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div id="div_deb_principal" style="display: none;">
                    <hr>
                    <p>Valor do pagamento: R$ <input type="text" id="vl_deb_principal"
                            value="<?php echo number_format($valor_total, 2, ',', '.'); ?>"
                            oninput="formatarValorPagamentoDeb()"></p>
                    <p>Restante: R$ <span id="restante_deb_principal">0,00</span></p>
                    <hr>
                </div>
                <div class="div_bt_principal">
                    <button type="button" class="usar-outro-cartao-debito" onclick="abrirPopupNovoCartao()">Usar outro
                        cartão</button>
                    <button type="button" id="btn_continuar_cartaoDeb" onclick="validarValorPagamentoDeb()"
                        style="display: none;">Continuar</button>
                </div>
            </div>

            <div id="popup_novo_cartao" class="popup-content-cartoes" style="display: none;">
                <span class="close" onclick="fecharPopup('popup_novo_cartao')">&times;</span>
                <h3>Adicionar Novo Cartão</h3>
                <?php if (isset($mensagem_erro)): ?>
                    <p style="color: red;"><?php echo $mensagem_erro; ?></p>
                <?php endif; ?>
                <h3>Valor da compra: R$ <?php echo number_format($valor_total, 2, ',', '.'); ?></h3>


                <div id="dados_cartao">
                    <div>
                        <label for="tipo_cartao">Tipo de Cartão:</label>
                        <select id="tipo_cartao" name="tipo_cartao" onchange="atualizarBandeiras()">
                            <option value="Crédito" <?php if ($tipo_pagamento == 2)
                                echo 'selected'; ?>>Crédito</option>
                            <option value="Débito" <?php if ($tipo_pagamento == 3)
                                echo 'selected'; ?>>Débito</option>
                        </select>
                    </div>
                    <p>Bandeiras aceitas: <span
                            id="bandeiras_aceitas_texto"><?php echo $tipo_pagamento == 2 ? $admin_cartoes_credito : $admin_cartoes_debito; ?></span>
                    </p>
                    <div>
                        <label for="nome_cartao">Nome descrito no Cartão:</label>
                        <input type="text" id="nome_cartao" name="nome_cartao"
                            value="<?php echo htmlspecialchars($nome_cartao); ?>">
                    </div>
                    <div>
                        <label for="num_cartao">Número do Cartão:</label>
                        <input type="text" id="num_cartao" name="num_cartao" oninput="formatarNumeroCartao(this)"
                            value="">
                    </div>
                    <div>
                        <label for="validade">Validade:</label>
                        <input type="text" id="validade" name="validade" oninput="formatarValidadeCartao(this)"
                            value="">
                    </div>
                    <div>
                        <label for="cod_seguranca">Código de Segurança:</label>
                        <input type="text" id="cod_seguranca" name="cod_seguranca" oninput="formatarCodSeguranca(this)"
                            value="">
                    </div>
                    <p>Valor a pagar: R$ <input type="text" id="vl_novo"
                            value="<?php echo number_format($valor_total, 2, ',', '.'); ?>"
                            oninput="formatarValorPagamentoNovo(this)"></p>
                    <div id="div_parcelas_cartaoCred_entrada_novo"
                        style="<?php echo $tipo_pagamento == 2 ? 'display: block;' : 'display: none;'; ?>">
                        <label for="parcelas_cartaoCred_entrada_novo">Quantidade de parcelas:</label>
                        <input id="parcelas_cartaoCred_entrada_novo" name="parcelas_cartaoCred_entrada_novo"
                            type="number" min="1" value="1" max="12" onchange="calcularValorParcelaNovo()">
                        <p>Valor da Parcela: R$ <span
                                id="valor_parcelas_cartaoCred_entrada_novo"><?php echo number_format($valor_total, 2, ',', '.'); ?></span>
                        </p>
                    </div>
                    <p>Restante: R$ <span id="restante_novo">0,00</span></p>
                </div>
                <div class="popup-buttons">
                    <button type="button" class="cancelar" onclick="fecharPopup('popup_novo_cartao')">Cancelar</button>
                    <button type="button" class="btn_proximo" onclick="adicionarNovoCartao(1)">Usar e Salvar</button>
                    <button type="button" class="btn_proximo" onclick="adicionarNovoCartao(0)">Usar só dessa
                        vez</button>
                </div>
            </div>

            <div id="detalhes_cartao" style="display: block;">
                <input type="hidden" id="num_cartao_selecionado" name="num_cartao_selecionado" readonly>
                <input type="hidden" id="nome_cartao_selecionado" name="nome_cartao_selecionado" readonly>
                <input type="hidden" id="validade_selecionado" name="validade_selecionado" readonly>
                <input type="hidden" id="cod_seguranca_selecionado" name="cod_seguranca_selecionado" readonly>

                <input type="hidden" id="valor_parcela_cartao_selecionado" name="valor_parcela_cartao_selecionado"
                    readonly>
                <input type="hidden" id="parcelas_cartaoCred_entrada_selecionado"
                    name="parcelas_cartaoCred_entrada_selecionado" readonly>
                <input type="hidden" id="salvar_cartao" name="salvar_cartao" readonly>
                <input type="hidden" id="restante" name="restante" readonly>
            </div>

            <div id="popup-restante" class="popup-content" style="display: none;">
                <h3>Pagamento do Restante</h3>
                <p>Valor Restante: R$ <span id="pg_restante"></span></p>
                <label for="segunda_forma_pg">Selecione a forma de pagamento:</label>
                <select id="segunda_forma_pg" name="segunda_forma_pg" required>
                    <option value="0">Selecione</option>
                    <option value="1">PIX</option>
                    <option value="2">Cartão de Crédito</option>
                    <option value="3">Cartão de Débito</option>
                </select>
                <div>
                    <button type="button" id="btn_voltar" onclick="voltarParaEntrada()">Voltar</button>
                    <button type="submit" id="btn_continuar_pg" name="btn_continuar_pg" class="btn_continuar"
                        onclick="return validarFormulario()">Continuar</button>
                </div>
            </div>

            <div id="popup-confirmacao" class="popup-content" style="display: none;">
                <h3>Confirmação de Pagamento</h3>
                <p>Ao clicar em "Finalizar", você concorda com os termos e condições de compra.</p>
                <p id="msg_erro" style="color: red; display: none;"></p>
                <p id="msg_sucesso" style="color: green; display: none;"></p>
                <button type="button" id="btn_cancelar" class="btn_cancelar"
                    onclick="fecharPopupConfirmar()">Cancelar</button>
                <button type="button" id="btn_finalizar" class="btn_continuar"
                    onclick="finalizarCompra()">Finalizar</button>
            </div>
        </form>

        <form id="form-voltar" action="forma_entrega.php" method="GET">
            <input type="hidden" name="id_cliente" value="<?php echo $id_cliente; ?>">
            <input type="hidden" name="id_parceiro" value="<?php echo $id_parceiro; ?>">
            <input type="hidden" name="valor_total" value="<?php echo $valor_total; ?>">
            <input type="hidden" name="valor_frete" value="<?php echo $valor_frete; ?>">
            <input type="hidden" name="detalhes_produtos" value="<?php echo $detalhes_produtos; ?>">
            <input type="hidden" name="entrega" value="<?php echo $entrega; ?>">
            <input type="hidden" name="rua" value="<?php echo $rua; ?>">
            <input type="hidden" name="bairro" value="<?php echo $bairro; ?>">
            <input type="hidden" name="numero" value="<?php echo $numero; ?>">
            <input type="hidden" name="contato" value="<?php echo $contato; ?>">
            <button type="submit">Voltar</button>
        </form>
    </div>
</body>
<script>

    function atualizarBandeiras() {
        const tipoCartao = document.getElementById('tipo_cartao').value;
        const bandeirasTexto = document.getElementById('bandeiras_aceitas_texto');
        const div_parcelas_cartaoCred_entrada_novo = document.getElementById('div_parcelas_cartaoCred_entrada_novo');
        if (tipoCartao === 'Crédito') {
            document.getElementById("tipo_pagamento_principal").value = 2;
            console.log('Tipo de pagamento: ' + document.getElementById("tipo_pagamento_principal").value);
            bandeirasTexto.textContent = "<?php echo $admin_cartoes_credito; ?>";
            div_parcelas_cartaoCred_entrada_novo.style.display = 'block';
        } else if (tipoCartao === 'Débito') {
            document.getElementById("tipo_pagamento_principal").value = 3;
            console.log('Tipo de pagamento: ' + document.getElementById("tipo_pagamento_principal").value);
            bandeirasTexto.textContent = "<?php echo $admin_cartoes_debito; ?>";
            div_parcelas_cartaoCred_entrada_novo.style.display = 'none';
        }
    }

    function fecharPopupConfirmar() {
        //document.getElementById('popup-confirmacao').style.display = 'none';
        //document.getElementById('popup-background').style.display = 'none';

        if (document.getElementById('popup-confirmacao').style.display === 'block') {
            //console.log('Popup de novo cartão está aberto.');
            document.getElementById('popup-confirmacao').style.display = 'none';
        }

        if (document.getElementById('popup-background').style.display === 'block') {
            console.log('Popup de novo cartão está aberto.');
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
        document.getElementById('data_hora').value = dataFormatada;
    }

    function abrirPopupNovoCartao() {
        const popupNovoCartao = document.getElementById('popup_novo_cartao');
        const checkboxes = document.querySelectorAll('input[name="cartao_credito_selecionado"], input[name="cartao_debito_selecionado"]');
        // Desmarcar todos os outros cartões
        checkboxes.forEach((cb) => {
            cb.checked = false;
            //console.log('Desmarcando cartão:', cb);
            limparDetalhesCartao();
        });
        if (popupNovoCartao) {
            popupNovoCartao.style.display = 'block'; // Exibir o popup
            popupNovoCartao.style.position = 'fixed'; // Garantir que o popup seja exibido sobre a página
            popupNovoCartao.style.top = '50%';
            popupNovoCartao.style.left = '50%';
            popupNovoCartao.style.transform = 'translate(-50%, -50%)';
            popupNovoCartao.style.zIndex = '1000'; // Garantir que o popupNovoCartao fique acima do background

            document.getElementById('popup-background').style.display = 'block';
            document.getElementById('popup-background').style.zIndex = "999"; // Garantir que o background fique abaixo dos popups
            //console.log('Popup de novo cartão aberto.');
        } else {
            console.error('Elemento com ID "popup_novo_cartao" não encontrado.');
        }
    }

    function fecharPopup(popupId) {
        document.getElementById(popupId).style.display = 'none';
        document.getElementById('popup-background').style.display = 'none';
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

    function mostrarParcelasCartaoCred(Primeiro_pag) {
        const parcelasSelect = document.getElementById('parcelas_cartaoCred_entrada_principal');
        parcelasSelect.innerHTML = '';

        const maxParcelas = 12; // Defina o número máximo de parcelas

        if (maxParcelas > 0) {
            for (let i = 1; i <= maxParcelas; i++) {
                let valorParcela;
                let labelJuros = ''; // Texto para indicar se há juros

                if (i > 3) {
                    // Aplicar juros compostos para parcelas acima de 3x
                    const taxaJuros = 0.0299; // 2.99% ao mês
                    valorParcela = (Primeiro_pag * Math.pow(1 + taxaJuros, i)) / i;
                    labelJuros = ' com 2,99% a.m.';
                } else {
                    // Parcelas sem juros
                    valorParcela = Primeiro_pag / i;
                    labelJuros = ' sem juros';
                }

                const option = document.createElement('option');
                option.value = `${i}x de R$ ${valorParcela.toFixed(2).replace('.', ',')}${labelJuros}`;
                option.textContent = `${i}x de R$ ${valorParcela.toFixed(2).replace('.', ',')}${labelJuros}`;
                parcelasSelect.appendChild(option);
            }
        } else {
            console.error('Erro: maxParcelas inválido.');
        }
        calcularValorParcela();
    }

    /*function mostrarParcelasCartaoCred(restante) {
        const parcelasSelect = document.getElementById('parcelas_cartaoCred_entrada_secundario');
        parcelasSelect.innerHTML = '';

        const maxParcelas = 12; // Defina o número máximo de parcelas

        if (maxParcelas > 0) {
            for (let i = 1; i <= maxParcelas; i++) {
                let valorParcela;
                let labelJuros = ''; // Texto para indicar se há juros

                if (i > 3) {
                    // Aplicar juros compostos para parcelas acima de 3x
                    const taxaJuros = 0.0299; // 2.99% ao mês
                    valorParcela = (restante * Math.pow(1 + taxaJuros, i)) / i;
                    labelJuros = ' com 2,99% a.m.';
                } else {
                    // Parcelas sem juros
                    valorParcela = restante / i;
                    labelJuros = ' sem juros';
                }

                const option = document.createElement('option');
                option.value = `${i}x de R$ ${valorParcela.toFixed(2).replace('.', ',')}${labelJuros}`;
                option.textContent = `${i}x de R$ ${valorParcela.toFixed(2).replace('.', ',')}${labelJuros}`;
                parcelasSelect.appendChild(option);
            }
        } else {
            console.error('Erro: maxParcelas inválido.');
        }
        calcularValorParcela();
    }*/

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

    function carregarDetalhesCartao(cartao) {
        const valor_total = document.getElementById('valor_total').value;
        const numCartao = cartao.dataset.numCartao;
        const validade = cartao.dataset.validade;
        const codSeguranca = cartao.dataset.codSeguranca;
        const nomeCartao = cartao.dataset.nomeCartao;

        document.getElementById('num_cartao_selecionado').value = numCartao;
        document.getElementById('validade_selecionado').value = validade;
        document.getElementById('cod_seguranca_selecionado').value = codSeguranca;
        document.getElementById('nome_cartao_selecionado').value = nomeCartao;

        // Exibir os campos e botões relevantes

        if (cartao.name === "cartao_credito_selecionado") {
            const restante_cred_principal = document.getElementById('restante_cred_inicio').textContent.replace(/\./g, '').replace(',', '.');

            //console.log('Cartão de crédito selecionado');

            document.getElementById('div_cred_principal').style.display = 'block';
            document.getElementById('btn_continuar_cartaoCred').style.display = 'block';
            document.getElementById('btn_continuar_cartaoDeb').style.display = 'none';

            document.getElementById('valor_parcela_cartao_selecionado').value = valor_total;
            document.getElementById('parcelas_cartaoCred_entrada_principal').value = '1';
            document.getElementById('salvar_cartao').value = '0';
            document.getElementById('restante').value = restante_cred_principal;


        } else if (cartao.name === "cartao_debito_selecionado") {
            const restante_deb_principal = document.getElementById('restante_deb_principal').textContent.replace(/\./g, '').replace(',', '.');

            //console.log('Cartão de débito selecionado');

            document.getElementById('div_deb_principal').style.display = 'block';
            document.getElementById('btn_continuar_cartaoDeb').style.display = 'block';
            document.getElementById('btn_continuar_cartaoCred').style.display = 'none';

            document.getElementById('valor_parcela_cartao_selecionado').value = valor_total;
            document.getElementById('parcelas_cartaoCred_entrada_principal').value = '';
            document.getElementById('salvar_cartao').value = '0';
            document.getElementById('restante').value = restante_deb_principal;
        }
    }

    function limparDetalhesCartao() {
        // Limpar os detalhes do cartão   

        document.getElementById('div_cred_principal').style.display = 'none';
        document.getElementById('div_deb_principal').style.display = 'none';

        document.getElementById('num_cartao_selecionado').value = '';
        document.getElementById('nome_cartao_selecionado').value = '';
        document.getElementById('validade_selecionado').value = '';
        document.getElementById('cod_seguranca_selecionado').value = '';

        //document.getElementById('vl_cred_principal').value = "<?php echo number_format($valor_total, 2, ',', '.'); ?>";
        document.getElementById('valor_parcela_cartao_selecionado').value = '';
        document.getElementById('parcelas_cartaoCred_entrada_principal').value = 1;
        document.getElementById('salvar_cartao').value = '';
        document.getElementById('restante').value = '';

        document.getElementById('btn_continuar_cartaoCred').style.display = 'none';
        document.getElementById('btn_continuar_cartaoDeb').style.display = 'none';
    }

    function validarCartao() {
        const numCartao = document.getElementById('num_cartao').value.replace(/\s/g, '');
        const validade = document.getElementById('validade').value;
        const codSeguranca = document.getElementById('cod_seguranca').value;

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

        // Verificar se o nome do cartão não está vazio
        const nomeCartao = document.getElementById('nome_cartao').value;
        if ((nomeCartao.trim() === '') || nomeCartao.length < 5) {
            alert('O nome do cartão precisa ser preenchido corretamente.');
            document.getElementById('nome_cartao').focus();
            return false;
        }
        return true;
    }

    function adicionarNovoCartao(salvar) {
        if (validarCartao()) {
            document.getElementById('salvar_cartao').value = salvar === 1 ? 1 : 0;
            //console.log('Valor de salvar_cartao:', document.getElementById('salvar_cartao').value);

            // Preencher os detalhes do cartão selecionado
            document.getElementById('num_cartao_selecionado').value = document.getElementById('num_cartao').value.replace(/\s/g, '');
            document.getElementById('validade_selecionado').value = document.getElementById('validade').value;
            document.getElementById('cod_seguranca_selecionado').value = document.getElementById('cod_seguranca').value;
            document.getElementById('nome_cartao_selecionado').value = document.getElementById('nome_cartao').value;
            document.getElementById('parcelas_cartaoCred_entrada_selecionado').value = document.getElementById('parcelas_cartaoCred_entrada_novo').value;
            document.getElementById('valor_parcela_cartao_selecionado').value = parseFloat(document.getElementById('valor_parcelas_cartaoCred_entrada_novo').textContent.replace('.', '').replace(',', '.'));

            let vl_a_pg_novo = parseFloat(document.getElementById('vl_novo').value.replace(/\./g, '').replace(',', '.'));
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
                    abrirPopupRestante();
                }
            }
        }
    }

    function voltarParaEntrada() {
        const tipo_pagamento = <?php echo $tipo_pagamento; ?>;
        const popupRestante = document.getElementById("popup-restante");
        const popup_background = document.getElementById('popup-background');
        const checkboxes = document.querySelectorAll('input[name="cartao_credito_selecionado"], input[name="cartao_debito_selecionado"]');

        // Desmarcar todos os outros cartões
        checkboxes.forEach((cb) => {
            cb.checked = false;
            //console.log('Desmarcando cartão:', cb);
            //limparDetalhesCartao();
        });

        popup_background.style.display = "none";
        popupRestante.style.display = "none";

        if (tipo_pagamento == 1) {
            document.getElementById('popup-pix').style.display = "block";
        } else if (tipo_pagamento == 2) {
            document.getElementById('popup_cartaoCred').style.display = "block";
        } else {
            document.getElementById('popup_cartaoDeb').style.display = "block";
        }
        //limparDetalhesCartao();
    };

    /*function cancelarSalvarUsar() {
        document.getElementById('popup_confirmacao_salvar_usar').style.display = 'none';
        document.getElementById('popup-background').style.display = 'none';
    }*/

    function calcularValorParcelaCred() {
        const valorTotalCompra = document.getElementById('valor_total').value;
        const valor = document.getElementById('vl_cred_principal');
        const valorTotal = parseFloat(valor.value.replace(/\./g, '').replace(',', '.')) || 0;
        const numParcelas = parseInt(document.getElementById('parcelas_cartaoCred_entrada_principal').value) || 1;
        const restante_cred_inicio = document.getElementById('restante_cred_inicio').textContent;

        if (numParcelas <= 0) {
            alert('O número de parcelas deve ser maior que zero.');
            return;
        }

        let valorParcela;
        if (numParcelas > 3) {
            const taxaJuros = 0.0299; // 2.99% ao mês
            valorParcela = Math.round(valorTotal * Math.pow(1 + taxaJuros, numParcelas)) / numParcelas;

        } else {
            valorParcela = valorTotal / numParcelas; // Sem juros para até 3 parcelas
            valorParcela = Math.round((valorTotal / numParcelas) * 100) / 100; // Arredondar para 2 casas decimais
        }

        const valorParcelaFormatado = valorParcela.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        document.getElementById('valor_parcela_cartaoCred_entrada').textContent = valorParcela.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('valor_parcela_cartao_selecionado').value = valorParcela.toFixed(2);;
        document.getElementById('parcelas_cartaoCred_entrada_selecionado').value = numParcelas;

        const pg_restante = document.getElementById('pg_restante');
        document.getElementById('restante_cred_inicio').textContent = (valorTotalCompra - valorTotal).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('restante').value = (valorTotalCompra - valorTotal).toFixed(2);
        pg_restante.textContent = (valorTotalCompra - valorTotal).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    }

    function validarValorPagamentoCred() {
        const vl_cred_principal = document.getElementById('vl_cred_principal');
        const valorTotal = Math.round(<?php echo $valor_total; ?> * 100) / 100; // Arredondar para 2 casas decimais

        let valorCred = Math.round((parseFloat(vl_cred_principal.value.replace(/\./g, '').replace(',', '.')) || 0) * 100) / 100; // Arredondar para 2 casas decimais

        if (valorCred > valorTotal) {
            alert('O valor não pode ser maior que o valor total.');
            vl_cred_principal.value = "<?php echo number_format($valor_total, 2, ',', '.'); ?>";
            document.getElementById('vl_cred_principal').value = "<?php echo number_format($valor_total, 2, ',', '.'); ?>";
            calcularValorParcelaCred();
            return;
        } else if (valorCred <= 0) {
            alert('O valor não pode ser igual ou menor que 0.');
            vl_cred_principal.value = "<?php echo number_format(0, 2, ',', '.'); ?>";
            document.getElementById('vl_cred_principal').value = "<?php echo number_format($valor_total, 2, ',', '.'); ?>";
            calcularValorParcelaCred();
            return;
        }


        // Se o valor for válido, continuar com o processo
        if (valorCred == valorTotal) {
            // abrir popup de confirmação de compra
            // Esconder os outros popups
            const popupConfirmacao = document.getElementById("popup-confirmacao");
            const popup_background = document.getElementById('popup-background');

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

            abrirPopupConfirmacaoCompra();
        } else {
            abrirPopupRestante();
        }
    }

    function validarValorPagamentoDeb() {
        const vl_deb_principal = document.getElementById('vl_deb_principal');
        const valorTotal = Math.round(<?php echo $valor_total; ?> * 100) / 100; // Arredondar para 2 casas decimais

        let valorDeb = Math.round((parseFloat(vl_deb_principal.value.replace(/\./g, '').replace(',', '.')) || 0) * 100) / 100; // Arredondar para 2 casas decimais

        if (valorDeb > valorTotal) {
            alert('O valor não pode ser maior que o valor total.');
            vl_deb_principal.value = "<?php echo number_format($valor_total, 2, ',', '.'); ?>";
            document.getElementById('vl_deb_principal').value = "<?php echo number_format($valor_total, 2, ',', '.'); ?>";
            document.getElementById('restante_deb_principal').textContent = '0,00';
            document.getElementById('valor_parcela_cartao_selecionado').value = valorTotal;
            document.getElementById('restante').value = '0.00';
            //calcularValorParcelaCred();
            return;
        } else if (valorDeb <= 0) {
            alert('O valor não pode ser igual ou menor que 0.');
            vl_deb_principal.value = "<?php echo number_format(0, 2, ',', '.'); ?>";
            document.getElementById('vl_deb_principal').value = "<?php echo number_format($valor_total, 2, ',', '.'); ?>";
            document.getElementById('restante_deb_principal').textContent = '0,00';
            document.getElementById('valor_parcela_cartao_selecionado').value = valorTotal;
            document.getElementById('restante').value = '0.00';
            //calcularValorParcelaCred();
            return;
        }


        // Se o valor for válido, continuar com o processo
        if (valorDeb == valorTotal) {
            // abrir popup de confirmação de compra
            // Esconder os outros popups
            const popupConfirmacao = document.getElementById("popup-confirmacao");
            const popup_background = document.getElementById('popup-background');

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

            abrirPopupConfirmacaoCompra();
        } else {
            abrirPopupRestante();
        }
    }

    function abrirPopupConfirmacaoCompra() {
        const popupConfirmacao = document.getElementById("popup-confirmacao");
        const popup_background = document.getElementById('popup-background');
        //const popupRestante = document.getElementById("popup-restante");
        //const popup_novo_cartao = document.getElementById('popup_novo_cartao');

        //popup_novo_cartao.style.display = 'block';
        //popup_novo_cartao.style.zIndex = "999";

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

    function formatarValorPagamentoPix(input) {
        let valor = input.value.replace(/[^\d]/g, ''); // Remove tudo que não for número
        if (valor) {
            valor = (parseInt(valor) / 100).toFixed(2); // Divide por 100 para ajustar os centavos
            input.value = valor.replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); // Formata com ponto de milhar e vírgula para centavos
        } else {
            input.value = '';
        }
        calcularValorRestantePix();
    }

    function calcularValorRestantePix() {
        const valorTotalInput = document.getElementById('valor_total');
        const vl_pix = document.getElementById('vl_pix');
        const restante_pix = document.getElementById('restante_pix');

        // Converter os valores para float
        const valorTotal = parseFloat(valorTotalInput.value) || 0;
        const valorPagamento = parseFloat(vl_pix.value.replace(/\./g, '').replace(',', '.')) || 0;

        // Calcular valor restante
        const restante = valorTotal - valorPagamento;

        // Exibir o valor restante formatado
        restante_pix.textContent = 'Valor do restante: R$ ' + restante.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        document.getElementById('qr_code_pix').style.display = 'none';
        document.getElementById('link_pix').style.display = 'none';
        document.getElementById('btn_continuar').style.display = 'none';

        document.getElementById('valor_parcela_cartao_selecionado').value = Math.round(valorPagamento * 100) / 100; // Arredondar para 2 casas decimais
        document.getElementById('parcelas_cartaoCred_entrada_selecionado').value = '1';
        document.getElementById('restante').value = Math.round(restante * 100) / 100; // Arredondar para 2 casas decimais

    }

    // Função para gerar o QR Code
    window.gerarQRCode = function () {
        const p_testo_orientacao = document.getElementById("p_testo_orientacao");
        const qrCodePix = document.getElementById("qr_code_pix");
        const linkPix = document.getElementById("link_pix");
        const btnContinuar = document.getElementById("btn_continuar");

        p_testo_orientacao.style.display = "block";
        qrCodePix.style.display = "block";
        linkPix.style.display = "block";
        btnContinuar.style.display = "inline-block";
    };

    function validarValorPix() {
        const valor = document.getElementById('valor_total');
        const valorTotal = parseFloat(valor.value) || 0;
        const vl_pix = document.getElementById('vl_pix');
        const valorPagamento = parseFloat(vl_pix.value.replace(/\./g, '').replace(',', '.')) || 0;
        const valorTotalArredondado = Math.round(valorTotal * 100) / 100; // Arredondar para 2 casas decimais
        const vl_pix_arredondado = Math.round(valorPagamento * 100) / 100; // Arredondar para 2 casas decimais
        const restante_pix = document.getElementById('restante_pix');

        vl_pix.value = vl_pix_arredondado.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        //console.log('Valor a pagar:', vl_pg_pix_arredondado);
        //console.log('Valor total:', valorTotalArredondado);
        if (vl_pix_arredondado > valorTotalArredondado) {
            alert('O valor a pagar não pode ser maior que o valor total.');
            vl_pix.value = "<?php echo number_format($valor_total, 2, ',', '.'); ?>";
            restante_pix.textContent = 'Valor do restante: R$ 0,00';
            return;
        } else if (vl_pix_arredondado <= 0) {
            alert('O valor a pagar deve ser maior que zero.');
            vl_pix.value = "<?php echo number_format($valor_total, 2, ',', '.'); ?>";
            restante_pix.textContent = 'Valor do restante: R$ 0,00';
            return;
        }
        calcularValorRestantePix();
        gerarQRCode();
    }

    function confirmar_pix() {
        const valor = document.getElementById('valor_total');
        const valorTotal = parseFloat(valor.value) || 0;
        const vl_pix = document.getElementById('vl_pix');
        const valorPagamento = parseFloat(vl_pix.value.replace(/\./g, '').replace(',', '.')) || 0;
        const valorTotalArredondado = Math.round(valorTotal * 100) / 100; // Arredondar para 2 casas decimais
        const vl_pix_arredondado = Math.round(valorPagamento * 100) / 100; // Arredondar para 2 casas decimais
        const restante_pix = document.getElementById('restante_pix');

        vl_pix.value = vl_pix_arredondado.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        //console.log('Valor a pagar:', vl_pg_pix_arredondado);
        //console.log('Valor total:', valorTotalArredondado);
        if (vl_pix_arredondado < valorTotalArredondado && vl_pix_arredondado > 0) {
            abrirPopupRestante();
        } else {
            finalizarCompra();
        }
    }

    function formatarValorPagamentoCred() {
        let valor = document.getElementById('vl_cred_principal').value.replace(/[^\d]/g, ''); // Remove tudo que não for número
        if (valor) {
            valor = (parseInt(valor) / 100).toFixed(2); // Divide por 100 para ajustar os centavos
            document.getElementById('vl_cred_principal').value = valor.replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); // Formata com ponto de milhar e vírgula para centavos
        } else {
            document.getElementById('vl_cred_principal').value = '';
        }
        calcularValorParcelaCred();
    }

    function formatarValorPagamentoDeb() {
        let valor_total = document.getElementById('valor_total').value.replace(/[^\d]/g, ''); // Remove tudo que não for número
        let valor = document.getElementById('vl_deb_principal').value.replace(/[^\d]/g, ''); // Remove tudo que não for número
        //let restante = document.getElementById('restante_deb_principal').textContent.replace(/[^\d]/g, ''); // Remove tudo que não for número

        if (valor) {
            valor_total = (parseInt(valor_total) / 100).toFixed(2); // Divide por 100 para ajustar os centavos
            valor = (parseInt(valor) / 100).toFixed(2); // Divide por 100 para ajustar os centavos
            //restante = (parseInt(restante) / 100).toFixed(2); // Divide por 100 para ajustar os centavos

            restante = valor_total - valor; // Calcula o restante
            restante = Math.round(restante * 100) / 100; // Arredondar para 2 casas decimais

            //console.log('Valor total:', valor_total);
            //console.log('Valor:', valor);
            //console.log('Restante:', restante);

            document.getElementById('vl_deb_principal').value = valor.replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); // Formata com ponto de milhar e vírgula para centavos
            document.getElementById('restante_deb_principal').textContent = restante.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            document.getElementById('valor_parcela_cartao_selecionado').value = valor;
            document.getElementById('restante').value = restante; // Atualiza o valor da parcela


        } else {
            document.getElementById('vl_deb_principal').value = '';
        }

        //calcularValorParcela();
    }

    function formatarValorPagamentoNovo(input) {
        let valor = input.value.replace(/[^\d]/g, ''); // Remove tudo que não for número
        if (valor) {
            valor = (parseInt(valor) / 100).toFixed(2); // Divide por 100 para ajustar os centavos
            input.value = valor.replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); // Formata com ponto de milhar e vírgula para centavos
        } else {
            input.value = '';
        }
        calcularValorParcelaNovo();
    }

    function calcularValorParcelaNovo() {
        const valorTotalCompra = document.getElementById('valor_total').value;
        let valor = document.getElementById('vl_novo').value.replace(/\./g, '').replace(',', '.'); // Remove pontos de milhar e troca vírgula por ponto
        const valorTotal = valor;
        const numParcelas = parseInt(document.getElementById('parcelas_cartaoCred_entrada_novo').value) || 1;
        const restante = document.getElementById('restante_novo').textContent.replace(/\./g, '').replace(',', '.'); // Remove pontos de milhar e troca vírgula por ponto

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
        document.getElementById('restante_novo').textContent = (valorTotalCompra - valorTotal).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function abrirPopupRestante() {
        const popupRestante = document.getElementById("popup-restante");
        const pg_restante = document.getElementById('restante').value;

        document.getElementById('pg_restante').textContent = pg_restante.replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        document.getElementById('popup_novo_cartao').style.zIndex = "999"; // Garantir que o popup fique abaixo do background
        document.getElementById('popup-background').style.display = "block";
        document.getElementById('popup-background').style.zIndex = "1000"; // Garantir que o background fique abaixo dos popups

        popupRestante.style.display = "block";
        popupRestante.style.position = "fixed";
        popupRestante.style.top = "50%";
        popupRestante.style.left = "50%";
        popupRestante.style.transform = "translate(-50%, -50%)";
        popupRestante.style.zIndex = "1001"; // Garantir que o popupRestante fique acima do background
        popupRestante.style.backgroundColor = "#fff";
        popupRestante.style.padding = "20px";
        popupRestante.style.boxShadow = "0 4px 8px rgba(0, 0, 0, 0.2)";
        obterHorarioLocal();
    }

    // Função para enviar os dados via JavaScript em formato JSON
    function finalizarCompra() {
        obterHorarioLocal();

        const dataFormatada = document.getElementById('data_hora').value;
        const popup_confirmacao = document.getElementById("popup-confirmacao");
        // Calcular o valor total da compra no cliente (se necessário)
        const restante = parseFloat(document.getElementById("restante").value) || 0;
        const valorFrete = parseFloat(document.getElementById("valor_frete").value) || 0;
        const valorTotal = parseFloat(document.getElementById("valor_total").value) || 0;
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

        if (restante > 0 || restante < 0) {
            //console.error("Erro: O valor restante não pode ser menor ou igual a zero.");
            //document.getElementById("msg_erro").textContent = "Erro: O valor restante não pode ser menor ou igual a zero.";
            //document.getElementById("msg_erro").style.display = "block";
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
            id_cliente: document.getElementById("id_cliente").value,
            id_parceiro: document.getElementById("id_parceiro").value,
            detalhes_produtos: document.getElementById("detalhes_produtos").value,

            valor_frete: valorFrete,
            total_compra: totalCompra, // Enviar o valor total calculado
            momen_pagamento: document.getElementById("momen_pagamento").value,

            tipo_entrega: document.getElementById("entrega").value,
            tipo_pagamento: document.getElementById("tipo_pagamento_principal").value,
            valor_entrada: '', // Valor de entrada
            restante: document.getElementById("restante").value,
            saldo_usado: document.getElementById("saldo_usado").value,

            num_cartao: document.getElementById("num_cartao_selecionado").value,
            nome_cartao: document.getElementById("nome_cartao_selecionado").value,
            validade: document.getElementById("validade_selecionado").value,
            cod_seguranca: document.getElementById("cod_seguranca_selecionado").value,
            valor_parcela: document.getElementById("valor_parcela_cartao_selecionado").value, // Valor da parcela                
            qt_parcelas_entrada: document.getElementById("parcelas_cartaoCred_entrada_selecionado").value,
            bandeiras_aceitas: document.getElementById("bandeiras_aceitas").value,
            salvar_cartao: document.getElementById("salvar_cartao").value,

            rua: document.getElementById("rua").value,
            bairro: document.getElementById("bairro").value,
            numero: document.getElementById("numero").value,
            contato: document.getElementById("contato").value,
            comentario: document.getElementById("comentario").value
        };

        fetch("finalizar_compra_pg_online.php", {
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

    function validarFormulario() {
        // Exemplo de validação de campo obrigatório
        const valorTotal = document.getElementById('valor_total').value;
        if (!valorTotal || parseFloat(valorTotal) <= 0) {
            alert("O valor total deve ser maior que zero.");
            return false; // Impede o envio do formulário
        }

        const tipoPagamento = document.getElementById('segunda_forma_pg').value;
        if (tipoPagamento === '0') { // Verifica se o valor é '1', que representa "Selecione"
            alert("Selecione uma forma de pagamento.");
            return false; // Impede o envio do formulário
        }

        // Garantir que o formulário seja enviado
        console.log("Validação do formulário executada com sucesso.");
        return true; // Permite o envio do formulário
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

    document.addEventListener('scroll', function () {
        const popupCartoes = document.querySelector('.popup-content-cartoes');
        if (popupCartoes.style.display === 'block') {
            const scrollPosition = window.scrollY;
            popupCartoes.style.top = `${50 + scrollPosition / 10}%`; // Ajusta a posição com base na rolagem
        }
    });
</script>

</html>