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

    if(isset($_GET['pesquisa'])) {
        $nome_socio = $_GET['pesquisa'];
    
        $sql = "SELECT * FROM socios WHERE nome_completo LIKE '%$nome_socio%' LIMIT 1";
        $result = $mysqli->query($sql);
    
        if ($result->num_rows > 0) {
            $result_pesquisa = $result->fetch_assoc();
    
            $id_socio = $result_pesquisa['id'];
            $apelido = $result_pesquisa['apelido'];
            $nome = $result_pesquisa['nome_completo'];
            $celular1 = $result_pesquisa['celular1'];
            $celular2 = $result_pesquisa['celular2'];
            $email = $result_pesquisa['email'];
    
            echo json_encode($result_pesquisa); // Retorna os dados como JSON
        } else {
            //echo json_encode(['error' => 'Nenhum sócio encontrado']); // Retorna uma mensagem de erro em JSON
            echo json_encode([
                'msg' => 'Nenhum sócio encontrado.',
                'id' => '',
                'apelido' => '',
                'nome_completo' => '',
                'celular1' => '',
                'celular2' => '',
                'email' => ''
            ]);
        }
    }
    
?>