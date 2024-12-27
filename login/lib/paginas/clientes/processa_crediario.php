<?php

include('../../conexao.php');

//var_dump($_POST);
//die();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $cep = $_POST['cep'];
    $uf = $_POST['uf'];
    $cidade = $_POST['cidade'];
    $endereco = $_POST['endereco'];
    $numero = $_POST['numero'];
    $bairro = $_POST['bairro'];
    $selfieData = $_POST['selfie_data'];
    $aceito = $_POST['aceito'];

    // Inicializa variáveis para os nomes dos arquivos
    $frenteNome = null;
    $versoNome = null;
    $selfieNome = null;

    // Verificar e salvar o arquivo frente
    if (isset($_FILES['documento_foto_frente']) && $_FILES['documento_foto_frente']['error'] === UPLOAD_ERR_OK) {
        $frenteTmp = $_FILES['documento_foto_frente']['tmp_name'];
        $extensaoFrente = pathinfo($_FILES['documento_foto_frente']['name'], PATHINFO_EXTENSION);

        // Validar a extensão
        $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'pdf'];
        if (in_array(strtolower($extensaoFrente), $extensoesPermitidas)) {
            $frenteNome = uniqid() . "." . $extensaoFrente;
            move_uploaded_file($frenteTmp, "arquivos/$frenteNome");
        } else {
            echo "Extensão do arquivo frente não permitida.";
        }
    }

    // Verificar e salvar o arquivo verso
    if (isset($_FILES['documento_foto_verso']) && $_FILES['documento_foto_verso']['error'] === UPLOAD_ERR_OK) {
        $versoTmp = $_FILES['documento_foto_verso']['tmp_name'];
        $extensaoVerso = pathinfo($_FILES['documento_foto_verso']['name'], PATHINFO_EXTENSION);

        // Validar a extensão
        $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'pdf'];
        if (in_array(strtolower($extensaoVerso), $extensoesPermitidas)) {
            $versoNome = uniqid() . "." . $extensaoVerso;
            move_uploaded_file($versoTmp, "arquivos/$versoNome");
        } else {
            echo "Extensão do arquivo verso não permitida.";
        }
    }


    // Salvar a selfie
    if (!empty($selfieData)) {
        $selfieData = str_replace('data:image/png;base64,', '', $selfieData);
        $selfieData = base64_decode($selfieData);
        $selfieNome = uniqid() . ".png";
        file_put_contents("arquivos/".$selfieNome, $selfieData);
    }

        // Atualiza os dados no banco de dados
    $sql_update = "UPDATE meus_clientes SET 
        cep = '$cep',
        uf = '$uf',
        cidade = '$cidade',
        endereco = '$endereco',
        numero = '$numero',
        bairro = '$bairro',
        status_crediario = 'AGUARDANDO',
        img_frente = '$frenteNome',
        img_verso = '$versoNome',
        img_self = '$selfieNome',
        termos_crediario = '$aceito'
    WHERE id = '$id'";

    // Executa a consulta
    if ($mysqli->query($sql_update)) {
        $notif='1';
        // Monta a query SQL para inserir os dados do produto no banco de dados usando prepared statements
        $sql_not = "INSERT INTO contador_notificacoes_admin (data, id_cliente, not_crediario) 
        VALUES (NOW(), ?, ?)";

        $stmt_not = $mysqli->prepare($sql_not);

        // Verifica se a query foi preparada com sucesso
        if ($stmt_not === false) {
            die('Erro na preparação da query: ' . $mysqli->error);
        }
    
        // Liga os parâmetros da query ao prepared statement
        $stmt_not->bind_param('ii', $id, $notif);
    
        // Executa a query para inserir a notificação
        if ($stmt_not->execute() === false) {
            die('Erro ao inserir notificação: ' . $stmt_not->error);
        } else {
            //$msg = 'dados com sucesso.';
        }

        echo "
        <div style='
            background-color: #d4edda; 
            color: #155724; 
            border: 1px solid #c3e6cb; 
            padding: 15px; 
            margin: 20px 0; 
            border-radius: 5px; 
            font-size: 16px; 
            text-align: center;
            font-family: Arial, sans-serif;
        '>
            Solicitação enviada com sucesso! Você será redirecionado em 5 segundos...
        </div>
        <meta http-equiv='refresh' content='5;url=perfil_crediario.php'>";
    } else {
        echo "
        <div style='
            background-color: #f8d7da; 
            color: #721c24; 
            border: 1px solid #f5c6cb; 
            padding: 15px; 
            margin: 20px 0; 
            border-radius: 5px; 
            font-size: 16px; 
            text-align: center;
            font-family: Arial, sans-serif;
        '>
            Erro ao atualizar dados: " . $mysqli->error . "
        </div>";
    }


    $mysqli->close();
}
?>
