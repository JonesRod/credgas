<?php
    include('conexao.php');
    include('generateRandomString.php');
    include('enviarEmail.php');

    $sql_primeiro_registro = "SELECT * FROM socios";
    $registros = $mysqli->query($sql_primeiro_registro) or die("Falha na execução do código SQL: " . $mysqli->$error);

    // Verifica se existem registros na tabela 'socios'
    if ($registros->num_rows > 0) {
        // Existem registros, continuar com o código atual
        // ...
    //} else {
        // Não existem registros, redirecionar para a página de registro como administrador
        header("Location: ../../index.php");
        exit();
    }

$msg = false;

if(isset($_POST['nome']) || isset($_POST['email'])) {

    if(strlen($_POST['nome']) == 0 ) {
        $msg = true;
        $msg = "Preencha o campo do Nome.";
        echo $msg;
    }else if(strlen($_POST['email']) == 0 ) {
        $msg = true;
        $msg = "Preencha ocampo E-mail.";
        echo $msg;
    } else {

        $email = $mysqli->escape_string($_POST['email']);
        $nome = $mysqli->escape_string($_POST['nome']);

        $sql_query = $mysqli->query("SELECT * FROM socios WHERE email = '$email'");
        $result = $sql_query->fetch_assoc();
        $registro = $sql_query->num_rows;

        if(($registro ) == 0) {

            $senha = $_POST['confSenha'];
            $senha_criptografada = password_hash($senha, PASSWORD_DEFAULT);

            $sql_socio = "INSERT INTO socios (data, admin, apelido, email, senha, status) 
            VALUES(NOW(), '1', '$nome','$email','$senha_criptografada', 'ATIVO')";
            $mysqli->query($sql_socio) or die($mysqli->$error);

            //if ($mysqli->query($sql_socio)) {
            $id_admin = $mysqli->insert_id; // Obtém o ID do último registro inserido
            
            // Agora, você pode usar $id_socio como necessário
            // Por exemplo, para salvar o ID do admin
            $sql_config_admin = "INSERT INTO config_admin (id, id_admin, data_alteracao, nome_tesoureiro)
            VALUES('1', '$id_admin',NOW(), '$nome')";
            $mysqli->query($sql_config_admin) or die($mysqli->error);

            $sql_historico_config_admin = "INSERT INTO historico_config_admin (id, id_admin, data_alteracao, nome_tesoureiro)
            VALUES('1', '$id_admin',NOW(), '$nome')";
            $deu_certo = $mysqli->query($sql_historico_config_admin) or die($mysqli->error);
                
            //}

            if($deu_certo){
                enviar_email($email, "Sua nova senha de acesso na plataforma", "
                <h1>Seja bem vindo " . $nome . "</h1>
                <p><b>Seu E-mail de acesso é: </b> $email</p>
                <p><b>Sua senha de acesso é: </b> $senha</p>
                <p><b>Para redefinir sua senha </b><a href='redefinir_senha.php'>clique aqui.</a></p>
                <p><b>Para entrar </b><a href='../../index.php'>clique aqui.</a></p>");

                unset($_POST);

                $msg = "A confirmação de seu cadastrado será enviada para esse e-mail!";
                echo $msg;

                header("refresh: 5; ../../index.php"); //Atualiza a pagina em 5s e redireciona apagina
            } else {
                die($mysqli->error); // Se houver um erro na consulta
            }
        }
        if(($registro ) != 0) {
            $msg = true;
            $msg = "Já existe um Usuario cadastrado com esse e-mail!";
            echo $msg;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
 
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f4f4f4;
        }

        #login {
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

        #msg {
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
        #inome{
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
        #iemail{
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
        #senhaInput1{
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
        #senhaInput2{
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

        #toggleSenha1 {
            position: absolute;
            right: 0px;
            top: 30%;
            transform: translateY(-50%);
            cursor: pointer;
        }
        #toggleSenha2 {
            position: absolute;
            right: 0px;
            top: 85%;
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
        function toggleSenha1() {
            var senhaInput1 = document.getElementById('senhaInput1');
            var toggleSenha2 = document.getElementById('toggleSenha1');

            if (senhaInput1.type === 'password') {
                senhaInput1.type = 'text';
                toggleSenha1.textContent = '👁️';
            } else {
                senhaInput1.type = 'password';
                toggleSenha1.textContent = '👁️';
            }
        }
        function toggleSenha2() {
            var senhaInput2 = document.getElementById('senhaInput2');
            var toggleSenha2 = document.getElementById('toggleSenha2');

            if (senhaInput2.type === 'password') {
                senhaInput2.type = 'text';
                toggleSenha2.textContent = '👁️';
            } else {
                senhaInput2.type = 'password';
                toggleSenha2.textContent = '👁️';
            }
        }
    </script>
    <title>Cadastro de Usuario</title>
</head>
<body>
    <form id ="login" method="POST" action="">
        <h1>Cadastre-se</h1>
        <p>
            <label id="">Nome:</label>
            <input required id="inome" placeholder="Primeiro Nome" value="<?php if(isset($_POST['nome'])) echo $_POST['nome']; ?>" name="nome" type="text"><br>
        </p>
        <p>
            <label id="email">E-mail:</label>
            <input required id="iemail" placeholder="E-mail" value="<?php if(isset($_POST['email'])) echo $_POST['email']; ?>" name="email" type="email"><br>
        </p>
        <p>
            <div id="senhaInputContainer">
                <label for="">Senha: </label>
                <input required placeholder="Minimo 8 digitos" id="senhaInput1" value="<?php if(isset($_POST['senha'])) echo $_POST['senha']; ?>" type="password" name="senha">
                <span id="toggleSenha1" onclick="toggleSenha1()">👁️</span>

                <label for="">Confirmar Senha: </label>
                <input placeholder="Minimo 8 digitos" id="senhaInput2" value="<?php if(isset($_POST['confSenha'])) echo $_POST['confSenha']; ?>" type="password" name="confSenha">
                <span id="toggleSenha2" onclick="toggleSenha2()">👁️</span>
            </div>
        </p>
        <p>
            <a href="paginas/administrativo/admin_logout.php">Voltar para tela de login</a>
            <button type="submit">Cadastrar</button>
        </p>
    </form>
</body>
</html> 
