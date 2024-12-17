<?php
    include('../../conexao.php');

    $id = '1';
    $dados = $mysqli->query("SELECT * FROM config_admin WHERE termos_cliente_vista != '' ORDER BY data_alteracao DESC LIMIT 1") or die($mysqli->error);
    $dadosEscolhido = $dados->fetch_assoc();
    //echo $dadosEscolhido['validade'];

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="termos.css">
    <title>Termos de Compras</title>
</head>
<body>
    <div class="container">
        <h1>Termos de compras.</h1>
        <textarea placeholder="Texto..." name="" id="" cols="120" rows="35"><?php echo $dadosEscolhido['termos_cliente_vista']; ?></textarea>
    </div>
</body>
</html>