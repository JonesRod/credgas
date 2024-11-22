<?php

    //require ('../../vendor/autoload.php');

    use PHPMailer\PHPMailer\PHPMailer;
    //use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;
function enviar_email($destinatario, $assunto, $mensagemHTML): bool{

    require ('src/PHPMailer.php');
    require ('src/Exception.php');
    require ('src/SMTP.php');

    include('conexao.php');

    $id = '1';
    $dados = $mysqli->query("SELECT * FROM config_admin WHERE razao != '' ORDER BY data_alteracao DESC LIMIT 1") or die($mysqli->error);
    $dadosEscolhido = $dados->fetch_assoc();

    //$destinatario ='batata_jonesrodrigues@hotmail.com';
    //$assunto = 'teste';
    //$mensagemHTML = 'oii';

    $razao = $dadosEscolhido['razao'];
    $email_suporte = $dadosEscolhido['email_suporte'];
    $senha = 'xqurngdmehhkfhob';//$dadosEscolhido['senha_email'];

    //$razao = 'razao';
    //$email_suporte = 'batatajonesrodrigues@gmail.com';
    //$senha = '#@//Jones?'; xqurngdmehhkfhob 
    //  //batata2023

    if (strstr(haystack: $email_suporte, needle: "@gmail.com")) {
        //$email = "exemplo@gmail.com"; // Substitua pelo endereço de e-mail que você quer verificar
        $email_host = 'smtp.gmail.com';
        //$senha ='xqurngdmehhkfhob'; //senha para acesso de app
        $num_port = 587;
        //return "Este é um endereço do Gmail.";

    } elseif (strstr(haystack: $email_suporte, needle: "@hotmail.com")) {
        $email_host= 'smtp-mail.outlook.com';
        $num_port = 587;    
        //return "Este é um endereço do Hotmail.";
    }  elseif (strstr(haystack: $email_suporte, needle: "@yahoo.com")) {
        $email_host= 'smtp.mail.yahoo.com';
        $num_port = 587;    
        //return "Este é um endereço do Hotmail.";
    } else {
       return false;
    }
    //'batata_jonesrodrigues@hotmail.com'
    $mail = new PHPMailer(exceptions: true);
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
        $mail->isHTML(isHtml: true);
        $mail->CharSet = 'UTF-8';

        $mail->setFrom(address: $email_suporte, name: $razao);
        $mail->addAddress(address: $destinatario);
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
