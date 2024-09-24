<?php

    $msg= false;
    $minimo = 8;
    $maximo = 16;

if (isset($_POST['email']) || isset($_POST['senhaAtual'])) {
    if(strlen($_POST['senhaAtual']) == 0 ) {
        $msg = "Preencha sua senha Atual."; 

    } else if(strlen($_POST['novaSenha']) == 0 ) {
        $msg = "Preencha o campo Nova Senha.";

    }else if(strlen($_POST['novaSenha']) < $minimo ) {
        $msg = "Nova senha deve ter no minimo 8 digito.";
        
    }else if(strlen($_POST['novaSenha']) > $maximo ) {
        $msg = "Nona senha deve ter no maximo 16 digito.";

    }else if(strlen($_POST['confSenha']) == 0 ) {
        $msg = "Preencha o campo confirmar Senha.";

    }else if(strlen($_POST['confSenha']) < $minimo) {
        $msg = "Campo Confirmar Senha deve ter no minimo 8 digito.";

    }else if(strlen($_POST['confSenha']) > $maximo) {
        $msg = "Campo Confirmar Senha deve ter no maximo 16 digito.";

    }else{

        include("conexao.php");
        include('enviarEmail.php');

        $email = $mysqli->escape_string($_POST['email']);//$mysqli->escape_string SERVE PARA PROTEGER O ACESSO 
        $senha = $mysqli->escape_string($_POST['senhaAtual']);
        $novaSenha = $_POST['novaSenha'];

        $sql_code = "SELECT * FROM socios WHERE email = '$email' LIMIT 1";
        $sql_query =$mysqli->query($sql_code) or die("Falha na execução do código SQL: " . $mysqli->$error);
        $usuario = $sql_query->fetch_assoc();
        $quantidade = $sql_query->num_rows;//retorna a quantidade encontrado
       
        if(($quantidade ) == 1) {

            if(password_verify($senha, $usuario['senha'])) {

                $_SESSION['usuario'] = $usuario['id'];
                $nome = $usuario['apelido'];
                $_SESSION['admin'] = $usuario['admin'];

                $nova_senha_criptografada = password_hash($novaSenha, PASSWORD_DEFAULT);

                $sql_code = "UPDATE socios
                SET senha = '$nova_senha_criptografada'
                WHERE email = '$email'";

                $editado = $mysqli->query($sql_code) or die($mysqli->$error);

                if($editado) {   
                    $msg = "Nova senha definida com sucesso. Você será redirecionado para a tele de login.";
                    
                    enviar_email($email, "Sua nova senha de acesso da plataforma", "
                    <h1>Seja bem vindo " . $nome . "</h1>
                    <p><b>Seu E-mail de acesso é: </b> $email</p>
                    <p><b>Sua senha de acesso é: </b> $novaSenha</p>
                    <p><b>Para redefinir sua senha </b><a href='redefinir_senha.php'>clique aqui.</a></p>
                    <p><b>Para entrar </b><a href='../../index.php'>clique aqui.</a></p>");
                    
                    unset($_POST);

                    header("refresh: 5; paginas/usuarios/usuario_logout.php");
                }
                    
            }else{
            $msg = "Senha inválida!";
            }
        }else{
            $msg = "O e-mail informado não esta correto ou não está cadastrado!";
        }
    }      
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>redefinição de senha</title>
    <script>
        function ver_senha_atual() {
            var senhaInput = document.getElementById('isenha_atual');
            var ver_senha_atual = document.getElementById('ver_senha_atual');

            if (senhaInput.type === 'password') {
                senhaInput.type = 'text';
                ver_senha_atual.textContent = '👁️';
            } else {
                senhaInput.type = 'password';
                ver_senha_atual.textContent = '👁️';
            }
        }
        function ver_nova_senha() {
            var iNova_senha = document.getElementById('iNova_senha');
            var ver_nova_senha = document.getElementById('ver_nova_senha');

            if (iNova_senha.type === 'password') {
                iNova_senha.type = 'text';
                ver_nova_senha.textContent = '👁️';
            } else {
                iNova_senha.type = 'password';
                ver_nova_senha.textContent = '👁️';
            }
        }
        function ver_conf_senha() {
            var iConf_senha = document.getElementById('iConf_senha');
            var ver_conf_senha = document.getElementById('ver_conf_senha');

            if (iConf_senha.type === 'password') {
                iConf_senha.type = 'text';
                ver_conf_senha.textContent = '👁️';
            } else {
                iConf_senha.type = 'password';
                ver_conf_senha.textContent = '👁️';
            }
        }
    </script>
</head>
<body>
    <h2>Redefina sua nova Senha</h2>
    <form action="" method="POST">
        <span>
            <?php 
                echo $msg; 
            ?>
        </span>
        <p>
            <label for="">E-mail: </label>
            <input value="<?php if(isset($_POST['email'])) echo $_POST['email']; ?>" required type="text" name="email">
        </p>
        <p>
            <label for="isenha_atual">Senha Atual: </label>
            <input required type="password" name="senhaAtual" id="isenha_atual" value="<?php if(isset($_POST['senhaAtual'])) echo $_POST['senhaAtual']; ?>">
            <span id="ver_senha_atual" onclick="ver_senha_atual()">👁️</span>
        </p>
        <p>
            <label for="iNova_senha">Nova Senha: </label>
            <input required placeholder="Minimo 8 digitos" type="password" id="iNova_senha" name="novaSenha" value="<?php if(isset($_POST['novaSenha'])) echo $_POST['novaSenha']; ?>">
            <span id="ver_nova_senha" onclick="ver_nova_senha()">👁️</span>
        </p>
        <p>
            <label for="iConf_senha">Confirmar Senha: </label>
            <input required placeholder="Minimo 8 digitos" type="password" id="iConf_senha" name="confSenha" value="<?php if(isset($_POST['confSenha'])) echo $_POST['confSenha']; ?>">
            <span id="ver_conf_senha" onclick="ver_conf_senha()">👁️</span>
        </p>
        <a href="paginas/usuarios/usuario_logout.php">Ir para login</a>
        <button type="submit">Salvar</button>
    </form>
</body>
</html>