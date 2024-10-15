<?php
    /*session_start();

    include('../../../conexao.php');
    
    if (!isset($_SESSION['id_parceiro'])) {
        // Redireciona para a página de login se não houver sessão ativa
        header(header: "Location: ../../login.php");
        exit();
    }

    $id_parceiro = $_SESSION['id_parceiro'];*/


    include('../../../conexao.php');

    // Inicia a sessão
    if (!isset($_SESSION)) {
        session_start(); 
    }
    // Verifica se o ID do parceiro foi enviado via POST
   /*if (isset($_POST['id_parceiro'])) {
        $id_parceiro = $_POST['id_parceiro'];
        // Agora você pode usar $id_parceiro
        echo "ID do Parceiro: " . $id_parceiro;

        die();
    } else {
        session_unset();
        session_destroy(); 
        header(header: "Location: ../../../../../index.php");
        exit();
    }*/


    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Recuperar os dados do formulário
        $id_parceiro = $_POST['id_parceiro'] ?? null;
        $nome_produto = $_POST['nome_produto'] ?? null;
        $descricao_produto = $_POST['descricao_produto'] ?? null;
        $valor_produto = $_POST['valor_produto'] ?? null;
        $valor_produto_taxa = $_POST['valor_produto_taxa'] ?? null;
        $frete_gratis = $_POST['frete_gratis'] ?? null;
        $valor_frete = $_POST['valor_frete'] ?? null;
    
        // Verificar se o upload das imagens foi realizado
        $totalImagens = count(value: $_FILES['produtoImagens']['name']);

        if ($totalImagens > 0) {
            // Processo de upload das imagens
            $imagemNomes = [];
            for ($i = 0; $i < $totalImagens; $i++) {
                // Verifica se há um arquivo
                if ($_FILES['produtoImagens']['error'][$i] === UPLOAD_ERR_OK) {
                    $imagemNome = $_FILES['produtoImagens']['name'][$i];
                    $imagemTmp = $_FILES['produtoImagens']['tmp_name'][$i];
    
                    // Caminho para salvar a imagem no servidor
                    $caminhoImagem = 'img_produtos/' . basename($imagemNome);

                    if (move_uploaded_file(from: $imagemTmp, to: $caminhoImagem)) {
                        $imagemNomes[] = $caminhoImagem; // Armazena o caminho da imagem
                    } else {
                        echo "Erro ao fazer o upload da imagem " . $imagemNome;
                    }
                }
            }
            
            // Serializar os nomes das imagens
            $imagemNomesSerializados = implode(separator: ',', array: $imagemNomes);
    
            // Inserir o produto no banco de dados
            // (Seu código de inserção no banco deve estar aqui)
            // Inserir o produto no banco de dados, incluindo o id_parceiro
            $sql = "INSERT INTO produtos (data, id_parceiro, nome_produto, descricao_produto, valor_produto, valor_produto_taxa, frete_gratis, valor_frete, imagens) 
                    VALUES (NOW(),'$id_parceiro', '$nome_produto', '$descricao_produto', '$valor_produto', '$valor_produto_taxa', '$frete_gratis', '$valor_frete', '$imagemNomesSerializados')";

            $deu_certo = $mysqli->query(query: $sql) or die($mysqli->error);

            if($deu_certo){
                $msg = "Produto salvo com sucesso.";

                $mysqli->close();
            }else { 
                $msg = "Erro ao salvar o produto!";

                $mysqli->close();

                echo "Erro: " . $sql . "<br>" . $conn->$error;
                //echo "E-mail já cadastrado!";
            }
        } else {
            echo "Nenhuma imagem foi enviada.";
            die();
        }
    }else {
        session_unset();
        session_destroy(); 
        header(header: "Location: ../../../../../index.php");
        exit();
    }

    
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produto Salva</title>
</head>
<body>
    <h2>
        <?php
            echo $msg;
            header(header: "refresh: 5;../parceiro_home.php");
        ?>
    </h2>
</body>
</html>