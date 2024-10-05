<?php

    $msg= false;
    $minimo = 6;
    $maximo = 16;

if (isset($_POST['email']) || isset($_POST['senha_atual'])) {
    if(strlen(string: $_POST['senha_atual']) == 0 ) {
        $msg = "Preencha sua senha Atual."; 

    } else if(strlen(string: $_POST['Nova_senha']) == 0 ) {
        $msg = "Preencha o campo Nova Senha.";

    }else if(strlen(string: $_POST['Nova_senha']) < $minimo ) {
        $msg = "Nova senha deve ter no minimo 8 digito.";
        
    }else if(strlen(string: $_POST['Nova_senha']) > $maximo ) {
        $msg = "Nona senha deve ter no maximo 16 digito.";

    }else if(strlen(string: $_POST['Conf_senha']) == 0 ) {
        $msg = "Preencha o campo confirmar Senha.";

    }else if(strlen(string: $_POST['Conf_senha']) < $minimo) {
        $msg = "Campo Confirmar Senha deve ter no minimo 8 digito.";

    }else if(strlen(string: $_POST['Conf_senha']) > $maximo) {
        $msg = "Campo Confirmar Senha deve ter no maximo 16 digito.";

    }else{

        include("conexao.php");
        include('enviarEmail.php');

        $email = $mysqli->escape_string($_POST['email']);//$mysqli->escape_string SERVE PARA PROTEGER O ACESSO 
        $senha = $mysqli->escape_string($_POST['senha_atual']);
        $novaSenha = $_POST['Nova_senha'];

        $verifica = "SELECT * FROM meus_clientes WHERE email = '$email' LIMIT 1";
        $sql_verifica =$mysqli->query(query: $verifica) or die("Falha na execução do código SQL: " . $mysqli->error);
        $cliente = $sql_verifica->fetch_assoc();
        $quantidade = $sql_verifica->num_rows;//retorna a quantidade encontrado

        //var_dump(value: $cliente['senha_login']);        
        
        if(($quantidade ) == 1) {
            //var_dump(value: $cliente['id']);

            if(password_verify($senha, $cliente['senha_login'])) {

                $_SESSION['cliente'] = $cliente['id'];
                $nome = $cliente['primeiro_nome'];
                $_SESSION['admin'] = $cliente['admin'];

                $nova_senha_criptografada = password_hash(password: $novaSenha, algo: PASSWORD_DEFAULT);

                $sql_code = "UPDATE meus_clientes
                SET senha_login = '$nova_senha_criptografada'
                WHERE email = '$email'";

                $editado = $mysqli->query(query: $sql_code) or die($mysqli->$error);

                if($editado) {   
                    $msg = "Nova senha definida com sucesso. Você será redirecionado para a tele de login.";
                    
                    enviar_email(destinatario: $email, assunto: "Sua nova senha de acesso da plataforma", mensagemHTML: "
                    <h1>Seja bem vindo " . $nome . "</h1>
                    <p><b>Seu E-mail de acesso é: </b> $email</p>
                    <p><b>Sua senha de acesso é: </b> $novaSenha</p>
                    <p><b>Para redefinir sua senha </b><a href='redefinir_senha.php'>clique aqui.</a></p>
                    <p><b>Para entrar </b><a href='../../index.php'>clique aqui.</a></p>");
                    
                    unset($_POST);

                    header(header: "refresh: 5; paginas/clientes/cliente_logout.php");
                }
                    
            }else{
                $msg = "Sua senha atual está incorreta!";
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
            var senhaInput = document.getElementById('senha_atual');
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
            var Nova_senha = document.getElementById('Nova_senha');
            var ver_nova_senha = document.getElementById('ver_nova_senha');

            if (Nova_senha.type === 'password') {
                Nova_senha.type = 'text';
                ver_nova_senha.textContent = '👁️';
            } else {
                Nova_senha.type = 'password';
                ver_nova_senha.textContent = '👁️';
            }
        }
        function ver_conf_senha() {
            var Conf_senha = document.getElementById('Conf_senha');
            var ver_conf_senha = document.getElementById('ver_conf_senha');

            if (Conf_senha.type === 'password') {
                Conf_senha.type = 'text';
                ver_conf_senha.textContent = '👁️';
            } else {
                Conf_senha.type = 'password';
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
            <label for="senha_atual">Senha Atual: </label>
            <input required type="password" name="senha_atual" id="senha_atual" value="<?php if(isset($_POST['senha_atual'])) echo $_POST['senha_atual']; ?>">
            <span id="ver_senha_atual" onclick="ver_senha_atual()">👁️</span>
        </p>
        <p>
            <label for="Nova_senha">Nova Senha: </label>
            <input required placeholder="Minimo 6 digitos" type="password" id="Nova_senha" name="Nova_senha" value="<?php if(isset($_POST['Nova_senha'])) echo $_POST['Nova_senha']; ?>">
            <span id="ver_nova_senha" onclick="ver_nova_senha()">👁️</span>
        </p>
        <p>
            <label for="Conf_senha">Confirmar Senha: </label>
            <input required placeholder="Minimo 6 digitos" type="password" id="Conf_senha" name="Conf_senha" value="<?php if(isset($_POST['Conf_senha'])) echo $_POST['Conf_senha']; ?>">
            <span id="ver_conf_senha" onclick="ver_conf_senha()">👁️</span>
        </p>

        <a href="paginas/clientes/cliente_logout.php">Ir para login</a>
        <button type="submit">Salvar</button>
    </form>
</body>
</html>