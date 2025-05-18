<?php
session_start();
include('../../../conexao.php'); // Conexão com o banco

// Verificação de sessão
if (!isset($_SESSION['id'])) {
    header("Location: ../../../../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_cliente = intval($_POST['id_cliente']);
    $id_parceiro = intval($_POST['id_parceiro']);
    $total = floatval($_POST['valor_total']);
    $valor_frete = floatval($_POST['valor_frete']);

    $entrada_saldo = isset($_POST['input_saldo']) ? $_POST['input_saldo'] : '';
    $entrada_saldo = str_replace('.', '', $entrada_saldo); // Remove os pontos dos milhares
    $entrada_saldo = str_replace(',', '.', $entrada_saldo); // Troca a vírgula decimal por ponto
    $total -= $entrada_saldo; // Subtrai o valor do saldo do total

    $detalhes_produtos = isset($_POST['detalhes_produtos']) ? $_POST['detalhes_produtos'] : '';
    $entrega = $_POST['entrega'];
    $rua = $_POST['rua'];
    $bairro = $_POST['bairro'];
    $numero = $_POST['numero'];
    $contato = $_POST['contato'];

    $produtos = isset($_POST['produtos']) ? json_decode($_POST['produtos'], true) : [];

    $total_vende_crediario = 0;
    $total_nao_vende_crediario = 0;
    $maior_parcelas = 0;
    $maior_frete = 0;
    $produto_maior_frete_vende_crediario = false;

    // Calcula os valores separados e encontra o maior frete e maior quantidade de parcelas
    foreach ($produtos as $produto) {
        if ($produto['vende_crediario'] == 1) {
            $total_vende_crediario += $produto['valor_produto'] * $produto['qt'];
        } else {
            $total_nao_vende_crediario += $produto['valor_produto'] * $produto['qt'];
        }

        if ($produto['qt_parcelas'] > $maior_parcelas) {
            $maior_parcelas = $produto['qt_parcelas'];
        }

        if ($produto['frete'] > $maior_frete) {
            $maior_frete = $produto['frete'];
            $produto_maior_frete_vende_crediario = $produto['vende_crediario'] == 1;
        }
    }

    // Buscar os dados do cliente
    $stmt = $mysqli->prepare("SELECT * FROM meus_clientes WHERE id = ?");
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $result = $stmt->get_result();
    $cliente = $result->fetch_assoc();

    $limite_cred = !empty($cliente['limite_cred']) ? $cliente['limite_cred'] : 0;
    $status_crediario = $cliente['status_crediario'];
    $situacao_crediario = $cliente['situacao_crediario'];

    // Calcula o valor total a pagar
    $valor_total_a_pagar = $total_nao_vende_crediario + $total_vende_crediario;

    // Buscar se o parceiro aceita cartão de crédito
    $stmt = $mysqli->prepare("SELECT * FROM meus_parceiros WHERE id = ?");
    $stmt->bind_param("i", $id_parceiro);
    $stmt->execute();
    $result = $stmt->get_result();
    $parceiro = $result->fetch_assoc();

    $cartao_debito_ativo = !empty($parceiro['cartao_debito']);
    $cartao_credito_ativo = !empty($parceiro['cartao_credito']);
    $outros = !empty($parceiro['outras_formas']);

    // Buscar os cartões de crédito e débito aceitos online
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

    // Buscar as taxas da plataforma
    $stmt = $mysqli->prepare("
        SELECT * FROM config_admin WHERE taxa_padrao <> '' 
        ORDER BY data_alteracao DESC 
        LIMIT 1
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $admin_taxas = $result->fetch_assoc();

    $taxa_crediario = isset($admin_taxas['taxa_crediario']) ? floatval($admin_taxas['taxa_crediario']) : 0;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processar Pagamento</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            text-align: center;
            /* Centraliza o conteúdo */
        }

        h2,
        h3 {
            text-align: center;
            /* Centraliza os títulos */
            /*margin: 10px 0;*/
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            margin-top: 10px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        select,
        textarea,
        button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .bt_pg_online {
            background-color: rgb(76, 145, 73);
            color: white;
            border: none;
            cursor: pointer;
        }

        .bt_pg_online:hover {
            background-color: rgb(0, 255, 0);
        }

        .popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .popup-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            position: relative;
            width: 90%;
            max-width: 400px;
        }

        #pg_entrega h3 {
            margin: 50px;
        }

        #pg_entrega {
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .close {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 20px;
            cursor: pointer;
        }

        #momento_pagamento {
            background-color: #fff;
            margin-top: 20px;

            border: 1px solid #ccc;
            border-radius: 5px;
            display: flex;
            flex-direction: row;
            /* Mantém os elementos lado a lado */
            justify-content: space-between;
            /* Distribui os espaços entre as divs */
        }

        #momento_pagamento div {
            padding-top: 8px;
            flex: 1;
            /* Cada div ocupa o mesmo espaço */
            display: flex;
            align-items: center;
            /* Alinha os elementos verticalmente */
            justify-content: center;
            /* Centraliza o conteúdo */
            gap: 0px;
            /* Espaço entre o radio e o texto */
        }

        #momento_pagamento div:hover {
            border-top: 8px;
            background-color:rgb(89, 186, 24);
            /* Cor de fundo ao passar o mouse */
            cursor: pointer;
            /* Muda o cursor para indicar que é clicável */
        }

        #momento_pagamento input[type="radio"] {
            cursor: pointer;
            text-align: center;
            position: relative;
            top: -5px;
            transform: scale(1.2);
            margin-right: 0;
        }
        #momento_pagamento input[type="radio"]:checked {
            accent-color:rgb(0, 8, 255); /* Moderno, funciona nos navegadores mais recentes */
        }

        #momento_pagamento label {
            cursor: pointer;
            margin-left: 0;
            padding-left: 5px;
        }

        /* Estilização geral do contêiner */
        #formas_pagamento {
            background-color: #fff;
            margin-top: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            display: flex;
            flex-direction: column;
            gap: 0px;
            /* Espaço entre os itens */
            width: 100%;
            /* Define um tamanho máximo para evitar expansão excessiva */
        }

        /* Estilização dos checkboxes e rótulos */
        #formas_pagamento div {
            display: flex;
            align-items: center;
            gap: 8px;
            /* Espaço entre o checkbox e o texto */
            padding: 6px;
            padding-left: 15%;
            transition: background-color 0.3s;
        }

        /* Efeito hover para destacar a opção selecionável */
        #formas_pagamento div:hover {
            background-color: #f4f4f4;
        }

        /* Ajuste no tamanho dos checkboxes */
        #formas_pagamento input[type="checkbox"] {
            transform: scale(1.2);
            /* Aumenta um pouco o tamanho */
            cursor: pointer;
        }

        /* Estilização dos spans (bandeiras aceitas) */
        #formas_pagamento span {
            font-size: 12px;
            color: #555;
            display: block;
            font-style: italic;
        }

        #formas_pagamento button {
            width: 95%;
            /* Define o comprimento do botão */
            margin: 0 auto;
            /* Centraliza o botão */
            display: block;
            /* Garante que o botão seja exibido como bloco */
            margin-bottom: 10px;
            background-color: #28a745;
            /* Cor verde */
            color: white;
            /* Cor do texto */
            border: none;
            /* Remove a borda */
            cursor: pointer;
            /* Cursor de ponteiro */
        }

        #formas_pagamento button:hover {
            background-color: #218838;
            /* Cor verde mais escura ao passar o mouse */
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
                border: none;
                border-radius: 0;
            }

            #momento_pagamento {
                flex-direction: column;
                /* Alinha os elementos verticalmente */
            }

            #momento_pagamento div {
                flex: none;
                /* Remove o tamanho igual entre as divs */
                width: 100%;
                /* Cada div ocupa toda a largura */
            }

            #formas_pagamento div {
                padding-left: 10%;
                /* Ajusta o espaçamento para telas menores */
            }

            .popup-content {
                width: 95%;
                /* Ajusta a largura do popup para telas menores */
            }

            button {
                padding: 8px;
                /* Reduz o padding dos botões */
            }
        }

        @media (max-width: 480px) {

            h2,
            h3 {
                font-size: 18px;
                /* Reduz o tamanho das fontes dos títulos */
            }

            label {
                font-size: 14px;
                /* Reduz o tamanho da fonte dos rótulos */
            }

            select,
            textarea,
            button {
                font-size: 14px;
                /* Ajusta o tamanho da fonte dos inputs */
            }

            #momento_pagamento div {
                gap: 5px;
                /* Reduz o espaço entre os elementos */
            }

            #formas_pagamento div {
                padding-left: 5%;
                /* Ajusta o espaçamento para telas muito pequenas */
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Formas de Pagamento</h2>

        <?php if ($status_crediario == 1 && !in_array($situacao_crediario, ['atrasado', 'inadimplente', 'em analise']) && $total_vende_crediario > 0): ?>
            <?php if (!empty($limite_cred) && $limite_cred > 0): ?>
                <h3>Limite disponível no crediário: R$ <?php echo number_format($limite_cred, 2, ',', '.'); ?></h3>
            <?php endif; ?>
        <?php endif; ?>

        <h3>Valor á pagar: <span id="valor_a_pagar"><?php echo 'R$ ' . number_format($total, 2, ',', '.'); ?></span>
        </h3>
        <div>
            <label for="comentario">Comentário (opcional):</label>
            <textarea id="comentario" name="comentario" rows="4" cols="50"
                placeholder="Deixe um recado, uma referência, nome de quem vai receber ou retirar, ..."></textarea>
        </div>
        <form method="POST" action="">
            <h3>Escolha o momento do pagamento</h3>
            <div id="momento_pagamento" class="form-group">
                <div>
                    <input type="radio" id="pag_online" name="momento_pagamento" value="online" required checked
                        onclick="mostrarDiv('pg_online')" title="Clique para pagar online">
                    <label for="pag_online">Pagar Online</label>
                </div>
                <div>
                    <input type="radio" id="pag_entrega" name="momento_pagamento" value="entrega" required
                        onclick="mostrarDiv('pg_entrega')" title="Clique para pagar na entrega">
                    <label for="pag_entrega">Pagar na Entrega</label>
                </div>
                <?php if ($status_crediario == '1' && !in_array($situacao_crediario, ['atrasado', 'inadimplente', 'em analise']) && $total_vende_crediario > 0): ?>
                    <?php if (!empty($limite_cred) && $limite_cred > 0): ?>
                        <div>
                            <input type="radio" id="pag_crediario" name="momento_pagamento" value="crediario" required
                                onclick="mostrarDiv('pg_crediario'), carregarEntradaMinima()"
                                title="Clique para pagar no crediário">
                            <label for="pag_crediario">Crediário</label>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div id="pg_online" style="display: block; margin-top: 10px;">
                <h3>Escolha até 2 formas de pagamento</h3>
                <div>
                    <label>Escolha a 1ª forma de pagamento ou Entrada:</label>
                    <select id="formas_pagamento_online" name="forma_pagamento[]" onchange="limitarSelect(this)">
                        <option value="selecionar">Selecionar</option>
                        <option value="pix">PIX</option>
                        <?php if ($admin_cartoes_credito): ?>
                            <option value="cartaoCred">Cartão de Crédito</option>
                        <?php endif; ?>
                        <?php if ($admin_cartoes_debito): ?>
                            <option value="cartaoDeb">Cartão de Débito</option>
                        <?php endif; ?>
                    </select>

                    <div id="bandeiras_aceitas" style="margin-top: 10px;">
                        <p id="bandeiras_credito" style="display: none;">Cartões de Crédito aceitos:
                            <?php echo $admin_cartoes_credito; ?>
                        </p>
                        <p id="bandeiras_debito" style="display: none;">Cartões de Débito aceitos:
                            <?php echo $admin_cartoes_debito; ?>
                        </p>
                    </div>

                    <button id="btn_pix_online" class="bt_pg_online" type="button" style="display: none;"
                        onclick="enviarDadosPgOnline()">Pagar com PIX</button>
                    <button id="btn_cartaoCred_online" class="bt_pg_online" type="button" style="display: none;"
                        onclick="enviarDadosPgOnline()">Pagar com Cartão de Crédito</button>
                    <button id="btn_cartaoDeb_online" class="bt_pg_online" type="button" style="display: none;"
                        onclick="enviarDadosPgOnline()">Pagar com Cartão de Débito</button>
                </div>
            </div>

            <div id="pg_entrega" style="display: none; margin-top: 10px;">
                <h3>Selecione até 3 formas de pagamento:</h3>
                <div id="formas_pagamento">
                    <div>
                        <label>
                            <input type="checkbox" name="forma_pagamento_entrega[]" value="pix"
                                onchange="limitarCheckboxes(this, 3)"> PIX
                        </label>
                    </div>
                    <div>
                        <label>
                            <input type="checkbox" name="forma_pagamento_entrega[]" value="dinheiro"
                                onchange="limitarCheckboxes(this, 3)"> Dinheiro
                        </label>
                    </div>
                    <?php if ($cartao_credito_ativo): ?>
                        <div>
                            <label>
                                <input type="checkbox" name="forma_pagamento_entrega[]" value="cartaoCred"
                                    onchange="limitarCheckboxes(this, 3)"> Cartão de Crédito
                                <span id="bandeiras_credito_entrega" style="display: none;">Cartões de Crédito aceitos:
                                    <?php echo $parceiro['cartao_credito']; ?></span>
                                <input type="hidden" value="<?php echo $parceiro['cartao_credito']; ?>"
                                    id="input_bandeiras_credito_crediario">
                            </label>
                        </div>
                    <?php endif; ?>
                    <?php if ($cartao_debito_ativo): ?>
                        <div>
                            <label>
                                <input type="checkbox" name="forma_pagamento_entrega[]" value="cartaoDeb"
                                    onchange="limitarCheckboxes(this, 3)"> Cartão de Débito
                                <span id="bandeiras_debito_entrega" style="display: none;">Cartões de Débito aceitos:
                                    <?php echo $parceiro['cartao_debito']; ?></span>
                                <input type="hidden" value="<?php echo $parceiro['cartao_debito']; ?>"
                                    id="input_bandeiras_debito_crediario">
                            </label>
                        </div>
                    <?php endif; ?>
                    <?php if ($outros): ?>
                        <div>
                            <label>
                                <input type="checkbox" name="forma_pagamento_entrega[]" value="outros"
                                    onchange="limitarCheckboxes(this, 3)"> Outros
                                <span id="bandeiras_outros_entrega" style="display: none;">Outras formas aceitas:
                                    <?php echo $parceiro['outras_formas']; ?></span>
                                <input type="hidden" value="<?php echo $parceiro['outras_formas']; ?>"
                                    id="input_bandeiras_outros_crediario">
                            </label>
                        </div>
                    <?php endif; ?>
                    <input type="text" id="pag_selecionados" name="pag_selecionados" value="" style="display: none;">
                    <button id="" type="button" style="display: none;"
                        onclick="enviarDadosPgEntrega()">Continuar</button>
                </div>
            </div>

            <div id="pg_crediario" style="display: none; margin-top: 10px;">
                <div id="formas_pagamento_crediario" style="display: block;">
                    <p>
                        <label>Entrada: R$
                            <input type="text" id="input_entrada" name="input_entrada"
                                oninput="formatarMoeda(this); atualizarRestante();">
                        </label>
                        <label>Restante: R$
                            <span id="span_restante"></span>
                        </label>
                    </p>
                    <select id="formas_pagamento_crediario" name="forma_pagamento[]"
                        onchange="mostrarBandeirasCriterio(this); limitarSelect(this);">
                        <option value="selecionar">Selecionar</option>
                        <option value="pix">PIX</option>
                        <?php if ($admin_cartoes_credito): ?>
                            <option value="cartaoCred">Cartão de Crédito</option>
                        <?php endif; ?>
                        <?php if ($admin_cartoes_debito): ?>
                            <option value="cartaoDeb">Cartão de Débito</option>
                        <?php endif; ?>
                    </select>
                    <div id="bandeiras_crediario" style="margin-top: 10px;">
                        <p id="bandeiras_credito_crediario" style="display: none;">Cartões de Crédito aceitos:
                            <?php echo $admin_cartoes_credito; ?>
                        </p>
                        <p id="bandeiras_debito_crediario" style="display: none;">Cartões de Débito aceitos:
                            <?php echo $admin_cartoes_debito; ?>
                        </p>
                    </div>
                    <input type="hidden" id="valor_total_crediario">
                    <input type="hidden" name="tipo_entrada_crediario" id="tipo_entrada_crediario">
                    <input type="hidden" name="bandeiras_aceita" id="bandeiras_aceita">
                    <input type="hidden" name="restanteInput" id="restanteInput">
                    <input type="hidden" name="taxa_crediario" id="taxa_crediario"
                        value="<?php echo $taxa_crediario; ?>">
                </div>
                <button id="bt_comprar_crediario" type="button" style="display: none;"
                    onclick="verificarEntradaMinima()">Continuar</button>
            </div>

            <input type="text" id="momen_pagamento" name="momen_pagamento" value="online" style="display: none;">
            <input type="text" id="tipo_pagamento" name="tipo_pagamento" value="" style="display: none;">

            <input type="text" id="valor_total_sem_crediario" name="valor_total_sem_crediario"
                value="<?php echo $total_nao_vende_crediario; ?>" style="display: none;">
            <input type="text" id="maior_frete" name="maior_frete" value="<?php echo $maior_frete; ?>"
                style="display: none;">
            <input type="text" id="maior_parcelas" accept="" name="maior_parcelas"
                value="<?php echo $maior_parcelas; ?>" style="display: none;">
            <input type="text" id="maior_frete_vende_crediario" name="maior_frete_vende_crediario"
                value="<?php echo $produto_maior_frete_vende_crediario ? '1' : '0'; ?>" style="display: none;">
            <button type="button" onclick="window.history.back();">Voltar</button>
        </form>
    </div>

    <script>
        // Função para formatar o valor como moeda
        function mostrarDiv(divId) {
            document.getElementById('pg_online').style.display = 'none';
            document.getElementById('pg_entrega').style.display = 'none';
            document.getElementById('pg_crediario').style.display = 'none';
            document.getElementById(divId).style.display = 'block';

            // Atualizar o valor do campo tipo_pagamento
            const tipoPagamentoInput = document.getElementById('momen_pagamento');
            if (divId === 'pg_online') {
                tipoPagamentoInput.value = 'online';
                document.getElementById('tipo_pagamento').value = '';
                document.getElementById('formas_pagamento_online').value = 'selecionar'; // Redefinir para "Selecionar"
            } else if (divId === 'pg_entrega') {
                tipoPagamentoInput.value = 'hr_entrega';

                // Limpar os checkboxes de pg_entrega
                const checkboxes = document.querySelectorAll('input[name="forma_pagamento_entrega[]"]');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });

                document.getElementById('bandeiras_credito_entrega').style.display = 'none';
                document.getElementById('input_bandeiras_credito_crediario').style.display = 'none';
                document.getElementById('bandeiras_debito_entrega').style.display = 'none';
                document.getElementById('input_bandeiras_debito_crediario').style.display = 'none';
                document.getElementById('bandeiras_outros_entrega').style.display = 'none';
                document.getElementById('input_bandeiras_outros_crediario').style.display = 'none';
                document.getElementById('tipo_pagamento').value = '';

            }

            // Atualizar o valor exibido ao selecionar "Pagar Online" ou "Pagar na Entrega"
            if (divId === 'pg_online' || divId === 'pg_entrega') {
                const total = parseFloat('<?php echo $total; ?>');
                const valorAPagarSpan = document.getElementById('valor_a_pagar');
                valorAPagarSpan.innerText = 'R$ ' + total.toFixed(2).replace('.', ',');
            }
        }

        function limitarSelect(select) {
            const selectedOptions = Array.from(select.selectedOptions);
            if (selectedOptions.length > 2) {
                selectedOptions[selectedOptions.length - 1].selected = false;
                alert('Você pode selecionar no máximo 2 formas de pagamento.');
            }

            // Mostrar ou esconder os botões conforme a seleção
            document.getElementById('btn_pix_online').style.display = selectedOptions.some(option => option.value === 'pix') ? 'block' : 'none';
            document.getElementById('btn_cartaoCred_online').style.display = selectedOptions.some(option => option.value === 'cartaoCred') ? 'block' : 'none';
            document.getElementById('btn_cartaoDeb_online').style.display = selectedOptions.some(option => option.value === 'cartaoDeb') ? 'block' : 'none';
            document.getElementById('tipo_pagamento').value = selectedOptions.some(option => option.value === 'cartaoCred') ? 'cartaoCred' : selectedOptions.some(option => option.value === 'cartaoDeb') ? 'cartaoDeb' : 'pix';
            // Mostrar ou esconder as bandeiras conforme a seleção
            document.getElementById('bandeiras_credito').style.display = selectedOptions.some(option => option.value === 'cartaoCred') ? 'block' : 'none';
            document.getElementById('bandeiras_debito').style.display = selectedOptions.some(option => option.value === 'cartaoDeb') ? 'block' : 'none';
        }

        function limitarCheckboxes(checkbox, max) {
            const checkboxes = document.querySelectorAll('input[name="forma_pagamento_entrega[]"]');
            const checkedCheckboxes = Array.from(checkboxes).filter(chk => chk.checked);
            if (checkedCheckboxes.length > max) {
                checkbox.checked = false;
                alert('Você pode selecionar no máximo ' + max + ' formas de pagamento.');
                return;
            }

            // Mostrar ou esconder as bandeiras conforme a seleção
            document.getElementById('bandeiras_credito_entrega').style.display = checkedCheckboxes.some(option => option.value === 'cartaoCred') ? 'block' : 'none';
            document.getElementById('bandeiras_debito_entrega').style.display = checkedCheckboxes.some(option => option.value === 'cartaoDeb') ? 'block' : 'none';
            document.getElementById('bandeiras_outros_entrega').style.display = checkedCheckboxes.some(option => option.value === 'outros') ? 'block' : 'none';

            // Mostrar ou esconder as bandeiras conforme a seleção
            document.getElementById('bandeiras_credito_entrega').style.display = checkedCheckboxes.some(option => option.value === 'cartaoCred') ? 'block' : 'none';
            document.getElementById('bandeiras_debito_entrega').style.display = checkedCheckboxes.some(option => option.value === 'cartaoDeb') ? 'block' : 'none';
            document.getElementById('bandeiras_outros_entrega').style.display = checkedCheckboxes.some(option => option.value === 'outros') ? 'block' : 'none';

            // Atualizar o campo tipo_pagamento com os tipos selecionados se a divId for pg_entrega
            if (document.getElementById('pg_entrega').style.display === 'block') {
                const tiposSelecionados = checkedCheckboxes.map(chk => {
                    if (chk.value === 'cartaoCred') return 'Cartão de Crédito';
                    if (chk.value === 'cartaoDeb') return 'Cartão de Débito';
                    if (chk.value === 'pix') return 'Pix';
                    if (chk.value === 'dinheiro') return 'Dinheiro';
                    if (chk.value === 'outros') return 'Outros';
                    return chk.value;
                }).join(', ');

                document.getElementById('tipo_pagamento').value = tiposSelecionados;
            }
        }

        function enviarDadosPgOnline() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'popup_pg_online.php';

            const idClienteInput = document.createElement('input');
            idClienteInput.type = 'hidden';
            idClienteInput.name = 'id_cliente';
            idClienteInput.value = '<?php echo $id_cliente; ?>';
            form.appendChild(idClienteInput);

            const idParceiroInput = document.createElement('input');
            idParceiroInput.type = 'hidden';
            idParceiroInput.name = 'id_parceiro';
            idParceiroInput.value = '<?php echo $id_parceiro; ?>';
            form.appendChild(idParceiroInput);

            const freteInput = document.createElement('input');
            freteInput.type = 'hidden';
            freteInput.name = 'valor_frete';
            freteInput.value = '<?php echo $valor_frete; ?>';
            form.appendChild(freteInput);

            const totalInput = document.createElement('input');
            totalInput.type = 'hidden';
            totalInput.name = 'valor_total';
            totalInput.value = '<?php echo $total; ?>';
            form.appendChild(totalInput);

            const saldoInput = document.createElement('input'); // Adicionar esta linha
            saldoInput.type = 'hidden';
            saldoInput.name = 'entrada_saldo';
            saldoInput.value = '<?php echo $entrada_saldo; ?>';
            form.appendChild(saldoInput);

            const tipo_pagamento = document.createElement('input');
            tipo_pagamento.type = 'hidden';
            tipo_pagamento.name = 'tipo_pagamento';
            tipo_pagamento.value = document.getElementById('tipo_pagamento').value; // Envia a forma de pagamento selecionada
            form.appendChild(tipo_pagamento);

            const detalhesProdutosInput = document.createElement('input');
            detalhesProdutosInput.type = 'hidden';
            detalhesProdutosInput.name = 'detalhes_produtos';
            detalhesProdutosInput.value = '<?php echo $detalhes_produtos; ?>';
            form.appendChild(detalhesProdutosInput);

            const momenPagamentoInput = document.createElement('input');
            momenPagamentoInput.type = 'hidden';
            momenPagamentoInput.name = 'momen_pagamento';
            momenPagamentoInput.value = document.getElementById('momen_pagamento').value; // Envia a forma de pagamento selecionada
            form.appendChild(momenPagamentoInput);

            const entregaInput = document.createElement('input');
            entregaInput.type = 'hidden';
            entregaInput.name = 'entrega';
            entregaInput.value = '<?php echo $entrega; ?>';
            form.appendChild(entregaInput);

            const ruaInput = document.createElement('input');
            ruaInput.type = 'hidden';
            ruaInput.name = 'rua';
            ruaInput.value = '<?php echo $rua; ?>';
            form.appendChild(ruaInput);

            const bairroInput = document.createElement('input');
            bairroInput.type = 'hidden';
            bairroInput.name = 'bairro';
            bairroInput.value = '<?php echo $bairro; ?>';
            form.appendChild(bairroInput);

            const numeroInput = document.createElement('input');
            numeroInput.type = 'hidden';
            numeroInput.name = 'numero';
            numeroInput.value = '<?php echo $numero; ?>';
            form.appendChild(numeroInput);

            const contatoInput = document.createElement('input'); // Adicionar esta linha
            contatoInput.type = 'hidden';
            contatoInput.name = 'contato';
            contatoInput.value = '<?php echo $contato; ?>';
            form.appendChild(contatoInput);

            const comentarioInput = document.createElement('input'); // Adicionar esta linha
            comentarioInput.type = 'hidden';
            comentarioInput.name = 'comentario';
            comentarioInput.value = document.getElementById('comentario').value;
            form.appendChild(comentarioInput);

            document.body.appendChild(form);
            form.submit();
        }

        function enviarDadosPgEntrega() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'pg_hora_entrega.php';

            const idClienteInput = document.createElement('input');
            idClienteInput.type = 'hidden';
            idClienteInput.name = 'id_cliente';
            idClienteInput.value = '<?php echo $id_cliente; ?>';
            form.appendChild(idClienteInput);

            const idParceiroInput = document.createElement('input');
            idParceiroInput.type = 'hidden';
            idParceiroInput.name = 'id_parceiro';
            idParceiroInput.value = '<?php echo $id_parceiro; ?>';
            form.appendChild(idParceiroInput);

            const freteInput = document.createElement('input');
            freteInput.type = 'hidden';
            freteInput.name = 'valor_frete';
            freteInput.value = '<?php echo $valor_frete; ?>';
            form.appendChild(freteInput);

            const totalInput = document.createElement('input');
            totalInput.type = 'hidden';
            totalInput.name = 'valor_total';
            totalInput.value = '<?php echo $total; ?>';
            form.appendChild(totalInput);

            const saldoInput = document.createElement('input'); // Adicionar esta linha
            saldoInput.type = 'hidden';
            saldoInput.name = 'entrada_saldo';
            saldoInput.value = '<?php echo $entrada_saldo; ?>';
            form.appendChild(saldoInput);

            const detalhesProdutosInput = document.createElement('input');
            detalhesProdutosInput.type = 'hidden';
            detalhesProdutosInput.name = 'detalhes_produtos';
            detalhesProdutosInput.value = '<?php echo $detalhes_produtos; ?>';
            form.appendChild(detalhesProdutosInput);

            const entregaInput = document.createElement('input');
            entregaInput.type = 'hidden';
            entregaInput.name = 'entrega';
            entregaInput.value = '<?php echo $entrega; ?>';
            form.appendChild(entregaInput);

            const ruaInput = document.createElement('input');
            ruaInput.type = 'hidden';
            ruaInput.name = 'rua';
            ruaInput.value = '<?php echo $rua; ?>';
            form.appendChild(ruaInput);

            const bairroInput = document.createElement('input');
            bairroInput.type = 'hidden';
            bairroInput.name = 'bairro';
            bairroInput.value = '<?php echo $bairro; ?>';
            form.appendChild(bairroInput);

            const numeroInput = document.createElement('input');
            numeroInput.type = 'hidden';
            numeroInput.name = 'numero';
            numeroInput.value = '<?php echo $numero; ?>';
            form.appendChild(numeroInput);

            const contatoInput = document.createElement('input'); // Adicionar esta linha
            contatoInput.type = 'hidden';
            contatoInput.name = 'contato';
            contatoInput.value = '<?php echo $contato; ?>';
            form.appendChild(contatoInput);

            const comentarioInput = document.createElement('input'); // Adicionar esta linha
            comentarioInput.type = 'hidden';
            comentarioInput.name = 'comentario';
            comentarioInput.value = document.getElementById('comentario').value;
            form.appendChild(comentarioInput);

            const bandeirasDebitoInput = document.createElement('input'); // Adicionar esta linha
            bandeirasDebitoInput.type = 'hidden';
            bandeirasDebitoInput.name = 'bandeiras_outros_aceitos';
            bandeirasDebitoInput.value = document.getElementById('pag_selecionados').value;
            form.appendChild(bandeirasDebitoInput);

            document.body.appendChild(form);
            form.submit();
        }

        document.querySelectorAll('input[name="forma_pagamento_entrega[]"]').forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                const selecionados = Array.from(document.querySelectorAll('input[name="forma_pagamento_entrega[]"]:checked'))
                    .map(chk => {
                        let label = chk.value;
                        if (chk.value === 'pix') {
                            label = ` Pix`;
                        } else if (chk.value === 'dinheiro') {
                            label = ` Dinheiro`;
                        } else if (chk.value === 'cartaoCred') {
                            label = ` Cartão de Crédito(${document.getElementById('input_bandeiras_credito_crediario').value})`;
                        } else if (chk.value === 'cartaoDeb') {
                            label = ` Cartão de Débito(${document.getElementById('input_bandeiras_debito_crediario').value})`;
                        } else if (chk.value === 'outros') {
                            label = ` Outros(${document.getElementById('input_bandeiras_outros_crediario').value})`;
                        }
                        return label;
                    })
                    .join(', ');
                document.getElementById('pag_selecionados').value = selecionados;

                // Mostrar ou esconder o botão "Continuar"
                const continuarButton = document.querySelector('#formas_pagamento button');
                continuarButton.style.display = selecionados ? 'block' : 'none';
            });
        });

        function carregarEntradaMinima() {
            const maior_frete_vende_crediario = document.getElementById('maior_frete_vende_crediario').value;
            const maior_frete = parseFloat('<?php echo $maior_frete; ?>') || 0;
            const total_vende_crediario = parseFloat('<?php echo $total_vende_crediario; ?>') || 0;
            const total_nao_vende_crediario = parseFloat('<?php echo $total_nao_vende_crediario; ?>') || 0;
            const taxaCrediario = parseFloat('<?php echo $admin_taxas['taxa_crediario']; ?>') || 0;
            const limiteCred = parseFloat('<?php echo $limite_cred; ?>') || 0;
            const entrada_saldo = parseFloat('<?php echo $entrada_saldo; ?>') || 0;

            let total;
            if (maior_frete_vende_crediario === '1') {
                total = (total_vende_crediario + maior_frete) + total_nao_vende_crediario - entrada_saldo;
                //console.log('Total que vende no cred:', total);
            } else {
                total = (total_nao_vende_crediario + maior_frete - entrada_saldo) + total_vende_crediario;
                //console.log('Total só a vista:', total);
            }

            const valorTotal = total + (total * taxaCrediario) / 100;
            const entrada = Math.max(valorTotal - limiteCred, 0);
            const restante = valorTotal - entrada;
            const entradaInput = document.getElementById('input_entrada');
            const span_restante = document.getElementById('span_restante');
            const valorAPagarSpan = document.getElementById('valor_a_pagar');

            entradaInput.value = entrada.toFixed(2).replace('.', ',');
            span_restante.innerText = restante.toFixed(2).replace('.', ',');
            valorAPagarSpan.innerText = 'R$ ' + valorTotal.toFixed(2).replace('.', ','); // Atualiza corretamente o valor exibido

            document.getElementById('tipo_entrada_crediario').value = '1';
            document.getElementById('bandeiras_aceita').value = '';
            document.getElementById('valor_total_crediario').value = valorTotal.toFixed(2).replace('.', ',');
            document.getElementById('valor_total_sem_crediario').value = total_vende_crediario + total_nao_vende_crediario + maior_frete;

            /*console.log('entrada_saldo:', entrada_saldo);
            console.log('Valor total:', valorTotal);
            console.log('Entrada mínima:', entrada);
            console.log('Restante:', restante);
            console.log('Valor frete:', maior_frete);
            console.log('Total não vende crediário:', total_nao_vende_crediario);
            console.log('Valor Total de produtos que vende crediário:', total_vende_crediario);
            console.log('Taxa crediário:', taxaCrediario);
            console.log('Limite crediário:', limiteCred);
            console.log('Entrada:', entrada);
            console.log('Restante:', restante);*/

        }

        function formatarMoeda(input) {
            let valor = input.value.replace(/\D/g, '');
            valor = (valor / 100).toFixed(2) + '';
            valor = valor.replace(".", ",");
            valor = valor.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
            input.value = valor;
        }

        function atualizarRestante() {
            const total = parseFloat('<?php echo $total; ?>');
            const taxaCrediario = parseFloat('<?php echo $admin_taxas['taxa_crediario']; ?>') || 0;
            const entrada_saldo = parseFloat('<?php echo $entrada_saldo; ?>') || 0;
            const valorTotal = total + (total * taxaCrediario) / 100;
            const entradaInput = document.getElementById('input_entrada');
            const span_restante = document.getElementById('span_restante');
            const entrada = parseFloat(entradaInput.value.replace(/\./g, '').replace(',', '.')) || 0;

            const restante = valorTotal - entrada;

            span_restante.textContent = restante.toFixed(2).replace('.', ',');
            //console.log('Valor total:', valorTotal);
            /*console.log('Valor total:', valorTotal);
            console.log('Entrada:', entrada);
            console.log('Restante:', restante);
            console.log('Entrada mínima:', entrada);
            console.log('Entrada saldo:', entrada_saldo);
            console.log('Taxa crediário:', taxaCrediario);
            console.log(total);*/
        }

        function mostrarBandeirasCriterio(select) {
            const selectedValues = Array.from(select.selectedOptions).map(option => option.value);

            // Atualizar o campo de seleção com os valores escolhidos
            document.getElementById('pag_selecionados').value = selectedValues;

            // Mostrar ou esconder as bandeiras de acordo com a seleção
            const bandeirasCredito = document.getElementById('bandeiras_credito_crediario');
            const bandeirasDebito = document.getElementById('bandeiras_debito_crediario');

            bandeirasCredito.style.display = selectedValues.includes('cartaoCred') ? 'block' : 'none';
            bandeirasDebito.style.display = selectedValues.includes('cartaoDeb') ? 'block' : 'none';

            // Atualizar os campos ocultos
            document.getElementById('tipo_entrada_crediario').value = selectedValues.includes('cartaoCred') ? '2' : selectedValues.includes('cartaoDeb') ? '3' : '1';
            document.getElementById('bandeiras_aceita').value = selectedValues.includes('cartaoCred') ? '<?php echo $admin_cartoes_credito; ?>' : selectedValues.includes('cartaoDeb') ? '<?php echo $admin_cartoes_debito; ?>' : '';

            // Mostrar o botão "Continuar" se o valor selecionado for diferente de "selecionar"
            const continuarButton = document.getElementById('bt_comprar_crediario');
            continuarButton.style.display = selectedValues.includes('selecionar') ? 'none' : 'block';
        }

        function recalcularValor() {
            const produto_maior_frete_vende_crediario = document.getElementById('maior_frete_vende_crediario').value === '1';
            const valor_frete = parseFloat('<?php echo $valor_frete; ?>');
            const total_nao_vende_crediario = parseFloat('<?php echo $total_nao_vende_crediario; ?>');
            const total_vende_crediario = parseFloat('<?php echo $total_vende_crediario; ?>');
            const taxaCrediario = parseFloat('<?php echo $admin_taxas['taxa_crediario']; ?>') || 0;
            const limiteCred = parseFloat('<?php echo $limite_cred; ?>');
            const entradaInput = document.getElementById('input_entrada');
            const span_restante = document.getElementById('span_restante');
            const entrada = parseFloat(entradaInput.value.replace(/\./g, '').replace(',', '.')) || 0;

            let valorTotal;
            if (produto_maior_frete_vende_crediario) {
                valorTotal = total_vende_crediario + valor_frete + ((total_vende_crediario + valor_frete) * taxaCrediario) / 100;
            } else {
                valorTotal = total_vende_crediario + ((total_vende_crediario) * taxaCrediario) / 100 + total_nao_vende_crediario + valor_frete;
            }

            valorTotal = Math.round(valorTotal * 100) / 100; // Arredondar para 2 casas decimais
            const diferenca = Math.round((valorTotal - limiteCred) * 100) / 100; // Arredondar para 2 casas decimais
            const restante = Math.round((valorTotal - entrada) * 100) / 100; // Arredondar para 2 casas decimais

            if (entrada < diferenca) {
                alert('A entrada deve ser no mínimo R$ ' + diferenca.toFixed(2).replace('.', ','));
                entradaInput.value = (diferenca + 1).toFixed(2).replace('.', ',');
                span_restante.value = (valorTotal - diferenca - 1).toFixed(2).replace('.', ',');
                return;
            }

            if (entrada > valorTotal) {
                alert('A entrada não pode ser maior que o valor da compra.');
                entradaInput.value = (diferenca + 1).toFixed(2).replace('.', ',');
                span_restante.value = (valorTotal - diferenca - 1).toFixed(2).replace('.', ',');
                return;
            }

            span_restante.value = restante.toFixed(2).replace('.', ',');

            document.getElementById('valor_a_pagar').innerText = 'R$ ' + valorTotal.toFixed(2).replace('.', ',');

            /*console.log('Valor total:', valorTotal);
            console.log('Entrada mínima:', diferenca);
            console.log('Restante:', restante);
            console.log('Entrada:', entrada);
            console.log('Valor frete:', valor_frete);
            console.log('Total não vende crediário:', total_nao_vende_crediario);
            console.log('Total vende crediário:', total_vende_crediario);
            console.log('Taxa crediário:', taxaCrediario);
            console.log('Limite crediário:', limiteCred);
            console.log('Produto maior frete vende crediário:', produto_maior_frete_vende_crediario);*/
        }

        function verificarEntradaMinima() {
            const entradaInput = document.getElementById('input_entrada');
            const span_restante = document.getElementById('span_restante');
            const valorAPagarSpan = document.getElementById('valor_a_pagar');
            const entrada = parseFloat(entradaInput.value.replace(/\./g, '').replace(',', '.')) || 0;
            const valorTotal = parseFloat(valorAPagarSpan.innerText.replace('R$ ', '').replace(/\./g, '').replace(',', '.')) || 0;
            const restanteInput = document.getElementById('restanteInput');
            const limiteCred = parseFloat('<?php echo $limite_cred; ?>');
            const diferenca = Math.round((valorTotal - limiteCred) * 100) / 100; // Arredondar para 2 casas decimais

            if (entrada < diferenca) { // Corrigir para "<" ao invés de "<="
                alert('A entrada deve ser no mínimo R$ ' + diferenca.toFixed(2).replace('.', ','));
                entradaInput.value = diferenca.toFixed(2).replace('.', ',');
                span_restante.innerText = (valorTotal - diferenca).toFixed(2).replace('.', ',');
                return false;
            }

            if (entrada > valorTotal) {
                alert('A entrada não pode ser maior que o valor total da compra.');
                entradaInput.value = valorTotal.toFixed(2).replace('.', ',');
                span_restante.innerText = '0,00';
                return false;
            }

            span_restante.innerText = (valorTotal - entrada).toFixed(2).replace('.', ',');
            restanteInput.value = (valorTotal - entrada).toFixed(2);
            enviarDadosCrediario();
            return true;
        }

        function enviarDadosCrediario() {
            const valorAPagarSpan = document.getElementById('valor_a_pagar');
            const valorAPagar = parseFloat(valorAPagarSpan.innerText.replace('R$ ', '').replace('.', '').replace(',', '.')) || 0;


            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'comprar_crediario.php';

            const idClienteInput = document.createElement('input');
            idClienteInput.type = 'hidden';
            idClienteInput.name = 'id_cliente';
            idClienteInput.value = '<?php echo $id_cliente; ?>';
            form.appendChild(idClienteInput);

            const idParceiroInput = document.createElement('input');
            idParceiroInput.type = 'hidden';
            idParceiroInput.name = 'id_parceiro';
            idParceiroInput.value = '<?php echo $id_parceiro; ?>';
            form.appendChild(idParceiroInput);

            const freteInput = document.createElement('input');
            freteInput.type = 'hidden';
            freteInput.name = 'valor_frete';
            freteInput.value = '<?php echo $valor_frete; ?>';
            form.appendChild(freteInput);

            const totalsemCrediarioInput = document.createElement('input'); // Adicionar esta linha
            totalsemCrediarioInput.type = 'hidden';
            totalsemCrediarioInput.name = 'valor_total_sem_crediario';
            totalsemCrediarioInput.value = document.getElementById('valor_total_sem_crediario').value;
            form.appendChild(totalsemCrediarioInput);

            const totalInput = document.createElement('input'); // Adicionar esta linha
            totalInput.type = 'hidden';
            totalInput.name = 'valor_total_crediario';
            totalInput.value = document.getElementById('valor_total_crediario').value;
            form.appendChild(totalInput);

            const taxaCrediarioInput = document.createElement('input'); // Adicionar esta linha
            taxaCrediarioInput.type = 'hidden';
            taxaCrediarioInput.name = 'taxa_crediario';
            taxaCrediarioInput.value = document.getElementById('taxa_crediario').value;
            form.appendChild(taxaCrediarioInput);

            const maiorParcelas = document.createElement('input'); // Adicionar esta linha
            maiorParcelas.type = 'hidden';
            maiorParcelas.name = 'maiorParcelas';
            maiorParcelas.value = document.getElementById('maior_parcelas').value;
            form.appendChild(maiorParcelas);

            const detalhesProdutosInput = document.createElement('input');
            detalhesProdutosInput.type = 'hidden';
            detalhesProdutosInput.name = 'detalhes_produtos';
            detalhesProdutosInput.value = '<?php echo $detalhes_produtos; ?>';
            form.appendChild(detalhesProdutosInput);

            const entregaInput = document.createElement('input');
            entregaInput.type = 'hidden';
            entregaInput.name = 'entrega';
            entregaInput.value = '<?php echo $entrega; ?>';
            form.appendChild(entregaInput);

            const ruaInput = document.createElement('input');
            ruaInput.type = 'hidden';
            ruaInput.name = 'rua';
            ruaInput.type = 'hidden';
            ruaInput.name = 'rua';
            ruaInput.value = '<?php echo $rua; ?>';
            form.appendChild(ruaInput);

            const bairroInput = document.createElement('input');
            bairroInput.type = 'hidden';
            bairroInput.name = 'bairro';
            bairroInput.value = '<?php echo $bairro; ?>';
            form.appendChild(bairroInput);

            const numeroInput = document.createElement('input');
            numeroInput.type = 'hidden';
            numeroInput.name = 'numero';
            numeroInput.value = '<?php echo $numero; ?>';
            form.appendChild(numeroInput);

            const contatoInput = document.createElement('input'); // Adicionar esta linha
            contatoInput.type = 'hidden';
            contatoInput.name = 'contato';
            contatoInput.value = '<?php echo $contato; ?>';
            form.appendChild(contatoInput);

            const entradaInput = document.createElement('input'); // Adicionar esta linha
            entradaInput.type = 'hidden';
            entradaInput.name = 'entrada';
            entradaInput.value = document.getElementById('input_entrada').value;
            form.appendChild(entradaInput);

            const saldoInput = document.createElement('input'); // Adicionar esta linha
            saldoInput.type = 'hidden';
            saldoInput.name = 'entrada_saldo';
            saldoInput.value = '<?php echo $entrada_saldo; ?>';
            form.appendChild(saldoInput);

            const restanteInput = document.createElement('input'); // Adicionar esta linha
            restanteInput.type = 'hidden';
            restanteInput.name = 'restante';
            restanteInput.value = document.getElementById('restanteInput').value;
            form.appendChild(restanteInput);

            const entradaCrediarioInput = document.createElement('input'); // Adicionar esta linha
            entradaCrediarioInput.type = 'hidden';
            entradaCrediarioInput.name = 'tipo_entrada_crediario';
            entradaCrediarioInput.value = document.getElementById('tipo_entrada_crediario').value;
            form.appendChild(entradaCrediarioInput);

            const bandeirasAceitaInput = document.createElement('input'); // Adicionar esta linha
            bandeirasAceitaInput.type = 'hidden';
            bandeirasAceitaInput.name = 'bandeiras_aceita';
            bandeirasAceitaInput.value = document.getElementById('bandeiras_aceita').value;
            form.appendChild(bandeirasAceitaInput);

            const comentarioInput = document.createElement('input'); // Adicionar esta linha
            comentarioInput.type = 'hidden';
            comentarioInput.name = 'comentario';
            comentarioInput.value = document.getElementById('comentario').value;
            form.appendChild(comentarioInput);

            document.body.appendChild(form);
            form.submit();
        }

    </script>
</body>

</html>