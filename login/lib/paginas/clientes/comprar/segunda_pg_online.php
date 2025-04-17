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
//$nome_cartao = isset($nome_cartao) ? $nome_cartao : '';
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
        #popup-background {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            /* Fundo preto com transparência */
            z-index: 998;
            /* Abaixo do popup */
        }

        /* Estilo para o popup */
        #popup_novo_cartao {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            max-width: 500px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 999;
            /* Acima do fundo */
            padding: 20px;
        }

        /* Botão de fechar */
        #popup_novo_cartao .close {
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
            justify-content: space-between;
            margin-top: 20px;
        }

        .popup-buttons .btn_proximo {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }

        .popup-buttons .btn_proximo:hover {
            background-color: #0056b3;
        }

        .popup-buttons .cancelar {
            background-color: #dc3545;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }

        .popup-buttons .cancelar:hover {
            background-color: #a71d2a;
        }
    </style>
</head>

<body>
    <div id="popup-background" class="popup-background"></div>
    <h3>2ª Formas de pagamento</h3>
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
        <p>Saldo Usado: <input type="text" id="saldo_usado_valor" name="saldo_usado_valor"
                value="<?= htmlspecialchars($_POST['saldo_usado'] ?? '') ?>" readonly></p>
        <p>Restante: <input type="text" id="restante_valor" name="restante_valor"
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
        <p>Valor Parcela Cartão Selecionado: <input type="text" id="valor_parcela_cartao_selecionado_valor"
                name="valor_parcela_cartao_selecionado_valor"
                value="<?= htmlspecialchars($_POST['valor_parcela_cartao_selecionado'] ?? '') ?>" readonly></p>
        <p>Parcelas Cartão Crédito Entrada Selecionado: <input type="text"
                id="parcelas_cartaoCred_entrada_selecionado_valor" name="parcelas_cartaoCred_entrada_selecionado_valor"
                value="<?= htmlspecialchars($_POST['parcelas_cartaoCred_entrada_selecionado'] ?? '') ?>" readonly></p>
        <p>Salvar Cartão: <input type="text" id="salvar_cartao_valor" name="salvar_cartao_valor"
                value="<?= htmlspecialchars($_POST['salvar_cartao'] ?? '') ?>" readonly></p>

        <p>Segunda Forma de Pagamento: <input type="text" id="segunda_forma_pg_valor" name="segunda_forma_pg_valor"
                value="<?= htmlspecialchars($_POST['segunda_forma_pg'] ?? '') ?>" readonly></p>
    </div>
    <div id="popup-pix"
        style="display: <?php echo (isset($segunda_forma_pg) && $segunda_forma_pg == '1') ? 'block' : 'none'; ?>;">
        <h3>Pagar entrada com PIX</h3>
        <p>Valor do pagamento: R$ <input type="text" value="<?php echo number_format($valor_total, 2, ',', '.'); ?>"
                id="vl_pix" name="vl_pix" oninput="formatarValorPagamentoPix(this)">
        </p>
        <p id="restante_pix">Valor do restante: R$ <?php echo number_format('0', 2, ',', '.'); ?></p>
        <p id="p_testo_orientacao" style="display: none;">Abra o aplicativo do seu banco e faça a leitura do QR
            Code abaixo para efetuar o pagamento.</p>

        <img id="qr_code_pix" src="" alt="QR Code PIX" style="display: none;">
        <br>
        <p id="link_pix" style="display: none;">Link de cópia e cola do PIX: <a href="#" id="pix_link">Copiar</a></p>
        <button type="button" id="id_gr_qrCode" onclick="validarValorPix()">Gerar QR Code</button>
        <button type="button" id="btn_continuar" onclick="confirmar_pix()" style="display: none;">Continuar</button>
    </div>

    <div id="popup_cartaoCred"
        style="display: <?php echo (isset($segunda_forma_pg) && $segunda_forma_pg == '2') ? 'block' : 'none'; ?>;">
        <hr>
            <p>Valor Restante: R$ <span id="restante_cred_inicio"><?php echo number_format($restante, 2, ',', '.'); ?></span></p>
        <hr>
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
        <div class="div_bt_principal">
            <button type="button" class="btn_proximo" onclick="abrirPopupNovoCartao()">Usar outro
                cartão</button>
            <button type="button" id="btn_continuar_cartaoCred" class="continuar" onclick="validarValorPagamentoCred()"
                style="display: none;">Continuar</button>
        </div>
    </div>
    <div id="popup_cartaoDeb"
        style="display: <?php echo (isset($segunda_forma_pg) && $segunda_forma_pg == '3') ? 'block' : 'none'; ?>;">
        <div id="div_deb_principal" style="display: block;">
            <hr>
                <p>Valor Restante: R$ <span id="restante_deb_principal"><?php echo number_format($restante, 2, ',', '.'); ?></span></p>
            <hr>
        </div>
        <h3>Selecione o Cartão de Débito a ser usado.</h3>
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
                    <option value="Crédito" <?php if ($segunda_forma_pg == 2)
                        echo 'selected'; ?>>Crédito</option>
                    <option value="Débito" <?php if ($segunda_forma_pg == 3)
                        echo 'selected'; ?>>Débito</option>
                </select>
            </div>
            <p>Bandeiras aceitas: <span
                    id="bandeiras_aceitas_texto"><?php echo $segunda_forma_pg == 2 ? $admin_cartoes_credito : $admin_cartoes_debito; ?></span>
            </p>
            <div>
                <label for="nome_cartao">Nome descrito no Cartão:</label>
                <input type="text" id="nome_cartao" name="nome_cartao"
                    value="<?php echo htmlspecialchars($nome_cartao); ?>">
            </div>
            <div>
                <label for="num_cartao">Número do Cartão:</label>
                <input type="text" id="num_cartao" name="num_cartao" oninput="formatarNumeroCartao(this)" value="">
            </div>
            <div>
                <label for="validade">Validade:</label>
                <input type="text" id="validade" name="validade" oninput="formatarValidadeCartao(this)" value="">
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
                style="<?php echo $segunda_forma_pg == 2 ? 'display: block;' : 'display: none;'; ?>">
                <label for="parcelas_cartaoCred_entrada_novo">Quantidade de parcelas:</label>
                <input id="parcelas_cartaoCred_entrada_novo" name="parcelas_cartaoCred_entrada_novo" type="number"
                    min="1" value="1" max="12" onchange="calcularValorParcelaNovo()">
                <p>Valor da Parcela: R$ <span
                        id="valor_parcelas_cartaoCred_entrada_novo"><?php echo number_format($valor_total, 2, ',', '.'); ?></span>
                </p>
            </div>
            <p>Restante: R$ <span id="restante_novo">0,00</span></p>
        </div>
        <div class="popup-buttons">
            <button type="button" class="cancelar" onclick="fecharPopup('popup_novo_cartao')">Cancelar</button>
            <button type="button" class="btn_proximo" onclick="adicionarNovoCartao(1)">Usar e Salvar</button>
            <button type="button" class="btn_proximo" onclick="adicionarNovoCartao(0)">Usar só dessa vez</button>
        </div>
    </div>
    <div id="detalhes_cartao" style="display: block;">
        <input type="text" id="nome_cartao_selecionado" name="nome_cartao_selecionado" readonly>
        <input type="text" id="num_cartao_selecionado" name="num_cartao_selecionado" readonly>
        <input type="text" id="validade_selecionado" name="validade_selecionado" readonly>
        <input type="text" id="cod_seguranca_selecionado" name="cod_seguranca_selecionado" readonly>

        <input type="text" id="valor_parcela_cartao_selecionado" name="valor_parcela_cartao_selecionado"readonly>
        <input type="text" id="parcelas_cartaoCred_entrada_selecionado" name="parcelas_cartaoCred_entrada_selecionado" readonly>
        <input type="text" id="salvar_cartao" name="salvar_cartao" readonly>
        <input type="text" id="restante" name="restante" readonly>
    </div>
</body>
    <script>
        function abrirPopupNovoCartao() {
            const popupNovoCartao = document.getElementById('popup_novo_cartao');
            const popupBackground = document.getElementById('popup-background'); // Certifique-se de que o elemento existe

            if (!popupNovoCartao) {
                console.error('Elemento com ID "popup_novo_cartao" não encontrado.');
                return;
            }

            // Exibir o popup de novo cartão
            popupNovoCartao.style.display = 'block';
            popupNovoCartao.style.position = 'fixed';
            popupNovoCartao.style.top = '50%';
            popupNovoCartao.style.left = '50%';
            popupNovoCartao.style.transform = 'translate(-50%, -50%)';
            popupNovoCartao.style.zIndex = '1000';

            // Exibir o background se ele existir
            if (popupBackground) {
                popupBackground.style.display = 'block';
                popupBackground.style.zIndex = '999';
            } else {
                console.warn('Elemento com ID "popup-background" não encontrado. Ignorando exibição do background.');
            }
        }

        function fecharPopup(popupId) {
            document.getElementById(popupId).style.display = 'none';
            document.getElementById('popup-background').style.display = 'none';
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
            }
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

        /*function validarValorPagamentoDeb() {
            const valorDeb = parseFloat(document.getElementById('vl_deb_principal').value.replace(/\./g, '').replace(',', '.')) || 0;
            const valorTotal = parseFloat(document.getElementById('valor_pedido').value.replace(/\./g, '').replace(',', '.')) || 0;

            if (valorDeb <= 0 || valorDeb > valorTotal) {
                alert('O valor do pagamento deve ser maior que zero e menor ou igual ao valor total.');
                return;
            }

            const restante = valorTotal - valorDeb;
            document.getElementById('restante_deb_principal').textContent = restante.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }*/
    </script>
</html>