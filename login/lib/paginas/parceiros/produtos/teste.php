<?php
include('../../conexao.php');

// Inicia a sessão, se não estiver iniciada
if (!isset($_SESSION)) {
    session_start();
}

// Verifica se o usuário está logado
if (!isset($_SESSION['id'])) {
    header("Location: ../../../../index.php");
    exit();
}

$id = $_SESSION['id'];

// Valida e sanitiza os dados recebidos
$razao = $mysqli->real_escape_string($_POST['razao']);
$nomeFantasia = $mysqli->real_escape_string($_POST['nomeFantasia']);
$cnpj = $mysqli->real_escape_string($_POST['cnpj']);
$inscricaoEstadual = $mysqli->real_escape_string($_POST['inscricaoEstadual']);
$categoria = $mysqli->real_escape_string($_POST['categoria']);
$telefoneComercial = $mysqli->real_escape_string($_POST['telefoneComercial']);
$telefoneResponsavel = $mysqli->real_escape_string($_POST['telefoneResponsavel']);
$email = $mysqli->real_escape_string($_POST['email']);
$cep = $mysqli->real_escape_string($_POST['cep']);
$uf = $mysqli->real_escape_string($_POST['uf']);
$cidade = $mysqli->real_escape_string($_POST['cidade']);
$rua = $mysqli->real_escape_string($_POST['rua']);
$numero = $mysqli->real_escape_string($_POST['numero']);
$bairro = $mysqli->real_escape_string($_POST['bairro']);


// Configuração do upload da nova logo (se houver)
if (isset($_FILES['logoInput']) && $_FILES['logoInput']['error'] === 0) {
    $arquivo = $_FILES['logoInput']; // Pega os dados do arquivo de logo

    // Obtém o nome do arquivo e a extensão
    $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));

    // Verifica se a extensão do arquivo é permitida
    if (in_array($extensao, ['jpg', 'jpeg', 'png', 'gif'])) {
        // Gera um nome único para o arquivo
        $novoNome = uniqid() . '.' . $extensao;
        
        // Define o destino do arquivo na pasta 'arquivos/'
        $destino = 'arquivos/' . $novoNome;

        // Tenta mover o arquivo para a pasta de destino
        if (move_uploaded_file($arquivo['tmp_name'], $destino)) {
            $logo = $novoNome;  // Atualiza o nome do arquivo com o novo nome gerado
            $msg = "Logo salva como: $logo <br>"; // Exibe o nome do arquivo salvo
        } else {
            $msg =  "Erro ao mover a nova logo.<br>";
        }
    } else {
        $msg =  "Extensão da logo não permitida. As extensões permitidas são: jpg, jpeg, png, gif.<br>";
    }
} elseif (!isset($_FILES['logoInput']) && isset($_POST['img_anterior'])) {
    // Caso o arquivo de logo anterior tenha sido enviado e o upload de novo arquivo não tenha ocorrido
    $verifica_arq = $_POST['img_anterior'];  // Pega o caminho completo do arquivo

    // Extrai o nome do arquivo do caminho completo
    $arquivo = basename($verifica_arq);

    // Obtém o nome do arquivo e a extensão
    $extensao = strtolower(pathinfo($arquivo, PATHINFO_EXTENSION));

    // Verifica se a extensão do arquivo é permitida
    if (in_array($extensao, ['jpg', 'jpeg', 'png', 'gif'])) {
        // Gera um nome único para o arquivo
        $novoNome = uniqid() . '.' . $extensao;
        $msg =  $novoNome;

        // Define o destino do arquivo na pasta 'arquivos/'
        $destino = 'arquivos/' . $novoNome;

        // Copia o arquivo original para o destino (não usa move_uploaded_file, já que é um arquivo existente)
        if (copy($verifica_arq, $destino)) {
            $logo = $novoNome;  // Atualiza o nome do arquivo com o novo nome gerado
            $msg = "Logo salva como: $logo <br>"; // Exibe o nome do arquivo salvo
        } else {
            echo "Erro ao copiar a logo anterior.<br>";
        }
    } else {
        $msg = "Extensão da logo não permitida. As extensões permitidas são: jpg, jpeg, png, gif.<br>";
    }
} else {
    $msg = "Nenhuma logo foi enviada ou ocorreu um erro.<br>";
}



//echo $logo;
//die();

// Configuração do upload do arquivo de comprovante
$end_comprovante = isset($_POST['arquivoComprovante']) ? $_POST['arquivoComprovante'] : '';
$filePath = $end_comprovante;
$comprovante = basename($filePath); // Retorna 'icone_loja.jpg'
$arquivoComprovante= basename($filePath); // Retorna 'icone_loja.jpg'

//var_dump($arquivoComprovante);
//var_dump($comprovante);

if (isset($_FILES['arquivoEmpresa']) && $_FILES['arquivoEmpresa']['error'] === 0) {
    $arquivoComprovante = $_FILES['arquivoEmpresa'];
    $extensaoComprovante = strtolower(pathinfo($arquivoComprovante['name'], PATHINFO_EXTENSION));

    // Verifica extensão permitida para o comprovante
    if (in_array($extensaoComprovante, ['pdf', 'png'])) {
        $novoNomeComprovante = uniqid() . '.' . $extensaoComprovante;
        $destinoComprovante = 'arquivos/' . $novoNomeComprovante;

        // Move o arquivo e atualiza o caminho do comprovante
        if (move_uploaded_file($arquivoComprovante['tmp_name'], $destinoComprovante)) {
            $comprovante = $novoNomeComprovante;
            $msg = "Comprovante salvo como: $comprovante <br>"; // Debug
        } else {
            $msg = "Erro ao mover o comprovante.<br>";
        }
    } else {
        $msg = "Extensão do comprovante não permitida.<br>";
    }
} else {
    $msg = "Nenhum comprovante foi enviado ou ocorreu um erro.<br>";
}

// Exibe os valores que serão salvos (para depuração)
//echo "Logo final: $logo <br>";
//echo "Comprovante final: $comprovante <br>";

// Atualiza os dados no banco de dados
$sql_update = "
    UPDATE meus_parceiros SET 
        razao = '$razao',
        logo = '$logo',
        nomeFantasia = '$nomeFantasia',
        cnpj = '$cnpj',
        inscricaoEstadual = '$inscricaoEstadual',
        categoria = '$categoria',
        anexo_comprovante = '$comprovante',
        telefoneComercial = '$telefoneComercial',
        telefoneResponsavel = '$telefoneResponsavel',
        email = '$email',
        cep = '$cep',
        estado = '$uf',
        cidade = '$cidade',
        endereco = '$rua',
        numero = '$numero',
        bairro = '$bairro'
    WHERE id = '$id'";

    // Executa a consulta e verifica o resultado
    if ($mysqli->query($sql_update)) {
        $msg =  "Dados atualizados com sucesso!<br>";
        
        // Exibe a mensagem de forma estilizada
        echo "<div class='msg-container'>$msg</div>";

        //sleep(3); // Aguarda 3 segundos
        //header("Location: perfil_loja.php?status=sucesso");
        //exit();
    } else {
        $msg =  "Erro ao atualizar dados: " . $mysqli->error;
    }

    $mysqli->close();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <style>
        /* Estilo para a div da mensagem */
        .msg-container {
            width: 100%;
            max-width: 500px;
            margin: 20px auto; /* Centraliza horizontalmente */
            padding: 20px;
            background-color: #f4f4f4;
            border: 1px solid #ddd;
            border-radius: 8px;
            text-align: center; /* Centraliza o texto dentro da div */
            font-size: 18px;
            color: #333;
        }

        .msg-container.success {
            background-color: #d4edda; /* Cor verde para sucesso */
            color: #155724;
        }

        .msg-container.error {
            background-color: #f8d7da; /* Cor vermelha para erro */
            color: #721c24;
        }

    </style>
</head>
<body>
    <script>
        setTimeout(function() {    
            window.location.href = 'perfil_loja.php?status=sucesso';
        }, 3000);
    </script>
</body>
</html>
