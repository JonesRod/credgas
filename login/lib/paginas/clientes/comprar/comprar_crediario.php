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

    // Formatação para moeda
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
    <meta name="viewport" width="device-width, initial-scale=1.0">
    <title>Compra no Crediário</title>
</head>
<body>


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

        <h1>Compra no Crediário</h1>
        <p>Valor da Compra: R$ <?php echo $valor_total_crediario_formatado; ?></p>
        <p>Entrada: R$ <?php echo $entrada_formatado; ?></p>
        <p>Restante: R$ <?php echo $restante_formatado; ?></p>
        <div>
            <p style="display: none;"><span><?php echo 'Bandeiras aceitas: '.$bandeiras_aceitas; ?></span></p>
            <input id="tipo_entrada_crediario" name="tipo_entrada_crediario" style="display: none;" value="<?php echo $tipo_entrada_crediario; ?>" readonly>
            <input type="text" id="bandeiras_aceitas" name="bandeiras_aceitas" style="display: none;" value="<?php echo $bandeiras_aceitas; ?>" readonly>
        
            <div id="popup-pix" class="popup-content" style="display: none;">
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
                <select id="parcelas" name="parcelas" onchange="calcularParcelas()">
                    <?php for ($i = 1; $i <= $maior_parcelas; $i++): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?>x <?php echo $i === 1 ? '(sem juros)' : ''; ?></option>
                    <?php endfor; ?>
                </select>
                <p id="valor_parcela"></p>

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
                <button type="submit" id="btn_finalizar">Finalizar</button>
            </div>
        </div>  
    </form>

    <form action="forma_entrega.php" method="GET">
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
                popupPix.style.display = "none";
                popupRestante.style.display = "block";
                popupRestante.style.position = "fixed";
                popupRestante.style.top = "50%";
                popupRestante.style.left = "50%";
                popupRestante.style.transform = "translate(-50%, -50%)";
                popupRestante.style.zIndex = "1000";
                popupRestante.style.backgroundColor = "#fff";
                popupRestante.style.padding = "20px";
                popupRestante.style.boxShadow = "0 4px 8px rgba(0, 0, 0, 0.2)";
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

            // Função para fechar o popup de senha ao clicar em "Cancelar"
            btnCancelar.addEventListener("click", function () {
                popupSenha.style.display = "none";
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
        });
    </script>
</body>
</html>