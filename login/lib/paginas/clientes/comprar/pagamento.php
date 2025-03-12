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
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processar Pagamento</title>
    <style>
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
        }

        .popup-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            position: relative;
        }

        .close {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 20px;
            cursor: pointer;
        }
    </style>
</head>
<body>

    <h2>Finalizar Compra</h2>
    
    <?php if ($status_crediario === 'Aprovado' && !in_array($situacao_crediario, ['atrasado', 'inadimplente', 'em analise'])): ?>
        <?php if (!empty($limite_cred) && $limite_cred > 0): ?>
            <h3>Limite disponível no crediário: R$ <?php echo number_format($limite_cred, 2, ',', '.'); ?></h3>
        <?php endif; ?>
    <?php endif; ?>

    <h3>Valor da minha compra: <?php echo 'R$ ' . number_format($total, 2, ',', '.'); ?></h3>
    
    <form method="POST" action="processar_pagamento.php">
        <h3>Escolha o momento do pagamento</h3>
        <div>
            <!-- ...existing form fields... -->
            <label>
                <input type="radio" name="momento_pagamento" value="online" required checked onclick="mostrarDiv('pg_online')"> Pagar Online
            </label>
            <label>
                <input type="radio" name="momento_pagamento" value="entrega" required onclick="mostrarDiv('pg_entrega')"> Pagar na Entrega
            </label>   
            <?php if ($status_crediario === 'Aprovado' && !in_array($situacao_crediario, ['atrasado', 'inadimplente', 'em analise'])): ?>
                <?php if (!empty($limite_cred) && $limite_cred > 0): ?>
                    <label>
                        <input type="radio" name="momento_pagamento" value="crediario" required onclick="mostrarDiv('pg_crediario')"> Crediario
                    </label>
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
                <button id="btn_cartaoCred_online" type="button" style="display: none;" onclick="abrirPopup('Cartão de Crédito', 'primeira')">Pagar com Cartão de Crédito</button>
                <button id="btn_cartaoDeb_online" type="button" style="display: none;" onclick="abrirPopup('Cartão de Débito', 'primeira')">Pagar com Cartão de Débito</button>
            </div>
        </div>

        <div id="popup_cartaoCred" class="popup" style="display: none;">
            <div class="popup-content">
                <span class="close" onclick="fecharPopup('popup_cartaoCred')">&times;</span>
                <h3>Pagar com Cartão de Crédito</h3>
                <h3>Valor da minha compra: <?php echo 'R$ ' . number_format($total, 2, ',', '.'); ?></h3>
                <p>Insira os dados do seu cartão de crédito para efetuar o pagamento.</p>
                <!-- Adicione aqui o formulário para pagamento com cartão de crédito -->
                <label for="valor_cartaoCred">Valor a ser pago: R$ </label>
                <input type="text" id="valor_cartaoCred" name="valor_cartaoCred" value="<?php echo number_format('0', 2, ',', '.'); ?>" oninput="formatarMoeda(this); verificarValorCartaoCred()">
                <br>
                <label for="parcelas_cartaoCred">Quantidade de parcelas:</label>
                <select id="parcelas_cartaoCred" name="parcelas_cartaoCred">
                    <option value="1">1x de R$ <?php echo number_format($total, 2, ',', '.'); ?> sem juros</option>
                    <option value="2">2x de R$ <?php echo number_format($total / 2, 2, ',', '.'); ?> sem juros</option>
                    <option value="3">3x de R$ <?php echo number_format($total / 3, 2, ',', '.'); ?> sem juros</option>
                    <?php for ($i = 4; $i <= 12; $i++): 
                        $valorParcela = ($total * pow(1 + 0.0299, $i)) / $i; ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?>x de R$ <?php echo number_format($valorParcela, 2, ',', '.'); ?> com 2,99% a.m.</option>
                    <?php endfor; ?>
                </select>
                <br>
                <button type="button" onclick="fecharPopup('popup_cartaoCred')">Cancelar</button>
                <button type="button" onclick="continuarPagamento('Cartão de Crédito')">Continuar</button>
            </div>
        </div>

        <div id="popup_cartaoDeb" class="popup" style="display: none;">
            <div class="popup-content">
                <span class="close" onclick="fecharPopup('popup_cartaoDeb')">&times;</span>
                <h3>Pagar com Cartão de Débito</h3>
                <h3>Valor da minha compra: <?php echo 'R$ ' . number_format($total, 2, ',', '.'); ?></h3>
                <p>Insira os dados do seu cartão de débito para efetuar o pagamento.</p>
                <!-- Adicione aqui o formulário para pagamento com cartão de débito -->
                <label for="valor_cartaoDeb">Valor a ser pago: R$ </label>
                <input type="text" id="valor_cartaoDeb" name="valor_cartaoDeb" value="<?php echo number_format('0', 2, ',', '.'); ?>" oninput="formatarMoeda(this); verificarValorCartaoDeb()">
                <br>
                <button type="button" onclick="fecharPopup('popup_cartaoDeb')">Cancelar</button>
                <button type="button" onclick="continuarPagamento('Cartão de Débito')">Continuar</button>
            </div>
        </div>

        <div id="popup_segunda_forma" class="popup" style="display: none;">
            <div class="popup-content">
                <span class="close" onclick="fecharPopup('popup_segunda_forma')">&times;</span>
                <h3>Escolha a 2ª forma de pagamento</h3>
                <h3>Valor restante: R$ <span id="valor_restante"></span></h3>
                <label>Escolha a 2ª forma de pagamento:</label>
                <select id="segunda_forma_pagamento" name="segunda_forma_pagamento">
                    <option value="selecionar">Selecionar</option>    
                    <option value="pix">PIX</option>
                    <?php if ($admin_cartoes_credito): ?>
                        <option value="cartaoCred">Cartão de Crédito</option>
                    <?php endif; ?>
                    <?php if ($admin_cartoes_debito): ?>
                        <option value="cartaoDeb">Cartão de Débito</option>
                    <?php endif; ?>
                </select>
                <br>
                <button type="button" onclick="fecharPopup('popup_segunda_forma')">Cancelar</button>
                <button type="button" onclick="abrirSegundaForma()">Continuar</button>
            </div>
        </div>

        <div id="pg_entrega" style="display: none; margin-top: 10px;">
            <h3>Selecione até 3 formas de pagamento:</h3>
            <div>
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
                        </label>
                        <span id="bandeiras_credito_entrega" style="display: none;">Cartões de Crédito aceitos: <?php echo $parceiro['cartao_credito']; ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($cartao_debito_ativo): ?>
                    <div>
                        <label>
                            <input type="checkbox" name="forma_pagamento_entrega[]" value="cartaoDeb" onchange="limitarCheckboxes(this, 3)"> Cartão de Débito
                        </label>
                        <span id="bandeiras_debito_entrega" style="display: none;">Cartões de Débito aceitos: <?php echo $parceiro['cartao_debito']; ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($outros): ?>
                    <div>
                        <label>
                            <input type="checkbox" name="forma_pagamento_entrega[]" value="outros" onchange="limitarCheckboxes(this, 3)"> Outros
                        </label>
                        <span id="bandeiras_outros_entrega" style="display: none;">Outras formas aceitas: <?php echo $parceiro['outras_formas']; ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div id="pg_crediario" style="display: none; margin-top: 10px;">
            <div>
                <label>Dar entrada: R$ </label>
                <input type="text" id="entradaInput" name="entrada" value="0,00" oninput="formatarMoeda(this); atualizarRestante(); verificarEntradaMinima()">
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
            </div>
        </div>
        
        <button type="button" onclick="window.history.back();">Voltar</button>
        
    </form>

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
            }

            // Mostrar ou esconder as bandeiras conforme a seleção
            document.getElementById('bandeiras_credito_entrega').style.display = checkedCheckboxes.some(option => option.value === 'cartaoCred') ? 'block' : 'none';
            document.getElementById('bandeiras_debito_entrega').style.display = checkedCheckboxes.some(option => option.value === 'cartaoDeb') ? 'block' : 'none';
            document.getElementById('bandeiras_outros_entrega').style.display = checkedCheckboxes.some(option => option.value === 'outros') ? 'block' : 'none';
        }

        function abrirPopup(metodo, etapa) {
            if (metodo === 'PIX') {
                document.getElementById('popup_pix').style.display = 'block';
            } else if (metodo === 'Cartão de Crédito') {
                document.getElementById('popup_cartaoCred').style.display = 'block';
                calcularParcelas(); // Calcular parcelas ao abrir o popup
            } else if (metodo === 'Cartão de Débito') {
                document.getElementById('popup_cartaoDeb').style.display = 'block';
            }
        }

        function continuarPagamento(metodo) {
            fecharPopup('popup_' + metodo.toLowerCase().replace(' ', ''));
            document.getElementById('valor_restante').innerText = calcularRestante(metodo);
            document.getElementById('popup_segunda_forma').style.display = 'block';
        }

        function abrirSegundaForma() {
            const segundaForma = document.getElementById('segunda_forma_pagamento').value;
            fecharPopup('popup_segunda_forma'); // Fechar o popup atual antes de abrir o próximo
            if (segundaForma === 'pix') {
                abrirPopup('PIX', 'segunda');
            } else if (segundaForma === 'cartaoCred') {
                abrirPopup('Cartão de Crédito', 'segunda');
            } else if (segundaForma === 'cartaoDeb') {
                abrirPopup('Cartão de Débito', 'segunda');
            }
        }

        function calcularRestante(metodo) {
            const total = parseFloat('<?php echo $total; ?>');
            let valorPago = 0;
            if (metodo === 'PIX') {
                valorPago = parseFloat(document.getElementById('valor_pix').value.replace(/\./g, '').replace(',', '.'));
            } else if (metodo === 'Cartão de Crédito') {
                valorPago = parseFloat(document.getElementById('valor_cartaoCred').value.replace(/\./g, '').replace(',', '.'));
            } else if (metodo === 'Cartão de Débito') {
                valorPago = parseFloat(document.getElementById('valor_cartaoDeb').value.replace(/\./g, '').replace(',', '.'));
            }
            return (total - valorPago).toFixed(2).replace('.', ',');
        }

        function fecharPopup(popupId) {
            document.getElementById(popupId).style.display = 'none';
        }

        function formasPagamento() {
            let formaPagamento = document.querySelector('input[name="forma_pagamento[]"]:checked').value;
            let pix = document.getElementById("pix");
            let cartoesCredAceitos = document.getElementById("cartoesCredAceitos");
            let cartoesDebAceitos = document.getElementById("cartoesDebAceitos");
            let crediarioOpcoes = document.getElementById("crediarioOpcoes");
            let parcelasSelect = document.getElementById("parcelas");
            let outros = document.getElementById("outros");
            let taxaCred = document.getElementById("taxaCred");
            let entrada = document.getElementById("entrada");

            // Esconde todos os elementos antes de exibir o correto
            if (pix) pix.style.display = "none";
            if (cartoesCredAceitos) cartoesCredAceitos.style.display = "none";
            if (cartoesDebAceitos) cartoesDebAceitos.style.display = "none";
            if (crediarioOpcoes) crediarioOpcoes.style.display = "none";
            if (outros) outros.style.display = "none";
            if (taxaCred) taxaCred.style.display = "none";
            if (entrada) entrada.style.display = "none";

            if (formaPagamento === "selecionar") {
                if (entrada) entrada.style.display = "none";
            } else {
                if (entrada) {
                    entrada.style.display = "block";
                    atualizarRestante(); // Calcular o restante quando o campo de entrada for exibido
                }
            }

            if (formaPagamento === "pix") {
                if (pix) pix.style.display = "block";
            } else if (formaPagamento === "cartaoCred") {
                if (cartoesCredAceitos) cartoesCredAceitos.style.display = "block";
            } else if (formaPagamento === "cartaoDeb") {
                if (cartoesDebAceitos) cartoesDebAceitos.style.display = "block";
            } else if (formaPagamento === "crediario") {
                if (crediarioOpcoes) crediarioOpcoes.style.display = "block";
                parcelasSelect.innerHTML = '<option value="">Selecione</option>';

                let maxParcelas = document.getElementById("qt_parcelas").value;

                if (entrada) entrada.style.display = "block";

                if (maxParcelas > 0) {
                    for (let i = 1; i <= maxParcelas; i++) {
                        let valorParcela;
                        let labelJuros = ""; // Texto para indicar se há juros

                        if (i > 3) {
                            // Aplicar juros compostos para parcelas acima de 3x
                            let taxaJuros = 0.0299; // 2.99% ao mês
                            valorParcela = (totalAtual * Math.pow(1 + taxaJuros, i)) / i;
                            labelJuros = " 2,99% a.m.";
                        } else {
                            // Parcelas sem juros
                            valorParcela = totalAtual / i;
                            labelJuros = " sem juros";
                        }

                        let option = document.createElement("option");
                        option.value = i + "x";
                        option.textContent = `${i}x de R$ ${valorParcela.toFixed(2).replace('.', ',')}${labelJuros}`;
                        parcelasSelect.appendChild(option);
                    }
                } else {
                    console.error("Erro: qt_parcelas inválido.");
                }
            } else if (formaPagamento === "outros") {
                if (outros) outros.style.display = "block";
            }

            atualizarTotal(document.querySelector('input[name="entrega"]:checked').value === "entregar");
            atualizarRestante(); // Recalcular o restante toda vez que a forma de pagamento for alterada
        }

        document.querySelectorAll('input[name="entrega"]').forEach(radio => {
            radio.addEventListener('change', function() {
                atualizarTotal(this.value === "entregar");
            });
        });

        document.querySelectorAll('input[name="forma_pagamento[]"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                formasPagamento();
            });
        });

        function atualizarTotal(cobrarFrete) {
            // Garantir que o cálculo do total sempre seja reiniciado corretamente
            let totalBase = totalGeral;

            // Atualizar o frete na tela
            let freteComTaxa = maiorFrete;
            let freteTexto = (cobrarFrete && maiorFrete > 0) ? 'R$ ' + freteComTaxa.toFixed(2).replace('.', ',') : 'Entrega Grátis';
            document.getElementById('frete').innerText = freteTexto;

            // Atualizar o valor total
            let totalComFrete = totalBase + (cobrarFrete ? freteComTaxa : 0);
            document.getElementById('ValorTotal').innerText = 'R$ ' + totalComFrete.toFixed(2).replace('.', ',');

            // Recalcular o restante após atualizar o total
            atualizarRestante();
        }

        function atualizarRestante() {
            let entrada = parseFloat(document.getElementById('entradaInput').value.replace(',', '.')) || 0;
            let totalBase = parseFloat(document.getElementById('ValorTotal').innerText.replace('R$', '').replace(',', '.'));
            let restante = totalBase - entrada;
            document.getElementById('restante').innerText = 'R$ ' + restante.toFixed(2).replace('.', ',');
        }

        function gerarQRCode() {
            // Lógica para gerar o QR Code e o link de cópia e cola do PIX
            const valor = document.getElementById('valor_pix').value;
            // Supondo que a função gerarLinkPix(valor) retorna o link do PIX
            const linkPix = gerarLinkPix(valor);
            document.getElementById('pix_link').href = linkPix;
            document.getElementById('link_pix').style.display = 'block';
        }

        function gerarLinkPix(valor) {
            // Implementar a lógica para gerar o link do PIX com base no valor
            return 'https://example.com/pix?valor=' + encodeURIComponent(valor);
        }

        function calcularParcelas() {
            const total = parseFloat(document.getElementById('valor_cartaoCred').value.replace(/\./g, '').replace(',', '.'));
            const parcelasSelect = document.getElementById('parcelas_cartaoCred');
            parcelasSelect.innerHTML = `
                <option value="1">1x de R$ ${total.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.')} sem juros</option>
                <option value="2">2x de R$ ${(total / 2).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.')} sem juros</option>
                <option value="3">3x de R$ ${(total / 3).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.')} sem juros</option>
            `;
            for (let i = 4; i <= 12; i++) {
                const valorParcela = (total * Math.pow(1 + 0.0299, i)) / i;
                parcelasSelect.innerHTML += `<option value="${i}">${i}x de R$ ${valorParcela.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.')} com 2,99% a.m.</option>`;
            }
        }

        for (let i = 4; i <= 12; i++) {
            const valorParcela = (total * Math.pow(1 + 0.0299, i)) / i;
            parcelasSelect.innerHTML += `<option value="${i}">${i}x de R$ ${valorParcela.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.')} com 2,99% a.m.</option>`;
        }

        function formatarMoeda(input) {
            let valor = input.value.replace(/\D/g, '');
            valor = (valor / 100).toFixed(2) + '';
            valor = valor.replace(".", ",");
            valor = valor.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
            input.value = valor;
        }

        function verificarValorPix() {
            const total = parseFloat('<?php echo $total; ?>');
            const valorPix = parseFloat(document.getElementById('valor_pix').value.replace(/\./g, '').replace(',', '.'));
            if (valorPix > total) {
                alert('O valor a ser pago não pode ser maior que o valor da compra.');
                //exit;
                document.getElementById('valor_pix').value = '<?php echo number_format('0', 2, ',', '.'); ?>';
            }
        }

        function verificarValorCartaoCred() {
            const total = parseFloat('<?php echo $total; ?>');
            const valorCartaoCred = parseFloat(document.getElementById('valor_cartaoCred').value.replace(/\./g, '').replace(',', '.'));
            if (valorCartaoCred > total) {
                alert('O valor a ser pago não pode ser maior que o valor da compra.');
                //exit;
                document.getElementById('valor_cartaoCred').value = '<?php echo number_format('0', 2, ',', '.'); ?>';
                calcularParcelas();
            } else {
                calcularParcelas();
            }
        }

        function confirmarPagamentoCartao() {
            // Lógica para confirmar o pagamento com cartão de crédito
            alert('Pagamento com cartão de crédito confirmado!');
            fecharPopup('popup_cartaoCred'); // Fechar o popup após confirmar o pagamento
        }

        function verificarValorCartaoDeb() {
            const total = parseFloat('<?php echo $total; ?>');
            const valorCartaoDeb = parseFloat(document.getElementById('valor_cartaoDeb').value.replace(/\./g, '').replace(',', '.'));
            if (valorCartaoDeb > total) {
                alert('O valor a ser pago não pode ser maior que o valor da compra.');
                //exit;
                document.getElementById('valor_cartaoDeb').value = '<?php echo number_format('0', 2, ',', '.'); ?>';
            }
        }

        function confirmarPagamentoCartaoDeb() {
            // Lógica para confirmar o pagamento com cartão de débito
            alert('Pagamento com cartão de débito confirmado!');
            fecharPopup('popup_cartaoDeb');
        }

        function mostrarBandeirasCriterio(select) {
            const selectedOptions = Array.from(select.selectedOptions);
            document.getElementById('bandeiras_credito_crediario').style.display = selectedOptions.some(option => option.value === 'cartaoCred') ? 'block' : 'none';
            document.getElementById('bandeiras_debito_crediario').style.display = selectedOptions.some(option => option.value === 'cartaoDeb') ? 'block' : 'none';
        }

        function mostrarBandeirasCriterio(select) {
            const selectedOptions = Array.from(select.selectedOptions);
            document.getElementById('bandeiras_credito_crediario').style.display = selectedOptions.some(option => option.value === 'cartaoCred') ? 'block' : 'none';
            document.getElementById('bandeiras_debito_crediario').style.display = selectedOptions.some(option => option.value === 'cartaoDeb') ? 'block' : 'none';
        }

        function verificarEntradaMinima() {
            const total = parseFloat('<?php echo $total; ?>');
            const limiteCred = parseFloat('<?php echo $limite_cred; ?>');
            const entradaInput = document.getElementById('entradaInput');
            const entrada = parseFloat(entradaInput.value.replace(/\./g, '').replace(',', '.')) || 0;
            const diferenca = total - limiteCred;

            if (total > limiteCred && entrada < diferenca) {
                alert('A entrada deve ser no mínimo R$ ' + diferenca.toFixed(2).replace('.', ','));
                entradaInput.value = diferenca.toFixed(2).replace('.', ',');
                atualizarRestante();
            }
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

            const totalInput = document.createElement('input');
            totalInput.type = 'hidden';
            totalInput.name = 'valor_total';
            totalInput.value = '<?php echo $total; ?>';
            form.appendChild(totalInput);

            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html>
