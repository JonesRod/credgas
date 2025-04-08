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

    // Verificação e sanitização dos dados recebidos
    $tipo_compra = 'crediario';
    $id_cliente = isset($_POST['id_cliente']) ? intval($_POST['id_cliente']) : 0;
    $id_parceiro = isset($_POST['id_parceiro']) ? intval($_POST['id_parceiro']) : 0;
    $valor_frete = isset($_POST['valor_frete']) ? floatval(str_replace(',', '.', $_POST['valor_frete'])) : 0.0;
    $valor_total_sem_crediario = isset($_POST['valor_total_sem_crediario']) ? floatval(str_replace(',', '.', $_POST['valor_total_sem_crediario'])) : 0.0;
    $valor_total_crediario = isset($_POST['valor_total_crediario']) ? floatval(str_replace(',', '.', $_POST['valor_total_crediario'])) : 0.0;
    $detalhes_produtos = isset($_POST['detalhes_produtos']) ? $_POST['detalhes_produtos'] : '';
    $entrega = isset($_POST['entrega']) ? $_POST['entrega'] : '';
    $rua = isset($_POST['rua']) ? $_POST['rua'] : '';
    $bairro = isset($_POST['bairro']) ? $_POST['bairro'] : '';
    $numero = isset($_POST['numero']) ? $_POST['numero'] : '';
    $contato = isset($_POST['contato']) ? $_POST['contato'] : '';
    $entrada = isset($_POST['entrada']) ? floatval(str_replace(',', '.', $_POST['entrada'])) : 0.0;
    $restante = isset($_POST['restante']) ? floatval(str_replace(',', '.', $_POST['restante'])) : 0.0;
    $tipo_entrada_crediario = isset($_POST['tipo_entrada_crediario']) ? $_POST['tipo_entrada_crediario'] : '';
    $bandeiras_aceitas = isset($_POST['bandeiras_aceita']) ? $_POST['bandeiras_aceita'] : '';
    $comentario = isset($_POST['comentario']) ? $_POST['comentario'] : '';
    $maior_parcelas = isset($_POST['maiorParcelas']) ? intval($_POST['maiorParcelas']) : 1;

    // Formatação para moeda com ponto de milhar e vírgula nos centavos
    $valor_total_crediario_formatado = number_format($valor_total_crediario, 2, ',', '.');
    $entrada_formatado = number_format($entrada, 2, ',', '.');
    $restante_formatado = number_format($restante, 2, ',', '.');

    $bd_cliente = $mysqli->query("SELECT senha_login FROM meus_clientes WHERE id = $id_session") or die($mysqli->error);
    $dados = $bd_cliente->fetch_assoc();
    $senha_compra = $dados['senha_login'];
    
    //echo $senha_compra;

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
   
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compra no Crediário</title>
    <style>
        /* Estilos gerais */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .form-container {
            max-width: 800px;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        #popup-pix {
            text-align: center
        }

        p {
            margin: 10px 0;
            color: #555;
        }

        /* Botões gerais */
        button {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        /* Botões de ação positiva (Continuar, Finalizar) */
        button#btn_continuar,
        button#btn_continuar_pg,
        button#btn_finalizar {
            background-color: #28a745; /* Verde */
            color: #fff;
        }

        button#btn_continuar:hover,
        button#btn_continuar_pg:hover,
        button#btn_finalizar:hover {
            background-color: #218838; /* Verde mais escuro */
        }

        /* Botão de ação negativa (Cancelar) */
        button#btn_cancelar {
            background-color: #dc3545; /* Vermelho */
            color: #fff;
        }

        button#btn_cancelar:hover {
            background-color: #c82333; /* Vermelho mais escuro */
        }

        /* Botões de ação neutra (Voltar, Gerar QR Code) */
        button#btn_voltar,
        button[onclick="gerarQRCode()"],
        button[type="submit"] {
            background-color: #007bff; /* Azul */
            color: #fff;
        }

        button#btn_voltar:hover,
        button[onclick="gerarQRCode()"]:hover,
        button[type="submit"]:hover {
            background-color: #0056b3; /* Azul mais escuro */
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
        .popup-content-cartoes {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 50%;
            max-width: 90%;
            border: 1px solid #888;
            text-align: center;
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

        .mensagem-sucesso .content-content {
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 5px;
            text-align: center;
            background-color: #fff;
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
                padding: 8px 16px;
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
                padding: 6px 12px;
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
        }

    </style>
</head>
<body>
    <div id="popup-background" class="popup-background"></div>
    <div class="form-container">
        <form id="form_crediario" method="POST" action="finalizar_compra_crediario.php">
            <input type="text" id="senha_compra" name="senha_compra" value="<?php echo $senha_compra; ?>" hidden>
            <input type="text" id="tipo_compra" name="tipo_compra" value="<?php echo $tipo_compra; ?>" hidden>
            <input type="text" id="id_cliente" value="<?php echo $id_cliente; ?>" hidden>
            <input type="text" id="id_parceiro" value="<?php echo $id_parceiro; ?>" hidden>
            <input type="text" id="valor_frete" value="<?php echo $valor_frete; ?>" hidden>
            <input type="text" id="valor_total_sem_crediario" value="<?php echo $valor_total_sem_crediario; ?>" hidden>
            <input type="text" id="valor_total_crediario" value="<?php echo $valor_total_crediario; ?>" hidden>
            <input type="text" id="detalhes_produtos" value="<?php echo $detalhes_produtos; ?>" hidden>
            <input type="text" id="entrega" value="<?php echo $entrega; ?>" hidden>
            <input type="text" id="rua" value="<?php echo $rua; ?>" hidden>
            <input type="text" id="bairro" value="<?php echo $bairro; ?>" hidden>
            <input type="text" id="numero" value="<?php echo $numero; ?>" hidden>
            <input type="text" id="contato" value="<?php echo $contato; ?>" hidden>
            <input type="text" id="entrada" value="<?php echo $entrada; ?>" hidden>
            <input type="text" id="restante" value="<?php echo $restante; ?>" hidden>
            <input type="text" id="tipo_entrada_crediario" value="<?php echo $tipo_entrada_crediario; ?>" hidden>
            <input type="text" id="bandeiras_aceitas" value="<?php echo $bandeiras_aceitas; ?>" hidden>
            <input type="text" id="comentario" value="<?php echo $comentario; ?>" hidden>
            <input type="text" id="tipo_compra" value="<?php echo $tipo_compra; ?>" hidden>
            <input type="text" id="data_hora" name="data_hora" hidden>

            <h1>Compra no Crediário</h1>
            <p>Valor da Compra: R$ <?php echo $valor_total_crediario_formatado; ?></p>
            <p>Entrada: R$ <?php echo $entrada_formatado; ?></p>
            <p>Restante: R$ <?php echo $restante_formatado; ?></p>
            
            <p style="display: none;"><span><?php echo 'Bandeiras aceitas: '.$bandeiras_aceitas; ?></span></p>
            <input id="tipo_entrada_crediario" name="tipo_entrada_crediario" style="display: none;" value="<?php echo $tipo_entrada_crediario; ?>" readonly>
            <input type="text" id="bandeiras_aceitas" name="bandeiras_aceitas" style="display: none;" value="<?php echo $bandeiras_aceitas; ?>" readonly>
        
            <hr style="border: 1px solid #ccc; margin: 10px 0;">

            <div id="popup-pix" style="<?php if ($tipo_entrada_crediario == 1) { echo 'display: block;'; } else { echo 'display: none;'; } ?>">
                <h3>Pagar entrada com PIX</h3>
                <p>Valor da Entrada: R$ <?php echo $entrada_formatado; ?></p>
                <p>Abra o aplicativo do seu banco e faça a leitura do QR Code abaixo para efetuar o pagamento.</p>

                <img id="qr_code_pix" src="qr_code_pix.png" alt="QR Code PIX" style="display: none;">
                <br>
                <p id="link_pix" style="display: none;">Link de cópia e cola do PIX: <a href="#" id="pix_link">Copiar</a></p>
                <button type="button" onclick="gerarQRCode()">Gerar QR Code</button>
                <button type="button" id="btn_continuar" onclick="abrirPopupRestante()" style="display: none;">Continuar</button>
            </div>

            <div id="popup_cartaoCred" style="<?php if ($tipo_entrada_crediario == 2) { echo 'display: block;'; } else { echo 'display: none;'; } ?>">
                <h3>Selecione o cartão de Crédito a ser usado</h3>
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
                                <?php if ($cartao['tipo'] === 'credito'): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="cartao_debito_selecionado" value="<?php echo $cartao['id']; ?>" 
                                            data-num-cartao="<?php echo $cartao['num_cartao']; ?>" 
                                            data-validade="<?php echo $cartao['validade']; ?>" 
                                            data-cod-seguranca="<?php echo $cartao['cod_seguranca']; ?>" 
                                            data-nome-cartao="<?php echo $cartao['nome']; ?>" 
                                            onchange="verificarCartaoSelecionado()">
                                        </td>
                                        <td>**** **** **** <?php echo substr($cartao['num_cartao'], -4); ?></td>
                                        <td>
                                            <button type="button" onclick="confirmarExclusaoCartao(<?php echo $cartao['id']; ?>)">Excluir</button>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div>
                    <label for="parcelas_cartaoCred_entrada">Quantidade de parcelas:</label>
                    <input id="parcelas_cartaoCred_entrada" name="parcelas_cartaoCred_entrada" type="number" min="1"  value="1" onchange="calcularValorParcela()">
                </div>
                <div>
                    <p>Valor da Parcela: R$ <span id="valor_parcela_cartaoCred_entrada"><?php echo $entrada_formatado; ?></span></p>
                </div>
                <button type="button" class="usar-outro-cartao" onclick="abrirPopupNovoCartao()">Usar outro cartão</button>
                <button type="button" id="btn_continuar_cartaoCred" onclick="abrirPopupRestante()" style="display: none;">Continuar</button>
                <br>
            </div>

            <div id="popup_cartaoDeb" style="<?php if ($tipo_entrada_crediario == 3) { echo 'display: block;'; } else { echo 'display: none;'; } ?>">
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
                                            <input type="checkbox" name="cartao_credito_selecionado" value="<?php echo $cartao['id']; ?>" 
                                            data-num-cartao="<?php echo $cartao['num_cartao']; ?>" 
                                            data-validade="<?php echo $cartao['validade']; ?>" 
                                            data-cod-seguranca="<?php echo $cartao['cod_seguranca']; ?>" 
                                            data-nome-cartao="<?php echo $cartao['nome']; ?>"
                                            data-valor-entreda="<?php echo $entrada; ?>"
                                            data-valor-parcela=""
                                            onchange="verificarCartaoSelecionado()">
                                        </td>
                                        <td>**** **** **** <?php echo substr($cartao['num_cartao'], -4); ?></td>
                                        <td>
                                            <button type="button" onclick="confirmarExclusaoCartao(<?php echo $cartao['id']; ?>)">Excluir</button>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <br>

                <button type="button" class="usar-outro-cartao-debito" onclick="abrirPopupNovoCartao()">Usar outro cartão</button>
                <button type="button" id="btn_continuar_cartaoDeb" onclick="abrirPopupRestante()" style="display: none;">Continuar</button>
                <br>
            </div>
            
            <div id="detalhes_cartao" style="display: none;">
                <input type="text" id="num_cartao_selecionado" name="num_cartao_selecionado" readonly>
                <input type="text" id="nome_cartao_selecionado" name="nome_cartao_selecionado" readonly>
                <input type="text" id="validade_selecionado" name="validade_selecionado_selecionado" readonly>
                <input type="text" id="cod_seguranca_selecionado" name="cod_seguranca_selecionado" readonly>
                <input type="text" id="valor_parcela_cartao_selecionado" name="valor_parcela_cartao_selecionado" readonly>
                <input type="text" id="parcelas_cartaoCred_entrada_selecionado" name="parcelas_cartaoCred_entrada_selecionado" readonly>
                <input type="text" id="salvar_cartao" name="salvar_cartao" readonly>
            </div>
            
            <div id="popup_novo_cartao" class="popup-content-cartoes" style="display: none;">
                <span class="close" onclick="fecharPopup('popup_novo_cartao')">&times;</span>
                <h3>Adicionar Novo Cartão</h3>
                <?php if (isset($mensagem_erro)): ?>
                    <p style="color: red;"><?php echo $mensagem_erro; ?></p>
                <?php endif; ?>
                <h3>Valor da entrada: R$ <?php echo $entrada_formatado; ?></h3>
                <p>Bandeiras aceitas: <?php echo $bandeiras_aceitas; ?>.</p>

                <div id="dados_cartao">
                    <div>
                        <label for="tipo_cartao">Tipo de Cartão:</label>
                        <input type="text" id="tipo_cartao" name="tipo_cartao" value="<?php if ($tipo_entrada_crediario == 2) { echo 'Crédito'; } else { echo 'Débito'; } ?>" readonly>
                    </div>
                    <div>
                        <label for="nome_cartao">Nome descrito Cartão:</label>
                        <input type="text" id="nome_cartao" name="nome_cartao" required value="<?php echo isset($nome_cartao) ? $nome_cartao : ''; ?>">
                    </div>
                    <div>
                        <label for="num_cartao">Número do Cartão:</label>
                        <input type="text" id="num_cartao" name="num_cartao" required oninput="formatarNumeroCartao(this)" value="<?php echo isset($num_cartao) ? $num_cartao : ''; ?>">
                    </div>
                    <div>
                        <label for="validade">Validade:</label>
                        <input type="text" id="validade" name="validade" required oninput="formatarValidadeCartao(this)" value="<?php echo isset($validade) ? $validade : ''; ?>">
                    </div>
                    <div>
                        <label for="cod_seguranca">Código de Segurança:</label>
                        <input type="text" id="cod_seguranca" name="cod_seguranca" required oninput="formatarCodSeguranca(this)" value="<?php echo isset($cod_seguranca) ? $cod_seguranca : ''; ?>">
                    </div>
                    <div style="<?php if ($tipo_entrada_crediario == 2) { echo 'display: block;'; } else { echo 'display: none;'; } ?>">
                        <label for="parcelas_cartaoCred_entrada_novo">Quantidade de parcelas:</label>
                        <input id="parcelas_cartaoCred_entrada_novo" name="parcelas_cartaoCred_entrada_novo" type="number" min="1"  value="1" onchange="calcularValorParcelaNovo()">
                    </div>
                    <div style="<?php if ($tipo_entrada_crediario == 2) { echo 'display: block;'; } else { echo 'display: none;'; } ?>">
                        <p>Valor da Parcela: R$ <span id="valor_parcela_cartaoCred_entrada_novo"><?php echo $entrada_formatado; ?></span></p>
                    </div>
                </div>
                <div class="popup-buttons">
                    <button type="button" class="cancelar" onclick="fecharPopup('popup_novo_cartao')">Cancelar</button>
                    <button type="button" onclick="adicionarNovoCartao(true)">Salvar e Usar</button>
                    <button type="button" onclick="adicionarNovoCartao(false)">Usar só dessa vez</button>
                </div>
            </div>

            <div id="popup-restante" class="popup-content" style="display: none;">
                <h3>Pagamento do Restante</h3>
                <p>Valor Restante: R$ <?php echo $restante_formatado; ?></p>
                <label for="parcelas">Selecione o número de parcelas:</label>
                <select id="parcelas" name="parcelas">
                    <?php 
                    if ($maior_parcelas > 0): 
                        for ($i = 1; $i <= $maior_parcelas; $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?>x <?php echo $i === 1 ? '(sem juros)' : ''; ?></option>
                        <?php endfor; 
                    else: ?>
                        <option value="1">1x (sem juros)</option>
                    <?php endif; ?>
                </select>

                <p id="valor_parcela"></p>
                <input type="text" id="input_parcela" style="display: none;">

                <button type="button" id="btn_voltar" onclick="voltarParaEntrada()">Voltar</button>
                <button type="button" id="btn_continuar_pg">Continuar</button>
            </div>

            <div id="popup-senha" class="popup-content" style="display: none;">
                <p>Senha do Cliente: 
                    <input type="password" id="senha_cliente" name="senha_cliente" >
                    <span id="toggle_senha" style="cursor: pointer;">👁️</span>
                </p>
                <p id="msg_erro" style="color: red; display: none;"></p>
                <p id="msg_sucesso" style="color: green; display: none;"></p>
                <p>Digite a senha do cliente para continuar com o pagamento.</p>
                <p>Após a confirmação, o pedido será finalizado e o restante do valor será cobrado.</p>
                <button type="button" id="btn_cancelar">Cancelar</button>
                <button type="button" id="btn_finalizar">Finalizar</button>
            </div>    
        </form>

        <form id="form-voltar" action="forma_entrega.php" method="GET">
            <input type="hidden" name="id_cliente" value="<?php echo $id_cliente; ?>">
            <input type="hidden" name="id_parceiro" value="<?php echo $id_parceiro; ?>">
            <input type="hidden" name="valor_total" value="<?php echo $valor_total_sem_crediario; ?>">
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

        function abrirPopupRestante() {
            const popupRestante = document.getElementById("popup-restante");
            document.getElementById('popup_novo_cartao').style.display = 'none';
            //document.getElementById('popup-background').style.display = 'block';
            document.getElementById('popup-background').style.display = "block";

            popupRestante.style.display = "block";
            popupRestante.style.position = "fixed";
            popupRestante.style.top = "50%";
            popupRestante.style.left = "50%";
            popupRestante.style.transform = "translate(-50%, -50%)";
            popupRestante.style.zIndex = "1000";
            popupRestante.style.backgroundColor = "#fff";
            popupRestante.style.padding = "20px";
            popupRestante.style.boxShadow = "0 4px 8px rgba(0, 0, 0, 0.2)";
            obterHorarioLocal();
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
                popupNovoCartao.style.zIndex = '1000'; // Garantir que o popup esteja acima de outros elementos

                document.getElementById('popup-background').style.display = 'block';

                //console.log('Popup de novo cartão aberto.');
            } else {
                //console.error('Elemento com ID "popup_novo_cartao" não encontrado.');
            }
        }

        function fecharPopup(popupId) {
            document.getElementById(popupId).style.display = 'none';
            document.getElementById('popup-background').style.display = 'none';
            document.getElementById('salvar_cartao').value = '0';
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

        function verificarCartaoSelecionado() {
            const checkboxes = document.querySelectorAll('input[name="cartao_credito_selecionado"], input[name="cartao_debito_selecionado"]');
            
            checkboxes.forEach((checkbox) => {
                checkbox.addEventListener('change', function () {
                    if (this.checked) {
                        // Desmarcar todos os outros cartões
                        checkboxes.forEach((cb) => {
                            if (cb !== this) cb.checked = false;
                            limparDetalhesCartao();
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
            const numCartao = cartao.dataset.numCartao;
            const validade = cartao.dataset.validade;
            const codSeguranca = cartao.dataset.codSeguranca;
            const nomeCartao = cartao.dataset.nomeCartao;

            document.getElementById('num_cartao_selecionado').value = numCartao;
            document.getElementById('validade_selecionado').value = validade;
            document.getElementById('cod_seguranca_selecionado').value = codSeguranca;
            document.getElementById('nome_cartao_selecionado').value = nomeCartao;

            // Atualizar o valor da parcela, se aplicável
            const parcelasInput = document.getElementById('parcelas_cartaoCred_entrada');
            if (parcelasInput) {
                const parcelaSelecionada = parcelasInput.value || 1;
                document.getElementById('parcelas_cartaoCred_entrada_selecionado').value = parcelaSelecionada;
            }
            const valorParcela = document.getElementById('valor_parcela_cartaoCred_entrada');
            if (valorParcela) {
                const valor = parseFloat(valorParcela.textContent.replace('R$ ', '').replace('.', '').replace(',', '.'));
                document.getElementById('valor_parcela_cartao_selecionado').value = valor;
            }
            // Exibir o botão de continuar
            document.getElementById('btn_continuar_cartaoCred').style.display = 'block';
            document.getElementById('btn_continuar_cartaoDeb').style.display = 'block'; 
        }

        function limparDetalhesCartao() {
            //console.log('Limpar detalhes do cartão');
            document.getElementById('num_cartao_selecionado').value = '';
            document.getElementById('validade_selecionado').value = '';
            document.getElementById('cod_seguranca_selecionado').value = '';
            document.getElementById('nome_cartao_selecionado').value = '';
            document.getElementById('parcelas_cartaoCred_entrada_selecionado').value = '1'; // Resetar para 1 parcela
            document.getElementById('valor_parcela_cartao_selecionado').value = '';
            // Esconder o botão de continuar
            document.getElementById('btn_continuar_cartaoCred').style.display = 'none';
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

            // Verificar se o nome do cartão não está vazio
            const nomeCartao = document.getElementById('nome_cartao').value;
            if ((nomeCartao.trim() === '') || nomeCartao.length < 5) {
                alert('O nome do cartão precisa ser preenchido corretamente.');
                return false;
            }

            return true;
        }

        function adicionarNovoCartao(salvar) {
            if (validarCartao()) {
            
                // Corrigido: usar o valor passado no parâmetro
                if (salvar === true) {
                    document.getElementById('salvar_cartao').value = '1';
                } else {
                    document.getElementById('salvar_cartao').value = '0';
                }

                // Preencher os detalhes do cartão selecionado
                document.getElementById('num_cartao_selecionado').value = document.getElementById('num_cartao').value.replace(/\s/g, '');
                document.getElementById('validade_selecionado').value = document.getElementById('validade').value;
                document.getElementById('cod_seguranca_selecionado').value = document.getElementById('cod_seguranca').value;
                document.getElementById('nome_cartao_selecionado').value = document.getElementById('nome_cartao').value;
                document.getElementById('parcelas_cartaoCred_entrada_selecionado').value = document.getElementById('parcelas_cartaoCred_entrada_novo').value;
                document.getElementById('valor_parcela_cartao_selecionado').value = parseFloat(document.getElementById('valor_parcela_cartaoCred_entrada_novo').textContent.replace('.', '').replace(',', '.'));

                //console.log('Valor do cartão selecionado:', document.getElementById('num_cartao_selecionado').value);
                //document.getElementById('popup_novo_cartao').style.display = 'block';
                //document.getElementById('popup-background').style.display = 'block';

                abrirPopupRestante();
            }

        }

        // Função para voltar ao popup entrada
        function voltarParaEntrada() {
            const tipo_entrada_crediario = document.getElementById('tipo_entrada_crediario').value;
            const popupRestante = document.getElementById("popup-restante");
            const popup_background = document.getElementById('popup-background');
            const checkboxes = document.querySelectorAll('input[name="cartao_credito_selecionado"], input[name="cartao_debito_selecionado"]');

            // Desmarcar todos os outros cartões
            checkboxes.forEach((cb) => {
                cb.checked = false;
                //console.log('Desmarcando cartão:', cb);
                limparDetalhesCartao();
            });

            popup_background.style.display = "none";
            popupRestante.style.display = "none";

            if (tipo_entrada_crediario == 1) {
                document.getElementById('popup-pix').style.display = "block";
            } else if (tipo_entrada_crediario == 2) {
                document.getElementById('popup_cartaoCred').style.display = "block";
            } else {
                document.getElementById('popup_cartaoDeb').style.display = "block";
            }
            limparDetalhesCartao();
        };

        function cancelarSalvarUsar() {
            document.getElementById('popup_confirmacao_salvar_usar').style.display = 'none';
            document.getElementById('popup-background').style.display = 'none';
        }

        function calcularValorParcela() {
            const valorTotal = parseFloat(document.getElementById('entrada').value) || 0;
            const numParcelas = parseInt(document.getElementById('parcelas_cartaoCred_entrada').value) || 1;

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

            const valorParcelaFormatado = valorParcela.toFixed(2).replace('.', ',');
            document.getElementById('valor_parcela_cartaoCred_entrada').textContent = valorParcelaFormatado;
            document.getElementById('valor_parcela_cartao_selecionado').value = parseFloat(valorParcelaFormatado.replace('R$ ', '').replace('.', '').replace(',', '.')); // Atualiza o valor da parcela no campo oculto
            document.getElementById('parcelas_cartaoCred_entrada_selecionado').value = numParcelas;
        }

        function calcularValorParcelaNovo() {valor_parcela_cartaoCred_entrada_novo
            const valorTotal = parseFloat(document.getElementById('entrada').value) || 0;
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

            const valorParcelaFormatado = valorParcela.toFixed(2).replace('.', ',');
            document.getElementById('valor_parcela_cartaoCred_entrada_novo').textContent = valorParcelaFormatado;
        }

        document.addEventListener("DOMContentLoaded", function () {
            const tipoEntrada = "<?php echo $tipo_entrada_crediario; ?>";
            const popupPix = document.getElementById("popup-pix");
            const popupCartaoDeb = document.getElementById("popup_cartaoDeb");
            const popupCartaoCred = document.getElementById("popup_cartaoCred");
            const popupRestante = document.getElementById("popup-restante");
            const popupSenha = document.getElementById("popup-senha");
            const qrCodePix = document.getElementById("qr_code_pix");
            const linkPix = document.getElementById("link_pix");
            const btnContinuar = document.getElementById("btn_continuar");
            const btnContinuarPg = document.getElementById("btn_continuar_pg");
            const btnCancelar = document.getElementById("btn_cancelar");
            const senhaInput = document.getElementById("senha_cliente");
            const toggleSenha = document.getElementById("toggle_senha");
            const parcelasSelect = document.getElementById("parcelas");
            const btnVoltar = document.getElementById("btn_voltar");
            
            let senhaVisivelTimeout;

            // Mostrar o popup PIX se tipo_entrada_crediario for 1
            if (tipoEntrada === "1") {
                popupPix.style.display = "block";
            } else if (tipoEntrada === "2") {
                // Mostrar o popup de cartão de débito
                popupCartaoCred.style.display = "block";
            } else {
                // Mostrar o popup de cartão de crédito
                popupCartaoDeb.style.display = "block";
            }

            // Função para gerar o QR Code
            window.gerarQRCode = function () {
                qrCodePix.style.display = "block";
                linkPix.style.display = "block";
                btnContinuar.style.display = "inline-block";
            };

            // Executar só quando o botão for clicado:
            btnContinuar.onclick = abrirPopupRestante;

            // Função para abrir o popup de senha ao clicar em "Continuar"
            btnContinuarPg.onclick = function () {
                popupRestante.style.display = "none";
                popupSenha.style.display = "block";
                popupSenha.style.position = "fixed";
                popupSenha.style.top = "50%";
                popupSenha.style.left = "50%";
                popupSenha.style.transform = "translate(-50%, -50%)";
                popupSenha.style.zIndex = "1000";
                popupSenha.style.backgroundColor = "#fff";
                popupSenha.style.padding = "20px";
                popupSenha.style.boxShadow = "0 4px 8px rgba(0, 0, 0, 0.2)";
            };

            // Função para fechar o popup de senha e reabrir o popup do restante
            btnCancelar.addEventListener("click", function () {
                popupSenha.style.display = "none";
                popupRestante.style.display = "block";
                popupRestante.style.position = "fixed";
                popupRestante.style.top = "50%";
                popupRestante.style.left = "50%";
                popupRestante.style.transform = "translate(-50%, -50%)";
                popupRestante.style.zIndex = "1000";
                popupRestante.style.backgroundColor = "#fff";
                popupRestante.style.padding = "20px";
                popupRestante.style.boxShadow = "0 4px 8px rgba(0, 0, 0, 0.2)";
            });

            // Função para alternar visibilidade da senha
            toggleSenha.addEventListener("click", function () {
                if (senhaInput.type === "password") {
                    senhaInput.type = "text";
                    toggleSenha.textContent = "🙈"; // Ícone para ocultar
                    // Ocultar automaticamente após 5 segundos
                    clearTimeout(senhaVisivelTimeout);
                    senhaVisivelTimeout = setTimeout(() => {
                        senhaInput.type = "password";
                        toggleSenha.textContent = "👁️"; // Ícone para visualizar
                    }, 5000);
                } else {
                    senhaInput.type = "password";
                    toggleSenha.textContent = "👁️"; // Ícone para visualizar
                    clearTimeout(senhaVisivelTimeout);
                }
            });

            // Função para calcular parcelas
            function calcularParcelas() {
                const restante = <?php echo $restante; ?>;
                const parcelas = parcelasSelect.value;
                const taxa = 0.0299; // 2,99% ao mês
                let valorParcela;

                if (parcelas == 1) {
                    valorParcela = restante;
                } else {
                    valorParcela = restante * Math.pow(1 + taxa, parcelas) / parcelas;
                }

                // Formatar o valor com ponto de milhar e vírgula para centavos
                const valorFormatado = valorParcela.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                document.getElementById("valor_parcela").innerText = 
                    `Valor de cada parcela: R$ ${valorFormatado}`;
                document.getElementById("input_parcela").value = valorParcela.toFixed(2);
            }

            // Calcular parcelas ao carregar a página
            calcularParcelas();

            // Recalcular parcelas ao selecionar uma nova parcela
            parcelasSelect.addEventListener("change", calcularParcelas);

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

            // Função para enviar os dados via JavaScript em formato JSON
            document.getElementById("btn_finalizar").addEventListener("click", function () {
                const dataFormatada = document.getElementById('data_hora').value;

                // Calcular o valor total da compra no cliente (se necessário)
                const valorTotalCrediario = parseFloat(document.getElementById("valor_total_crediario").value) || 0;
                const valorFrete = parseFloat(document.getElementById("valor_frete").value) || 0;
                const valorTotalSemCrediario = parseFloat(document.getElementById("valor_total_sem_crediario").value) || 0;
                const totalCompra = valorTotalSemCrediario;

                console.log("Valor total da compra:", totalCompra);
                //return;
                if (totalCompra === 0) {
                    console.error("Erro: O valor total da compra não foi calculado corretamente.");
                    document.getElementById("msg_erro").textContent = "Erro ao calcular o valor total da compra.";
                    document.getElementById("msg_erro").style.display = "block";
                    return;
                }

                const formData = {
                    senha_compra: document.getElementById("senha_compra").value,
                    tipo_compra: document.getElementById("tipo_compra").value,
                    id_cliente: document.getElementById("id_cliente").value,
                    id_parceiro: document.getElementById("id_parceiro").value,
                    valor_frete: valorFrete,
                    valor_total_sem_crediario: valorTotalSemCrediario,
                    valor_total_crediario: valorTotalCrediario,
                    total_compra: totalCompra, // Enviar o valor total calculado
                    detalhes_produtos: document.getElementById("detalhes_produtos").value,
                    entrega: document.getElementById("entrega").value,
                    rua: document.getElementById("rua").value,
                    bairro: document.getElementById("bairro").value,
                    numero: document.getElementById("numero").value,
                    contato: document.getElementById("contato").value,
                    entrada: document.getElementById("entrada").value,

                    num_cartao: document.getElementById("num_cartao_selecionado").value,
                    nome_cartao: document.getElementById("nome_cartao_selecionado").value,
                    validade: document.getElementById("validade_selecionado").value,
                    cod_seguranca: document.getElementById("cod_seguranca_selecionado").value,
                    qt_parcelas_entrada: document.getElementById("parcelas_cartaoCred_entrada_selecionado").value,
                    valorParcela_entrada: document.getElementById("valor_parcela_cartao_selecionado").value,
                    salvar_cartao: document.getElementById("salvar_cartao").value,

                    restante: document.getElementById("restante").value,
                    tipo_entrada_crediario: document.getElementById("tipo_entrada_crediario").value,
                    bandeiras_aceitas: document.getElementById("bandeiras_aceitas").value,
                    comentario: document.getElementById("comentario").value,
                    parcelas: document.getElementById("parcelas").value, // Corrigido para usar o ID correto
                    valor_parcela: document.getElementById("input_parcela").value,
                    senha_cliente: document.getElementById("senha_cliente").value,
                    data_hora: dataFormatada
                };

                fetch("finalizar_compra_crediario.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const popupSenha = document.getElementById("popup-senha");
                        popupSenha.style.display = "none"; // Ocultar o popup de senha
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
                    }
                })
                .catch(error => {
                    console.error("Erro:", error);
                    document.getElementById("msg_erro").textContent = "Erro ao processar a solicitação.";
                    document.getElementById("msg_erro").style.display = "block";
                });
            });
        });
    </script>
</html>