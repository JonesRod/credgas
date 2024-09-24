<?PHP
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

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        
        $admin = $_POST['admin'];
        $id_socio = $_POST['id_socio'];
        $apelido = $_POST['apelido'];
        $nome = $_POST['nome'];
        $celular1 = $_POST['celular1'];
        $celular2 = $_POST['celular2'];
        $email = $_POST['email'];
        $valor_joia = $_POST['valor_joia'];
        $entrada = $_POST['entrada'];
        $restante = $_POST['restante'];
        $qt_parcelas = $_POST['qt_parcelas'];
        $valor_parcela = $_POST['valor_parcela'];
        $a_receber = $restante;
        // Lógica para gerar parcelas
        $vencimento = date('Y-m-d'); // Data de vencimento inicial (hoje)
        $vencimento = date('Y-m-d', strtotime($vencimento . "+30 days"));
        //echo $admin;
        //die();
        for ($i = 1; $i <= $qt_parcelas; $i++) {
            
            $sql_recebe_joia = "INSERT INTO joias_receber (data, admin, id_socio, apelido, nome_completo, celular1, 
            celular2, email, valor, entrada, restante, num_parcela, qt_parcelas, valor_parcelas, vencimento, a_receber)
            VALUES (NOW(), '$admin','$id_socio', '$apelido', '$nome', '$celular1', '$celular2', '$email', '$valor_joia', '$entrada',
            '$restante','$i','$qt_parcelas','$valor_parcela','$vencimento','$valor_parcela')";
            $mysqli->query($sql_recebe_joia) or die($mysqli->error);

            $sql_historico_joia = "INSERT INTO historico_joias_receber (data, admin, id_socio, apelido, nome_completo, celular1, 
            celular2, email, valor, entrada, restante, num_parcela, qt_parcelas, valor_parcelas, vencimento, a_receber)
            VALUES (NOW(), '$admin', '$id_socio', '$apelido', '$nome', '$celular1', '$celular2', '$email', '$valor_joia', '$entrada',
            '$restante','$i','$qt_parcelas','$valor_parcela','$vencimento','$valor_parcela')";
            $mysqli->query($sql_historico_joia) or die($mysqli->error);

            // Atualiza a data de vencimento para 30 dias depois
            $vencimento = date('Y-m-d', strtotime($vencimento . "+30 days"));

            //$qt_parcelas++;
        }
        
        $msg = "<p><b>Registrado com sucesso!</b></p>";
        unset($_POST);
        $mysqli->close();
        header("refresh: 5; inicio.php");

    }
    //echo $admin;
    //var_dump($_POST);
    //fecha a conexão
    
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
</head>
<body>
    <div>
        <span><?php echo $msg;?></span>
    </div>
</body>
</html>