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

            $primeiro_nome = $mysqli->escape_string($_POST['primeiro_nome']);
            //$apelido = $mysqli->escape_string($_POST['apelido']);

            // Separar o nome do sobrenome
            //$partesNome = explode(separator: ' ', string: $nome_completo);
            //$primeiroNome = $partesNome[0];
            //$sobrenome = end($partesNome);
            //if($apelido == ''){
                //$apelido = $primeiroNome;
            //}
            
            $nascimento = $mysqli->escape_string($_POST['nascimento']);
            $cep = $mysqli->escape_string($_POST['cep']);
            $uf = $mysqli->escape_string($_POST['uf']);
            $cidade = $mysqli->escape_string($_POST['cidade']);        
            $celular1 = $mysqli->escape_string($_POST['celular1']);
            $celular2 = $mysqli->escape_string($_POST['celular2']);
            $email = $mysqli->escape_string($_POST['email']);
            $termos = $mysqli->escape_string($_POST['aceito']);
            
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

            //$id = '1';
            //$dados = $mysqli->query("SELECT * FROM config_admin WHERE id = '$id'") or die($mysqli->$error);
            //$dadosEscolhido = $dados->fetch_assoc();
            //$idade_minima = $dadosEscolhido['idade_minima'];

            $idade_minima = '18';
            $anos_idade = $idade->y;

            $dataAtual = date(format: 'Y-m-d'); // Obtém a data atual no formato ano-mês-dia
            $validade = date(format: 'Y-m-d', timestamp: strtotime(datetime: $dataAtual . '+'. $dadosEscolhido['validade'].' days')); // Adiciona 365 dias

            /*echo "Diferença de " . $idade->d . " dias";
            echo " e " . $idade->m . " mese s";
            echo " e " . $idade->y . " anos.";*/
            
            //var_dump($_POST);

            if(($anos_idade) >= $idade_minima) {
                //echo "Você tem " . $idade->y . " anos, ". $idade->m ." meses e ". $idade->d ." dias.";

                $sql_cpf = $mysqli->query(query: "SELECT * FROM meus_clientes WHERE cpf = '$cpf'");
                $result_cpf= $sql_cpf->fetch_assoc();
                $cpf_registrado = $sql_cpf->num_rows;

                $sql_email = $mysqli->query(query: "SELECT * FROM meus_clientes WHERE email = '$email'");
                $result_email= $sql_email->fetch_assoc();
                $email_registrado = $sql_email->num_rows;

                //var_dump($_POST);
                //die();

                if(($cpf_registrado) == 0) {
            
                    if(($email_registrado ) == 0) {
                        
                        $sql_cpf_cliente = $mysqli->query(query: "SELECT * FROM meus_clientes WHERE cpf = '$cpf'");
                        $result_cpf_cliente= $sql_cpf_cliente->fetch_assoc();
                        $cpf_registrado_cliente = $sql_cpf_cliente->num_rows;
        
                        $sql_email_cliente = $mysqli->query(query: "SELECT * FROM meus_clientes WHERE email = '$email'");
                        $result_email_cliente= $sql_email_cliente->fetch_assoc();
                        $email_registrado_cliente = $sql_email_cliente->num_rows;
                        //var_dump($_POST);
                        //die();

                        if(($cpf_registrado_cliente) == 0) {
                    
                            if(($email_registrado_cliente) == 0) {

                                $arq = $_FILES['imageInput'];
                                $path = enviarArquivo($arq['error'], $arq['name'], $arq['tmp_name']);
                                //echo $path;

                                $status = 'ATIVO';
                                $status_crediario = 'INATIVO';
                                
                                $sql_code = "INSERT INTO meus_clientes (data_cadastro, primeiro_nome, cpf, 
                                nascimento, cep, uf, cidade, celular1, celular2, email, status, status_crediario, termos) 
                                VALUES (NOW(),'$path','$apelido', '$nome_completo','$cpf','$rg','$nasc', '$uf', 
                                '$cid_natal', '$mae', '$pai', '$sexo', '$uf_atual','$cep','$cid_atual','$endereco', 
                                '$numero','$bairro','$celular1','$celular2','$email', '$motivo', '$termos', '$validade', '$status', '$votacao')";
                                
                                $deu_certo = $mysqli->query($sql_code) or die($mysqli->$error);

                                if($deu_certo){
                                    $msg = true;
                                    $msg = "Sua solicitação foi enviada e registrada com sucesso.";
                                    $msg1 = "";
                                    $msg2 = "";
                                    //echo $msg;

                                    enviar_email($email, "Registro de solicitação de para associação ao Club 40Ribas", "
                                    <h1>Olá Sr. " . $apelido . "</h1>
                                    <p>Sua solicitação foi registrada com sucesso. Assim que surgir uma vaga passaremos sua 
                                    solicitação por votação de aprovação. Lhe avisaremos assim ...</p>
                                    <p>Menssagem automatica. Não responda!</p>");

                                    unset($_POST);
                                    $mysqli->close();
                                    header("refresh: 5;../index.php"); //Atualiza a pagina em 5s e redireciona apagina
                                }     
                            }
                            if(($email_registrado_socio) != 0) {
        
                                $msg = "Já existe uma associado cadastrada com esse e-mail!";
                                $msg1 = "";
                                $msg2 = "";
                                //echo $msg;
                                $mysqli->close();
                                header("refresh: 10;../index.php");

                            }
                        }
                        if(($cpf_registrado_socio) != 0) {
        
                            $msg = ("Já existe um cadastrado com esse CPF!");
                            $msg1 = "";
                            $msg2 = "";
                            //echo $msg;
                            $mysqli->close();
                            header("refresh: 10;../index.php");
                        }                                                    
                                                                                
                    }
                    if(($email_registrado) != 0) {
                        // Obtém a data de hoje
                        $dataAtual = new DateTime();
                        $validade = new DateTime($result_email['validade']); 
                        $status = 'ATIVO';

                        if ($dataAtual < $validade) {

                            $msg = "Já existe uma Solicitação cadastrada com esse e-mail!";
                            $msg1 = "";
                            $msg2 = "";
                            //echo $msg;
                            $mysqli->close();
                            header("refresh: 10;../index.php");

                        } elseif ($dataAtual == $validade) {

                            $msg = "Já existe uma Solicitação cadastrada com esse e-mail, mas será incerrada hoje!";
                            $msg1 = "Atualize sua inscrição novamente a partir de amanhã!";
                            $msg2 = "";
                            $mysqli->close();
                            header("refresh: 15;../index.php");

                        } elseif($dataAtual > $validade){
                            $msg = "Já existe um cadastro de Solicitação com esse e-mail!";
                            $msg1 = "Sua inscrição está sendo renovada.";
                            $msg2 = "";

                            $dataAtual = date('Y-m-d'); // Obtém a data atual no formato ano-mês-dia
                            $validade = date('Y-m-d', strtotime($dataAtual . '+'. $dadosEscolhido['validade'].' days')); // Adiciona 365 dias

                            $arq = $_FILES['imageInput'];
                            $path = enviarArquivo($arq['error'], $arq['name'], $arq['tmp_name']);

                            if($erro) {
                                echo "<p><b>ERRO: $erro</b></p>";
                            } else {
                                $id = $result_email['id'];

                                $sql_code = "UPDATE int_associar
                                SET 
                                data = NOW(),
                                foto = '$path',
                                apelido = '$apelido',
                                nome_completo = '$nome_completo',
                                cpf ='$cpf',
                                rg = '$rg',
                                nascimento = '$nasc',
                                cid_natal = '$cid_natal',
                                mae = '$mae',
                                pai = '$pai',
                                sexo = '$sexo',
                                uf_atual = '$uf_atual',
                                cep = '$cep',
                                cid_atual = '$cid_atual',
                                endereco = '$endereco',
                                nu = '$numero',
                                bairro = '$bairro',
                                celular1 = '$celular1',
                                celular2 = '$celular2',
                                email = '$email',
                                motivo = '$motivo',
                                termos ='$termos',
                                validade = '$validade',
                                status = '$status'
                                WHERE id = '$id'";

                                $deu_certo = $mysqli->query($sql_code) or die($mysqli->error);
                                if($deu_certo) {
                                    //echo $estatuto_int.'4';
                                    //var_dump($_POST);

                                    enviar_email($email, "Sua solicitação de para associação ao Club 40Ribas foi renovada.", "
                                    <h1>Olá Sr. " . $apelido . "</h1>
                                    <p>Sua solicitação foi renovada com sucesso. Assim que surgir uma vaga passaremos sua 
                                    solicitação por votação de aprovação. Lhe avisaremos assim ...</p>
                                    <p>Menssagem automatica. Não responda!</p>");

                                    $mysqli->close();

                                    header("refresh: 10; ../index.php");
                                }
                            }
                            
                        }
                    }
                }
                if(($cpf_registrado) != 0) {

                    // Obtém a data de hoje
                    $dataAtual = new DateTime();
                    $validade = new DateTime($result_cpf['validade']); 
                    $status = 'ATIVO';
                    //echo $validade;
                    //die();
                    if ($dataAtual < $validade) {

                        $msg = "Já existe uma Solicitação cadastrada com esse CPF!";
                        $msg1 = "";
                        $msg2 = "";
                        //echo $msg;
                        $mysqli->close();
                        header("refresh: 10;../index.php");

                    } elseif ($dataAtual == $validade) {

                        $msg = "Já existe uma Solicitação cadastrada com esse CPF, mas será incerrada hoje!";
                        $msg1 = "Atualize sua inscrição novamente a partir de amanhã!";
                        $msg2 = "";
                        $mysqli->close();
                        header("refresh: 15;../index.php");

                    } elseif($dataAtual > $validade) {
                        $msg = "Já existe um cadastro de Solicitação com esse CPF!";
                        $msg1 = "Sua inscrição está sendo renovada.";
                        $msg2 = "";

                        $dataAtual = date('Y-m-d'); // Obtém a data atual no formato ano-mês-dia
                        $validade = date('Y-m-d', strtotime($dataAtual . '+'. $dadosEscolhido['validade'].' days')); // Adiciona 365 dias

                        $arq = $_FILES['imageInput'];
                        $path = enviarArquivo($arq['error'], $arq['name'], $arq['tmp_name']);

                        if($erro) {
                            echo "<p><b>ERRO: $erro</b></p>";
                        } else {
                            $id = $result_cpf['id'];
                            //echo $id;

                            $sql_code = "UPDATE int_associar
                            SET 
                            foto = '$path',
                            apelido = '$apelido',
                            nome_completo = '$nome_completo',
                            cpf ='$cpf',
                            rg = '$rg',
                            nascimento = '$nasc',
                            cid_natal = '$cid_natal',
                            mae = '$mae',
                            pai = '$pai',
                            sexo = '$sexo',
                            uf_atual = '$uf_atual',
                            cep = '$cep',
                            cid_atual = '$cid_atual',
                            endereco = '$endereco',
                            nu = '$numero',
                            bairro = '$bairro',
                            celular1 = '$celular1',
                            celular2 = '$celular2',
                            email = '$email',
                            motivo = '$motivo',
                            termos ='$termos',
                            validade = '$validade',
                            status = '$status'
                            WHERE id = '$id'";
                            
                            $deu_certo = $mysqli->query($sql_code) or die($mysqli->error);
                            if($deu_certo) {
                                //echo $estatuto_int.'4';
                                //var_dump($_POST);

                                enviar_email($email, "Sua solicitação de para associação ao Club 40Ribas foi renovada.", "
                                <h1>Olá Sr. " . $apelido . "</h1>
                                <p>Sua solicitação foi renovada com sucesso. Assim que surgir uma vaga passaremos sua 
                                solicitação por votação de aprovação. Lhe avisaremos assim ...</p>
                                <p>Menssagem automatica. Não responda!</p>");

                                $mysqli->close();
                                header("refresh: 10; ../index.php");
                            }
                        }
                    }
                }
            }else{

                $msg = "Você tem " . $idade->y . " anos, ". $idade->m ." meses e ". $idade->d ." dias.";
                $msg1 = "Você ainda não tem idade o suficiente para ser sócio!";
                $msg2 = "Complete a idade minima que é ".$idade_minima ." anos e tente novamente.";
                unset($_POST);
                $mysqli->close();
                header("refresh: 10;../index.php");
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
