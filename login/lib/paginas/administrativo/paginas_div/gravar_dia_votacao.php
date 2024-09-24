<?php
    include('../../../conexao.php');

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
                    header("Location: ../../usuarios/usuario_home.php");      
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
            header("Location: ../../../../../index.php");  
        }
    
    }else{
        //echo "6";
        session_unset();
        session_destroy(); 
        header("Location: ../../../../../index.php");  
    }

    $id = $_SESSION['usuario'];
    $sql_query = $mysqli->query("SELECT * FROM socios WHERE id = '$id'") or die($mysqli->$error);
    $usuario = $sql_query->fetch_assoc();

    //echo $id;

    $id_insc = $_POST['inscrito'];
    $sql_insc = $mysqli->query("SELECT * FROM int_associar WHERE id = '$id_insc'") or die($mysqli->$error);
    $inscrito = $sql_insc->fetch_assoc();

    $votacao = 'SIM';
    $admin = $usuario['apelido']; //echo $admin;
    $apelido = $_POST['apelido'];
    $nome = $_POST['nome_completo']; //echo $nome;
    $data_ini = $_POST['data_ini'];
    $data_ini_formatada = date('Y-m-d', strtotime(str_replace('/', '-', $data_ini)));
    $hora_ini = $_POST['hora_ini'];
    $data_final = $_POST['data_final'];  
    $data_final_formatada = date('Y-m-d', strtotime(str_replace('/', '-', $data_final)));

    $hora_final = $_POST['hora_final'];
    //var_dump('_POST');
    $sql_code = "UPDATE int_associar
    SET 
    em_votacao = '$votacao',
    admin = '$admin',
    inicio_votacao = '$data_ini_formatada',
    inicio_hora = '$hora_ini',
    fim_votacao = '$data_final_formatada',
    fim_hora = '$hora_final'
    WHERE id = '$id_insc'";

    $deu_certo = $mysqli->query($sql_code) or die($mysqli->$erro);

    if($deu_certo) {
       $msg = "Registrado com sucesso!";

        unset($_POST);
        header("refresh: 3; integrarSocio.php");
    } else {
        //echo "<p><b>ERRO: $erro</b></p>";
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
        }
    </style>
    <title></title>
</head>
<body>
    <span><?php echo $msg; ?></span>
</body>
</html>