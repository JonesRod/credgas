<?php
    session_start();
    include('../../../conexao.php'); // Conexão com o banco

    // Verificação de sessão
    if (!isset($_SESSION['id'])) {
        header("Location: ../../../../index.php");
        exit;
    }
    var_dump($_POST);
    //echo 'crediario';

    // Sanitização e validação dos dados recebidos
    $tipo_compra = 'crediario';
    $id_cliente = intval($_POST['id_cliente']);
    $id_parceiro = intval($_POST['id_parceiro']);
    $valor_frete = floatval(str_replace(',', '.', $_POST['valor_frete']));
    $valor_total_crediario = floatval(str_replace(',', '.', $_POST['valor_total_crediario']));
    $detalhes_produtos = $_POST['detalhes_produtos']; // Certifique-se de validar este campo
    $entrega = $_POST['entrega'];
    $rua = $_POST['rua'];
    $bairro = $_POST['bairro'];
    $numero = $_POST['numero'];
    $contato = $_POST['contato'];
    $entrada = floatval(str_replace(',', '.', $_POST['entrada']));
    $restante = floatval(str_replace(',', '.', $_POST['restante']));
    $tipo_entrada_crediario = $_POST['tipo_entrada_crediario'];
    $bandeiras_aceitas = $_POST['bandeiras_aceita'];
    $comentario = $_POST['comentario'];

    // Formatação para moeda
    $valor_total_crediario_formatado = number_format($valor_total_crediario, 2, ',', '.');
    $entrada_formatado = number_format($entrada, 2, ',', '.');
    $restante_formatado = number_format($restante, 2, ',', '.');
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compra no Crediário</title>
</head>
<body>
    <form action="">
        <input type="text" id="id_cliente" value="<?php echo $id_cliente; ?>" hidden>
        <input type="text" id="id_parceiro" value="<?php echo $id_parceiro; ?>" hidden>
        <input type="text" id="valor_frete" value="<?php echo $valor_frete; ?>" hidden>
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
                <select id="parcelas" onchange="calcularParcelas()">
                    <option value="1">1x (sem juros)</option>
                    <option value="2">2x</option>
                    <option value="3">3x</option>
                    <option value="4">4x</option>
                    <option value="5">5x</option>
                    <option value="6">6x</option>
                </select>
                <p id="valor_parcela"></p>
                <button type="button" id="btn_voltar" onclick="voltarParaPix()">Voltar</button>
                <button type="button" id="btn_finalizar" onclick="finalizarPagamento()">Finalizar</button>
            </div>
        </div>
        
    </form>

    <form action="pagamento.php" method="POST">
        <input type="hidden" name="id_cliente" value="<?php echo $id_cliente; ?>">
        <input type="hidden" name="id_parceiro" value="<?php echo $id_parceiro; ?>">
        <input type="hidden" name="valor_total" value="<?php echo $valor_total_crediario; ?>">
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
            const qrCodePix = document.getElementById("qr_code_pix");
            const linkPix = document.getElementById("link_pix");
            const btnContinuar = document.getElementById("btn_continuar");

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

            // Função para finalizar o pagamento
            window.finalizarPagamento = function () {
                alert("Pagamento finalizado com sucesso!");
                // Aqui você pode adicionar a lógica para finalizar o pagamento
            };
        });

        function calcularParcelas() {
            const restante = <?php echo $restante; ?>;
            const parcelas = document.getElementById("parcelas").value;
            const taxa = 0.0299; // 2,99% ao mês
            let valorParcela;

            if (parcelas == 1) {
                valorParcela = restante;
            } else {
                valorParcela = restante * Math.pow(1 + taxa, parcelas) / parcelas;
            }

            document.getElementById("valor_parcela").innerText = 
                `Valor de cada parcela: R$ ${valorParcela.toFixed(2).replace('.', ',')}`;
        }
    </script>
</body>
</html>