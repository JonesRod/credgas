<?php
// Inclui a conexão com o banco de dados
include('../../../conexao.php');
var_dump(value: $_POST); // Verifique os dados recebidos
//die();
// Inicia a sessão para pegar os dados do usuário, se necessário
session_start();

// Verifica se o ID do produto foi enviado via POST
if (isset($_POST['id_produto'])) {
    //var_dump($_POST); // Verifique os dados recebidos
    $id_produto = intval($_POST['id_produto']);
    $nome_produto = $_POST['nome_produto'];
    $descricao_produto = $_POST['descricao_produto'];
    $valor_produto = floatval(value: $_POST['valor_produto']);
    $valor_produto_taxa = floatval(value: $_POST['valor_produto_taxa']);
    $frete_gratis = $_POST['frete_gratis'];
    $valor_frete = ($frete_gratis == 'sim') ? 0.00 : floatval($_POST['valor_frete']);
    $imagens_existentes = isset($_POST['imagens_existentes']) ? $_POST['imagens_existentes'] : []; // Imagens existentes
    
    // Processa as novas imagens enviadas
    $imagens = $imagens_existentes; // Inicializa com as imagens existentes
    if (isset($_FILES['produtoImagens']) && count(value: $_FILES['produtoImagens']['name']) > 0) {
        for ($i = 0; $i < count(value: $_FILES['produtoImagens']['name']); $i++) {
            $imagem = $_FILES['produtoImagens']['name'][$i];
            $tmp_name = $_FILES['produtoImagens']['tmp_name'][$i];

            if ($imagem && is_uploaded_file(filename: $tmp_name)) {
                $upload_dir = 'img_produtos/'; // Defina o diretório de upload
                $upload_file = $upload_dir . basename($imagem);

                // Move o arquivo carregado para o diretório de destino
                if (move_uploaded_file(from: $tmp_name, to: $upload_file)) {
                    $imagens[] = $upload_file; // Adiciona a nova imagem ao array de imagens
                }
            }
        }
    }

    // Converte o array de imagens para string separada por vírgulas
    $imagens_string = implode(separator: ',', array: $imagens);

    // Atualiza o produto no banco de dados
    $sql = "UPDATE produtos SET nome_produto = ?, descricao_produto = ?, valor_produto = ?, valor_produto_taxa = ?, frete_gratis = ?, valor_frete = ?, imagens = ? WHERE id_produto = ?";
    $stmt = $mysqli->prepare($sql);
    
    if ($stmt) {
        // A string de tipos agora tem 8 caracteres para corresponder às 8 variáveis
        $stmt->bind_param("ssdsdsii", $nome_produto, $descricao_produto, $valor_produto, $valor_produto_taxa, $frete_gratis, $valor_frete, $imagens_string, $id_produto);

        if ($stmt->execute()) {
            echo "Produto atualizado com sucesso!";
            // Redireciona para a página de edição do produto ou uma página de sucesso
            header("Location: editar_produto.php?id=" . $id_produto);
            exit;
        } else {
            echo "Erro ao atualizar o produto: " . $mysqli->error;
        }
    } else {
        echo "Erro na preparação da consulta: " . $mysqli->error;
    }
} else {
    echo "ID do produto não fornecido.";
    // Para depurar, você pode usar:
    var_dump(value: $_POST); // Isso vai mostrar todos os dados enviados via POST
}
?>
