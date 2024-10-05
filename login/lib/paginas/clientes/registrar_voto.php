<?php
    include('../../conexao.php');

    if(!isset($_SESSION)){
        session_start(); 

        if(isset($_SESSION['usuario'])){

            if (isset($_POST["tipoLogin"])) {
                // echo "1";
                $usuario = $_SESSION['usuario'];
                $valorSelecionado = $_POST["tipoLogin"];// Obter o valor do input radio
                $admin = $valorSelecionado;

                if($admin != 1){
                    $usuario = $_SESSION['usuario'];
                    $admin = $_SESSION['admin'];
                    //echo "1";
                    header("Location: ../usuarios/usuario_home.php");      
                }else{
                    $usuario = $_SESSION['usuario'];
                    $admin = $_SESSION['admin'];
                    $_SESSION['usuario'];
                    $_SESSION['admin'];  
                }
            }  

        }else{
            //echo "5";
            session_unset();
            session_destroy(); 
            header("Location: ../../../../index.php");  
        }

    }else{
        //echo "6";
        session_unset();
        session_destroy(); 
        header("Location: ../../../../index.php");  
    }

    $id = $_SESSION['usuario'];
    $sql_query = $mysqli->query("SELECT * FROM socios WHERE id = '$id'") or die($mysqli->$error);
    $usuario = $sql_query->fetch_assoc();

    $msg ='';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Verificar se todos os campos necessários foram recebidos
        if (isset($_POST['voto']) && isset($_POST['id_inscrito'])) {
            $voto = $_POST['voto'];
            $id_inscrito = $_POST['id_inscrito'];

            // Atualize a contagem de votos no banco de dados
            if ($voto == 'SIM') {
                $sql_update_voto = "UPDATE int_associar SET voto_sim = voto_sim + 1 WHERE id = '$id_inscrito'";
            } else {
                $sql_update_voto = "UPDATE int_associar SET voto_nao = voto_nao + 1 WHERE id = '$id_inscrito'";
            }

            $result_update_voto = $mysqli->query($sql_update_voto);

            // Verificar se a atualização foi bem-sucedida
            if ($result_update_voto) {
                // Registrar o voto do usuário
                $sql_insert_voto = "INSERT INTO em_votacao (id_socio, id_inscrito, voto) VALUES ('$id', '$id_inscrito', '$voto')";
                $result_insert_voto = $mysqli->query($sql_insert_voto);

                if ($result_insert_voto) {
                    $msg = "Voto registrado com sucesso!";
                    unset($_POST);
                    header("refresh: 5; inicio.php");
                } else {
                    $msg = "Erro ao registrar o voto.";
                    unset($_POST);
                    header("refresh: 5; inicio.php");
                }
            } else {
                $msg = "Erro ao atualizar a contagem de votos.";
                unset($_POST);
                header("refresh: 5; inicio.php");
            }

            // Fechar a conexão
            $mysqli->close();
        } else {
            $msg = "Campos necessários não foram preenchidos.";
            unset($_POST);
            header("refresh: 5; inicio.php");
        }
    } else {
        $msg = "Acesso inválido.";
        unset($_POST);
        header("refresh: 5; inicio.php");
    }
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <style>
        body{
            margin-top: 30px;
            text-align: center;
        }
    </style>
</head>
<body>
    <span><?php echo $msg; ?></span>
</body>
</html>