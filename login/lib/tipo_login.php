<?php
    include('conexao.php');

    if(!isset($_SESSION)){
        session_start();
        var_dump( $cliente['id']);

        die();
        if(isset($_SESSION['id'])){
            var_dump(value: $_SESSION['id']);

            //$usuario= $SESSION['usuario'];
            $id = $_SESSION['id'];
            //die();
            $sql_query = $mysqli->query(query: "SELECT * FROM meus_clientes WHERE id = '$id'") or die($mysqli->$error);
            $usuario = $sql_query->fetch_assoc(); 

            if($usuario['admin'] != 1){

                $_SESSION['id'];
                header(header: "Location: paginas/clientes/cliente_home.php"); 

            }else{

                $_SESSION['id'];

                header(header: "Location: paginas/administrativo/admin_home.php");     
            }
        }else{
            // Destruir todas as variáveis de sessão
            session_unset();
            session_destroy();
            header(header: "Location: ../../../../index.php");  
        }
    }else{
        if(isset($_SESSION['usuario'])){
            $usuario = $_SESSION['usuario'];
            $admin = $_SESSION['admin'];  
            
            if($admin == 0){
                $_SESSION['usuario'];
                header(header: "Location: paginas/clientes/cliente_home.php"); 

            }else if($admin == 1){
                $usuario = $_SESSION['usuario'];
                $admin = $_SESSION['admin'];
                $_SESSION['usuario'];
                $_SESSION['admin']; 

                $id = $_SESSION['usuario'];
                $sql_query = $mysqli->query(query: "SELECT * FROM meus_clientes WHERE id = '$id'") or die($mysqli->$error);
                $usuario = $sql_query->fetch_assoc(); 
                //header("Location: paginas/administrativo/admin_home.php");     
            }else{
                session_unset();
                session_destroy();
                header(header: "Location: ../../../../index.php"); 
            }
        }else{
            // Destruir todas as variáveis de sessão
            session_unset();
            session_destroy();
            header(header: "Location: ../../../../index.php");  
        } 
    }

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        /* Estilo geral para o corpo da página */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        /* Estilo principal para o conteúdo */
        main {
            background-color: white;
            padding: 20px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
        }

        h3 {
            color: #555;
            margin-bottom: 20px;
        }

        /* Estilo para os radio buttons */
        label {
            display: inline-block;
            margin: 10px 20px;
            font-size: 16px;
            cursor: pointer;
        }

        input[type="radio"] {
            margin-right: 8px;
        }

        /* Estilo para o botão de login */
        button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
            margin-top: 20px;
        }

        button:hover {
            background-color: #218838;
        }

        /* Estilo para mensagem de erro ou respostas ocultas */
        #iresposta {
            display: none;
        }
    </style>
</head>
<body>
    <main>
        <h2>Olá, <?php echo $usuario['primeiro_nome']; ?></h2>
        <h3>Escolha o tipo de login:</h3>

        <form id="escolherLoginForm" method="POST" action="paginas/administrativo/admin_home.php" onsubmit="return resposta()">
            <label>
                <input type="radio" name="tipoLogin" value="1"> Admin
            </label>
            <label>
                <input type="radio" name="tipoLogin" value="0"> Usuário
            </label>

            <a id="iresposta" href="outra-pagina.html" type="hidden"></a>

            <button type="submit" onclick="responder()">Logar</button>
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
