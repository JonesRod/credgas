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
            <select id="segunda_forma_pagamento" name="segunda_forma_pagamento">
                <option value="selecionar">Selecionar</option>    
                <option value="pix">PIX</option>
                <option value="cartaoCred">Cartão de Crédito</option>
                <option value="cartaoDeb">Cartão de Débito</option>
            </select>
            <br>
            <button type="button" onclick="fecharPopup('popup_segunda_forma')">Cancelar</button>
            <button type="button" onclick="abrirSegundaForma()">Continuar</button>
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
                alert('Pagamento com ' + metodo + ' confirmado!');
                window.history.back();
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
            fecharPopup('popup_segunda_forma'); // Fechar o popup atual antes de abrir o próximo
            if (segundaForma === 'pix') {
                abrirPopup('PIX', 'segunda');
            } else if (segundaForma === 'cartaoCred') {
                abrirPopup('Cartão de Crédito', 'segunda');
            } else if (segundaForma === 'cartaoDeb') {
                abrirPopup('Cartão de Débito', 'segunda');
            }
        }

        function fecharPopup(popupId) {
            document.getElementById(popupId).style.display = 'none';
        }
    </script>
</body>
</html>
