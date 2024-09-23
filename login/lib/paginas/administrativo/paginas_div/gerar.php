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

    /*$sql_dados = $mysqli->query("SELECT * FROM config_admin WHERE id = '1'") or die($mysqli->$error);
    $dados = $sql_dados->fetch_assoc();*/

    $id = $_SESSION['usuario'];
    $sql_query = $mysqli->query("SELECT * FROM socios WHERE id = '$id'") or die($mysqli->$error);
    $usuario = $sql_query->fetch_assoc();
    $usuario_admin = $usuario['apelido'];


   if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $ultima_mensalidade = $_POST['ultmes'];
        $ultima_mensalidade = $ultima_mensalidade + 1;
        $ultimo_ano = $_POST['ultano'];

        if ($ultima_mensalidade > 12) {
            $ultima_mensalidade = 1;
            $ultimo_ano = $ultimo_ano + 1;
        }

        $qtmes = $_POST['qtmes'];
        $valormes = $_POST['valormes'];
        $diavenc = $_POST['diavenc'];
        $desc = $_POST['desc'];
        $multa = $_POST['multa'];

        //var_dump($_POST);

        $mes = $ultima_mensalidade + 1;
        $ano = $ultimo_ano;

        for ($i = 1; $i <= $qtmes; $i++) {
            if ($mes > 12) {
                $mes = 1;
                //$ultimo_ano ++;
                $ano = $ano + 1;
            }
           
            /*$data_vencimento = date("$ultimo_ano-$mes-$diavenc");*/
            $mensalidade = $mes - 1;
            //$ano = $ano;

            if ($mensalidade == 1) {
                //$mes = 1;
                $ultimo_ano = $ultimo_ano + 1;
                //$ano = $ano + 1;
            } 
            if ($mensalidade < 1) {
                $mensalidade = 12;
                //$ano = $ano - 1;
            }

            $data_vencimento = date("$ano-$mes-$diavenc");
            //$mensalidade = $mes;
        
            $sql_code = "INSERT INTO mensalidades_geradas (data, admin, mensalidade_dia, mensalidade_mes, mensalidade_ano, data_vencimento, valor, desconto, multa)
            VALUES (NOW(), '$usuario_admin', '$diavenc', '$mensalidade', '$ultimo_ano', '$data_vencimento', '$valormes', '$desc', '$multa')";
            $mysqli->query($sql_code) or die($mysqli->error);

            // Adicionar mensalidades para sócios ativos e suspensos
            $sql_socios = "SELECT * FROM socios WHERE status IN ('ATIVO', 'SUSPENSO')";
            $result_socios = $mysqli->query($sql_socios);

            while ($socio = $result_socios->fetch_assoc()) {
                $id_socio = $socio['id'];
                $apelido = $socio['apelido'];
                $nome_completo = $socio['nome_completo'];
                $status = $socio['status'];

                // Inserir na tabela mensalidades
                $sql_mensalidade = "INSERT INTO mensalidades (id_socio, data, admin, apelido, nome_completo, status, mensalidade_dia, 
                mensalidade_mes, mensalidade_ano, valor_mensalidade, data_vencimento, desconto_mensalidade, multa_mensalidade)
                VALUES ('$id_socio', NOW(), '$usuario_admin', '$apelido', '$nome_completo', '$status', '$diavenc','$mensalidade', 
                '$ultimo_ano', '$valormes', '$data_vencimento',  '$desc', '$multa')";
                $mysqli->query($sql_mensalidade) or die($mysqli->error);

                // Inserir no histórico de mensalidades
                $sql_historico = "INSERT INTO historico_mensalidades (id_socio, data, admin, apelido, nome_completo, status, mensalidade_dia, 
                mensalidade_mes, mensalidade_ano, valor_mensalidade, data_vencimento, desconto_mensalidade, multa_mensalidade)
                VALUES ('$id_socio', NOW(), '$usuario_admin', '$apelido', '$nome_completo', '$status', '$diavenc','$mensalidade', 
                '$ultimo_ano', '$valormes', '$data_vencimento',  '$desc', '$multa')";
                $mysqli->query($sql_historico) or die($mysqli->error);
                
            }

            $mes++;
        }
        
            echo "<p><b>Mensalidades geradas com sucesso!</b></p>";
            unset($_POST);
            header("refresh: 3; GerarMensalidades.php");

    }
    //fecha a conexão
    $mysqli->close();
?>