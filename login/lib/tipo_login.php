<?php
    include('conexao.php');

    if(!isset($_SESSION)){
        session_start();
    }

    if(isset($_SESSION['id'])){
        $usuario = $_SESSION['id'];
        $admin = $_SESSION['admin'];  
        
        if($admin == 0){
            $_SESSION['usuario'];
            header(header: "Location: paginas/clientes/cliente_home.php"); 

        }else if($admin == 1){
            $usuario = $_SESSION['id'];
            $admin = $_SESSION['admin'];
            $_SESSION['id'];
            $_SESSION['admin']; 

            $id =$usuario;
            
            $sql_query = $mysqli->query(query: "SELECT * FROM meus_clientes WHERE id = '$id'") or die($mysqli->$error);
            $usuario = $sql_query->fetch_assoc(); 
                
        }else{
            session_unset();
            session_destroy();
            header(header: "Location: ../../../../index.php"); 
        }
    }else{
        // Destruir todas as variáveis de sessão
        session_unset();
        session_destroy();
        header(header: "Location: ../../index.php");  
    } 

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="tipo_login.css">
    <title>Login</title>
</head>
<body>
    <main>
        <h2>Olá, <strong><?php echo explode(' ', trim($usuario['nome_completo']))[0]; ?></h2>
        <h3>Escolha o tipo de login:</h3>
        <form id="escolherLoginForm" method="POST" action="paginas/administrativo/admin_home.php" onsubmit="return resposta()">
            <label>
                <input type="radio" name="tipoLogin" value="1"> Admin
            </label>
            <label>
                <input type="radio" name="tipoLogin" value="0"> Usuário
            </label>
            <a id="iresposta" href="outra-pagina.html" type="hidden"></a>

            <button type="submit" onclick="responder()">Logar</button><!---->
        </form>

    </main>
    <script>
        function responder() {
            var escolha = document.querySelector('input[name="tipoLogin"]:checked').value;

            if (escolha === "1") {

                document.getElementById("iresposta").click();
            } else if (escolha === "0") {

                document.getElementById("iresposta").click();
            }
        }
        
        function resposta() {
            var radioSelecionado = document.querySelector('input[name="tipoLogin"]:checked');

            if (!radioSelecionado) {
                alert("Selecione uma opção antes de enviar o formulário.");
                return false; // Impede o envio do formulário
            }
            return true; // Permite o envio do formulário
        }

    </script>
</body>
</html>
