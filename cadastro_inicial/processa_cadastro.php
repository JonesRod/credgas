<?php
    include('../login/lib/conexao.php');
    include('../login/lib/enviarEmail.php');
    include('../login/lib/generateRandomString.php');

    $razao = $_POST['razao'];
    $nomeFantasia = $_POST['nomeFantasia'];
    $cnpj = $_POST['cnpj'];
    $inscricaoEstadual = $_POST['inscricaoEstadual'];
    $categoria = $_POST['categoria'];
    $telefoneComercial = $_POST['telefoneComercial'];
    $telefoneResponsavel = $_POST['telefoneResponsavel'];
    $email = $_POST['email'];
    $cep = $_POST['cep'];
    $estado = $_POST['uf'];
    $cidade = $_POST['cidade'];
    $termos =$_POST['aceito'];

    var_dump(value: $_POST);

    // Verifica se o CNPJ já está cadastrado
    $sqlCNPJ = "SELECT * FROM meus_parceiros WHERE cnpj = '$cnpj'";
    $resultCNPJ = $conn->query($sqlCNPJ);

    // Verifica se o e-mail já está cadastrado
    $sqlEmail = "SELECT * FROM meus_parceiros WHERE email = '$email'";
    $resultEmail = $conn->query($sqlEmail);

    if ($resultCNPJ->num_rows == 0) {
        
        if ($resultEmail->num_rows == 0){
            $senha = generateRandomString(length: 6);
            $senha_criptografada = password_hash(password: $senha, algo: PASSWORD_DEFAULT);
            $status = 'INATIVO';

            // Insere os dados se o CNPJ e o e-mail não estiverem cadastrados
            $sql = "INSERT INTO meus_parceiros (
            data_cadastro
            razao, 
            nomeFantasia, 
            cnpj, 
            inscricaoEstadual, 
            categoria, 
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
                $msg = "Cadastro realizado com sucesso!";
                $msg1 = "";
                $msg2 = "";
                //echo $msg;

                enviar_email(destinatario: $email, assunto: "Cadastro realizado com sucesso!", mensagemHTML: "
                <h1>È um prazer ter você, " . $nomeFantasia . " de parceiria. Boas vendas!</h1>
                <p><b>Você pode logar com seu CNPJ.</p>
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
            echo "Erro: " . $sql . "<br>" . $conn->error;
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

$conn->close();
?>

