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

    $id_mensalidade = $_POST['id_mensalidade'];
    $sql_mensalidade = $mysqli->query("SELECT * FROM mensalidades WHERE id = '$id_mensalidade'") or die($mysqli->error);
    $mensalidade = $sql_mensalidade->fetch_assoc();
    //echo $id_mensalidade;

    $id_socio = $mensalidade['id_socio']; //echo $id_socio;
    $data = $mensalidade['data']; //echo $data;
    $admin = $mensalidade['admin']; //echo $admin;
    $apelido = $mensalidade['apelido']; //echo $apelido;
    $nome = $mensalidade['nome_completo']; //echo $nome;
    $status = $mensalidade['status']; //echo $status;
    $mensalidade_dia = $mensalidade['mensalidade_dia']; //echo $mensalidade_dia;
    $mensalidade_mes = $mensalidade['mensalidade_mes']; //echo $mensalidade_mes;
    $mensalidade_ano = $mensalidade['mensalidade_ano']; //echo $mensalidade_ano;
    $valor_mensalidade = $mensalidade['valor_mensalidade']; //echo $valor_mensalidade;
    $data_vencimento = $mensalidade['data_vencimento']; //echo $data_vencimento;
    $desconto_mensalidade = $_POST['desconto']; //echo $desconto_mensalidade;
    $multa_mensalidade = $mensalidade['multa_mensalidade']; //echo $multa_mensalidade;
    $recebido_antes = $_POST['recebido']; //echo $recebido_antes;
    $valor_recebido = $_POST['receber'];// echo $valor_recebido;
    $data_recebida = date('Y-m-d'); //echo $data_recebida;
    $restante = $_POST['restante']; //echo $restante;
    $valor_receber = $restante;

    $proximo_valor_receber = floatval($recebido_antes) + floatval($valor_recebido);

    if ($restante == '0,00') {
        // Excluir a linha no banco de dados
        $mysqli->query("DELETE FROM mensalidades WHERE id = '$id_mensalidade'") or die($mysqli->error);

        // Inserir dados no histórico de mensalidades
        // Inserir dados no histórico de mensalidades
        $mysqli->query("INSERT INTO historico_mensalidades (id_socio, data, admin, apelido, nome_completo, 
        status, mensalidade_dia, mensalidade_mes, mensalidade_ano, valor_mensalidade, data_vencimento, 
        desconto_mensalidade, multa_mensalidade, valor_receber, valor_recebido, data_recebida, restante, status_pagamento)
        VALUES ('$id_socio', '$data', '$admin', '$apelido', '$nome', '$status', '$mensalidade_dia', 
        '$mensalidade_mes', '$mensalidade_ano', '$valor_mensalidade', '$data_vencimento', '$desconto_mensalidade', 
        '$multa_mensalidade', '$valor_receber', '$valor_recebido', '$data_recebida', '$restante', 'PAGO')") or die($mysqli->error);

    } else {
        // Atualizar os valores na linha
        $mysqli->query("UPDATE mensalidades SET 
            desconto_mensalidade = '$desconto_mensalidade', 
            multa_mensalidade = '$multa_mensalidade',
            valor_receber = '$valor_receber',
            valor_recebido = '$proximo_valor_receber',
            data_recebida = '$data_recebida',
            restante = '$restante'
            WHERE id = '$id_mensalidade'") or die($mysqli->error);

        // Inserir dados no histórico de mensalidades
        $mysqli->query("INSERT INTO historico_mensalidades (id_socio, data, admin, apelido, nome_completo, 
        status, mensalidade_dia, mensalidade_mes, mensalidade_ano, valor_mensalidade, data_vencimento, 
        desconto_mensalidade, multa_mensalidade, valor_receber, valor_recebido, data_recebida, restante)
        VALUES ('$id_socio', '$data', '$admin', '$apelido', '$nome', '$status', '$mensalidade_dia', 
        '$mensalidade_mes', '$mensalidade_ano', '$valor_mensalidade', '$data_vencimento', '$desconto_mensalidade', 
        '$multa_mensalidade', '$valor_receber', '$valor_recebido', '$data_recebida', '$restante')") or die($mysqli->error);
    }
    $msg= 'Mensalidade recebida com sucesso.';

?>
<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
    </style>
    <title></title>
</head>
<body>
    <span><?php echo $msg; ?></span>
    <script>
        setTimeout(function() {
            window.close();
        }, 5000);
    </script>
</body>
</html>

