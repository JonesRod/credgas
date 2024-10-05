<?php
    include('../../conexao.php');

    $erro = false;

    if(!isset($_SESSION))
        session_start();
        
    if(!isset($_SESSION['usuario'])){
        header("Location: ../../../../index.php");
    }
    function enviarArquivo($error, $name, $tmp_name) {
        // para obrigar a ter foto
        if($error)
            //echo("Falha ao enviar arquivo");
            return false;

        $pasta = "arquivos/";
        $nomeDoArquivo = $name;
        $novoNomeDoArquivo = uniqid();
        $extensao = strtolower(pathinfo($nomeDoArquivo, PATHINFO_EXTENSION));

        $path = $pasta . $novoNomeDoArquivo . "." . $extensao;
        $deu_certo = move_uploaded_file($tmp_name, $path);
        //echo $path;
        if ($deu_certo) {
            return $path;
        } else
            return false;
    }
    if(count($_POST) > 0) { 

        if(isset($_FILES['imageInput'])) {

            $arq = $_FILES['imageInput'];
            $path = enviarArquivo($arq['error'], $arq['name'], $arq['tmp_name']);
            //echo $path;
            if($path == false)
                $nova_foto = false;

                else
                    $nova_foto= " foto = '$path', ";
            
                if(empty($_POST['foto'])){
                    if(isset($_POST['foto']) && $_POST['foto'] !== '../arquivos_fixos/9734564-default-avatar-profile-icon-of-social-media-user-vetor.jpg')
                        unlink($_POST['foto']);
                }
            else
                $nova_foto = " foto = '$path', ";
        
            if(empty($_POST['foto'])) {
                if(isset($_POST['foto']) && $_POST['foto'] !== '../arquivos_fixos/9734564-default-avatar-profile-icon-of-social-media-user-vetor.jpg')
                    unlink($_POST['foto']);
            }
        }
        /*if(!isset($_FILES['imageInput'])) {
            echo "oii";
            die();
        }*/
        
        //$arq = $_FILES['imageInput'];
        $id = intval($_POST['id']);
        $nome_completo = $mysqli->escape_string($_POST['nome_completo']);
        $apelido = $mysqli->escape_string($_POST['apelido']);
        $cpf = $mysqli->escape_string($_POST['cpf']);
        $rg = $mysqli->escape_string($_POST['rg']);
        $nascimento = $mysqli->escape_string($_POST['nascimento']);
        $uf = $mysqli->escape_string($_POST['uf']);
        $cid_natal = $mysqli->escape_string($_POST['cidnatal']);
        $mae = $mysqli->escape_string($_POST['mae']);
        $pai = $mysqli->escape_string($_POST['pai']);
        $sexo = $mysqli->escape_string($_POST['sexo']);
        $uf_atual = $mysqli->escape_string($_POST['uf_atual']);
        $cep = $mysqli->escape_string($_POST['cep']);
        $cid_atual = $mysqli->escape_string($_POST['cid_atual']);
        $endereco = $mysqli->escape_string($_POST['endereco']);
        $numero = $mysqli->escape_string($_POST['numero']);
        $bairro = $mysqli->escape_string($_POST['bairro']);        
        $celular1 = $mysqli->escape_string($_POST['celular1']);
        $celular2 = $mysqli->escape_string($_POST['celular2']);
        $email = $mysqli->escape_string($_POST['email']);
    
        //$hoje = new DateTime('now');
        $dataStr = $nascimento;
        $dataFormatada = DateTime::createFromFormat('d/m/Y', $dataStr);

        $nasc = $dataFormatada->format('Y-m-d');

        /*$nome_completo = "Fulano de Tal";
        // Separar o nome do sobrenome
        $partesNome = explode(' ', $nome_completo);
        $primeiroNome = $partesNome[0];
        $sobrenome = end($partesNome);*/
        
        //var_dump($_POST);

        if($erro) {
            $msg = "<p><b>ERRO: $erro</b></p>";
        } else {
    
            $sql_code = "UPDATE socios
            SET 
            $nova_foto
            apelido ='$apelido',
            nome_completo = '$nome_completo', 
            cpf = '$cpf',
            rg = '$rg',
            nascimento = '$nasc',
            uf = '$uf',
            cid_natal = '$cid_natal',
            mae = '$mae',
            pai = '$pai',
            sexo = '$sexo',
            uf_atual = '$uf_atual',
            cep = '$cep',
            cid_atual = '$cid_atual',
            endereco = '$endereco',
            numero = '$numero',
            bairro = '$bairro',       
            celular1 = '$celular1',
            celular2 = '$celular2',
            email = '$email'
            WHERE id = '$id'";

            //var_dump($_POST);

            $deu_certo = $mysqli->query($sql_code) or die($mysqli->$erro);
            
            if($deu_certo) {
                $msg = "<p><b>Dados atualizado com sucesso!!!</b></p>";
                unset($_POST);
                header("refresh: 5; perfil.php");
            }

        }
    }
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body{
            text-align: center;
            margin-top: 30px;
        }
    </style>
    <title></title>
</head>
<body>
    <div>
        <span><?php echo $msg;?></span>
    </div>
</body>
</html>
