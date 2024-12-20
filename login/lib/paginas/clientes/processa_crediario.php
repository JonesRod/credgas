<?php

include('../../conexao.php');

var_dump($_POST);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $cep = $_POST['cep'];
    $uf = $_POST['uf'];
    $cidade = $_POST['cidade'];
    $endereco = $_POST['endereco'];
    $numero = $_POST['numero'];
    $bairro = $_POST['bairro'];
    $selfieData = $_POST['selfie_data'];

    // Inicializa variáveis para os nomes dos arquivos
    $frenteNome = null;
    $versoNome = null;
    $selfieNome = null;

    // Verificar e salvar o arquivo frente
    if (isset($_FILES['documento_foto_frente']) && $_FILES['documento_foto_frente']['error'] === UPLOAD_ERR_OK) {
        $frenteTmp = $_FILES['documento_foto_frente']['tmp_name'];
        $frenteNome = uniqid() . "_" . $_FILES['documento_foto_frente']['name'];
        move_uploaded_file($frenteTmp, "arquivos/$frenteNome");
    }

    // Verificar e salvar o arquivo verso
    if (isset($_FILES['documento_foto_verso']) && $_FILES['documento_foto_verso']['error'] === UPLOAD_ERR_OK) {
        $versoTmp = $_FILES['documento_foto_verso']['tmp_name'];
        $versoNome = uniqid() . "_" . $_FILES['documento_foto_verso']['name'];
        move_uploaded_file($versoTmp, "arquivos/$versoNome");
    }

    // Salvar a selfie
    if (!empty($selfieData)) {
        $selfieData = str_replace('data:image/png;base64,', '', $selfieData);
        $selfieData = base64_decode($selfieData);
        $selfieNome = uniqid() . "_selfie.png";
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
        img_frente = '$frenteNome',
        img_verso = '$versoNome',
        img_self = '$selfieNome'
    WHERE id = '$id'";

    // Executa a consulta
    if ($mysqli->query($sql_update)) {
    echo "<div class='msg-container'>Dados salvos com sucesso!</div>";
    } else {
    echo "Erro ao atualizar dados: " . $mysqli->error;
    }

    $mysqli->close();

}
?>
