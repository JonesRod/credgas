<?php
/*function enviarArquivo($error, $name, $tmp_name) {
    // para obrigar a ter foto
    if($error)
        //echo("Falha ao enviar arquivo");
        return false;

    $pasta = "arquivos/foto_perfil/";
    $nomeDoArquivo = $name;
    $novoNomeDoArquivo = uniqid();
    $extensao = strtolower(pathinfo($nomeDoArquivo, PATHINFO_EXTENSION));

    $path = $pasta . $novoNomeDoArquivo . "." . $extensao;
    $deu_certo = move_uploaded_file($tmp_name, "../".$path);
    if ($deu_certo) {
        return $path;
    } else
        return false;
}*/

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitação</title>
    <?php
        $erro = false;
        if(isset($_POST['email'])) {
            //include('upload.php');
            include('../login/lib/conexao.php');
            include('../login/lib/enviarEmail.php');
            include('../login/lib/generateRandomString.php');

            $primeiro_nome = $mysqli->escape_string($_POST['primeiro_nome']);
            //$apelido = $mysqli->escape_string($_POST['apelido']);

            // Separar o nome do sobrenome
            //$partesNome = explode(separator: ' ', string: $nome_completo);
            //$primeiroNome = $partesNome[0];
            //$sobrenome = end($partesNome);
            //if($apelido == ''){
                //$apelido = $primeiroNome;
            //}
            $cpf = $mysqli->escape_string($_POST['cpf']);
            $nascimento = $mysqli->escape_string($_POST['nascimento']);
            $cep = $mysqli->escape_string($_POST['cep']);
            $uf = $mysqli->escape_string($_POST['uf']);
            $cidade = $mysqli->escape_string($_POST['cidade']);        
            $celular1 = $mysqli->escape_string($_POST['celular1']);
            $celular2 = $mysqli->escape_string($_POST['celular2']);
            $email = $mysqli->escape_string($_POST['email']);
            
            $hoje = new DateTime(datetime: 'now');
            $dataStr = $nascimento;
            $dataFormatada = DateTime::createFromFormat(format: 'd/m/Y', datetime: $dataStr);

            if ($dataFormatada !== false) {
            // echo $dataFormatada->format('Y-m-d'); // Formato de data: yyyy-mm-dd
            $dataFormatada->format(format: 'Y-m-d');
            //$nasc = new DateTime($dataFormatada);
            } else {
            // echo "Formato de data inválido.";
            }
            //echo $dataFormatada->format('Y-m-d');
            $nasc = $dataFormatada->format(format: 'Y-m-d');
            $idade = $hoje->diff(targetObject: $dataFormatada);

            $id = '1';
            $dados = $mysqli->query(query: "SELECT * FROM config_admin WHERE id = '$id'") or die($mysqli->$error);
            $dadosEscolhido = $dados->fetch_assoc();
            $idade_minima = $dadosEscolhido['idade_minima'];

            $idade_minima = '18';
            $anos_idade = $idade->y;

            $dataAtual = date(format: 'Y-m-d'); // Obtém a data atual no formato ano-mês-dia
            $validade = date(format: 'Y-m-d', timestamp: strtotime(datetime: $dataAtual . '+'.' days')); // Adiciona 365 dias

            //echo "Diferença de " . $idade->d . " dias";
            //echo " e " . $idade->m . " meses";
            //echo " e " . $idade->y . " anos.";
            
            //var_dump(value: $_POST);

            if(($anos_idade) >= $idade_minima) {
                //echo "Você tem " . $idade->y . " anos, ". $idade->m ." meses e ". $idade->d ." dias.";

                //$cpf = $mysqli->real_escape_string($cpf);
                //$email = $mysqli->real_escape_string($email);
                
                // Verifica se o CPF já está registrado
                $sql_cpf = $mysqli->query(query: "SELECT * FROM meus_clientes WHERE cpf = '$cpf'");
                $cpf_registrado = $sql_cpf->num_rows;
                
                // Verifica se o email já está registrado
                $sql_email = $mysqli->query(query: "SELECT * FROM meus_clientes WHERE email = '$email'");
                $email_registrado = $sql_email->num_rows;
                

                //var_dump(value: $_POST);
                //die();

                if(($cpf_registrado) == 0) {
            
                    if(($email_registrado ) == 0) {

                        $senha = generateRandomString(length: 6);
                        $senha_criptografada = password_hash(password: $senha, algo: PASSWORD_DEFAULT);
                        $status = 'INATIVO';
                        $status_crediario = 'INATIVO';
                        
                        $sql_code = "INSERT INTO meus_clientes (
                        data_cadastro, 
                        primeiro_nome, 
                        cpf, 
                        nascimento, 
                        celular1, 
                        celular2, 
                        email,
                        senha_login,
                        cep, 
                        uf, 
                        cidade, 
                        status, 
                        status_crediario) 
                        VALUES (
                        NOW(),
                        '$primeiro_nome',
                        '$cpf',
                        '$nascimento',
                        '$celular1',
                        '$celular2',
                        '$email',
                        '$senha_criptografada',
                        '$cep', 
                        '$uf', 
                        '$cidade', 
                        '$status', 
                        '$status_crediario')";
                        
                        $deu_certo = $mysqli->query(query: $sql_code) or die($mysqli->error);

                        if($deu_certo){
                            $msg = true;
                            $msg = "Cadastro realizado com sucesso!";
                            $msg1 = "";
                            $msg2 = "";
                            //echo $msg;

                            enviar_email(destinatario: $email, assunto: "Cadastro realizado com sucesso!", mensagemHTML: "
                            <h1>Olá Sr. " . $primeiro_nome . ", seja bem vindo!</h1>
                            <p><b>Você pode logar com seu CPF,CNPJ ou E-MAIL.</p>
                            <p><b>Senha: </b>$senha</p>
                            <p><b>Para redefinir sua senha </b><a href='../../login/lib/redefinir_senha.php'>clique aqui.</a></p>
                            <p><b>Para entrar </b><a href='../index.php'>clique aqui.</a></p>
                            <p>Menssagem automatica. Não responda!</p>");

                            unset($_POST);
                            $mysqli->close();
                            header(header: "refresh: 5;../index.php"); //Atualiza a pagina em 5s e redireciona apagina
                        }    
                    }else{
                        $msg = "Já existe um cadastrado com esse e-mail!";
                        $msg1 = "";
                        $msg2 = "";
                        //echo $msg;
                        $mysqli->close();
                        header(header: "refresh: 10;../index.php");           
                    }                                                 
                                                                                
                }else{
                    $msg = "Já existe um cadastrado com esse CPF!";
                    $msg1 = "";
                    $msg2 = "";
                    //echo $msg;
                    $mysqli->close();
                    header(header: "refresh: 10;../index.php");

                }
            }else{

                $msg = "Você tem " . $idade->y . " anos, ". $idade->m ." meses e ". $idade->d ." dias.";
                $msg1 = "Você ainda não tem idade o suficiente para se cadastrar!";
                $msg2 = "Complete a idade minima que é ".$idade_minima ." anos e tente novamente.";
                unset($_POST);
                $mysqli->close();
                header(header: "refresh: 10;../index.php");
            }
        }else {
            exit;
        }
    ?>
</head>
<body>
    <div id="msg">
        <p><span><?php echo $msg; ?></span></p>
        <p><span><?php echo $msg1; ?></span></p>
        <p><span><?php echo $msg2; ?></span></p>
    </div>
</body>
</html>
