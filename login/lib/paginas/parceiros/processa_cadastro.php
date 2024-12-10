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
$aberto_fechado_manual = $mysqli->real_escape_string($_POST['statusLojaTextoManual']);
$aberto_fechado_aut = $mysqli->real_escape_string($_POST['statusLojaAutoTexto']);
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

//var_dump($_POST);
//die();
//echo ('oi'). $aberto_fechado;
//die();
// Configuração do upload da nova logo (se houver)
if (isset($_FILES['logoInput']) && $_FILES['logoInput']['error'] === 0) {
    // Verifica se existe uma logo anterior e exclui
    if (!empty($_POST['img_anterior'])) {
        $logoAntiga = $_POST['img_anterior'];
        //echo('oi'. $logoAntiga);
        if (file_exists(filename: $logoAntiga)) {
            unlink(filename: $logoAntiga);  // Exclui o arquivo antigo
            //echo "Logo anterior excluída. <br>";
        }
    }

    $arquivo = $_FILES['logoInput'];

    // Obtém a extensão do arquivo e gera um novo nome
    $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
    if (in_array($extensao, ['jpg', 'jpeg', 'png', 'gif'])) {
        $novoNome = uniqid() . '.' . $extensao;
        $destino = 'arquivos/' . $novoNome;

        // Move o novo arquivo e atualiza a variável $logo
        if (move_uploaded_file($arquivo['tmp_name'], $destino)) {
            $logo = $novoNome;
            //echo "Logo salva como: $logo <br>";
        } else {
            echo "Erro ao mover a nova logo.<br>";
        }
    } else {
        echo "Extensão da logo não permitida. <br>";
    }
} else {
    // Caso nenhum novo arquivo seja enviado, mantém a logo antiga ou define como nulo
    $logo = !empty($_POST['img_anterior']) ? basename($_POST['img_anterior']) : '';

    // Exibe apenas o nome do arquivo
    //echo $logo; // Saída: 6732a4ad3b2e6.jpg


        
    }
    //var_dump($_POST);
    //echo $logo;
    //echo $novoNome;
    //die();
    // Atualiza os dados no banco de dados
$sql_update = "
    UPDATE meus_parceiros SET 
        razao = '$razao',
        logo = '$logo',
        nomeFantasia = '$nomeFantasia',
        cnpj = '$cnpj',
        inscricaoEstadual = '$inscricaoEstadual',
        categoria = '$categoria',
        telefoneComercial = '$telefoneComercial',
        telefoneResponsavel = '$telefoneResponsavel',
        email = '$email',
        cep = '$cep',
        estado = '$uf',
        cidade = '$cidade',
        endereco = '$rua',
        numero = '$numero',
        bairro = '$bairro',
        aberto_fechado_manual ='$aberto_fechado_manual',
        aberto_fechado_aut ='$aberto_fechado_aut'
    WHERE id = '$id'";

// Executa a consulta
if ($mysqli->query($sql_update)) {
    echo "<div class='msg-container'>Dados atualizados com sucesso!</div>";
} else {
    echo "Erro ao atualizar dados: " . $mysqli->error;
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
            margin-top: 100px;
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
