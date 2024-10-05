<?php

    $msg1 = false;
    $msg2 = false;

    include('conexao.php');
    include('generateRandomString.php');
    include('enviarEmail.php');

if(isset($_POST['email'])) {

    if(strlen(string: $_POST['email']) == 0 ) {
        $msg1 = "Preencha ocampo E-mail.";
        $msg2 = '';
    } else {

        $email = $mysqli->escape_string($_POST['email']);

        // Verifica se o email já está registrado
        $sql_email = $mysqli->query(query: "SELECT * FROM meus_clientes WHERE email = '$email'");
        $email_registrado = $sql_email->num_rows;

        //var_dump(value: $email_registrado);
        //var_dump(value: $_POST);

        if(($email_registrado ) == 1) {
            // Obtém os dados da linha correspondente, incluindo o 'id'
            $cliente = $sql_email->fetch_assoc(); // Pega a linha como um array associativo
            $id_cliente = $cliente['id'];
                
            //if($id_cliente['id']) {

                $nova_senha = generateRandomString(length: 6);
                $nova_senha_criptografada = password_hash(password: $nova_senha, algo: PASSWORD_DEFAULT);
                //$id_usuario = $email_registrado['id'];

                $mysqli->query(query: "UPDATE meus_clientes SET senha = '$nova_senha_criptografada' WHERE id = '$id_cliente'");
                enviar_email(destinatario: $email, assunto: "Sua nova senha do seu site", mensagemHTML: "
                <h1>Olá " . $cliente['primeiro_nome'] . "</h1>
                <p>Uma nova senha foi definida para a sua conta.</p>
                <p><b>Nova senha: </b>$nova_senha</p>
                <p><b>Para redefinir sua senha </b><a href='redefinir_senha.php'>clique aqui.</a></p>
                <p><b>Para entrar </b><a href='../../index.php'>clique aqui.</a></p>");
                
                
                $msg2 = "Já enviamos sua nova senha em seu E-mail.";
                $msg1 = "Clique <a href='redefinir_senha.php'>aqui</a> e redefina sua senha.";
                ;

                //header("refresh: 5; ../../index.php");
            //}    
        }else{
            $msg1 = "Não existe nenhum Usuario cadastrado com esse e-mail!";
            $msg2 = '';

        }
    }  
}
 
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body{
            text-align: center;
        }
    </style>
    <title>Recuperar Senha</title>
</head>
<body>
    <h1>Recupere sua Senha.</h1>

    <span style="color: green;"><?php echo $msg2; ?></span>
    <span style="color: red;"><?php echo $msg1; ?></span>

    <form action="" method="POST">
        <p>  
            <label for="">Digite E-mail cadastrado</label>
            <input type="email" name="email">
        </p>
        <a style="margin-right:40px;" href="../../index.php">Voltar</a> 
        <button type="submit">Enviar</button>
    </form>
</body>
</html>