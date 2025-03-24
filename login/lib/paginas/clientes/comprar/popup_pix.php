<?php
    session_start();
    include('../../../conexao.php'); // Conexão com o banco

    // Verificação de sessão
    if (!isset($_SESSION['id'])) {
        header("Location: ../../../../index.php");
        exit;
    }

    $id_cliente = $_SESSION['id'];
    $produtos = isset($_POST['detalhes_produtos']) ? $_POST['detalhes_produtos'] : ''; // Detalhes dos produtos
    $id_parceiro = isset($_POST['id_parceiro']) ? intval($_POST['id_parceiro']) : 0;
    $total = isset($_POST['valor_total']) ? floatval($_POST['valor_total']) : 0.0;

    // Verificar se a conexão foi estabelecida
    if (!$mysqli) {
        die("Falha na conexão com o banco de dados: " . mysqli_connect_error());
    }

    // Função para excluir cartão
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_cartao'])) {
        $id_cartao = intval($_POST['id_cartao']);
        $stmt = $mysqli->prepare("DELETE FROM cartoes_clientes WHERE id = ? AND id_cliente = ?");
        
        if ($stmt) {
            $stmt->bind_param("ii", $id_cartao, $id_cliente);
            if ($stmt->execute()) {
                echo json_encode(["sucesso" => true]);
            } else {
                echo json_encode(["sucesso" => false, "erro" => "Falha ao excluir"]);
            }
            $stmt->close();
        } else {
            echo json_encode(["sucesso" => false, "erro" => $mysqli->error]);
        }
        exit;
    }
    

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

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['num_cartao']) || isset($_POST['num_cartao_selecionado']))) {
        $nome_cartao = isset($_POST['nome_cartao']) ? $_POST['nome_cartao'] : '';
        $num_cartao = isset($_POST['num_cartao']) ? $_POST['num_cartao'] : $_POST['num_cartao_selecionado'];
        $validade = isset($_POST['validade']) ? $_POST['validade'] : (isset($_POST['validade_selecionado']) ? $_POST['validade_selecionado'] : '');
        $cod_seguranca = isset($_POST['cod_seguranca']) ? $_POST['cod_seguranca'] : (isset($_POST['cod_seguranca_selecionado']) ? $_POST['cod_seguranca_selecionado'] : '');
        $tipo_cartao = isset($_POST['tipo_cartao']) && strtolower($_POST['tipo_cartao']) === 'débito' ? 'debito' : 'credito'; // Usar o valor enviado pelo input tipo_cartao
        $data_hora = date('Y-m-d H:i:s'); // Data e hora do pedido

        $produtos = isset($_POST['detalhes_produtos']) ? $_POST['detalhes_produtos'] : (isset($_POST['detalhes_produtos_dc']) ? $_POST['detalhes_produtos_dc'] : ''); // Detalhes dos produtos
        
        $entrada = isset($_POST['valor_pix_entrada']) && floatval($_POST['valor_pix_entrada']) < $total 
            ? floatval($_POST['valor_pix_entrada']) 
            : (isset($_POST['valor_pix_entrada_dc']) && is_numeric($_POST['valor_pix_entrada_dc']) 
                ? floatval($_POST['valor_pix_entrada_dc']) 
                : (isset($_POST['valor_pix_entrada_dc_debito']) && is_numeric($_POST['valor_pix_entrada_dc_debito']) 
                    ? floatval($_POST['valor_pix_entrada_dc_debito']) 
                    : 0.0)); // Entrada do pedido

        $valor_restante = $total - $entrada; // Valor restante do pedido

        $forma_pagamento_entrada = 'pix'; // Forma de pagamento da entrada
        $forma_pagamento = 'online'; // Forma de pagamento do pedido
        
        // Forma de pagamento do restante
        $tipo_cartao_pedido = isset($_POST['segunda_forma_pagamento']) && $_POST['segunda_forma_pagamento'] === 'cartaoDeb' 
            ? 'debito' 
            : (isset($_POST['tipo_cartao']) && strtolower($_POST['tipo_cartao']) === 'débito' ? 'debito' : 'credito');
        $forma_pagamento_restante = 'cartao de ' . $tipo_cartao_pedido; // Forma de pagamento do restante
        //echo $forma_pagamento_restante;
        //exit;
        //Quantidade de parcelas
        $qt_parcelas = isset($_POST['parcelas_cartaoCred_segunda']) && !empty($_POST['parcelas_cartaoCred_segunda']) 
            ? $_POST['parcelas_cartaoCred_segunda'] 
            : (isset($_POST['input_parcela_cartao']) ? $_POST['input_parcela_cartao'] : (isset($_POST['parcelas_cartaoCred_segunda_novo']) ? $_POST['parcelas_cartaoCred_segunda_novo'] : ''));


        $salvar_cartao = isset($_POST['salvar_cartao']) && $_POST['salvar_cartao'] === 'true';

        // Criptografar o código de segurança
        $cod_seguranca_criptografado = password_hash($cod_seguranca, PASSWORD_DEFAULT);

        if ($salvar_cartao) {
            // Verificar se o cartão já está cadastrado
            $stmt = $mysqli->prepare("SELECT id FROM cartoes_clientes WHERE id_cliente = ? AND num_cartao = ? AND tipo = ?");
            if ($stmt) {
                $stmt->bind_param("iss", $id_cliente, $num_cartao, $tipo_cartao);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $stmt->close();
                    $mensagem_erro = "Este cartão já está cadastrado.";
                } else {
                    $stmt->close();

                    // Verificar se o limite de cartões foi atingido
                    if (($tipo_cartao === 'credito' && $cartoes_credito >= 5) || ($tipo_cartao === 'debito' && $cartoes_debito >= 5)) {
                        $mensagem_erro = "Você atingiu o limite de 5 cartões de $tipo_cartao.";
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

        // Salvar o pedido no banco de dados
        $stmt = $mysqli->prepare("INSERT INTO pedidos (data, id_cliente, id_parceiro, produtos, valor, forma_pagamento, entrada, forma_pg_entrada, valor_restante, forma_pg_restante, qt_parcelas, status_cliente, status_parceiro) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $status_cliente = 'analise';
            $status_parceiro = 'pendente';
            $stmt->bind_param("siissssssssss", $data_hora, $id_cliente, $id_parceiro, $produtos, $total, $forma_pagamento, $entrada, $forma_pagamento_entrada, $valor_restante, $forma_pagamento_restante, $qt_parcelas, $status_cliente, $status_parceiro);
            $stmt->execute();
            $num_pedido = $stmt->insert_id; // Obter o ID do pedido inserido
            $stmt->close();

            // Salvar notificação na tabela contador_notificacoes_cliente
            $msg = "Pedido #$num_pedido em Análise.";
            $stmt = $mysqli->prepare("INSERT INTO contador_notificacoes_cliente (data, id_cliente, msg, referente, lida) VALUES (?, ?, ?, 'pedido', 1)");
            if ($stmt) {
                $stmt->bind_param("sis", $data_hora, $id_cliente, $msg);
                $stmt->execute();
                $stmt->close();
            } else {
                die("Erro ao salvar a notificação: " . $mysqli->error);
            }

            // Excluir o pedido do carrinho
            $stmt = $mysqli->prepare("DELETE FROM carrinho WHERE id_cliente = ? AND id_parceiro = ?");
            if ($stmt) {
                $stmt->bind_param("ii", $id_cliente, $id_parceiro);
                $stmt->execute();
                $stmt->close();
            } else {
                die("Erro ao excluir do carrinho: " . $mysqli->error);
            }

            $mensagem = "Pedido finalizado com sucesso! Número do pedido: " . $num_pedido;
            echo "<script>
                setTimeout(function() {
                    window.location.href = 'meus_pedidos.php';
                }, 3000);
            </script>";
        } else {
            die("Erro ao salvar o pedido: " . $mysqli->error);
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
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .popup-content {
            justify-content: center;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            position: relative;
            margin: 20px auto;
            max-width: 600px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .popup {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
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
            justify-content: center;
            align-items: center;
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

        #segunda_forma {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            position: relative;
            margin: 20px auto;
            max-width: 600px;
        }

        input[type="text"], select, button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        .label-input-container {
            display: flex;
            align-items: center;
            justify-content: center;
            max-width: 250px; /* Definir a largura máxima */
            margin: 0 auto; /* Centralizar o container */
        }

        .label-input-container label {
            margin-right: 20px;
            flex: 0 0 150px; /* Aumentar o espaço da label */
        }

        @media (max-width: 600px) {
            .popup-content {
                width: 90%;
                margin: 10% auto;
            }

            .mensagem-sucesso .popup-content {
                width: 90%;
                margin: 10% auto;
            }
            .label-input-container label {
                margin-bottom: 5px;
                flex: none; /* Resetar o flex para o layout responsivo */
            }
        }
        @media (max-width: 270px) {
            .label-input-container {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        button.cancelar {
            background-color: #f44336; /* Cor vermelha */
            color: white;
            border: none;
            cursor: pointer;
        }

        button.cancelar:hover {
            background-color: #d32f2f; /* Cor vermelha mais escura ao passar o mouse */
        }

        button.usar-outro-cartao {
            background-color: #2196F3; /* Cor azul */
            color: white;
            border: none;
            cursor: pointer;
        }

        button.usar-outro-cartao:hover {
            background-color: #1976D2; /* Cor azul mais escura ao passar o mouse */
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
            let valorPix = parseFloat(document.getElementById('valor_pix').value.replace(/\./g, '').replace(',', '.'));
            let valorTotal = <?php echo $total; ?>;
            // Ajustar a precisão dos valores para evitar problemas de comparação
            valorPix = parseFloat(valorPix).toFixed(2);
            valorTotal = parseFloat(valorTotal).toFixed(2);

            if (valorPix <= 0) {
                alert('Por favor, digite um valor válido para o pagamento.');
                return;
            }

            // Lógica para gerar o QR Code com o valor do PIX
            document.getElementById('qr_code_pix').src = 'gerar_qr_code.php?valor=' + valorPix;
            document.getElementById('qr_code_pix').style.display = 'block';

            // Lógica para gerar o link de cópia e cola do PIX
            let pixLink = 'pix://pagamento?valor=' + valorPix;
            document.getElementById('pix_link').href = pixLink;
            document.getElementById('link_pix').style.display = 'block';

            //console.log('Valor do PIX: ' + valorPix);
            //console.log('Valor total: ' + valorTotal);

            // Mostrar o botão "Continuar" apenas se o valor do pagamento for menor que o valor da compra
            if (parseFloat(valorPix) < parseFloat(valorTotal)) {
                document.getElementById('btn_continuar').style.display = 'inline-block';
            } else {
                document.getElementById('btn_continuar').style.display = 'none';
            }
        }

        function continuarPagamento(metodo) {
            let valorPix = parseFloat(document.getElementById('valor_pix').value.replace(/\./g, '').replace(',', '.'));
            let valorTotal = <?php echo $total; ?>;
            let valorRestante = valorTotal - valorPix;

            if (valorRestante > 0) {
                document.getElementById('valor_restante').innerText = valorRestante.toFixed(2).replace('.', ',');
                document.getElementById('popup_segunda_forma').style.display = 'block';
                document.getElementById('popup-background').style.display = 'block';

                // Carregar o valor selecionado em parcelas_cartaoCred_segunda para input_parcela_cartao
                const parcelasSelect = document.getElementById('parcelas_cartaoCred_segunda');
                if (parcelasSelect && parcelasSelect.options.length > 0) {
                    const parcelaSelecionada = parcelasSelect.options[parcelasSelect.selectedIndex].text;
                    document.getElementById('input_parcela_cartao').value = parcelaSelecionada;
                }
                definirValorPixEntrada();
            } else {
                // Lógica para continuar o pagamento com PIX
                alert('Pagamento concluído com PIX.');
            }
        }

        function fecharPopup(popupId) {
            document.getElementById(popupId).style.display = 'none';
            document.getElementById('popup-background').style.display = 'none';
        }

        function abrirPopupNovoCartao() {
            const parcelas = document.getElementById('parcelas_cartaoCred_segunda');
            const segundaFormaPagamento = document.getElementById('segunda_forma_pagamento').value;
            if (segundaFormaPagamento === 'cartaoCred' && parcelas.options.length > 0) {
                const parcelaSelecionada = parcelas.options[parcelas.selectedIndex].text;
                document.getElementById('input_segunda_forma_pagamento').value = `Cartão de Crédito: ${parcelaSelecionada}`;
                document.getElementById('parcelas_cartaoCred_segunda_novo').value = parcelaSelecionada; // Carregar o valor selecionado
            } else {
                document.getElementById('parcelas_cartaoCred_segunda_novo').value = ''; // Limpar o campo
            }
            document.getElementById('popup_novo_cartao').style.display = 'block';
            document.getElementById('popup-background').style.display = 'block';
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
                    option.value = `${i}x de R$ ${valorParcela.toFixed(2).replace('.', ',')}${labelJuros}`;
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
            const checkboxes = document.querySelectorAll('input[name="cartao_selecionado"], input[name="cartao_debito_selecionado"]');
            checkboxes.forEach((checkbox) => {
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        checkboxes.forEach((cb) => {
                            if (cb !== this) cb.checked = false;
                        });
                        // Exibir detalhes do cartão selecionado em inputs
                        const numCartao = this.dataset.numCartao;
                        const validade = this.dataset.validade;
                        const codSeguranca = this.dataset.codSeguranca;
                        const nomeCartao = this.dataset.nomeCartao;
                        document.getElementById('num_cartao_selecionado').value = numCartao;
                        document.getElementById('validade_selecionado').value = validade;
                        document.getElementById('cod_seguranca_selecionado').value = codSeguranca;
                        document.getElementById('nome_cartao_selecionado').value = nomeCartao;

                        // Atualizar input_parcela_cartao com o valor selecionado em parcelas_cartaoCred_segunda
                        const parcelasSelect = document.getElementById('parcelas_cartaoCred_segunda');
                        if (parcelasSelect && parcelasSelect.options.length > 0) {
                            const parcelaSelecionada = parcelasSelect.options[parcelasSelect.selectedIndex].text;
                            document.getElementById('input_parcela_cartao').value = parcelaSelecionada;
                        }

                        // Exibir detalhes do cartão de débito selecionado em inputs
                        document.getElementById('num_cartao_selecionado_debito').value = numCartao;
                        document.getElementById('validade_selecionado_debito').value = validade;
                        document.getElementById('cod_seguranca_selecionado_debito').value = codSeguranca;
                        document.getElementById('nome_cartao_selecionado_debito').value = nomeCartao;

                    } else {
                        // Limpar os inputs se o cartão for desmarcado
                        document.getElementById('num_cartao_selecionado').value = '';
                        document.getElementById('validade_selecionado').value = '';
                        document.getElementById('cod_seguranca_selecionado').value = '';
                        document.getElementById('nome_cartao_selecionado').value = '';
                        document.getElementById('input_parcela_cartao').value = '';

                        // Limpar os inputs do cartão de débito se o cartão for desmarcado
                        document.getElementById('num_cartao_selecionado_debito').value = '';
                        document.getElementById('validade_selecionado_debito').value = '';
                        document.getElementById('cod_seguranca_selecionado_debito').value = '';
                        document.getElementById('nome_cartao_selecionado_debito').value = '';
                    }
                });
            });

            const btnFinalizar = document.getElementById('btn_finalizar');
            if (document.querySelector('input[name="cartao_selecionado"]:checked') || document.querySelector('input[name="cartao_debito_selecionado"]:checked')) {
                btnFinalizar.style.display = 'inline-block';
            } else {
                btnFinalizar.style.display = 'none';
            }
        }

        function enviarSegundaForma() {
            let segundaForma = document.getElementById('segunda_forma_pagamento').value;
            document.getElementById('input_segunda_forma_pagamento').value = segundaForma; // Atualizar o campo hidden com o valor selecionado

            // Limpar campos de dados dos cartões e desmarcar o cartão selecionado
            document.getElementById('num_cartao_selecionado').value = '';
            document.getElementById('validade_selecionado').value = '';
            document.getElementById('cod_seguranca_selecionado').value = '';
            document.getElementById('input_parcela_cartao').value = '';
            document.getElementById('num_cartao_selecionado_debito').value = '';
            document.getElementById('validade_selecionado_debito').value = '';
            document.getElementById('cod_seguranca_selecionado_debito').value = '';
            document.querySelectorAll('input[name="cartao_selecionado"], input[name="cartao_debito_selecionado"]').forEach(cb => cb.checked = false);

            if (segundaForma === 'pix') {
                // Ocultar o botão de gerar QR Code do PIX
                document.getElementById('btn_finalizar').style.display = 'none';
                document.getElementById('segunada_forma_gerarQRCode').style.display = 'block';
                document.getElementById('campos_cartaoCred').style.display = 'none';
                document.getElementById('campos_cartaoDeb').style.display = 'none';
                document.getElementById('link_pix_segunda').style.display = 'none';
                document.getElementById('qr_code_pix_segunda').style.display = 'none';
            } else if (segundaForma === 'cartaoCred') {
                // Ocultar o botão de gerar QR Code do PIX
                document.getElementById('segunada_forma_gerarQRCode').style.display = 'none';
                document.getElementById('btn_finalizar').style.display = 'none';
                document.getElementById('campos_cartaoCred').style.display = 'block';
                document.getElementById('campos_cartaoDeb').style.display = 'none';
                document.getElementById('link_pix_segunda').style.display = 'none';
                document.getElementById('qr_code_pix_segunda').style.display = 'none';

                // Atualizar o tipo de cartão para Crédito
                document.getElementById('tipo_cartao').value = 'Crédito';

                let valorRestante = parseFloat(document.getElementById('valor_restante').innerText.replace(',', '.'));
                mostrarParcelasCartaoCred(valorRestante);
                verificarCartaoSelecionado();
            } else if (segundaForma === 'cartaoDeb') {
                // Ocultar o botão de gerar QR Code do PIX
                document.getElementById('segunada_forma_gerarQRCode').style.display = 'none';
                document.getElementById('btn_finalizar').style.display = 'none';
                document.getElementById('campos_cartaoDeb').style.display = 'block';
                document.getElementById('campos_cartaoCred').style.display = 'none';
                document.getElementById('link_pix_segunda').style.display = 'none';
                document.getElementById('qr_code_pix_segunda').style.display = 'none';
                
                // Atualizar o tipo de cartão para Débito
                document.getElementById('tipo_cartao').value = 'Débito';
            } else {
                // Ocultar o botão de gerar QR Code do PIX
                document.getElementById('segunada_forma_gerarQRCode').style.display = 'none'; 
                document.getElementById('btn_finalizar').style.display = 'none';             
                document.getElementById('campos_cartaoCred').style.display = 'none';
                document.getElementById('campos_cartaoDeb').style.display = 'none';
                document.getElementById('link_pix_segunda').style.display = 'none';
                document.getElementById('qr_code_pix_segunda').style.display = 'none';
            }
        }

        function mostrarQRCodeSegundaForma() {
            let valorRestante = parseFloat(document.getElementById('valor_restante').innerText.replace(',', '.'));
            // Lógica para gerar o QR Code com o valor restante do PIX
            document.getElementById('qr_code_pix_segunda').src = 'gerar_qr_code.php?valor=' + valorRestante;

            // Lógica para gerar o link de cópia e cola do PIX
            let pixLink = 'pix://pagamento?valor=' + valorRestante;
            document.getElementById('pix_link_segunda').href = pixLink;
            document.getElementById('link_pix_segunda').style.display = 'block';
            document.getElementById('qr_code_pix_segunda').style.display = 'block';
            document.getElementById('segunada_forma_gerarQRCode').style.display = 'none';
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

            if (numCartao.length !== 16) {
                alert('O número do cartão deve ter 16 dígitos.');
                return false;
            }

            if (validade.length !== 5 || !/^\d{2}\/\d{2}$/.test(validade)) {
                alert('A validade deve estar no formato MM/AA.');
                return false;
            }

            if (codSeguranca.length !== 3) {
                alert('O código de segurança deve ter 3 dígitos.');
                return false;
            }

            return true;
        }

        function definirValorPixEntrada() {
            let valorPix = document.getElementById('valor_pix').value.replace(/\./g, '').replace(',', '.');
            document.getElementById('valor_pix_entrada').value = valorPix;
            document.getElementById('valor_pix_entrada_dc').value = valorPix;
            document.getElementById('valor_pix_entrada_dc_debito').value = valorPix;
        }

        function adicionarNovoCartao() {
            if (validarCartao()) {
                definirValorPixEntrada(); // Definir o valor de valor_pix_entrada
                document.getElementById('popup_confirmacao_salvar_usar').style.display = 'block';
                document.getElementById('popup-background').style.display = 'block';
            }
        }

        function confirmarSalvarUsar() {
            const form = document.getElementById('form_novo_cartao');
            form.action = 'popup_pix.php'; // Defina a ação correta aqui
            form.method = 'POST';

            // Adicionar campo hidden para salvar o cartão
            const salvarCartaoInput = document.createElement('input');
            salvarCartaoInput.type = 'hidden';
            salvarCartaoInput.name = 'salvar_cartao';
            salvarCartaoInput.value = 'true';
            form.appendChild(salvarCartaoInput);

            // Adicionar campo hidden para redirecionar após salvar
            const redirectInput = document.createElement('input');
            redirectInput.type = 'hidden';
            redirectInput.name = 'redirect';
            redirectInput.value = 'meus_pedidos.php';
            form.appendChild(redirectInput);

            // Adicionar campo hidden para quantidade de parcelas
            const qtParcelasInput = document.createElement('input');
            qtParcelasInput.type = 'hidden';
            qtParcelasInput.name = 'parcelas_cartaoCred_segunda_novo';
            qtParcelasInput.value = document.getElementById('parcelas_cartaoCred_segunda_novo').value;
            form.appendChild(qtParcelasInput);

            form.submit();
        }

        function cancelarSalvarUsar() {
            document.getElementById('popup_confirmacao_salvar_usar').style.display = 'none';
            document.getElementById('popup-background').style.display = 'none';
        }

        function usarCartaoUmaVez() {
            if (validarCartao()) {
                definirValorPixEntrada(); // Definir o valor de valor_pix_entrada
                document.getElementById('popup_confirmacao_usar_uma_vez').style.display = 'block';
                document.getElementById('popup-background').style.display = 'block';
            }
        }

        function confirmarUsarUmaVez() {
            const form = document.getElementById('form_novo_cartao'); // Corrigir o formulário
            form.action = 'popup_pix.php'; // Defina a ação correta aqui
            form.method = 'POST';

            // Adicionar campo hidden para não salvar o cartão
            const salvarCartaoInput = document.createElement('input');
            salvarCartaoInput.type = 'hidden';
            salvarCartaoInput.name = 'salvar_cartao';
            salvarCartaoInput.value = 'false';
            form.appendChild(salvarCartaoInput);

            // Adicionar campo hidden para redirecionar após salvar
            const redirectInput = document.createElement('input');
            redirectInput.type = 'hidden';
            redirectInput.name = 'redirect';
            redirectInput.value = 'meus_pedidos.php';
            form.appendChild(redirectInput);

            // Adicionar campos hidden para id_parceiro e valor_total
            const idParceiroInput = document.createElement('input');
            idParceiroInput.type = 'hidden';
            idParceiroInput.name = 'id_parceiro';
            idParceiroInput.value = '<?php echo $id_parceiro; ?>';
            form.appendChild(idParceiroInput);

            const valorTotalInput = document.createElement('input');
            valorTotalInput.type = 'hidden';
            valorTotalInput.name = 'valor_total';
            valorTotalInput.value = '<?php echo $total; ?>';
            form.appendChild(valorTotalInput);

            // Adicionar campo hidden para quantidade de parcelas
            const qtParcelasInput = document.createElement('input');
            qtParcelasInput.type = 'hidden';
            qtParcelasInput.name = 'parcelas_cartaoCred_segunda_novo';
            qtParcelasInput.value = document.getElementById('parcelas_cartaoCred_segunda_novo').value;
            form.appendChild(qtParcelasInput);

            // Garantir que o formulário seja enviado corretamente
            form.submit();
        }

        function cancelarUsarUmaVez() {
            document.getElementById('popup_confirmacao_usar_uma_vez').style.display = 'none';
            document.getElementById('popup-background').style.display = 'none';
        }

        function finalizarPagamento() {
            definirValorPixEntrada(); // Definir o valor de valor_pix_entrada
            document.getElementById('popup_confirmacao_pagamento').style.display = 'block';
            document.getElementById('popup-background').style.display = 'block';
        }

        function confirmarPagamento() {
            const form = document.getElementById('segunda_forma'); // Corrigir o formulário
            form.action = 'popup_pix.php'; // Defina a ação correta aqui
            form.method = 'POST';

            // Adicionar campo hidden para não salvar o cartão
            const salvarCartaoInput = document.createElement('input');
            salvarCartaoInput.type = 'hidden';
            salvarCartaoInput.name = 'salvar_cartao';
            salvarCartaoInput.value = 'false';
            form.appendChild(salvarCartaoInput);

            // Adicionar campo hidden para redirecionar após salvar
            const redirectInput = document.createElement('input');
            redirectInput.type = 'hidden';
            redirectInput.name = 'redirect';
            redirectInput.value = 'meus_pedidos.php';
            form.appendChild(redirectInput);

            // Adicionar campos hidden para id_parceiro e valor_total
            const idParceiroInput = document.createElement('input');
            idParceiroInput.type = 'hidden';
            idParceiroInput.name = 'id_parceiro';
            idParceiroInput.value = '<?php echo $id_parceiro; ?>';
            form.appendChild(idParceiroInput);

            const valorTotalInput = document.createElement('input');
            valorTotalInput.type = 'hidden';
            valorTotalInput.name = 'valor_total';
            valorTotalInput.value = '<?php echo $total; ?>';
            form.appendChild(valorTotalInput);

            // Esconder a div #popup-content
            document.getElementById('popup-content').style.display = 'none';


            // Garantir que o formulário seja enviado corretamente
            form.submit();
        }

        function mostrarMensagemSucesso() {
            const mensagemSucesso = document.getElementById('mensagem_sucesso');

            // Esconder a div #popup-content
            document.getElementById('popup-content').style.display = 'none';

            if (mensagemSucesso) {
                mensagemSucesso.style.display = 'block';
                setTimeout(function() {
                    document.getElementById('mensagem_final').style.display = 'block';
                }, 3000);
            }
        }

        function confirmarExclusaoCartao(id_cartao) {
            if (!confirm("Tem certeza que deseja excluir este cartão?")) {
                return;
            }

            let formData = new FormData();
            formData.append("excluir_cartao", true);
            formData.append("id_cartao", id_cartao);

            fetch("popup_pix.php", { // Substitua pelo caminho correto do seu script PHP
                method: "POST",
                body: formData
            })
            .then(response => response.json()) // Espera uma resposta JSON do servidor
            .then(data => {
                if (data.sucesso) {
                    // Remover a linha da tabela
                    let row = document.querySelector(`input[value="${id_cartao}"]`);
                    if (row) {
                        row.closest("tr").remove();
                    }
                    // Verificar se ainda há cartões na tabela
                    if (document.querySelectorAll('#cartoes_cadastrados tbody tr').length === 0) {
                        let tbody = document.querySelector('#cartoes_cadastrados tbody');
                        let tr = document.createElement('tr');
                        let td = document.createElement('td');
                        td.colSpan = 3;
                        td.id = 'mensagem_sem_cartao';
                        td.textContent = 'Nenhum cartão salvo!';
                        tr.appendChild(td);
                        tbody.appendChild(tr);
                    }
                    if (document.querySelectorAll('#cartoes_debito_cadastrados tbody tr').length === 0) {
                        let tbody = document.querySelector('#cartoes_debito_cadastrados tbody');
                        let tr = document.createElement('tr');
                        let td = document.createElement('td');
                        td.colSpan = 3;
                        td.id = 'mensagem_sem_cartao_debito';
                        td.textContent = 'Nenhum cartão salvo!';
                        tr.appendChild(td);
                        tbody.appendChild(tr);
                    }
                    alert("Cartão excluído com sucesso!");
                } else {
                    alert("Erro ao excluir o cartão: " + data.erro);
                }
            })
            .catch(error => console.error("Erro ao excluir cartão:", error));
        }

        document.addEventListener('DOMContentLoaded', function() {
            verificarCartaoSelecionado();
        });
    </script>
</head>
<body>
    <div id="popup-background" class="popup-background"></div>
    <div id="popup-content" class="popup-content">
        <span class="close" onclick="window.history.back()">&times;</span>
        <h3>Pagar com PIX</h3>
        <h3>Valor da minha compra: <?php echo 'R$ ' . number_format($total, 2, ',', '.'); ?></h3>
        <p>Abra o aplicativo do seu banco e faça a leitura do QR Code abaixo para efetuar o pagamento.</p>
        <div class="label-input-container">
            <label for="valor_pix">Valor a ser pago: R$ </label>
            <input type="text" id="valor_pix" name="valor_pix" value="<?php echo number_format($total, 2, ',', '.'); ?>" oninput="formatarMoeda(this); verificarValorPix()">
        </div>
        <br>
        <img id="qr_code_pix" src="qr_code_pix.png" alt="QR Code PIX" style="display: none;">
        <br>
        <p id="link_pix" style="display: none;">Link de cópia e cola do PIX: <a href="#" id="pix_link">Copiar</a></p>
        <button type="button" class="cancelar" onclick="document.getElementById('form_voltar').submit();">Voltar</button>
        <button type="button" onclick="gerarQRCode()">Gerar QR Code</button>
        <button type="button" id="btn_continuar" onclick="continuarPagamento('PIX')" style="display: none;">Continuar</button>
    </div>

    <form id="form_voltar" action="forma_entrega.php" method="get" style="display: none;">
        <input type="hidden" name="id_cliente" value="<?php echo $id_cliente; ?>">
        <input type="hidden" name="id_parceiro" value="<?php echo $id_parceiro; ?>">
    </form>

    <div id="popup_segunda_forma" class="popup">
        <form id="segunda_forma" method="post">
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
                <select id="parcelas_cartaoCred_segunda" name="parcelas_cartaoCred_segunda" onclick="continuarPagamento()" onchange="atualizarValorParcelas()">
                    <!-- Opções serão preenchidas dinamicamente -->
                </select>
                <br>
                <h3>Selecione o cartão a ser usado</h3>
                <table id="cartoes_cadastrados">
                    <thead>
                        <tr>
                            <th>Selecionar</th>
                            <th>Número do Cartão</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($cartoes)): ?>
                            <tr>
                                <td colspan="3" id="mensagem_sem_cartao">Nenhum cartão salvo!</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($cartoes as $cartao): ?>
                                <?php if ($cartao['tipo'] === 'credito'): ?>
                                    <tr>
                                        <td><input type="checkbox" name="cartao_selecionado" value="<?php echo $cartao['id']; ?>" 
                                        data-num-cartao="<?php echo $cartao['num_cartao']; ?>" data-validade="<?php echo $cartao['validade']; ?>" 
                                        data-cod-seguranca="<?php echo $cartao['cod_seguranca']; ?>" data-nome-cartao="<?php echo $cartao['nome']; ?>" onchange="verificarCartaoSelecionado()"></td>
                                        <td>**** **** **** <?php echo substr($cartao['num_cartao'], -4); ?></td>
                                        <td><button type="button" onclick="confirmarExclusaoCartao(<?php echo $cartao['id']; ?>)">Excluir</button></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <br>
                <div id="detalhes_cartao">
                    <!-- precisara enviar esse valor para o backend -->
                    <input type="hidden" id="detalhes_produtos_dc" name="detalhes_produtos_dc" value="<?php echo $produtos; ?>">
                    <input type="hidden" id="id_parceiro_dc" name="id_parceiro_dc" value="<?php echo $id_parceiro; ?>">
                    <input type="hidden" id="valor_total_dc" name="valor_total_dc" value="<?php echo number_format($total, 2, ',', '.'); ?>">
                    <input type="hidden" id="valor_pix_entrada_dc" name="valor_pix_entrada_dc">

                    <input type="hidden" id="num_cartao_selecionado" name="num_cartao_selecionado" readonly>
                    <input type="hidden" id="nome_cartao_selecionado" name="nome_cartao_selecionado" readonly>
                    <input type="hidden" id="validade_selecionado" name="validade_selecionado_selecionado" readonly>
                    <input type="hidden" id="cod_seguranca_selecionado" name="cod_seguranca_selecionado" readonly>
                    <input type="hidden" id="input_parcela_cartao" name="input_parcela_cartao">
                </div>
                <br>
                <button type="button" class="usar-outro-cartao" onclick="abrirPopupNovoCartao()">Usar outro cartão</button>
                <br>
            </div>

            <div id="campos_cartaoDeb" style="display: none;">
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
                                        <td><input type="checkbox" name="cartao_debito_selecionado" value="<?php echo $cartao['id']; ?>" data-num-cartao="<?php echo $cartao['num_cartao']; ?>" data-validade="<?php echo $cartao['validade']; ?>" data-cod-seguranca="<?php echo $cartao['cod_seguranca']; ?>" data-nome-cartao="<?php echo $cartao['nome']; ?>" onchange="verificarCartaoSelecionado()"></td>
                                        <td>**** **** **** <?php echo substr($cartao['num_cartao'], -4); ?></td>
                                        <td><button type="button" onclick="confirmarExclusaoCartao(<?php echo $cartao['id']; ?>)">Excluir</button></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <br>
                <div id="detalhes_cartao_debito">
                    <!-- precisara enviar esse valor para o backend -->
                    <input type="hidden" id="detalhes_produtos_dc_debito" name="detalhes_produtos_dc_debito" value="<?php echo $produtos; ?>">
                    <input type="hidden" id="id_parceiro_dc_debito" name="id_parceiro_dc_debito" value="<?php echo $id_parceiro; ?>">
                    <input type="hidden" id="valor_total_dc_debito" name="valor_total_dc_debito" value="<?php echo number_format($total, 2, ',', '.'); ?>">
                    <input type="hidden" id="valor_pix_entrada_dc_debito" name="valor_pix_entrada_dc_debito">
                    <input type="hidden" id="num_cartao_selecionado_debito" name="num_cartao_selecionado_debito" readonly>
                    <input type="hidden" id="nome_cartao_selecionado_debito" name="nome_cartao_selecionado_debito" readonly>
                    <input type="hidden" id="validade_selecionado_debito" name="validade_selecionado_debito" readonly>
                    <input type="hidden" id="cod_seguranca_selecionado_debito" name="cod_seguranca_selecionado_debito" readonly>
                </div>
                <br>
                <button type="button" class="usar-outro-cartao" onclick="abrirPopupNovoCartao()">Usar outro cartão</button>
                <br>
            </div>
            <br>
            
            <button type="button" id="segunada_forma_gerarQRCode" onclick="mostrarQRCodeSegundaForma()" style="display: none;">Gerar QR Code</button>
            <button type="button" id="btn_finalizar" onclick="finalizarPagamento()" style="display: none;">Finalizar</button>
            <button type="button" class="cancelar" onclick="fecharPopup('popup_segunda_forma')">Cancelar</button>
        </form>
    </div>

    <div id="popup_novo_cartao" class="popup" style="display: <?php echo isset($mensagem_erro) ? 'block' : 'none'; ?>;">
        <div class="popup-content">
            <span class="close" onclick="fecharPopup('popup_novo_cartao')">&times;</span>
            <h3>Adicionar Novo Cartão</h3>
            <?php if (isset($mensagem_erro)): ?>
                <p style="color: red;"><?php echo $mensagem_erro; ?></p>
            <?php endif; ?>
            <form id="form_novo_cartao" method="post">
                <input type="hidden" id="detalhes_produtos" name="detalhes_produtos" value="<?php echo $produtos; ?>">
                <input type="hidden" id="id_parceiro" name="id_parceiro" value="<?php echo $id_parceiro; ?>">
                <input type="hidden" id="valor_total" name="valor_total" value="<?php echo $total; ?>">
                <input type="hidden" id="valor_pix_entrada" name="valor_pix_entrada">
                <input type="hidden" id="input_segunda_forma_pagamento" name="input_segunda_forma_pagamento">
                <input type="hidden" id="parcelas_cartaoCred_segunda_novo" name="parcelas_cartaoCred_segunda_novo">
                
                <label for="tipo_cartao">Tipo de Cartão:</label>
                <input type="text" id="tipo_cartao" name="tipo_cartao" value="<?php echo isset($_POST['tipo_cartao']) ? ucfirst($_POST['tipo_cartao']) : 'Crédito'; ?>" readonly>
                <br>
                <label for="nome_cartao">Nome descrito Cartão:</label>
                <input type="text" id="nome_cartao" name="nome_cartao" required value="<?php echo isset($nome_cartao) ? $nome_cartao : ''; ?>">
                <br>
                <label for="num_cartao">Número do Cartão:</label>
                <input type="text" id="num_cartao" name="num_cartao" required oninput="formatarNumeroCartao(this)" value="<?php echo isset($num_cartao) ? $num_cartao : ''; ?>">
                <br>
                <label for="validade">Validade:</label>
                <input type="text" id="validade" name="validade" required oninput="formatarValidadeCartao(this)" value="<?php echo isset($validade) ? $validade : ''; ?>">
                <br>
                <label for="cod_seguranca">Código de Segurança:</label>
                <input type="text" id="cod_seguranca" name="cod_seguranca" required oninput="formatarCodSeguranca(this)" value="<?php echo isset($cod_seguranca) ? $cod_seguranca : ''; ?>">
                <br>
                <button type="button" class="cancelar" onclick="fecharPopup('popup_novo_cartao')">Cancelar</button>
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

    <div id="popup_confirmacao_pagamento" class="popup">
        <div class="popup-content">
            <span class="close" onclick="cancelarConfirmacao()">&times;</span>
            <h3>Confirmação de Pagamento</h3>
            <p>Tem certeza que deseja confirmar o pagamento?</p>
            <button type="button" class="cancelar" onclick="cancelarConfirmacao()">Cancelar</button>
            <button type="button" onclick="confirmarPagamento()">Confirmar Pagamento</button>
        </div>
    </div>

    <div id="popup_confirmacao_salvar_usar" class="popup">
        <div class="popup-content">
            <span class="close" onclick="cancelarSalvarUsar()">&times;</span>
            <h3>Confirmação de Pagamento</h3>
            <p>Tem certeza que deseja salvar o cartão e finalizar o pagamento?</p>
            <button type="button" class="cancelar" onclick="cancelarSalvarUsar()">Cancelar</button>
            <button type="button" onclick="confirmarSalvarUsar()">Confirmar Pagamento</button>
        </div>
    </div>

    <div id="popup_confirmacao_usar_uma_vez" class="popup">
        <div class="popup-content">
            <span class="close" onclick="cancelarUsarUmaVez()">&times;</span>
            <h3>Confirmação de Pagamento</h3>
            <p>Tem certeza que deseja usar o cartão só dessa vez e finalizar o pagamento?</p>
            <button type="button" class="cancelar" onclick="cancelarUsarUmaVez()">Cancelar</button>
            <button type="button" onclick="confirmarUsarUmaVez()">Confirmar Pagamento</button>
        </div>
    </div>

</body>
</html>
