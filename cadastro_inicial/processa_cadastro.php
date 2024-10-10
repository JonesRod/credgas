<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Cadastro</title>
    <?php
        include('../login/lib/conexao.php');
        include('../login/lib/enviarEmail.php');
        include('../login/lib/generateRandomString.php');

        $razao = $_POST['razao'];
        $nomeFantasia = $_POST['nomeFantasia'];
        $cnpj = $_POST['cnpj'];
        $inscricaoEstadual = $_POST['inscricaoEstadual'];
        $categoria = $_POST['categoria'];
        $arquivoEmpresa =$_POST['arquivoEmpresa'];
        $telefoneComercial = $_POST['telefoneComercial'];
        $telefoneResponsavel = $_POST['telefoneResponsavel'];
        $email = $_POST['email'];
        $senha = $_POST['senha'];
        $cep = $_POST['cep'];
        $estado = $_POST['uf'];
        $cidade = $_POST['cidade'];
        $termos =$_POST['aceito'];

        //var_dump(value: $_POST);

        // Verifica se o CNPJ já está cadastrado
        $sqlCNPJ = $mysqli->query(query: "SELECT * FROM meus_parceiros WHERE cnpj = '$cnpj'");
        $resultCNPJ = $sqlCNPJ->num_rows;

        // Verifica se o e-mail já está cadastrado
        $sqlEmail = $mysqli->query(query: "SELECT * FROM meus_parceiros WHERE email = '$email'");
        $resultEmail = $sqlEmail->num_rows;

        if (($resultCNPJ)== 0) {
            
            if (($resultEmail) == 0){
                //$senha = generateRandomString(length: 6);
                $senha_criptografada = password_hash(password: $senha, algo: PASSWORD_DEFAULT);
                $status = 'INATIVO';

                // Insere os dados se o CNPJ e o e-mail não estiverem cadastrados
                $sql_code = "INSERT INTO meus_parceiros (
                data_cadastro,
                razao, 
                nomeFantasia, 
                cnpj, 
                inscricaoEstadual, 
                categoria, 
                anexo_comprovante,
                telefoneComercial, 
                telefoneResponsavel, 
                email, 
                senha,
                cep, 
                estado, 
                cidade,
                status,
                termos) 
                VALUES (
                NOW(),
                '$razao', 
                '$nomeFantasia', 
                '$cnpj', 
                '$inscricaoEstadual', 
                '$categoria', 
                '$arquivoEmpresa',
                '$telefoneComercial', 
                '$telefoneResponsavel', 
                '$email', 
                '$senha_criptografada',
                '$cep', 
                '$estado', 
                '$cidade',
                '$status',
                '$termos')";

                $deu_certo = $mysqli->query(query: $sql_code) or die($mysqli->error);

                if($deu_certo){
                    $msg = true;
                    $msg = "Cadastro foi enviado para analiza!";
                    $msg1 = "";
                    $msg2 = "";
                    //echo $msg;

                    enviar_email(destinatario: $email, assunto: "Cadastro foi enviado para analiza!", mensagemHTML: "
                    <h1>È um prazer ter você, " . $nomeFantasia . " de parceiria. Boas vendas!</h1>
                    <p><b>Faça login com seu CNPJ.</p>
                    <p><b>Senha: </b>$senha</p>
                    <p><b>Para redefinir sua senha </b><a href='../../login/lib/redefinir_senha.php'>clique aqui.</a></p>
                    <p><b>Para entrar </b><a href='../index.php'>clique aqui.</a></p>
                    <p>Menssagem automatica. Não responda!</p>");

                    unset($_POST);
                    $mysqli->close();
                    header(header: "refresh: 5;../index.php"); //Atualiza a pagina em 5s e redireciona apagina
                }           
            } else { 
                $msg = "Já existe um cadastrado com esse E-MAIL!";
                $msg1 = "";
                $msg2 = "";
                //echo $msg;
                $mysqli->close();
                header(header: "refresh: 10;../index.php");
                echo "Erro: " . $sql . "<br>" . $conn->$error;
                //echo "E-mail já cadastrado!";
            }
        }else{
            $msg = "Já existe um cadastrado com esse CNPJ!";
            $msg1 = "";
            $msg2 = "";
            //echo $msg;
            $mysqli->close();
            header(header: "refresh: 10;../index.php");
            //echo "CNPJ já cadastrado!";    
        }

        //$conn->close();
    ?>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4; /* Cor de fundo suave */
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh; /* Ocupa toda a altura da tela */
        }

        #msg {
            background-color: #fff; /* Fundo branco para o bloco de mensagens */
            border-radius: 8px; /* Cantos arredondados */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Sombra para profundidade */
            padding: 20px;
            text-align: center; /* Centraliza o texto */
            width: 90%;
            max-width: 500px; /* Largura máxima */
        }

        #msg span {
            display: block; /* Cada mensagem em uma nova linha */
            margin: 10px 0; /* Espaçamento entre as mensagens */
            font-size: 1.2rem; /* Tamanho da fonte */
        }

        /* Estilos para mensagens de erro e sucesso */
        #msg span:nth-child(1) {
            color: #28a745; /* Verde para sucesso */
            font-weight: bold;
        }

        #msg span:nth-child(2), 
        #msg span:nth-child(3) {
            color: #dc3545; /* Vermelho para erro */
            font-weight: bold;
        }

        /* Responsividade */
        @media (max-width: 600px) {
            body {
                padding: 10px; /* Reduz o preenchimento em telas menores */
            }

            #msg {
                width: 100%; /* Largura total em telas pequenas */
                padding: 15px; /* Reduz o preenchimento do bloco de mensagens */
            }

            #msg span {
                font-size: 1rem; /* Tamanho da fonte menor em telas pequenas */
            }
        }
    </style>
</head>
<body>
    <div id="msg">
        <p><span><?php echo $msg; ?></span></p>
        <p><span><?php echo $msg1; ?></span></p>
        <p><span><?php echo $msg2; ?></span></p>
    </div>
</body>
</html>
