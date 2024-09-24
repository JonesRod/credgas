<?php
    include('../../../conexao.php');

    // Verifica se o ID foi passado como parâmetro
    if(isset($_GET['id'])) {
        $id = $_GET['id'];
        $data = $_GET['data'];
        $status = $_GET['status'];
        $admin = $_GET['admin'];
        $obs = $_GET['obs'];

        if($admin == 1){
            // Agora, execute a consulta de atualização
            $sql_code = "UPDATE socios
            SET 
            data = '$data',
            admin = '$admin',
            status ='$status',
            observacao = '$obs'
            WHERE id = '$id'";

            // Executa a consulta
            if ($mysqli->query($sql_code) === TRUE) {
                $msg = "Registro atualizado com sucesso!";
            } else {
                $msg = "Erro ao atualizar o registro: " . $mysqli->error;
            }

            // Fecha a conexão
            $mysqli->close();
            header('refresh: 5; listaSocios.php');  

        }else{
            // Prepara a consulta SQL
            $sql_code = 
            "UPDATE socios
            SET 
            status = '$status',
            admin = '$admin',
            observacao = '$obs'
            WHERE id = '$id'";

            // Executa a consulta
            if ($mysqli->query($sql_code) === TRUE) {
                $msg = "Registro atualizado com sucesso!";
            } else {
                $msg = "Erro ao atualizar o registro: " . $mysqli->error;
            }

            // Fecha a conexão
            $mysqli->close();
            header('refresh: 5; listaSocios.php');

        }


    } else {
        $msg = "ID não fornecido.";
    }
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body{
            text-align: center;
            margin-top: 30px;
        }
    </style>
    <title></title>
</head>
<body>
    <?php 
        echo $msg; 
    ?>
</body>
</html>
