<?php 

    include('../../../../conexao.php');

    if(!isset($_SESSION)){
        session_start(); 

        if(isset($_SESSION['usuario'])){

            if (isset($_POST["tipoLogin"])) {
                $usuario_sessao = $_SESSION['usuario'];
                $valorSelecionado = $_POST["tipoLogin"];
                $admin = $valorSelecionado;

                if($admin != 1){
                    header("Location: ../../../usuarios/usuario_home.php");      
                } else {
                    $_SESSION['usuario'];
                    $_SESSION['admin'];  
                }
            }  

        } else {
            session_unset();
            session_destroy(); 
            header("Location: ../../../../../../index.php");  
        }
    } else {
        if(isset($_SESSION['usuario'])){

            if (isset($_POST["tipoLogin"])) {
                $usuario_sessao = $_SESSION['usuario'];
                $valorSelecionado = $_POST["tipoLogin"];
                $admin = $valorSelecionado;

                if($admin != 1){
                    header("Location: ../../../usuarios/usuario_home.php");      
                } else {
                    $_SESSION['usuario'];
                    $_SESSION['admin'];  
                }
            }  

        } else {
            session_unset();
            session_destroy(); 
            header("Location: ../../../../../../index.php");  
        }
 
    }

    $msg1= false;
    $msg2= false;

    if(isset($_POST['senha'])) {
        
        $senha_usuario = $mysqli->escape_string($_POST['senha']);
        
        if(strlen($_POST['senha']) == 0 ) {
            $msg1= true;
            $msg2= true;
            $msg1 = "";
            $msg2 = "Preencha sua senha.";
        } else {

            $sql_code = "SELECT * FROM socios WHERE admin = '1'";
            $sql_query =$mysqli->query($sql_code) or die("Falha na execução do código SQL: " . $mysqli->$error);
            $usuario_sessao = $sql_query->fetch_assoc();
            $quantidade = $sql_query->num_rows;//retorna a quantidade encontrado

            if(($quantidade ) >= 1) {

                if(password_verify($senha_usuario, $usuario_sessao['senha'])) {
                
                    //pega os Nomes dos dados de acesso do banco
                    $localhost = $mysqli->real_escape_string($host);
                    $usuario_log = $mysqli->real_escape_string($usuario);
                    $senha_log = $mysqli->real_escape_string($senha);
                    $database = $mysqli->real_escape_string($banco);

                    //Defina as credenciais do banco de dados
                    //$host = "localhost";
                    //$usuario = "root";
                    //$senha = "";
                    //$banco = "associacao_40ribas";

                    $mysqli = new mysqli($localhost, $usuario_log, $senha_log, $database);

                    if ($mysqli->connect_error) {
                        die("Falha na conexão: " . $mysqli->connect_error);
                    }

                    $backupFile = 'backup_' . date('Y-m-d') . '.sql';
                    $database = $mysqli->real_escape_string($banco);

                    //var_dump($usuario);
                    //var_dump($senha);
                    //var_dump($host);
                    //var_dump($database);
                    //var_dump($backupFile); 

                    // Defina o caminho para o mysqldump (escolha um dos dois caminhos disponíveis)
                    $mysqldump_path = 'C:\xampp\mysql\bin\mysqldump.exe';
                    //$mysqldump_path = 'C:\Program Files\MySQL\MySQL Server 8.1\bin\mysqldump.exe';
                    
                    // Defina o caminho e nome do arquivo de backup
                    $backupFile = 'backup/backup_' . date('Y-m-d') . '.sql';
                    
                    // Comando para realizar o backup
                    $commandBackup = "$mysqldump_path --user=$usuario_log --password=$senha_log --host=$localhost $database > $backupFile";
                    
                    // Executa o comando de backup
                    exec($commandBackup, $output, $return);
                    
                    if ($return === 0) {
                        $msg1 = "Backup realizado com sucesso.";
                        $msg2 = ""; 
                        header("refresh: 5;../inicio.php"); 

                    } else {
                        $msg1 = ""; 
                        $msg2 = "Erro ao realizar o backup.";
                        header("refresh: 5;../inicio.php"); 
                    }
                    //die();  

                }else{
                    $msg1 = "";
                    $msg2 = "Senaha inválida!";   
                    //echo $msg;
                }
            }else{
                $msg = "";  
                //echo 'oii';
                // Fecha a conexão
                $mysqli->close();
            }
        }
    } 
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Resetar Sócios</title>
    <style>
        
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f4f4f4;
        }

        #iform {
            max-width: 400px;
            margin: 50px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1); /*sombra*/

        }

        #ititulo {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }
        #msg1 {
            color: green;
        }

        #msg2 {
            color: red;
        }

        label {
            font-size: 16px;
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
            text-align: left;
            margin-left: 15px;
        }

        #senhaInput{
            width: 85%;
            padding: 5px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            text-align: left;
            display: block;
            margin-left: 15px;
        }

        #senhaInputContainer {
            position: relative;
        }

        #toggleSenha {
            position: absolute;
            right: 0px;
            top: 75%;
            transform: translateY(-50%);
            cursor: pointer;
        }

        button {
            padding: 10px 20px;
            font-size: 18px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        a {
            text-decoration: none;
            color: #007bff;
            font-size: 16px;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
    <script>
        function toggleSenha() {
            var senhaInput = document.getElementById('senhaInput');
            var toggleSenha = document.getElementById('toggleSenha');

            if (senhaInput.type === 'password') {
                senhaInput.type = 'text';
                toggleSenha.textContent = '👁️';
            } else {
                senhaInput.type = 'password';
                toggleSenha.textContent = '👁️';
            }
        }
    </script>
</head>
<body>
    <form id ="iform" action="" method="POST" >
        <h1 id="ititulo">Confirmação para Resetar</h1>
        <p>
            Você realmente deseja resetar e excluir todos os dados armazenado?
        </p>
        <p>
            Caso click em 'Reset': Os dados serão todos apagados, mas estarão disponiveis no Backup.
        </p>
        <span id="msg1"><?php echo $msg1; ?></span>
        <span id="msg2"><?php echo $msg2; ?></span>
        <p>
            <div id="senhaInputContainer">
                <label for="">Senha do admin: </label>
                <input required placeholder="Minimo 8 digitos" id="senhaInput" value="<?php if(isset($_POST['senha'])) echo $_POST['senha']; ?>" type="password" name="senha">
                <span id="toggleSenha" onclick="toggleSenha()">👁️</span>
            </div>
        </p>
        <p>
            <button type="submit">Reset</button>
        </p>
    </form>
    <a href="../inicio.php"  style="margin-left: 10px; margin-right: 10px;">Voltar</a>
</body>
</html>