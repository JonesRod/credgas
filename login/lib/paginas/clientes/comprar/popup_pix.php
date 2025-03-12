<?php
    session_start();
    include('../../../conexao.php'); // Conexão com o banco

    // Verificação de sessão
    if (!isset($_SESSION['id'])) {
        header("Location: ../../../../index.php");
        exit;
    }

    $id_cliente = intval($_POST['id_cliente']);
    $id_parceiro = intval($_POST['id_parceiro']);
    $total = floatval($_POST['valor_total']);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagar com PIX</title>
    <style>
        .popup-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            position: relative;
            margin: 20px auto;
            max-width: 600px;
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
    <div class="popup-content">
        <span class="close" onclick="window.history.back()">&times;</span>
        <h3>Pagar com PIX</h3>
        <h3>Valor da minha compra: <?php echo 'R$ ' . number_format($total, 2, ',', '.'); ?></h3>
        <p>Abra o aplicativo do seu banco e faça a leitura do QR Code abaixo para efetuar o pagamento.</p>
        <img src="qr_code_pix.png" alt="QR Code PIX">
        <label for="valor_pix">Valor a ser pago: R$ </label>
        <input type="text" id="valor_pix" name="valor_pix" value="<?php echo number_format('0', 2, ',', '.'); ?>" oninput="formatarMoeda(this); verificarValorPix()">
        <br>
        <p id="link_pix" style="display: none;">Link de cópia e cola do PIX: <a href="#" id="pix_link">Copiar</a></p>
        <button type="button" onclick="window.history.back()">Voltar</button>
        <button type="button" onclick="continuarPagamento('PIX')">Continuar</button>
        <button type="button" onclick="gerarQRCode()">Gerar QR Code</button>
    </div>

    <div id="popup_segunda_forma" class="popup" style="display: none;">
        <div class="popup-content">
            <span class="close" onclick="fecharPopup('popup_segunda_forma')">&times;</span>
            <h3>Escolha a 2ª forma de pagamento</h3>
            <h3>Valor restante: R$ <span id="valor_restante"></span></h3>
            <label>Escolha a 2ª forma de pagamento:</label>
            <select id="segunda_forma_pagamento" name="segunda_forma_pagamento" onchange="mostrarCamposSegundaForma()">
                <option value="selecionar">Selecionar</option>    
                <option value="pix">PIX</option>
                <option value="cartaoCred">Cartão de Crédito</option>
                <option value="cartaoDeb">Cartão de Débito</option>
            </select>
            <br>
            <div id="campos_pix" style="display: none;">
                <label for="valor_pix_segunda">Valor a ser pago: R$ </label>
                <input type="text" id="valor_pix_segunda" name="valor_pix_segunda" readonly>
            </div>
            <div id="campos_cartaoCred" style="display: none;">
                <label for="valor_cartaoCred_segunda">Valor a ser pago: R$ </label>
                <input type="text" id="valor_cartaoCred_segunda" name="valor_cartaoCred_segunda" readonly>
                <br>
                <label for="parcelas_cartaoCred_segunda">Quantidade de parcelas:</label>
                <select id="parcelas_cartaoCred_segunda" name="parcelas_cartaoCred_segunda">
                    <!-- Opções serão preenchidas dinamicamente -->
                </select>
            </div>
            <div id="campos_cartaoDeb" style="display: none;">
                <label for="valor_cartaoDeb_segunda">Valor a ser pago: R$ </label>
                <input type="text" id="valor_cartaoDeb_segunda" name="valor_cartaoDeb_segunda" readonly>
            </div>
            <br>
            <button type="button" onclick="fecharPopup('popup_segunda_forma')">Cancelar</button>
            <button type="button" onclick="enviarSegundaForma()">Continuar</button>
        </div>
    </div>

    <script>
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
                document.getElementById('valor_pix').value = '<?php echo number_format('0', 2, ',', '.'); ?>';
            }
        }

        function continuarPagamento(metodo) {
            const total = parseFloat('<?php echo $total; ?>');
            const valorPix = parseFloat(document.getElementById('valor_pix').value.replace(/\./g, '').replace(',', '.'));
            const restante = total - valorPix;

            if (restante > 0) {
                document.getElementById('valor_restante').innerText = restante.toFixed(2).replace('.', ',');
                document.getElementById('popup_segunda_forma').style.display = 'block';
            } else {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'processar_pagamento.php';

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

                const valorPixInput = document.createElement('input');
                valorPixInput.type = 'hidden';
                valorPixInput.name = 'valor_pix';
                valorPixInput.value = document.getElementById('valor_pix').value;
                form.appendChild(valorPixInput);

                document.body.appendChild(form);
                form.submit();
            }
        }

        function gerarQRCode() {
            // Lógica para gerar o QR Code e o link de cópia e cola do PIX
            const valor = document.getElementById('valor_pix').value;
            const linkPix = gerarLinkPix(valor);
            document.getElementById('pix_link').href = linkPix;
            document.getElementById('link_pix').style.display = 'block';
        }

        function gerarLinkPix(valor) {
            // Implementar a lógica para gerar o link do PIX com base no valor
            return 'https://example.com/pix?valor=' + encodeURIComponent(valor);
        }

        function abrirSegundaForma() {
            const segundaForma = document.getElementById('segunda_forma_pagamento').value;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = segundaForma + '_pagamento.php';

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

            const valorPixInput = document.createElement('input');
            valorPixInput.type = 'hidden';
            valorPixInput.name = 'valor_pix';
            valorPixInput.value = document.getElementById('valor_pix').value;
            form.appendChild(valorPixInput);

            if (segundaForma === 'pix') {
                const valorPixSegundaInput = document.createElement('input');
                valorPixSegundaInput.type = 'hidden';
                valorPixSegundaInput.name = 'valor_pix_segunda';
                valorPixSegundaInput.value = document.getElementById('valor_pix_segunda').value;
                form.appendChild(valorPixSegundaInput);
            } else if (segundaForma === 'cartaoCred') {
                const valorCartaoCredSegundaInput = document.createElement('input');
                valorCartaoCredSegundaInput.type = 'hidden';
                valorCartaoCredSegundaInput.name = 'valor_cartaoCred_segunda';
                valorCartaoCredSegundaInput.value = document.getElementById('valor_cartaoCred_segunda').value;
                form.appendChild(valorCartaoCredSegundaInput);

                const parcelasCartaoCredSegundaInput = document.createElement('input');
                parcelasCartaoCredSegundaInput.type = 'hidden';
                parcelasCartaoCredSegundaInput.name = 'parcelas_cartaoCred_segunda';
                parcelasCartaoCredSegundaInput.value = document.getElementById('parcelas_cartaoCred_segunda').value;
                form.appendChild(parcelasCartaoCredSegundaInput);
            } else if (segundaForma === 'cartaoDeb') {
                const valorCartaoDebSegundaInput = document.createElement('input');
                valorCartaoDebSegundaInput.type = 'hidden';
                valorCartaoDebSegundaInput.name = 'valor_cartaoDeb_segunda';
                valorCartaoDebSegundaInput.value = document.getElementById('valor_cartaoDeb_segunda').value;
                form.appendChild(valorCartaoDebSegundaInput);
            }

            document.body.appendChild(form);
            form.submit();
        }

        function mostrarCamposSegundaForma() {
            const forma = document.getElementById('segunda_forma_pagamento').value;
            const restante = parseFloat(document.getElementById('valor_restante').innerText.replace(',', '.'));

            document.getElementById('campos_pix').style.display = forma === 'pix' ? 'block' : 'none';
            document.getElementById('campos_cartaoCred').style.display = forma === 'cartaoCred' ? 'block' : 'none';
            document.getElementById('campos_cartaoDeb').style.display = forma === 'cartaoDeb' ? 'block' : 'none';

            if (forma === 'pix') {
                document.getElementById('valor_pix_segunda').value = restante.toFixed(2).replace('.', ',');
            } else if (forma === 'cartaoCred') {
                document.getElementById('valor_cartaoCred_segunda').value = restante.toFixed(2).replace('.', ',');
                mostrarParcelasCartaoCred(restante);
            } else if (forma === 'cartaoDeb') {
                document.getElementById('valor_cartaoDeb_segunda').value = restante.toFixed(2).replace('.', ',');
            }
        }

        function mostrarParcelasCartaoCred(restante) {
            const parcelasSelect = document.getElementById('parcelas_cartaoCred_segunda');
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
                    option.value = i + 'x';
                    option.textContent = `${i}x de R$ ${valorParcela.toFixed(2).replace('.', ',')}${labelJuros}`;
                    parcelasSelect.appendChild(option);
                }
            } else {
                console.error('Erro: maxParcelas inválido.');
            }
        }

        function atualizarValorParcelas() {
            const restante = parseFloat(document.getElementById('valor_restante').innerText.replace(',', '.'));
            const parcelas = parseInt(document.getElementById('parcelas_cartaoCred_segunda').value);
            let valorParcela;

            if (parcelas > 3) {
                const taxaJuros = 0.0299; // 2.99% ao mês
                valorParcela = (restante * Math.pow(1 + taxaJuros, parcelas)) / parcelas;
            } else {
                valorParcela = restante / parcelas;
            }

            document.getElementById('valor_parcelas_cartaoCred_segunda').value = `${parcelas}x de R$ ${valorParcela.toFixed(2).replace('.', ',')}`;
        }

        function fecharPopup(popupId) {
            document.getElementById(popupId).style.display = 'none';
        }
    </script>
</body>
</html>
