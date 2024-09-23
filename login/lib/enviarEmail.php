<?php

    //require ('../../vendor/autoload.php');

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;
function enviar_email($destinatario, $assunto, $mensagemHTML){

    require ('src/PHPMailer.php');
    require ('src/Exception.php');
    require ('src/SMTP.php');

    include('conexao.php');

    // Host = Endereço do servidor SMTP do hotmail: smtp-mail.outlook.com
    // Username = hotmail SMTP username: Seu endereço completo do hotmail (ex.: nome@hotmail.com)
    // Password = Senha hotmail SMTP: A senha que você usa para fazer login no hotmail
    // Port = Porta hotmail SMTP (TLS): 587

    $id = '1';
    $dados = $mysqli->query("SELECT * FROM config_admin WHERE id = '$id'") or die($mysqli->error);
    $dadosEscolhido = $dados->fetch_assoc();

    //$destinatario ='batata_jonesrodrigues@hotmail.com';
    //$assunto = 'teste';
    //$mensagemHTML = 'oii';

    $razao = $dadosEscolhido['razao'];
    $email_suporte = $dadosEscolhido['email_suporte'];
    $senha = $dadosEscolhido['senha'];

    //$razao = 'razao';
    //$email_suporte = 'batatajonesrodrigues@gmail.com';
    //$senha = '#@//Jones?';
    //  //batata2023

    if (strstr($email_suporte, "@gmail.com")) {
        //$email = "exemplo@gmail.com"; // Substitua pelo endereço de e-mail que você quer verificar
        $email_host = 'smtp.gmail.com';
        //$senha ='xqurngdmehhkfhob'; //senha para acesso de app
        $num_port = 587;
        //return "Este é um endereço do Gmail.";

    } elseif (strstr($email_suporte, "@hotmail.com")) {
        $email_host= 'smtp-mail.outlook.com';
        $num_port = 587;    
        //return "Este é um endereço do Hotmail.";
    }  elseif (strstr($email_suporte, "@yahoo.com")) {
        $email_host= 'smtp.mail.yahoo.com';
        $num_port = 587;    
        //return "Este é um endereço do Hotmail.";
    } else {
       return false;
    }
    //'batata_jonesrodrigues@hotmail.com'
    $mail = new PHPMailer(true);
    try{
        $mail->isSMTP();
        //$mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->Host = $email_host;
        $mail->Port =  $num_port;
        $mail->SMTPAuth = true;
        $mail->Username = $email_suporte;
        $mail->Password = $senha;
        //$mail->SMTPSecure = 'tls';//usado no gmail
        //$mail->SMTPSecure = false;
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';

        $mail->setFrom($email_suporte, $razao);
        $mail->addAddress($destinatario);
        $mail->Subject = $assunto;

        $mail->Body = $mensagemHTML;

        if ($mail->send()) {
            //echo 'E-mail enviado com sucesso!';
            return true;
        } else {
            //echo 'E-mail não enviado!';
            return false;
        }

    } catch (Exception $e){
        //echo "Erro ao enviar o e-mail: {$mail->ErrorInfo}";
        return false;
    }
    //var_dump($email_host);
}
?>
