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
        <p>Valor da Compra: R$ <?php echo $valor_total_crediario; ?></p>
        <p>Entrada: R$ <?php echo $entrada; ?></p>
        <p>Restante: R$ <?php echo $restante; ?></p>
        <div>
            <h3>Forma de Pagamento da entrada</h3>
            <label for="tipo_entrada_crediario">Tipo de Entrada:</label>
            <input id="tipo_entrada_crediario" name="tipo_entrada_crediario" value="<?php echo $tipo_entrada_crediario; ?>" readonly>
            <input type="text" id="bandeiras_aceitas" name="bandeiras_aceitas" value="<?php echo $bandeiras_aceitas; ?>" readonly>
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
</body>
</html>