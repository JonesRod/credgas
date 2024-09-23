<?php
    include('../../../conexao.php');

    $erro = false;

    if(!isset($_SESSION))
        session_start();

    if(!isset($_SESSION['usuario'])){
        header("Location: ../../../../../index.php");
    } 

    function enviarArquivo($error, $name, $tmp_name) {
        if ($error) {
            echo "Falha ao enviar o arquivo. Código de erro: " . $error;
            return false;
        }
    
        $pasta = "../arquivos/";
        $nomeDoArquivo = $name;
        $novoNomeDoArquivo = uniqid();
        $extensao = strtolower(pathinfo($nomeDoArquivo, PATHINFO_EXTENSION));
    
        $path = $pasta . $novoNomeDoArquivo . "." . $extensao;
        $deu_certo = move_uploaded_file($tmp_name, $path);
    
        if ($deu_certo) {
            return $path;
        } else {
            $msg = "Falha ao mover o arquivo para o diretório de destino.";
            echo $msg;
            return false;
        }
    }
    function enviarArquivoEstatuto($error, $name, $tmp_name) {
        if ($error) {
            $msg = "Falha ao enviar o arquivo. Código de erro: " . $error;
            echo $msg;
            return false;
        }
    
        $pasta = "../arquivos/";
        $nomeDoArquivo = $name;
        //$novoNomeDoArquivo = uniqid();
        $extensao = strtolower(pathinfo($nomeDoArquivo, PATHINFO_EXTENSION));
    
        $pathEstatuto = $pasta . $nomeDoArquivo  . "." . $extensao;
        $deu_certo = move_uploaded_file($tmp_name, $pathEstatuto);
    
        if ($deu_certo) {
            return $pathEstatuto;
        } else {
            $msg = "Falha ao mover o arquivo para o diretório de destino.";
            echo $msg;
            return false;
        }
    }
    function enviarArquivoRegimento($error, $name, $tmp_name) {
        if ($error) {
            $msg = "Falha ao enviar o arquivo. Código de erro: " . $error;
            echo $msg;
            return false;
        }
    
        $pasta = "../arquivos/";
        $nomeDoArquivo = $name;
        //$novoNomeDoArquivo = uniqid();
        $extensao = strtolower(pathinfo($nomeDoArquivo, PATHINFO_EXTENSION));
    
        $pathRegimento = $pasta . $nomeDoArquivo . "." . $extensao;
        $deu_certo = move_uploaded_file($tmp_name, $pathRegimento);
    
        if ($deu_certo) {
            return $pathRegimento;
        } else {
            $msg = "Falha ao mover o arquivo para o diretório de destino.";
            echo $msg;
            return false;
        }
    }
    if(count($_POST) > 0) { 

        if (isset($_FILES['imageInput']) && $_FILES['imageInput']['error'] != 4) {
            $arq = $_FILES['imageInput'];
            $path = enviarArquivo($arq['error'], $arq['name'], $arq['tmp_name']);
        
            if ($path !== false) {
                $nova_logo = $path;
                if(isset($_POST['end_logo']) && $_POST['end_logo'] !== 'IMG-20230811-WA0040.jpg'){
                    unlink($_POST['end_logo']);
                }
            } else {
                $nova_logo = $_POST['end_logo'];
                $nomeDoArquivoLogo = basename($nova_logo);
            }
        } else {
            $nova_logo = $_POST['end_logo'];
            $nomeDoArquivoLogo = basename($nova_logo);
        }

        if (isset($_FILES['novo_estatuto']) && $_FILES['novo_estatuto']['error'] != 4) {
            $arq = $_FILES['novo_estatuto'];
            $pathEstatuto = enviarArquivoEstatuto($arq['error'], $arq['name'], $arq['tmp_name']);

            if ($pathEstatuto !== false) {
                $estatuto_int = $pathEstatuto;
                //echo $estatuto_int.'1';
                //if(empty($_POST['estatuto'])) {
                    if(isset($_POST['estatuto'])){
                        $estatuto = $_POST['estatuto'];

                        if (file_exists($estatuto)) {
                            unlink($estatuto);
                        } else {
                            // O arquivo não existe, não faz nada
                        }
                    }
                //} 
            } else {
                if(isset($_POST['estatuto'])){
                    $estatuto_int = $_POST['estatuto'];
                    $nomeDoArquivoEs = basename($estatuto_int);
                }
            }
        } else {
            if(isset($_POST['estatuto'])) {
                $estatuto_int = $_POST['estatuto'];
                $nomeDoArquivoEs = basename($estatuto_int);
            }
        }

        if (isset($_FILES['novo_regimento']) && $_FILES['novo_regimento']['error'] != 4) {
            $arq = $_FILES['novo_regimento'];
            $pathRegimento = enviarArquivoRegimento($arq['error'], $arq['name'], $arq['tmp_name']);
               
            if ($pathRegimento !== false) {
                $reg_int = $pathRegimento;
                if(isset($_POST['regimento'])) {
                    $regimento = $_POST['regimento'];

                    if (file_exists($regimento)) {
                        unlink($regimento);
                    } else {
                        // O arquivo não existe, não faz nada
                    }
                }     
            } else {
                if(isset($_POST['regimento'])) {
                    $reg_int = $_POST['regimento'];
                    $nomeDoArquivoRe = basename($reg_int);
                }
            }   
        } else {
            if(isset($_POST['regimento'])) {
                $reg_int = $_POST['regimento'];
                $nomeDoArquivoRe = basename($reg_int);
            }
        }    

        $id= 1; 
        $admin = $mysqli->escape_string($_POST['admin']);
        $razao = $mysqli->escape_string($_POST['razao']);
        $cnpj = $mysqli->escape_string($_POST['cnpj']);
        $uf = $mysqli->escape_string($_POST['uf']);
        $cep = $mysqli->escape_string($_POST['cep']);
        $cid = $mysqli->escape_string($_POST['cid']);
        $rua = $mysqli->escape_string($_POST['endereco']);
        $numero = $mysqli->escape_string($_POST['numero']);
        $bairro = $mysqli->escape_string($_POST['bairro']);
        $nome_tesoureiro = $mysqli->escape_string($_POST['nome_tesoureiro']);
        $presidente = $mysqli->escape_string($_POST['presidente']);
        $vice_presidente = $mysqli->escape_string($_POST['vice_presidente']);
        $email_suporte = $mysqli->escape_string($_POST['email_suporte']);
        $senha = $mysqli->escape_string($_POST['senha']);
        $idade_min = $mysqli->escape_string($_POST['idade_min']);
        $termos_insc = $mysqli->escape_string($_POST['termos_insc']);
        $validade_insc = $mysqli->escape_string($_POST['validade_insc']);
        $dia_fecha_mes = $mysqli->escape_string($_POST['dia_fecha_mes']);        
        $valor_mensalidades = $mysqli->escape_string($_POST['valor_mensalidades']);
        $desconto_mensalidades = $mysqli->escape_string($_POST['desconto_mensalidades']);
        $multa = $mysqli->escape_string($_POST['multa']);
        $joia = $mysqli->escape_string($_POST['joia']);        
        $parcela_joia = $mysqli->escape_string($_POST['parcela_joia']);
        $meses_vence3 = $mysqli->escape_string($_POST['meses_vence3']); 
        $meses_vence5 = $mysqli->escape_string($_POST['meses_vence5']); 

        //echo $estatuto_int.'4';
        //var_dump($_POST);
        if($erro) {
            $msg = "<p><b>ERRO: $erro</b></p>";
        } else {
        //, 
            $sql_code = "UPDATE config_admin
            SET 
            id_admin = '$admin',
            data_alteracao = NOW(),
            logo = '$nomeDoArquivoLogo',
            razao = '$razao',
            cnpj = '$cnpj',
            uf = '$uf',
            cep = '$cep',
            cid = '$cid',
            rua = '$rua',
            numero = '$numero',
            bairro = '$bairro',
            presidente = '$presidente',
            vice_presidente = '$vice_presidente',
            nome_tesoureiro = '$nome_tesoureiro',
            email_suporte = '$email_suporte',
            senha = '$senha',
            idade_minima='$idade_min',
            termos_insc = '$termos_insc',
            validade_insc = '$validade_insc',
            estatuto_int = '$nomeDoArquivoEs',
            reg_int = '$nomeDoArquivoRe',
            dia_fecha_mes = '$dia_fecha_mes',       
            valor_mensalidades = '$valor_mensalidades',
            desconto_mensalidades = '$desconto_mensalidades',
            multa = '$multa',
            joia = '$joia',       
            parcela_joia = '$parcela_joia',
            meses_vence3 = '$meses_vence3', 
            meses_vence5 = '$meses_vence5'
            WHERE id = '$id'";

            $deu_certo = $mysqli->query($sql_code) or die($mysqli->$erro);
            
            //echo $estatuto_int.'4';
            //var_dump($_POST);
            

            $deu_certo_inserte = "INSERT INTO historico_config_admin (id_admin, data_alteracao, 
            logo, razao, cnpj, uf, cep, cid, rua, numero, bairro, presidente, vice_presidente, 
            nome_tesoureiro, email_suporte, senha, idade_minima, termos_insc, validade_insc, 
            estatuto_int, reg_int, dia_fecha_mes, valor_mensalidades, desconto_mensalidades, 
            multa, joia, parcela_joia, meses_vence3, meses_vence5)
            VALUES('$admin', NOW(), '$nomeDoArquivoLogo', '$razao', '$cnpj', '$uf', '$cep', 
            '$cid', '$rua', '$numero', '$bairro', '$presidente', '$vice_presidente', '$nome_tesoureiro', 
            '$email_suporte', '$senha', '$idade_min', '$termos_insc', '$validade_insc', '$nomeDoArquivoEs', 
            '$nomeDoArquivoRe', '$dia_fecha_mes', '$valor_mensalidades', '$desconto_mensalidades', '$multa', 
            '$joia', '$parcela_joia', '$meses_vence3', '$meses_vence5')";
            
            $deu_certo_inserte = $mysqli->query($deu_certo_inserte) or die($mysqli->error);

            if($deu_certo_inserte) {
            
                //var_dump($_POST);

                $msg = "<p><b>Dados atualizado com sucesso!!!</b></p>";
                unset($_POST);
                header("refresh: 3; admin_config.php");
            } else {
                //echo "<p><b>ERRO: $erro</b></p>";
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

