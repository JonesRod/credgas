<?php
include('../../../conexao.php');  // Inclui o arquivo de conexão com o banco de dados

// Verifica se as imagens foram enviadas e se há pelo menos uma imagem
/*if (isset($_FILES['produtoImagens']) && count($_FILES['produtoImagens']['name']) > 0) {
    var_dump($_FILES['produtoImagens']);
}*/

// Verifica se o formulário foi enviado via método POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //var_dump($_POST);
    //die();
    // Coleta e sanitiza os dados do formulário
    $id_parceiro = $_POST['id_parceiro'];
    $nome_produto = $mysqli->real_escape_string(trim($_POST['nome_produto']));
    $descricao_produto = $mysqli->real_escape_string(trim($_POST['descricao_produto']));
    $valor_produto = str_replace(search: ',', replace: '.', subject: $_POST['valor_produto']);
    $valor_produto_taxa = str_replace(search: ',', replace: '.', subject: $_POST['valor_produto_taxa']);
    $frete_gratis = isset($_POST['frete_gratis']) ? 1 : 0;  // Define 1 para frete grátis, caso esteja marcado
    $valor_frete = str_replace(search: ',', replace: '.', subject: $_POST['valor_frete']);

//die();

    $valor_produto = floatval($valor_produto);
    $valor_produto_taxa = floatval($valor_produto_taxa);
    $frete_gratis = floatval($frete_gratis);

    $imagens = [];  // Inicializa um array para armazenar os nomes das imagens salvas

    // Define o diretório de upload para as imagens
    $diretorio_upload = 'img_produtos/';

    // Verifica se as imagens foram enviadas e se há pelo menos uma imagem
    if (isset($_FILES['produtoImagens']) && count($_FILES['produtoImagens']['name']) > 0) {
        // Percorre cada imagem enviada
        for ($i = 0; $i < count($_FILES['produtoImagens']['name']); $i++) {
            // Verifica se a imagem tem um nome válido e se não houve erro no upload
            if (!empty($_FILES['produtoImagens']['name'][$i]) && $_FILES['produtoImagens']['error'][$i] == UPLOAD_ERR_OK) {
                $nome_original = $_FILES['produtoImagens']['name'][$i];  // Obtém o nome original da imagem
                $extensao = pathinfo($nome_original, PATHINFO_EXTENSION);  // Obtém a extensão do arquivo

                // Verifica se a extensão do arquivo é permitida (jpg, jpeg, png, gif)
                if (in_array(needle: strtolower(string: $extensao), haystack: ['jpg', 'jpeg', 'png', 'gif'])) {
                    $novo_nome = uniqid() . '.' . $extensao;  // Renomeia a imagem com um nome único
                    $caminho_completo = $diretorio_upload . $novo_nome;  // Define o caminho completo da imagem

                    // Move o arquivo da pasta temporária para o diretório de upload
                    if (move_uploaded_file($_FILES['produtoImagens']['tmp_name'][$i], $caminho_completo)) {
                        $imagens[] = $novo_nome;  // Adiciona o nome da imagem ao array de imagens
                    } else {
                        // Exibe uma mensagem de erro se o upload falhar
                        echo "Erro ao mover o arquivo: " . $_FILES['produtoImagens']['name'][$i] . " - " . error_get_last()['message'];
                    }
                } else {
                    // Exibe uma mensagem se a extensão do arquivo não for permitida
                    echo "Extensão não permitida para o arquivo: " . $nome_original;
                }
            } elseif ($_FILES['produtoImagens']['error'][$i] != UPLOAD_ERR_NO_FILE) {
                // Se houver algum erro no upload que não seja "Nenhum arquivo enviado"
                // Adiciona verificação para o código de erro UPLOAD_ERR_INI_SIZE
                if ($_FILES['produtoImagens']['error'][$i] == UPLOAD_ERR_INI_SIZE || $_FILES['produtoImagens']['error'][$i] == UPLOAD_ERR_FORM_SIZE) {
                    echo "Erro: O arquivo " . $_FILES['produtoImagens']['name'][$i] . " excede o tamanho máximo permitido.";
                } else {
                    echo "Erro no upload do arquivo: " . $_FILES['produtoImagens']['name'][$i] . " - Código de erro: " . $_FILES['produtoImagens']['error'][$i];
                }
            }
        }
    } else {
        // Exibe uma mensagem se nenhuma imagem for enviada
        echo "Nenhuma imagem foi enviada.";
    }

    // Verifica se pelo menos uma imagem foi salva
    if (empty($imagens)) {
        echo "Nenhuma imagem foi salva.";
    }

    // Monta a query SQL para inserir os dados do produto no banco de dados usando prepared statements
    $sql = "INSERT INTO produtos (data, id_parceiro, nome_produto, descricao_produto, valor_produto, valor_produto_taxa, frete_gratis, valor_frete, imagens) 
            VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $imagens_json = implode(',', $imagens);  // Converte o array de imagens para uma string separada por vírgulas

    // Liga os parâmetros da query ao prepared statement
    $stmt->bind_param(
        'issddids', 
        $id_parceiro, $nome_produto, $descricao_produto, $valor_produto, 
        $valor_produto_taxa, $frete_gratis, $valor_frete, $imagens_json
    );

    // Executa a query e armazena o resultado
    if ($stmt->execute()) {
        $msg = 'Produto salvo com sucesso.';

        // Pega o ID do produto inserido
        $id_produto = $mysqli->insert_id;
        $not_novos_produtos ='1';

        // Monta a query SQL para inserir os dados do produto no banco de dados usando prepared statements
        $sql_not = "INSERT INTO contador_notificacoes_admin (data, id_parceiro, id_produto, not_novos_produtos) 
                    VALUES (NOW(), ?, ?, ?)";
        $stmt_not = $mysqli->prepare($sql_not);

        // Verifica se a query foi preparada com sucesso
        if ($stmt_not === false) {
            die('Erro na preparação da query: ' . $mysqli->error);
        }

        // Liga os parâmetros da query ao prepared statement
        $stmt_not->bind_param('iii', $id_parceiro, $id_produto, $not_novos_produtos);

        // Executa a query de notificação
        /*if ($stmt_not->execute()) {
            $msg = 'Notificação registrada com sucesso.';
        } else {
            $msg = 'Erro ao registrar a notificação: ' . $stmt_not->error;
        }*/
    }


    // Fecha o statement e a conexão com o banco de dados
    $stmt->close();
    $mysqli->close();

}else{
    $msg = 'Erro ao salvar o produto: ' . $stmt->error;
    
    // Fecha o statement e a conexão com o banco de dados
    $stmt->close();
    $mysqli->close();
}
?>


<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
        // Redireciona após 5 segundos
        setTimeout(function() {
            window.location.href = '../parceiro_home.php';
        }, 5000);
    </script>
    <title>Produto Salvo</title>
    <style>
        .msg {
            font-size: 20px; 
            color: green;    
            text-align: center; 
            margin: 20px 0; 
            padding: 10px; 
            border: 2px solid green; 
            background-color: #f0fff0; 
            border-radius: 5px; 
        }
    </style>
</head>
<body>
    <h2 class="msg">
        <?php
            echo $msg ?? '';
        ?>
    </h2>
</body>
</html>
