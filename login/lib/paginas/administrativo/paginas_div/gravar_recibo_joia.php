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

    $id_parcela = $_POST['id_parcela'];
    $sql_parcela= $mysqli->query("SELECT * FROM joias_receber WHERE id = '$id_parcela'") or die($mysqli->error);

    $parcela = $sql_parcela->fetch_assoc();
    //echo $id_mensalidade;

    $id_socio = $parcela['id_socio']; //echo $id_socio;
    $data = $parcela['data']; //echo $data;
    $admin = $parcela['admin']; //echo $admin;
    $apelido = $parcela['apelido']; //echo $apelido;
    $nome = $parcela['nome_completo']; //echo $nome;
    $celular1 = $parcela['celular1'];
    $celular2 = $parcela['celular2'];
    $email = $parcela['email'];
    $valor = $parcela['valor'];
    $entrada = $parcela['entrada'];
    $restante_joia = $parcela['restante'];
    $num_parcela = $parcela['num_parcela'];
    $qt_parcelas = $parcela['qt_parcelas'];
    $valor_parcelas = $parcela['valor_parcelas']; //echo $mensalidade_dia;
    $vencimento = $parcela['vencimento']; //echo $mensalidade_mes;
    $desconto = $parcela['desconto_parcela'];
    $desconto_parcela = $_POST['desconto'];
    $desconto_total = floatval($desconto_parcela) + floatval($desconto); //echo $mensalidade_ano;
    $recebido = $parcela['recebido']; //echo $valor_mensalidade;
    $recebi_parcela = $_POST['receber'];
    $recebimento_total = floatval($recebi_parcela) + floatval($recebido);
    $data_recebi = date('Y-m-d');

    $restante = $_POST['restante']; //echo $desconto_mensalidade;

    $valor_receber = $restante;

    $proximo_valor_receber = $restante;//floatval($valor) - floatval($recebimento_total) + floatval($desconto_total);

    //echo $proximo_valor_receber;
    //die();
    if ($restante == '0,00') {
        // Excluir a linha no banco de dados
        $mysqli->query("DELETE FROM joias_receber WHERE id = '$id_parcela'") or die($mysqli->error);

        $sql_historico_joia = "INSERT INTO historico_joias_receber (admin, id_socio, apelido, nome_completo, celular1, celular2, email, valor, 
        entrada, restante, num_parcela, qt_parcelas, valor_parcelas, vencimento, desconto_parcela, recebido, data_recebeu, a_receber, status_pagamento)
        VALUES ('$admin','$id_socio', '$apelido', '$nome', '$celular1', '$celular2', '$email', '$valor', '$entrada', '$restante_joia',
        '$num_parcela', '$qt_parcelas','$valor_parcelas','$vencimento', '$desconto_parcela', '$recebi_parcela', NOW(), '$proximo_valor_receber','PAGO')";
        $mysqli->query($sql_historico_joia) or die($mysqli->error);

    } else {
        // Atualizar os valores na linha
        $mysqli->query("UPDATE joias_receber SET 
            admin = '$admin',
            id_socio = '$id_socio',
            apelido = '$apelido',
            nome_completo = '$nome', 
            celular1 = '$celular1',
            celular2 = '$celular2',
            email = '$email',
            valor = '$valor',
            entrada = '$entrada',
            restante = '$restante_joia',
            num_parcela = '$num_parcela',
            qt_parcelas = '$qt_parcelas',
            valor_parcelas = '$valor_parcelas',
            vencimento = '$vencimento',
            desconto_parcela = '$desconto_total',
            recebido = '$recebimento_total',
            data_recebeu = NOW(),
            a_receber = '$proximo_valor_receber'
            WHERE id = '$id_parcela'") or die($mysqli->error);

        $sql_historico_joia = "INSERT INTO historico_joias_receber (admin, id_socio, apelido, nome_completo, celular1, celular2, email, valor, 
        entrada, restante, num_parcela, qt_parcelas, valor_parcelas, vencimento, desconto_parcela, recebido, data_recebeu, a_receber)
        VALUES ('$admin','$id_socio', '$apelido', '$nome', '$celular1', '$celular2', '$email', '$valor', '$entrada', '$restante_joia',
        '$num_parcela', '$qt_parcelas','$valor_parcelas','$vencimento', '$desconto_parcela', '$recebi_parcela', NOW(), '$proximo_valor_receber')";
        $mysqli->query($sql_historico_joia) or die($mysqli->error);
    }
    $msg  = 'Parcela recebida com sucesso.';

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
</head>
<body>
    <span>?<php echo $msg; ?></span>
    <script>
        setTimeout(function() {
            window.close();
        }, 5000);
    </script>
</body>
</html>
