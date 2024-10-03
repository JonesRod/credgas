<?php
    include("login/lib/conexao.php");

    if(!isset($_SESSION)) {
        session_start(); 
    }

    if(isset($_SESSION['usuario']) && isset($_SESSION['admin'])) {
        $usuario = $_SESSION['usuario'];
        $admin = $_SESSION['admin'];

        if($admin == 1 ){
            header(header: "Location: login/lib/paginas/administrativo/admin_home.php");       
            exit(); // Importante adicionar exit() após o redirecionamento
        } else {
            header(header: "Location: login/lib/paginas/usuarios/usuario_home.php");
            exit(); // Importante adicionar exit() após o redirecionamento
        }
    }
        
    $msg= false;

   if(isset($_POST['email']) || isset($_POST['senha'])) {
        //echo 'oii';
        $sql_primeiro_registro = "SELECT * FROM socios";
        $registros = $mysqli->query(query: $sql_primeiro_registro) or die("Falha na execução do código SQL: " . $mysqli->error);

        // Verifica se existem registros na tabela 'socios'
        if ($registros->num_rows == 0) {
            header(header: "Location: login/lib/cadastro_usuario.php");
            exit();
        }

        $email = $mysqli->escape_string($_POST['email']);//$mysqli->escape_string SERVE PARA PROTEGER O ACESSO 
        $cpf = $mysqli->escape_string($_POST['email']);
        $senha = $mysqli->escape_string($_POST['senha']);
        

        //echo "oii";
        if(isset($_SESSION['email'])){
            $email = $_SESSION['email'];
            $senha = password_hash(password: $_SESSION['senha'], algo: PASSWORD_DEFAULT);
            $mysqli->query(query: "INSERT INTO senha (email, senha, cpf) VALUES('$email','$senha','$cpf')");
        }
        if(strlen(string: $_POST['email']) == 0 ) {
            $msg= true;
            $msg = "Preencha o campo Usuário.";
            //echo $msg;
        } else if(strlen(string: $_POST['senha']) == 0 ) {
            $msg= true;
            $msg = "Preencha sua senha.";
            //echo $msg;
        } else {

            $verifica = "SELECT * FROM socios WHERE email = '$email' LIMIT 1";
            $sql_verifiva =$mysqli->query(query: $verifica) or die("Falha na execução do código SQL: " . $mysqli->error);
            $usuario = $sql_verifiva->fetch_assoc();
            $quantidade = $sql_verifiva->num_rows;//retorna a quantidade encontrado

            if(($quantidade ) == 1) {

                if(password_verify(password: $senha, hash: $usuario['senha'])) {

                    $admin = $usuario['admin'];

                    if($admin == 1){
                        $_SESSION['usuario'] = $usuario['id'];
                        $_SESSION['admin'] = $admin;
                        //$msg = "1";
                        unset($_POST);
                        session_start(); 
                        header(header: "Location: login/lib/tipo_login.php");
                    }else if($admin != 1){
                        $_SESSION['usuario'] = $usuario['id'];
                        $_SESSION['admin'] = $admin;
                        //$msg = "2";
                        unset($_POST);
                        session_start(); 
                        header(header: "Location: login/lib/paginas/usuarios/usuario_home.php");
                    }    
                }else{
                    $msg= true;
                    $msg = "Usúario ou Senha estão inválidos!";    
                    //echo $msg;
                }
            }else{

                $sql_cpf = "SELECT * FROM socios WHERE cpf = '$cpf' LIMIT 1";
                $sql_query =$mysqli->query($sql_cpf) or die("Falha na execução do código SQL: " . $mysqli->error);
                $usuario = $sql_query->fetch_assoc();
                $quantidade_cpf = $sql_query->num_rows;//retorna a quantidade encontrado
        
                if(($quantidade_cpf) == 1) {
        
                    if(password_verify(password: $senha, hash: $usuario['senha'])) {
        
                        $admin = $usuario['admin'];
        
                        if($admin == 1){
                            $_SESSION['usuario'] = $usuario['id'];
                            $_SESSION['admin'] = $admin;
                            //$msg = "1";
                            unset($_POST);
                            session_start(); 
                            header(header: "Location: login/lib/tipo_login.php");
                        }else if($admin != 1){
                            $_SESSION['usuario'] = $usuario['id'];
                            $_SESSION['admin'] = $admin;
                            //$msg = "2";
                            unset($_POST);
                            session_start(); 
                            header(header: "Location: login/lib/paginas/usuarios/usuario_home.php");
                        }    
                    }else{
                        $msg= true;
                        $msg = "Usúario ou Senha estão inválidos!";   
                        //$mysqli->close(); 
                        //echo $msg;
                    }
                }else{
                    $msg= true;
                    $msg = "O Usúario informado não esta correto ou não está cadastrado!";
                    //$mysqli->close();
                    //echo $msg;
                }
            }
        }
    }
    $id = '1';
    $dados = $mysqli->query(query: "SELECT * FROM config_admin WHERE id = '$id'") or die($mysqli->error);
    $dadosEscolhido = $dados->fetch_assoc();

    //$logo = $dadosEscolhido['logo'];
    if(isset($dadosEscolhido['logo'])) {
        $logo = $dadosEscolhido['logo'];
        
        if($logo == ''){
            $logo = 'login/lib/paginas/arquivos_fixos/avatar_icone.jpg';
        }else{
            $logo = 'login/lib/paginas/arquivos_fixos/'. $logo;
        }
    }
    $mysqli->close();
?>
<!DOCTYPE html>
<html lang="PT-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />    <link rel="stylesheet" href="entrar/lib/css/index.css">
    <link rel="stylesheet" href="login/style/index.css">
    <style>
        img{
            width: 200px;
            height: 200px; /* Certifique-se de que a altura seja igual à largura */
            border-radius: 50%;
        }
    </style>
    <title>Entrar</title>
    <script>
        function toggleSenha() {
            var senhaInput = document.getElementById('senhaInput');
            var toggleSenha = document.getElementById('toggleSenha');

            if (senhaInput.type === 'password') {
                senhaInput.type = 'text';
                toggleSenha.textContent = 'visibility';
            } else {
                senhaInput.type = 'password';
                toggleSenha.textContent = 'visibility_off';
            }
        }
    </script>
</head>
<body>  
    <form id ="login" action="" method="POST" >
        <img src="<?php echo $logo; ?>" alt="">
        <h1 id="ititulo">Entrar</h1>
        <span id="msg"><?php echo $msg; ?></span>
        <p>
            <label id="email" for="iemail">Usuário</label>
            <input required type="text" name="email" id="iemail" placeholder="CPF, CNPJ ou E-mail" oninput="formatarCampo(this)" value="<?php if(isset($_POST['email'])) echo $_POST['email']; ?>">
        </p>
        <p>
            <div id="senhaInputContainer">
                <label id="senha" for="senha">Senha</label>
                <input required type="password" name="senha" id="senhaInput" placeholder="Sua Senha" value="<?php if(isset($_POST['senha'])) echo $_POST['senha']; ?>">
                <span id="toggleSenha" class="material-symbols-outlined" onclick="toggleSenha()">visibility_off</span>
            </div>
        </p>
        <p> 
            <a style="margin-right:10px;" href="cadastro_inicial/cadastro_inicial.html">Cadastre-se.</a> 
            <a style="margin-right:10px;" href="login/lib/Recupera_Senha.php">Esqueci minha Senha!</a> 
        </p>
        <button type="submit">Entrar</button>

    </form>
    <script>
        function formatarCampo(input) {
            let value = input.value.replace(/\D/g, ''); // Remove caracteres não numéricos

            if (/^[0-9]+$/.test(value)) {
                if (value.length > 12) {
                    value = value.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{1})/, '$1.$2.$3/$4-$5');
                } else if (value.length > 11) {
                    value = value.replace(/(\d{2})(\d{3})(\d{3})(\d{1})/, '$1.$2.$3/$4');
                }else if (value.length > 9){
                    value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{1})/, '$1.$2.$3-$4');
                } else if (value.length > 6) {
                    value = value.replace(/(\d{3})(\d{3})(\d{1})/, '$1.$2.$3');
                } else if (value.length > 3) {
                    value = value.replace(/(\d{3})(\d{1})/, '$1.$2');
                }
                input.value = value; 
            }   

            if (value.includes('@')) {
                // Se o valor contiver '@', formatar como E-mail
                let emailElement = document.getElementById('iemail');
                emailElement.textContent = value;
            }
        }
    </script>
</body>
</html>