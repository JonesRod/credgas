<?php
session_start(); // Iniciar a sessão
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Celke - Importar Excel csv e salvar no BD</title>
</head>
<body>

    <h1>Importar Excel .csv</h1>
    <h3>Antes de importar, retire da lista os dados de quem ja esta  no outro
        banco e verifique a ordem e a deixe na seguite seguência:
    </h3>

    <p>(DATA DE CADASTRO = "00/00/0000", APELIDO, NOME DO SOCIO, CPF = "000.000.000-00",
        RG, DATA NASC. = "00/00/0000", RUA, N°, BAIRRO, TELEFONE, E-MAIL,
        STATUS = "ATIVO, AFASTADO, SUSPENSO, EXCLUIDO",OBS., JOIA)
    </p>
    <?php
    // Apresentar a mensagem de erro ou sucesso
    if(isset($_SESSION['msg'])){
        echo $_SESSION['msg'];
        unset($_SESSION['msg']);
    }
    ?>

    <!-- Formulario para enviar arquivo .csv -->
    <form method="POST" action="processa.php" enctype="multipart/form-data">
        <label>Arquivo: </label>
        <input type="file" name="arquivo" id="arquivo" accept="text/csv"><br><br>

        <input type="submit" value="Enviar">
    </form>
    
</body>
</html>