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
    $primeiro_nome = $mysqli->real_escape_string($_POST['primeiro_nome']);
    $razao = $mysqli->real_escape_string($_POST['razao']);
    $nomeFantasia = $mysqli->real_escape_string($_POST['nomeFantasia']);
    $cnpj = $mysqli->real_escape_string($_POST['cnpj']);
    $inscricaoEstadual = $mysqli->real_escape_string($_POST['inscricaoEstadual']);
    $telefoneComercial = $mysqli->real_escape_string($_POST['telefoneComercial']);
    $email = $mysqli->real_escape_string($_POST['email']);
    $cep = $mysqli->real_escape_string($_POST['cep']);
    $uf = $mysqli->real_escape_string($_POST['uf']);
    $cidade = $mysqli->real_escape_string($_POST['cidade']);
    $rua = $mysqli->real_escape_string($_POST['rua']);
    $numero = $mysqli->real_escape_string($_POST['numero']);
    $bairro = $mysqli->real_escape_string($_POST['bairro']);


    // Configuração do upload da nova logo (se houver)
    if (isset($_FILES['logoInput']) && $_FILES['logoInput']['error'] === 0) {
        // Verifica se existe uma logo anterior e exclui
        if (!empty($_POST['img_anterior'])) {
            $logoAntiga = 'arquivos/' . $_POST['img_anterior']; // Caminho completo da logo antiga
            if (file_exists($logoAntiga)) {
                unlink($logoAntiga); // Exclui o arquivo antigo
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
                echo "Logo salva como: $logo <br>";
            } else {
                echo "Erro ao mover a nova logo.<br>";
            }
        } else {
            echo "Extensão da logo não permitida. <br>";
        }
    } else {
        // Caso nenhum novo arquivo seja enviado, mantém a logo antiga
        //$logo = !empty($_POST['img_anterior']) ? $_POST['img_anterior'] : '';
    }


    $data = date('Y-m-d H:i:s'); // Define a data atual

    $stmt = $mysqli->prepare("
        INSERT INTO config_admin (
            id_cliente,
            primeiro_nome,
            data_alteracao,
            logo,
            razao,
            nomeFantasia,
            cnpj,
            inscricaoEstadual,
            uf,
            cep,
            cidade,
            endereco, 
            numero,
            bairro,            
            email_suporte,            
            telefoneComercial
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    // Verifica se a consulta foi preparada corretamente
    if (!$stmt) {
        die("Erro na preparação: " . $mysqli->error);
    }
    
    $stmt->bind_param(
        'isssssssssssssss', 
        $id, 
        $primeiro_nome,
        $data,
        $logo,
        $razao, 
        $nomeFantasia,
        $cnpj, 
        $inscricaoEstadual, 
        $uf,  
        $cep,  
        $cidade, 
        $rua,
        $numero, 
        $bairro,  
        $email,            
        $telefoneComercial
    );
    
    // Executa a consulta
    if ($stmt->execute()) {
        echo "<div class='msg-container'>Dados salvos com sucesso!</div>";
    } else {
        echo "Erro ao salvar dados: " . $stmt->error;
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
       /*setTimeout(function() {    
            window.location.href = 'perfil_loja.php?status=sucesso';
        }, 3000);*/
    </script>
</body>
</html>
