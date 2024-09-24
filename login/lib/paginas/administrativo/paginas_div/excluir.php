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

    $id_insc = $_GET['id_socio'];
    $sql_insc = $mysqli->query("SELECT * FROM int_associar WHERE id = '$id_insc'") or die($mysqli->$error);
    $inscrito = $sql_insc->fetch_assoc();

    $id_insc = $_GET['id_socio'];
    $msg = '';
    $erro = false;
    // Verificar se o ID foi fornecido corretamente
    if (isset($id_insc)) {

        if($erro) {
            echo "<p><b>ERRO: $erro</b></p>";
        } else {
    
            $sql_excluir = "UPDATE int_associar
            SET 
            data = NOW(),
            motivo = '',
            termos = '',
            observacao = '',
            em_votacao = 'NÃO',
            inicio_votacao = '',
            inicio_hora = '',
            fim_votacao = '',
            fim_hora = '',
            voto_sim = '',
            voto_nao = '',
            resultado = '',
            status = 'DESATIVADO',
            validade = DATE_ADD(NOW(), INTERVAL 1 YEAR)
            WHERE id = '$id_insc'";

            //var_dump($_POST);

            $mysqli->query($sql_excluir) or die($mysqli->$erro);
            
            if ($mysqli->query($sql_excluir)) {
                $msg = "Inscrito Desativado com sucesso.";
            } else {
                $msg = "Erro ao excluir inscrito: " . $mysqli->error;
            }

            // Executar a consulta SQL para excluir o inscrito
            $sql_excluir_em_votacao = "DELETE FROM em_votacao WHERE id_inscrito = '$id_insc'";

            if ($mysqli->query($sql_excluir_em_votacao)) {
                unset($_POST);
                header("refresh: 5; integrarSocio.php");
            } else {
                $msg = "Erro ao excluir Em Votação: " . $mysqli->error;
            }
        }
    } else {
        $msg = "ID do inscrito não fornecido.";
    }
    
    // Fechar a conexão
    $mysqli->close();
    
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body{
            text-align: center;
            margin-top: 20px;
        }
    </style>
    <title></title>
</head>
<body>
    <span><?php echo $msg; ?></span>
</body>
</html>