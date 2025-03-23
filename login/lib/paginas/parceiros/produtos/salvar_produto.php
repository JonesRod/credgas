<?php
include('../../../conexao.php');  // Inclui o arquivo de conexão com o banco de dados

    // Inicia a sessão
    if (!isset($_SESSION)) {
        session_start();
    }

    // Verifica se o ID do parceiro foi enviado via POST
    if (isset($_SESSION['id']) && isset($_POST['id_parceiro'])) {
        $id_parceiro = mysqli_real_escape_string($mysqli, $_POST['id_parceiro']);
    } else {
        session_unset();
        session_destroy();
        header("Location: ../../../../../index.php");
        exit();
    }

// Verifica se o formulário foi enviado via método POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //var_dump($_POST);
    //die();
    // Coleta e sanitiza os dados do formulário
    $id_parceiro = $_POST['id_parceiro'];
    $nome_produto = $mysqli->real_escape_string(trim($_POST['nome_produto']));
    $descricao_produto = $mysqli->real_escape_string(trim($_POST['descricao_produto']));
    $categoria = $mysqli->real_escape_string(trim($_POST['categoria']));

    $valor_produto = $_POST['valor_produto'];

    // Remove pontos de milhares e substitui vírgula decimal por ponto
    $valor_produto = str_replace('.', '', $valor_produto); // Remove todos os pontos
    $valor_produto = str_replace(',', '.', $valor_produto); // Substitui a vírgula pelo ponto

    $taxa = str_replace(search: ',', replace: '.', subject: $_POST['taxa']);
    $frete_gratis = isset($_POST['frete_gratis']) ? 1 : 0;  // Define 1 para frete grátis, caso esteja marcado
    $valor_frete = str_replace(search: ',', replace: '.', subject: $_POST['valor_frete']);

    //die();

    $valor_produto = floatval($valor_produto);
    $taxa = floatval($taxa);
    $valor_venda_vista = $valor_produto + ($valor_produto * $taxa / 100);
    $valor_venda_vista = number_format($valor_venda_vista, 2, '.', '');
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

     $imagens_json = implode(',', $imagens);  // Converte o array de imagens para uma string separada por vírgulas
    
    // Monta a query SQL com placeholders
    $sql = "INSERT INTO produtos (data, id_parceiro, nome_produto, descricao_produto, categoria, valor_produto, taxa_padrao, valor_venda_vista, frete_gratis, valor_frete, imagens) 
            VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
    $stmt = $mysqli->prepare($sql);
   
    if (!$stmt) {
        die("Erro ao preparar a query: " . $mysqli->error);
    }
    // Associa os parâmetros à consulta preparada
    $stmt->bind_param(
        'isssddssss', 
        $id_parceiro,        // Inteiro (ID do parceiro)
        $nome_produto,       // String
        $descricao_produto,  // String
        $categoria,          // String
        $valor_produto,      // Double
        $taxa, // String ou Double (ajustar conforme necessidade)
        $valor_venda_vista,  // Double
        $frete_gratis,       // String (ex.: 'sim' ou 'não')
        $valor_frete,        // String
        $imagens_json        // String (imagens separadas por vírgulas)
    );

    if ($stmt->execute()) {
        // Pega o ID do produto inserido
        $id_produto = $mysqli->insert_id;
        $not_novos_produtos = 1;
    
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
    
        // Executa a query para inserir a notificação
        if ($stmt_not->execute() === false) {
            die('Erro ao inserir notificação: ' . $stmt_not->error);
        } else {
            $msg = 'Produto salvos com sucesso.';
        }
    
        $stmt_not->close();
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
