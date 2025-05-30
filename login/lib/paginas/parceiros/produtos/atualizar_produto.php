<?php
include('../../../conexao.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_parceiro = intval($_POST['id_parceiro']);
    $id_produto = intval($_POST['id_produto']);
    $nome_produto = mysqli_real_escape_string($mysqli, $_POST['nome_produto']);
    $descricao_produto = mysqli_real_escape_string($mysqli, $_POST['descricao_produto']);
    $categoria = mysqli_real_escape_string($mysqli, $_POST['categoria']);
    $valor_produto = floatval(str_replace(',', '.', $_POST['valor_produto']));
    $taxa = floatval(str_replace(',', '.', $_POST['taxa']));
    $frete_gratis = $_POST['frete_gratis'] === '1' ? '1' : '0';
    $valor_frete = $frete_gratis === 'sim' ? 0.00 : floatval(str_replace(',', '.', $_POST['valor_frete']));

    $imagens_existentes = isset($_POST['imagens_salvas']) ? explode(',', $_POST['imagens_salvas']) : [];
    $imagens_removidas = isset($_POST['imagens_removidas']) ? explode(',', $_POST['imagens_removidas']) : [];

    // Mantém apenas as imagens não removidas
    $imagens = array_diff($imagens_existentes, $imagens_removidas);
    $novas_imagens = [];
    $upload_dir = 'img_produtos/';
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

    // Upload das novas imagens
    if (isset($_FILES['produtoImagens']) && count($_FILES['produtoImagens']['name']) > 0) {
        for ($i = 0; $i < count($_FILES['produtoImagens']['name']); $i++) {
            $imagem = $_FILES['produtoImagens']['name'][$i];
            $tmp_name = $_FILES['produtoImagens']['tmp_name'][$i];

            if ($imagem && is_uploaded_file($tmp_name)) {
                $extensao = strtolower(pathinfo($imagem, PATHINFO_EXTENSION));
                if (in_array($extensao, $allowed_extensions)) {
                    $novo_nome_imagem = uniqid() . '.' . $extensao;
                    $upload_file = $upload_dir . $novo_nome_imagem;

                    if (move_uploaded_file($tmp_name, $upload_file)) {
                        $novas_imagens[] = $novo_nome_imagem;
                    } else {
                        //echo "Erro ao mover a imagem: $imagem";
                    }
                } else {
                    //echo "Extensão de arquivo não permitida: $imagem";
                }
            } else {
                //echo "Erro no upload da imagem: $imagem";
            }
        }
    }

    // Combina imagens existentes (não removidas) com as novas imagens
    $imagens = array_merge($imagens, $novas_imagens);
    $imagens_string = implode(',', $imagens);

    // Excluir as imagens removidas do servidor
    foreach ($imagens_removidas as $imagemRemovida) {
        $caminhoImagem = $upload_dir . $imagemRemovida;
        if (file_exists($caminhoImagem) && !empty($imagemRemovida)) {
            unlink($caminhoImagem); // Remove o arquivo
        }
    }
    //echo $imagemRemovida;

    // Código de atualização SQL, etc.
    // Resto do código permanece o mesmo...

    // Verifique o conteúdo e o tamanho de $imagens_string
    //echo "<pre>Tamanho de \$imagens_string: " . strlen($imagens_string) . " bytes</pre>";
    //echo "<pre>Conteúdo de \$imagens_string: $imagens_string</pre>";
    $ocultar = $_POST['ocultar'] === '1' ? '1' : '0';
    $promocao = $_POST['promocao'] === '1' ? '1' : '0';
    $valor_promocao = floatval(str_replace(',', '.', $_POST['valor_promocao']));
    $frete_gratis_promocao = $_POST['frete_gratis_promocao'] === '1' ? '1' : '0';  
    $valor_frete_promocao = $frete_gratis_promocao === '1' ? 0.00 : floatval(str_replace(',', '.', $_POST['valor_frete_promocao']));

    // Converte as datas para o formato esperado
    $ini_promocao = $_POST['ini_promocao'];
    $fim_promocao = $_POST['fim_promocao'];
    $dataFormatada_ini_promocao = DateTime::createFromFormat('Y-m-d', $ini_promocao);
    $dataFormatada_fim_promocao = DateTime::createFromFormat('Y-m-d', $fim_promocao);

    $ini = $dataFormatada_ini_promocao ? $dataFormatada_ini_promocao->format('Y-m-d') : null;
    $fim = $dataFormatada_fim_promocao ? $dataFormatada_fim_promocao->format('Y-m-d') : null;

    if (!$ini || !$fim) {
        echo "Erro na formatação das datas. Verifique o formato das datas enviadas.";
    }

    // Verifica a conexão
    if ($mysqli->connect_error) {
        die("Erro de conexão: " . $mysqli->connect_error);
    }

    // Executa o UPDATE diretamente para evitar truncamento
    $sql = "UPDATE produtos SET 
        nome_produto = '$nome_produto', 
        descricao_produto = '$descricao_produto',
        categoria = '$categoria', 
        valor_produto = $valor_produto, 
        taxa_padrao = $taxa, 
        valor_venda_vista = $valor_produto + ($valor_produto * $taxa) / 100,
        frete_gratis = '$frete_gratis', 
        valor_frete = $valor_frete, 
        imagens = '$imagens_string',
        promocao = '$promocao',
        valor_promocao = '$valor_promocao',
        frete_gratis_promocao = '$frete_gratis_promocao',
        valor_frete_promocao = $valor_frete_promocao,
        ini_promocao = '$ini',
        fim_promocao = '$fim',
        oculto = '$ocultar',
        produto_aprovado = '0'
        WHERE id_produto = $id_produto";

    if ($mysqli->query($sql)) {

        $sql_not = "INSERT INTO contador_notificacoes_admin (data, id_parceiro, id_produto, not_atualizar_produto) VALUES (NOW(),'$id_parceiro', '$id_produto', '1')";
        
        $deu_certo = $mysqli->query(query: $sql_not) or die($mysqli->error);

        if($deu_certo){
           /* $msg = true;
            $msg = "Cadastro realizado com sucesso!";
            $msg1 = "";
            $msg2 = "";
            //echo $msg;

            enviar_email(destinatario: $email, assunto: "Cadastro realizado com sucesso!", mensagemHTML: "
            <h1>Olá Sr. " . $primeiro_nome . ", seja bem vindo!</h1>
            <p><b>Você pode logar com seu CPF ou E-MAIL.</p>
            <p><b>Senha: </b>$senha</p>
            <p><b>Para redefinir sua senha </b><a href='../../login/lib/redefinir_senha.php'>clique aqui.</a></p>
            <p><b>Para entrar </b><a href='../index.php'>clique aqui.</a></p>
            <p>Menssagem automatica. Não responda!</p>");*/
        } 

        $msg = "<div class='message-box'>Produto atualizado com sucesso!</div>";

    } else {
        $msg = "Erro ao executar a atualização: " . $mysqli->error;
    }

} else {
    $msg = "Método de solicitação não permitido.";
}
?>


<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <style>
        .message-box {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            font-family: Arial, sans-serif;
            font-size: 18px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
            width: 300px; 
            max-width: 80%; 
            z-index: 9999;
        }
    </style>
    <title>Atualização de Produto</title>    
</head>
<body>
    <div class='message-box'>
        <?php echo $msg; ?>
    </div>
    <script>
        setTimeout(function() {
            window.location.href = 'editar_produto.php?id_produto=<?php echo $id_produto; ?>';
        }, 5000);
    </script>
</body>
</html> 
