<?php
    session_start();
    include('../../../conexao.php'); // Conexão com o banco

    // Verificação de sessão
    if (!isset($_SESSION['id'])) {
        header("Location: ../../../../index.php");
        exit;
    }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    var_dump($_POST);
    $id_cliente = intval($_POST['id_cliente']);
    $id_parceiro = intval($_POST['id_parceiro']);
    $total = floatval($_POST['valor_total']);
    $valor_frete = floatval($_POST['valor_frete']);
    $detalhes_produtos = isset($_POST['detalhes_produtos']) ? $_POST['detalhes_produtos'] : '';

    $entrega = $_POST['entrega'];
    $rua = $_POST['rua'];
    $bairro = $_POST['bairro'];
    $numero = $_POST['numero'];
    $contato = $_POST['contato']; // Adicionar esta linha

    //echo $detalhes_produtos;
    // Buscar os dados do cliente
    $stmt = $mysqli->prepare("SELECT * FROM meus_clientes WHERE id = ?");
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $result = $stmt->get_result();
    $cliente = $result->fetch_assoc();

    $limite_cred = !empty($cliente['limite_cred']) ? $cliente['limite_cred'] : 0;
    $status_crediario = $cliente['status_crediario'];
    $situacao_crediario = $cliente['situacao_crediario'];

    // Buscar se o parceiro aceita cartão de crédito
    $stmt = $mysqli->prepare("SELECT * FROM meus_parceiros WHERE id = ?");
    $stmt->bind_param("i", $id_parceiro);
    $stmt->execute();
    $result = $stmt->get_result();
    $parceiro = $result->fetch_assoc();

    $cartao_debito_ativo = !empty($parceiro['cartao_debito']); 
    $cartao_credito_ativo = !empty($parceiro['cartao_credito']); // Se estiver vazio, será falso
    $outros = !empty($parceiro['outras_formas']); 

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

    // Buscar as taxas da plataforma
    $stmt = $mysqli->prepare("
        SELECT * FROM config_admin WHERE taxa_padrao <> '' 
        ORDER BY data_alteracao DESC 
        LIMIT 1
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $admin_taxas = $result->fetch_assoc();
    //echo $admin_taxas['taxa_crediario'];
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
            text-align: center; /* Centraliza o conteúdo */
        }

        h2, h3 {
            text-align: center; /* Centraliza os títulos */
            /*margin: 10px 0;*/
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
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

        select, textarea, button {
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

        #pg_entrega h3{
            margin: 50px;
        }

        #pg_entrega{
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
            flex-direction: row; /* Mantém os elementos lado a lado */
            justify-content: space-between; /* Distribui os espaços entre as divs */
        }

        #momento_pagamento div {
            padding-top: 8px;
            flex: 1; /* Cada div ocupa o mesmo espaço */
            display: flex;
            align-items: center; /* Alinha os elementos verticalmente */
            justify-content: center; /* Centraliza o conteúdo */
            gap: 8px; /* Espaço entre o radio e o texto */
        }

        #momento_pagamento div:hover {
            border-top: 8px;
            background-color: #f0f0f0; /* Cor de fundo ao passar o mouse */
            cursor: pointer; /* Muda o cursor para indicar que é clicável */
        }

        #momento_pagamento input[type="radio"] {
            text-align: center;
            position: relative;
            top: -5px; /* Ajusta levemente para cima */
            transform: scale(1.2); /* Aumenta um pouco o tamanho do radio */
        }

        /* Estilização geral do contêiner */
        #formas_pagamento {
            background-color: #fff;
            margin-top: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            display: flex;
            flex-direction: column;
            gap: 10px; /* Espaço entre os itens */
            width: 100%; /* Define um tamanho máximo para evitar expansão excessiva */
        }

        /* Estilização dos checkboxes e rótulos */
        #formas_pagamento div {
            display: flex;
            align-items: center;
            gap: 8px; /* Espaço entre o checkbox e o texto */
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
            transform: scale(1.2); /* Aumenta um pouco o tamanho */
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
            width: 95%; /* Define o comprimento do botão */
            margin: 0 auto; /* Centraliza o botão */
            display: block; /* Garante que o botão seja exibido como bloco */
            margin-bottom: 10px;
            background-color: #28a745; /* Cor verde */
            color: white; /* Cor do texto */
            border: none; /* Remove a borda */
            cursor: pointer; /* Cursor de ponteiro */
        }

        #formas_pagamento button:hover {
            background-color: #218838; /* Cor verde mais escura ao passar o mouse */
        }
        @media (max-width: 768px) {
            .container {
                padding: 10px;
                border: none;
                border-radius: 0;
            }

            #momento_pagamento {
                flex-direction: column; /* Alinha os elementos verticalmente */
            }

            #momento_pagamento div {
                flex: none; /* Remove o tamanho igual entre as divs */
                width: 100%; /* Cada div ocupa toda a largura */
            }

            #formas_pagamento div {
                padding-left: 10%; /* Ajusta o espaçamento para telas menores */
            }

            .popup-content {
                width: 95%; /* Ajusta a largura do popup para telas menores */
            }

            button {
                padding: 8px; /* Reduz o padding dos botões */
            }
        }

        @media (max-width: 480px) {
            h2, h3 {
                font-size: 18px; /* Reduz o tamanho das fontes dos títulos */
            }

            label {
                font-size: 14px; /* Reduz o tamanho da fonte dos rótulos */
            }

            select, textarea, button {
                font-size: 14px; /* Ajusta o tamanho da fonte dos inputs */
            }

            #momento_pagamento div {
                gap: 5px; /* Reduz o espaço entre os elementos */
            }

            #formas_pagamento div {
                padding-left: 5%; /* Ajusta o espaçamento para telas muito pequenas */
            }
        }

    </style>
</head>
<body>
    <div class="container">
        <h2>Formas de Pagamento</h2>
        
        <?php if ($status_crediario === 'Aprovado' && !in_array($situacao_crediario, ['atrasado', 'inadimplente', 'em analise'])): ?>
            <?php if (!empty($limite_cred) && $limite_cred > 0): ?>
                <h3>Limite disponível no crediário: R$ <?php echo number_format($limite_cred, 2, ',', '.'); ?></h3>
            <?php endif; ?>
        <?php endif; ?>

        <h3>Valor á pagar: <span id="valor_a_pagar"><?php echo 'R$ ' . number_format($total, 2, ',', '.'); ?></span></h3>
        <div>
            <label for="comentario">Comentário (opcional):</label>
            <textarea id="comentario" name="comentario" rows="4" cols="50"></textarea>
        </div>
        <form method="POST" action="">
            <h3>Escolha o momento do pagamento</h3>
            <div id="momento_pagamento" class="form-group">
                <div>
                    <input type="radio" id="pag_online" name="momento_pagamento" value="online" required checked onclick="mostrarDiv('pg_online')" title="Clique para pagar online">
                    <label for="pag_online">Pagar Online</label>                    
                </div>
                <div>
                    <input type="radio" id="pag_entrega" name="momento_pagamento" value="entrega" required onclick="mostrarDiv('pg_entrega')" title="Clique para pagar na entrega">
                    <label for="pag_entrega">Pagar na Entrega</label>
                </div>   
                <?php if ($status_crediario === 'Aprovado' && !in_array($situacao_crediario, ['atrasado', 'inadimplente', 'em analise'])): ?>
                    <?php if (!empty($limite_cred) && $limite_cred > 0): ?>
                        <div>
                            <input type="radio" id="pag_crediario" name="momento_pagamento" value="crediario" required onclick="mostrarDiv('pg_crediario'), carregarEntradaMinima()" title="Clique para pagar no crediário">
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
                        <p id="bandeiras_credito" style="display: none;">Cartões de Crédito aceitos: <?php echo $admin_cartoes_credito; ?></p>
                        <p id="bandeiras_debito" style="display: none;">Cartões de Débito aceitos: <?php echo $admin_cartoes_debito; ?></p>
                    </div>
                    
                    <button id="btn_pix_online" type="button" style="display: none;" onclick="enviarDadosPix()">Pagar com PIX</button>
                    <button id="btn_cartaoCred_online" type="button" style="display: none;" onclick="enviarDadosCartCredito()">Pagar com Cartão de Crédito</button>
                    <button id="btn_cartaoDeb_online" type="button" style="display: none;" onclick="enviarDadosCartDebito()">Pagar com Cartão de Débito</button>
                </div>
            </div>

            <div id="pg_entrega" style="display: none; margin-top: 10px;">
                <h3>Selecione até 3 formas de pagamento:</h3>
                <div id="formas_pagamento">
                    <div>
                        <label>
                            <input type="checkbox" name="forma_pagamento_entrega[]" value="pix" onchange="limitarCheckboxes(this, 3)"> PIX
                        </label>
                    </div>
                    <div>
                        <label>
                            <input type="checkbox" name="forma_pagamento_entrega[]" value="dinheiro" onchange="limitarCheckboxes(this, 3)"> Dinheiro
                        </label>
                    </div>
                    <?php if ($cartao_credito_ativo): ?>
                        <div>
                            <label>
                                <input type="checkbox" name="forma_pagamento_entrega[]" value="cartaoCred" onchange="limitarCheckboxes(this, 3)"> Cartão de Crédito
                                <span id="bandeiras_credito_entrega" style="display: none;">Cartões de Crédito aceitos: <?php echo $parceiro['cartao_credito']; ?></span>
                                <input type="hidden" value="<?php echo $parceiro['cartao_credito']; ?>" id="input_bandeiras_credito_crediario">
                            </label>
                        </div>
                    <?php endif; ?>
                    <?php if ($cartao_debito_ativo): ?>
                        <div>
                            <label>
                                <input type="checkbox" name="forma_pagamento_entrega[]" value="cartaoDeb" onchange="limitarCheckboxes(this, 3)"> Cartão de Débito
                                <span id="bandeiras_debito_entrega" style="display: none;">Cartões de Débito aceitos: <?php echo $parceiro['cartao_debito']; ?></span>
                                <input type="hidden" value="<?php echo $parceiro['cartao_debito']; ?>" id="input_bandeiras_debito_crediario">
                            </label>
                        </div>
                    <?php endif; ?>
                    <?php if ($outros): ?>
                        <div>
                            <label>
                                <input type="checkbox" name="forma_pagamento_entrega[]" value="outros" onchange="limitarCheckboxes(this, 3)"> Outros
                                <span id="bandeiras_outros_entrega" style="display: none;">Outras formas aceitas: <?php echo $parceiro['outras_formas']; ?></span>
                                <input type="hidden" value="<?php echo $parceiro['outras_formas']; ?>" id="input_bandeiras_outros_crediario">
                            </label>
                        </div>
                    <?php endif; ?>
                    <input type="text" id="pag_selecionados" name="pag_selecionados" value="" style="display: none;"> 
                    <button id="" type="button" style="display: none;" onclick="enviarDadosPgEntrega()">Continuar</button>
                </div>
            </div>

            <div id="pg_crediario" style="display: none; margin-top: 10px;">
                    <div id="formas_pagamento_crediario" style="display: block;">
                        <p>
                            <label>Entrada: R$ 
                                <input type="text" id="entradaInput" name="entradaInput" 
                                    oninput="formatarMoeda(this); atualizarRestante();">
                            </label>
                            <label>Restante: R$ 
                                <input type="text" id="restanteInput" name="restanteInput" readonly>
                            </label>
                        </p>
                        <select id="formas_pagamento_crediario" name="forma_pagamento[]" onchange="mostrarBandeirasCriterio(this); limitarSelect(this);">
                            <option value="pix">PIX</option>
                            <?php if ($admin_cartoes_credito): ?>
                                <option value="cartaoCred">Cartão de Crédito</option>
                            <?php endif; ?>
                            <?php if ($admin_cartoes_debito): ?>
                                <option value="cartaoDeb">Cartão de Débito</option>
                            <?php endif; ?>
                        </select>
                        <div id="bandeiras_crediario" style="margin-top: 10px;">
                            <p id="bandeiras_credito_crediario" style="display: none;">Cartões de Crédito aceitos: <?php echo $admin_cartoes_credito; ?></p>
                            <p id="bandeiras_debito_crediario" style="display: none;">Cartões de Débito aceitos: <?php echo $admin_cartoes_debito; ?></p>
                        </div>
                        <input type="hidden" id="valor_total_crediario">
                        <input type="hidden" name="tipo_entrada_crediario" id="tipo_entrada_crediario">
                        <input type="hidden" name="bandeiras_aceita" id="bandeiras_aceita">
                    </div>
                <button id="bt_comprar_crediario" type="button" style="display: block;" onclick="verificarEntradaMinima()">Continuar</button>
            </div>
            <button type="button" onclick="window.history.back();">Voltar</button> 
        </form>
    </div>
    
    <script> 
        function mostrarDiv(divId) {
            document.getElementById('pg_online').style.display = 'none';
            document.getElementById('pg_entrega').style.display = 'none';
            document.getElementById('pg_crediario').style.display = 'none';
            document.getElementById(divId).style.display = 'block';
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
        }

        function enviarDadosPix() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'popup_pix.php';

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

            document.body.appendChild(form);
            form.submit();
        }

        function enviarDadosCartCredito() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'popup_cartao_credito.php';

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

            document.body.appendChild(form);
            form.submit();
        }

        function enviarDadosCartDebito() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'popup_cartao_debito.php';

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
            checkbox.addEventListener('change', function() {
                const selecionados = Array.from(document.querySelectorAll('input[name="forma_pagamento_entrega[]"]:checked'))
                    .map(chk => {
                        let label = chk.value;
                        if (chk.value === 'cartaoCred') {
                            label += ` (${document.getElementById('input_bandeiras_credito_crediario').value})`;
                        } else if (chk.value === 'cartaoDeb') {
                            label += ` (${document.getElementById('input_bandeiras_debito_crediario').value})`;
                        } else if (chk.value === 'outros') {
                            label += ` (${document.getElementById('input_bandeiras_outros_crediario').value})`;
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

        function formatarMoeda(input) {
            let valor = input.value.replace(/\D/g, '');
            valor = (valor / 100).toFixed(2) + '';
            valor = valor.replace(".", ",");
            valor = valor.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
            input.value = valor;
        }

        function carregarEntradaMinima() {
            const total = parseFloat('<?php echo $total; ?>');
            const taxaCrediario = parseFloat('<?php echo $admin_taxas['taxa_crediario']; ?>') || 0;
            const limiteCred = parseFloat('<?php echo $limite_cred; ?>');
            const entradaInput = document.getElementById('entradaInput');
            //const entrada = parseFloat(entradaInput.value.replace(/\./g, '').replace(',', '.')) || 0;
            const valorTotal = total + ( total * taxaCrediario) / 100;
            const entrada = valorTotal - limiteCred + 1;
            const restante = valorTotal - entrada;

            entradaInput.value = entrada.toFixed(2).replace('.', ',');

            const formas_pagamento_crediario = document.getElementById('formas_pagamento_crediario');

            if (valorTotal < limiteCred) {
                formas_pagamento_crediario.style.display = 'none';
                document.getElementById('restanteInput').value = restante.toFixed(2).replace('.', ',');
                console.log('Valor total menor que o limite de crediário');
            } else {
                formas_pagamento_crediario.style.display = 'block';
                document.getElementById('restanteInput').value = restante.toFixed(2).replace('.', ',');
                console.log('Valor total maior que o limite de crediário');
            }
            document.getElementById('tipo_entrada_crediario').value = 'Pix';
            document.getElementById('bandeiras_aceitas').value = '';
            document.getElementById('valor_total_crediario').value = valorTotal.toFixed(2).replace('.', ',');  
        }
        
        function verificarEntradaMinima() {
            const total = parseFloat('<?php echo $total; ?>');
            const taxaCrediario = parseFloat('<?php echo $admin_taxas['taxa_crediario']; ?>') || 0;
            const limiteCred = parseFloat('<?php echo $limite_cred; ?>');
            const entradaInput = document.getElementById('entradaInput');
            const restanteInput = document.getElementById('restanteInput');

            const valorTotal = total + (total * taxaCrediario) / 100;
            const entrada = parseFloat(entradaInput.value.replace(/\./g, '').replace(',', '.')) || 0;
            const diferenca = valorTotal - limiteCred;
            const restante = valorTotal - entrada;

            if (entrada < diferenca) {
                alert('A entrada deve ser no mínimo R$ ' + diferenca.toFixed(2).replace('.', ','));
                entradaInput.value = (diferenca + 1).toFixed(2).replace('.', ',');
                restanteInput.value = (valorTotal - diferenca - 1).toFixed(2).replace('.', ',');
                return;
            }

            if (entrada > valorTotal) {
                alert('A entrada não pode ser maior que o valor da compra.');
                entradaInput.value = (diferenca + 1).toFixed(2).replace('.', ',');
                restanteInput.value = (valorTotal - diferenca - 1).toFixed(2).replace('.', ',');
                return;
            }

            restanteInput.value = restante.toFixed(2).replace('.', ',');
            enviarDadosCrediario();
        }
        
        function mostrarBandeirasCriterio(select) {
            const selectedOptions = Array.from(select.selectedOptions);
            const selectedValues = selectedOptions.map(option => option.value);

            // Atualiza o campo de seleção com os valores escolhidos
            document.getElementById('pag_selecionados').value = selectedValues;

            // Mostra ou esconde as bandeiras de acordo com a seleção
            const bandeirasCredito = document.getElementById('bandeiras_credito_crediario');
            const bandeirasDebito = document.getElementById('bandeiras_debito_crediario');

            if (selectedValues.includes('pix')) {
                //bandeirasCredito.style.display = 'none';
                document.getElementById('tipo_entrada_crediario').value = '1';
                document.getElementById('bandeiras_aceita').value = '';            
            } else {
                //bandeirasCredito.style.display = 'none';
            }

            if (selectedValues.includes('cartaoCred')) {
                bandeirasCredito.style.display = 'block';
                bandeirasCredito.innerText = 'Cartões de Crédito aceitos: <?php echo $admin_cartoes_credito; ?>';
                document.getElementById('tipo_entrada_crediario').value = '2'; 
                document.getElementById('bandeiras_aceita').value = '<?php echo $admin_cartoes_credito; ?>';      
            } else {
                bandeirasCredito.style.display = 'none';
            }

            if (selectedValues.includes('cartaoDeb')) {
                bandeirasDebito.style.display = 'block';
                bandeirasDebito.innerText = 'Cartões de Débito aceitos: <?php echo $admin_cartoes_debito; ?>';
                document.getElementById('tipo_entrada_crediario').value = '3';
                document.getElementById('bandeiras_aceita').value = '<?php echo $admin_cartoes_debito; ?>';
            } else {
                bandeirasDebito.style.display = 'none';
            }
            console.log(selectedValues);
        }

        function recalcularValor() {
            const totalBase = parseFloat('<?php echo $total; ?>');
            const taxaCrediario = parseFloat('<?php echo $admin_taxas['taxa_crediario']; ?>') || 0;
            const momentoPagamento = document.querySelector('input[name="momento_pagamento"]:checked').value;

            let valorFinal = totalBase;

            if (momentoPagamento === 'crediario') {
                valorFinal += (totalBase * taxaCrediario) / 100; // Adiciona a taxa de crediário
            }

            document.getElementById('valor_a_pagar').innerText = 'R$ ' + valorFinal.toFixed(2).replace('.', ',');
        }

        document.querySelectorAll('input[name="momento_pagamento"]').forEach(radio => {
            radio.addEventListener('change', recalcularValor);
        });

        function enviarDadosCrediario() {
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

            const totalInput = document.createElement('input'); // Adicionar esta linha
            totalInput.type = 'hidden';
            totalInput.name = 'valor_total_crediario';
            totalInput.value = document.getElementById('valor_total_crediario').value;
            form.appendChild(totalInput);

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

            const entradaInput = document.createElement('input'); // Adicionar esta linha
            entradaInput.type = 'hidden';
            entradaInput.name = 'entrada';
            entradaInput.value = document.getElementById('entradaInput').value;
            form.appendChild(entradaInput);

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

        function atualizarRestante() {
            const total = parseFloat('<?php echo $total; ?>');
            const taxaCrediario = parseFloat('<?php echo $admin_taxas['taxa_crediario']; ?>') || 0;
            const valorTotal = total + (total * taxaCrediario) / 100;

            const entradaInput = document.getElementById('entradaInput');
            const restanteInput = document.getElementById('restanteInput');

            const entrada = parseFloat(entradaInput.value.replace(/\./g, '').replace(',', '.')) || 0;
            const restante = valorTotal - entrada;

            restanteInput.value = restante.toFixed(2).replace('.', ',');
        }

    </script>
</body>
</html>
