<?php
    session_start();
    include('../../../conexao.php'); // Conexão com o banco

    // Verificação de sessão
    if (!isset($_SESSION['id'])) {
        header("Location: ../../../../index.php");
        exit;
    }

    $id_cliente = $_SESSION['id'];
    $id_parceiro = isset($_POST['id_parceiro']) ? intval($_POST['id_parceiro']) : 0;
    $total = isset($_POST['valor_total']) ? floatval($_POST['valor_total']) : 0.0;

    var_dump($_POST);
    
    // Verificar se a conexão foi estabelecida
    if (!$mysqli) {
        die("Falha na conexão com o banco de dados: " . mysqli_connect_error());
    }

    // Buscar cartões do cliente usando prepared statements
    $stmt = $mysqli->prepare("SELECT id, num_cartao FROM cartoes_clientes WHERE id_cliente = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $result = $stmt->get_result();

        $cartoes = array();
        while ($row = $result->fetch_assoc()) {
            $cartoes[] = $row;
        }

        $stmt->close();
    } else {
        die("Erro na preparação da consulta: " . $mysqli->error);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['num_cartao']) && isset($_POST['validade']) && isset($_POST['cod_seguranca'])) {
        $num_cartao = $_POST['num_cartao'];
        $validade = $_POST['validade'];
        $cod_seguranca = $_POST['cod_seguranca'];

        // Salvar o novo cartão no banco de dados
        $stmt = $mysqli->prepare("INSERT INTO cartoes_clientes (id_cliente, num_cartao, validade, cod_seguranca) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("isss", $id_cliente, $num_cartao, $validade, $cod_seguranca);
            $stmt->execute();
            $stmt->close();

            $mensagem = "Pedido finalizado com sucesso!";
        } else {
            die("Erro ao salvar o cartão: " . $mysqli->error);
        }
    }
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

        .popup {
            display: none;
            position: fixed;
            z-index: 1000; /* Garantir que o popup esteja sobre outros elementos */
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5); /* Fundo semitransparente */
        }

        .popup-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
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

        .mensagem-sucesso {
            display: none;
            position: fixed;
            z-index: 1001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .mensagem-sucesso .popup-content {
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 5px;
            text-align: center;
            background-color: #fff;
        }
    </style>
    <script>
        function formatarMoeda(input) {
            let value = input.value.replace(/\D/g, '');
            value = (value / 100).toFixed(2) + '';
            value = value.replace(".", ",");
            value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
            input.value = value;
        }

        function verificarValorPix() {
            let valorPix = parseFloat(document.getElementById('valor_pix').value.replace(/\./g, '').replace(',', '.'));
            let valorTotal = <?php echo $total; ?>;
            if (valorPix > valorTotal) {
                alert('O valor digitado não pode ser maior que o valor da compra.');
                document.getElementById('valor_pix').value = '<?php echo number_format($total, 2, ',', '.'); ?>';
            }
        }

        function gerarQRCode() {
            let valorPix = document.getElementById('valor_pix').value;
            // Lógica para gerar o QR Code com o valor do PIX
            document.getElementById('qr_code_pix').src = 'gerar_qr_code.php?valor=' + valorPix;
            document.getElementById('qr_code_pix').style.display = 'block';

            // Lógica para gerar o link de cópia e cola do PIX
            let pixLink = 'pix://pagamento?valor=' + valorPix;
            document.getElementById('pix_link').href = pixLink;
            document.getElementById('link_pix').style.display = 'block';

            // Mostrar o botão "Continuar"
            document.getElementById('btn_continuar').style.display = 'inline-block';
        }

        function continuarPagamento(metodo) {
            let valorPix = parseFloat(document.getElementById('valor_pix').value.replace(/\./g, '').replace(',', '.'));
            let valorTotal = <?php echo $total; ?>;
            let valorRestante = valorTotal - valorPix;

            if (valorRestante > 0) {
                document.getElementById('valor_restante').innerText = valorRestante.toFixed(2).replace('.', ',');
                document.getElementById('popup_segunda_forma').style.display = 'block';
            } else {
                // Lógica para continuar o pagamento com PIX
                alert('Pagamento concluído com PIX.');
            }
        }

        function fecharPopup(popupId) {
            document.getElementById(popupId).style.display = 'none';
        }

        function abrirPopupNovoCartao() {
            document.getElementById('popup_novo_cartao').style.display = 'block';
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
                    option.value = i;
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
        }

        function verificarCartaoSelecionado() {
            const checkboxes = document.querySelectorAll('input[name="cartao_selecionado"]');
            checkboxes.forEach((checkbox) => {
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        checkboxes.forEach((cb) => {
                            if (cb !== this) cb.checked = false;
                        });
                    }
                });
            });

            const btnFinalizar = document.getElementById('btn_finalizar');
            if (document.querySelector('input[name="cartao_selecionado"]:checked')) {
                btnFinalizar.style.display = 'inline-block';
            } else {
                btnFinalizar.style.display = 'none';
            }
        }

        function enviarSegundaForma() {
            let segundaForma = document.getElementById('segunda_forma_pagamento').value;
            if (segundaForma === 'pix') {
                let valorRestante = parseFloat(document.getElementById('valor_restante').innerText.replace(',', '.'));
                // Lógica para gerar o QR Code com o valor restante do PIX
                document.getElementById('qr_code_pix_segunda').src = 'gerar_qr_code.php?valor=' + valorRestante;

                // Lógica para gerar o link de cópia e cola do PIX
                let pixLink = 'pix://pagamento?valor=' + valorRestante;
                document.getElementById('pix_link_segunda').href = pixLink;
                document.getElementById('link_pix_segunda').style.display = 'block';
                document.getElementById('qr_code_pix_segunda').style.display = 'block';

                document.getElementById('campos_cartaoCred').style.display = 'none';
                document.getElementById('campos_cartaoDeb').style.display = 'none';
            } else if (segundaForma === 'cartaoCred') {
                document.getElementById('campos_cartaoCred').style.display = 'block';
                document.getElementById('campos_cartaoDeb').style.display = 'none';
                document.getElementById('link_pix_segunda').style.display = 'none';
                document.getElementById('qr_code_pix_segunda').style.display = 'none';

                let valorRestante = parseFloat(document.getElementById('valor_restante').innerText.replace(',', '.'));
                mostrarParcelasCartaoCred(valorRestante);
                verificarCartaoSelecionado();
            } else if (segundaForma === 'cartaoDeb') {
                document.getElementById('campos_cartaoDeb').style.display = 'block';
                document.getElementById('campos_cartaoCred').style.display = 'none';
                document.getElementById('link_pix_segunda').style.display = 'none';
                document.getElementById('qr_code_pix_segunda').style.display = 'none';
            } else {
                document.getElementById('campos_cartaoCred').style.display = 'none';
                document.getElementById('campos_cartaoDeb').style.display = 'none';
                document.getElementById('link_pix_segunda').style.display = 'none';
                document.getElementById('qr_code_pix_segunda').style.display = 'none';
            }
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

        function adicionarNovoCartao() {
            const form = document.getElementById('form_novo_cartao');
            form.submit();
        }

        function finalizarPagamento() {
            const cartaoSelecionado = document.querySelector('input[name="cartao_selecionado"]:checked');
            if (cartaoSelecionado) {
                const idCartao = cartaoSelecionado.value;
                // Enviar o formulário via POST
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = ''; // Defina a ação correta aqui
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'id_cartao';
                input.value = idCartao;
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();

                // Mostrar a mensagem de sucesso
                mostrarMensagemSucesso();
            } else {
                alert('Nenhum cartão selecionado.');
            }
        }

        function mostrarMensagemSucesso() {
            const mensagemSucesso = document.getElementById('mensagem_sucesso');
            if (mensagemSucesso) {
                mensagemSucesso.style.display = 'block';
                setTimeout(function() {
                    document.getElementById('mensagem_final').style.display = 'block';
                }, 3000);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            verificarCartaoSelecionado();
        });
    </script>
</head>
<body>
    <div class="popup-content">
        <span class="close" onclick="window.history.back()">&times;</span>
        <h3>Pagar com PIX</h3>
        <h3>Valor da minha compra: <?php echo 'R$ ' . number_format($total, 2, ',', '.'); ?></h3>
        <p>Abra o aplicativo do seu banco e faça a leitura do QR Code abaixo para efetuar o pagamento.</p>
        <label for="valor_pix">Valor a ser pago: R$ </label>
        <input type="text" id="valor_pix" name="valor_pix" value="<?php echo number_format($total, 2, ',', '.'); ?>" oninput="formatarMoeda(this); verificarValorPix()">
        <br>
        <img id="qr_code_pix" src="qr_code_pix.png" alt="QR Code PIX" style="display: none;">
        <br>
        <p id="link_pix" style="display: none;">Link de cópia e cola do PIX: <a href="#" id="pix_link">Copiar</a></p>
        <button type="button" onclick="window.history.back()">Voltar</button>
        <button type="button" onclick="gerarQRCode()">Gerar QR Code</button>
        <button type="button" id="btn_continuar" onclick="continuarPagamento('PIX')" style="display: none;">Continuar</button>
    </div>

    <div id="popup_segunda_forma" class="popup">
        <div class="popup-content">
            <span class="close" onclick="fecharPopup('popup_segunda_forma')">&times;</span>
            <h3>Escolha a 2ª forma de pagamento</h3>
            <h3>Valor restante: R$ <span id="valor_restante"></span></h3>
            <label>Escolha a 2ª forma de pagamento:</label>
            <select id="segunda_forma_pagamento" name="segunda_forma_pagamento" onchange="enviarSegundaForma()">
                <option value="selecionar">Selecionar</option>    
                <option value="pix">PIX</option>
                <option value="cartaoCred">Cartão de Crédito</option>
                <option value="cartaoDeb">Cartão de Débito</option>
            </select>                
            <br>
            <img id="qr_code_pix_segunda" src="qr_code_pix.png" alt="QR Code PIX" style="display: none;">
            <p id="link_pix_segunda" style="display: none;">Link de cópia e cola do PIX: <a href="#" id="pix_link_segunda">Copiar</a></p>
            
            <div id="campos_cartaoCred" style="display: none;">         
                <label for="parcelas_cartaoCred_segunda">Quantidade de parcelas:</label>
                <select id="parcelas_cartaoCred_segunda" name="parcelas_cartaoCred_segunda" onchange="atualizarValorParcelas()">
                    <!-- Opções serão preenchidas dinamicamente -->
                </select>
                <br>
                <h3>Selecione o cartão a ser usado</h3>
                <table id="cartoes_cadastrados">
                    <thead>
                        <tr>
                            <th>Selecionar</th>
                            <th>Número do Cartão</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($cartoes)): ?>
                            <tr>
                                <td colspan="2" id="mensagem_sem_cartao">Nenhum cartão salvo!</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($cartoes as $cartao): ?>
                                <tr>
                                    <td><input type="checkbox" name="cartao_selecionado" value="<?php echo $cartao['id']; ?>" onchange="verificarCartaoSelecionado()"></td>
                                    <td>**** **** **** <?php echo substr($cartao['num_cartao'], -4); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <br>
                <button type="button" onclick="abrirPopupNovoCartao()">Usar outro cartão</button>
                <br>
            </div>

            <div id="campos_cartaoDeb" style="display: none;">
                <input type="hidden" id="valor_cartaoDeb_segunda" name="valor_cartaoDeb_segunda" readonly>
            </div>
            <br>
            <button type="button" onclick="fecharPopup('popup_segunda_forma')">Cancelar</button>
            <button type="button" id="btn_finalizar" onclick="finalizarPagamento()" style="display: none;">Finalizar</button>
        </div>
    </div>

    <div id="popup_novo_cartao" class="popup">
        <div class="popup-content">
            <span class="close" onclick="fecharPopup('popup_novo_cartao')">&times;</span>
            <h3>Adicionar Novo Cartão</h3>
            <form id="form_novo_cartao" method="post">
                <label for="num_cartao">Número do Cartão:</label>
                <input type="text" id="num_cartao" name="num_cartao" required oninput="formatarNumeroCartao(this)">
                <br>
                <label for="validade">Validade:</label>
                <input type="text" id="validade" name="validade" required oninput="formatarValidadeCartao(this)">
                <br>
                <label for="cod_seguranca">Código de Segurança:</label>
                <input type="text" id="cod_seguranca" name="cod_seguranca" required oninput="formatarCodSeguranca(this)">
                <br>
                <button type="button" onclick="fecharPopup('popup_novo_cartao')">Cancelar</button>
                <button type="button" onclick="adicionarNovoCartao()">Salvar e Usar</button>
                <button type="button" onclick="usarCartaoUmaVez()">Usar só dessa vez</button>
            </form>
        </div>
    </div>

    <div id="mensagem_final" class="popup">
        <div class="popup-content">
            <span class="close" onclick="fecharPopup('mensagem_final')">&times;</span>
            <p>Pedido finalizado com sucesso!</p>
        </div>
    </div>

    <?php if (isset($mensagem)): ?>
        <div id="mensagem_sucesso" class="mensagem-sucesso">
            <div class="popup-content">
                <span class="close" onclick="fecharPopup('mensagem_sucesso')">&times;</span>
                <p><?php echo $mensagem; ?></p>
            </div>
        </div>
        <script>
            mostrarMensagemSucesso();
        </script>
    <?php endif; ?>
</body>
</html>
