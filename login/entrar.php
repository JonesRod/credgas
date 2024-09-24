<?php
    /*include('login/lib/conexao.php');
    //echo 'oi';
    if(isset($_SESSION)){

        if(isset($_SESSION['usuario'])){

            if (isset($_POST["tipoLogin"])) {
                // echo "1";
                $usuario = $_SESSION['usuario'];
                $valorSelecionado = $_POST["tipoLogin"];// Obter o valor do input radio
                $admin = $valorSelecionado;

                if($admin != 1){
                    $id = $_SESSION['usuario'];
                    $sql_query = $mysqli->query("SELECT * FROM socios WHERE id = '$id'") or die($mysqli->$error);
                    $usuario = $sql_query->fetch_assoc();

                    $usuario = $_SESSION['usuario'];
                    $admin = $_SESSION['admin'];
                    //echo "1";
                    header("Location: login/lib/paginas/usuarios/usuario_home.php");    
                }else{
                    $id = $_SESSION['usuario'];
                    $sql_query = $mysqli->query("SELECT * FROM socios WHERE id = '$id'") or die($mysqli->$error);
                    $usuario = $sql_query->fetch_assoc();

                    $usuario = $_SESSION['usuario'];
                    $admin = $_SESSION['admin'];
                    $_SESSION['usuario'];
                    $_SESSION['admin'];  
                    header("Location: login/lib/paginas/administrativo/admin_home.php");       
                }
            }  

        }else{
            //echo "5";
            session_unset();
            session_destroy(); 
            header("Location: index.php");  
        }
    
    }
    if(!isset($_SESSION)){
        session_start(); 
    }*/

    /*$id = $_SESSION['usuario'];
    $sql_query = $mysqli->query("SELECT * FROM socios WHERE id = '$id'") or die($mysqli->$error);
    $usuario = $sql_query->fetch_assoc();*/
//--------------------
include("lib/conexao.php");

if(isset($_SESSION)) {
    
    $usuario = $_SESSION['usuario'];
    $admin = $_SESSION['admin'];

    if($admin == 1 ){
        //echo "2";  
       header("Location: lib/paginas/administrativo/admin_home.php");       
    }else{
        //echo "3";  
        header("Location: lib/paginas/usuarios/usuario_home.php");  
    }
}else{
    //echo "4"; 
    session_start(); 
}
    
    $msg= false;

    if(isset($_POST['email']) || isset($_POST['senha'])) {
        //echo 'oii';
        //var_dump($_POST);
        //die();
        $sql_primeiro_registro = "SELECT * FROM socios";
        $registros = $mysqli->query($sql_primeiro_registro) or die("Falha na execução do código SQL: " . $mysqli->$error);

        // Verifica se existem registros na tabela 'socios'
        if ($registros->num_rows == 0) {
            header("Location: lib/cadastro_usuario.php");
            exit();
        }

        $email = $_POST['email'];//$mysqli->escape_string SERVE PARA PROTEGER O ACESSO 
        $cpf = $_POST['email'];
        $senha = $_POST['senha'];
        

        //echo "oii";
        if(isset($_SESSION['email'])){
            $email = $_SESSION['email'];
            $senha = password_hash($_SESSION['senha'], PASSWORD_DEFAULT);
            $mysqli->query("INSERT INTO senha (email, senha, cpf) VALUES('$email','$senha','$cpf')");
        }
        if(strlen($_POST['email']) == 0 ) {
            $msg= true;
            $msg = "Preencha o campo Usuário.";
            //echo $msg;
        } else if(strlen($_POST['senha']) == 0 ) {
            $msg= true;
            $msg = "Preencha sua senha.";
            //echo $msg;
        } else {

            $sql_code = "SELECT * FROM socios WHERE email = '$email' LIMIT 1";
            $sql_query =$mysqli->query($sql_code) or die("Falha na execução do código SQL: " . $mysqli->$error);
            $usuario = $sql_query->fetch_assoc();
            $quantidade = $sql_query->num_rows;//retorna a quantidade encontrado

            if(($quantidade ) == 1) {

                if(password_verify($senha, $usuario['senha'])) {

                    $admin = $usuario['admin'];

                    if($admin == 1){
                        $_SESSION['usuario'] = $usuario['id'];
                        $_SESSION['admin'] = $admin;
                        //$msg = "1";
                        unset($_POST);
                        //session_start(); 
                        header("Location: lib/tipo_login.php");
                    }else if($admin != 1){
                        $_SESSION['usuario'] = $usuario['id'];
                        $_SESSION['admin'] = $admin;
                        //$msg = "2";
                        unset($_POST);
                        //session_start(); 
                        header("Location: lib/paginas/usuario_home.php");
                    }    
                }else{
                    $msg= true;
                    $msg = "Usúario ou Senha estão inválidos!";    
                    //echo $msg;
                }
            }else{

                $sql_cpf = "SELECT * FROM socios WHERE cpf = '$cpf' LIMIT 1";
                $sql_query =$mysqli->query($sql_cpf) or die("Falha na execução do código SQL: " . $mysqli->$error);
                $usuario = $sql_query->fetch_assoc();
                $quantidade_cpf = $sql_query->num_rows;//retorna a quantidade encontrado
        
                if(($quantidade_cpf) == 1) {
        
                    if(password_verify($senha, $usuario['senha'])) {
        
                        $admin = $usuario['admin'];
        
                        if($admin == 1){
                            $_SESSION['usuario'] = $usuario['id'];
                            $_SESSION['admin'] = $admin;
                            //$msg = "1";
                            unset($_POST);
                            //session_start(); 
                            header("Location: lib/tipo_login.php");
                        }else if($admin != 1){
                            $_SESSION['usuario'] = $usuario['id'];
                            $_SESSION['admin'] = $admin;
                            //$msg = "2";
                            unset($_POST);
                            //session_start(); 
                            header("Location: lib/paginas/usuario_home.php");
                        }    
                    }else{
                        $msg= true;
                        $msg = "Usúario ou Senha estão inválidos!";   
                        $mysqli->close(); 
                        //echo $msg;
                    }
                }else{
                    $msg= true;
                    $msg = "O Usúario informado não esta correto ou não está cadastrado!";
                    $mysqli->close();
                    //echo $msg;
                }
            }
        }
    }
?>