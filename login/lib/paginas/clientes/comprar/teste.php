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

        button {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
            background-color: #007bff;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
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
        }
    </style>
</head>
<body>
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
            <input type="text" id="data_hora" name="data_hora" accept="" hidden>

            <h1>Compra no Crediário</h1>
            <p>Valor da Compra: R$ <?php echo $valor_total_crediario_formatado; ?></p>
            <p>Entrada: R$ <?php echo $entrada_formatado; ?></p>
            <p>Restante: R$ <?php echo $restante_formatado; ?></p>
            
            <p style="display: none;"><span><?php echo 'Bandeiras aceitas: '.$bandeiras_aceitas; ?></span></p>
            <input id="tipo_entrada_crediario" name="tipo_entrada_crediario" style="display: none;" value="<?php echo $tipo_entrada_crediario; ?>" readonly>
            <input type="text" id="bandeiras_aceitas" name="bandeiras_aceitas" style="display: none;" value="<?php echo $bandeiras_aceitas; ?>" readonly>
        
            <hr style="border: 1px solid #ccc; margin: 10px 0;">

            <div id="popup-pix">
                <h3>Pagar entrada com PIX</h3>
                <p>Valor da Entrada: R$ <?php echo $entrada_formatado; ?></p>
                <p>Abra o aplicativo do seu banco e faça a leitura do QR Code abaixo para efetuar o pagamento.</p>

                <img id="qr_code_pix" src="qr_code_pix.png" alt="QR Code PIX" style="display: none;">
                <br>
                <p id="link_pix" style="display: none;">Link de cópia e cola do PIX: <a href="#" id="pix_link">Copiar</a></p>
                <button type="button" onclick="gerarQRCode()">Gerar QR Code</button>
                <button type="button" id="btn_continuar" onclick="" style="display: none;">Continuar</button>
            </div>

            <div id="popup-restante" class="popup-content" style="display: none;">
                <h3>Pagamento do Restante</h3>
                <p>Valor Restante: R$ <?php echo $restante_formatado; ?></p>
                <label for="parcelas">Selecione o número de parcelas:</label>
                <select id="parcelas" name="parcelas">
                    <?php for ($i = 1; $i <= $maior_parcelas; $i++): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?>x <?php echo $i === 1 ? '(sem juros)' : ''; ?></option>
                    <?php endfor; ?>
                </select>

                <p id="valor_parcela"></p>
                <input type="text" id="input_parcela" style="display: none;">

                <button type="button" id="btn_voltar" onclick="voltarParaPix()">Voltar</button>
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
        document.addEventListener("DOMContentLoaded", function () {
            const tipoEntrada = "<?php echo $tipo_entrada_crediario; ?>";
            const popupPix = document.getElementById("popup-pix");
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
            }

            // Função para gerar o QR Code
            window.gerarQRCode = function () {
                qrCodePix.style.display = "block";
                linkPix.style.display = "block";
                btnContinuar.style.display = "inline-block";
            };

            // Função para abrir o popup do restante sobre a página
            btnContinuar.onclick = function () {
                //popupPix.style.display = "none";
                popupRestante.style.display = "block";
                popupRestante.style.position = "fixed";
                popupRestante.style.top = "50%";
                popupRestante.style.left = "50%";
                popupRestante.style.transform = "translate(-50%, -50%)";
                popupRestante.style.zIndex = "1000";
                popupRestante.style.backgroundColor = "#fff";
                popupRestante.style.padding = "20px";
                popupRestante.style.boxShadow = "0 4px 8px rgba(0, 0, 0, 0.2)";

                // Obter o horário local
                obterHorarioLocal();
            };

            // Função para voltar ao popup PIX
            window.voltarParaPix = function () {
                popupRestante.style.display = "none";
                popupPix.style.display = "block";
            };

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
            popupSucesso.style.boxShadow = "0 4px 8px rgba(0, 0, 0, 0.2)";
            popupSucesso.innerHTML = `
                <h3>Compra Finalizada</h3>
                <p>Sua compra foi finalizada com sucesso!</p>
                <p>Você será redirecionado em <span id="contador">5</span> segundos...</p>
            `;
            document.body.appendChild(popupSucesso);

            // Função para enviar os dados via JavaScript em formato JSON
            document.getElementById("btn_finalizar").addEventListener("click", function () {
                const dataFormatada = document.getElementById('data_hora').value;

                // Calcular o valor total da compra no cliente (se necessário)
                const valorTotalCrediario = parseFloat(document.getElementById("valor_total_crediario").value) || 0;
                const valorFrete = parseFloat(document.getElementById("valor_frete").value) || 0;
                const valorTotalSemCrediario = parseFloat(document.getElementById("valor_total_sem_crediario").value) || 0;
                const totalCompra = valorTotalCrediario + valorFrete + valorTotalSemCrediario;

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
                    restante: document.getElementById("restante").value,
                    tipo_entrada_crediario: document.getElementById("tipo_entrada_crediario").value,
                    bandeiras_aceitas: document.getElementById("bandeiras_aceitas").value,
                    comentario: document.getElementById("comentario").value,
                    parcelas: parcelasSelect.value,
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
                        popupSucesso.style.display = "block"; // Mostrar o popup de sucesso
                        let contador = 5;
                        const intervalo = setInterval(() => {
                            contador--;
                            document.getElementById("contador").textContent = contador;
                            if (contador === 0) {
                                clearInterval(intervalo);
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