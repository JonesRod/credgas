<?php
// Inclui a conexão com o banco de dados
include('../../../conexao.php');

// Inicia a sessão para pegar os dados do usuário, se necessário
session_start();

// Verifica se o ID do produto foi enviado via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_produto = intval($_POST['id_produto']);
    $nome_produto = $_POST['nome_produto'];
    $descricao_produto = $_POST['descricao_produto'];
    $valor_produto = floatval($_POST['valor_produto']);
    $valor_produto_taxa = floatval($_POST['valor_produto_taxa']);
    $frete_gratis = $_POST['frete_gratis'];
    $valor_frete = ($frete_gratis == 'sim') ? 0.00 : floatval($_POST['valor_frete']);
    
// Verifica se as imagens salvas foram enviadas corretamente
$imagens_existentes = isset($_POST['imagens_salvas']) ? explode(',', $_POST['imagens_salvas']) : [];
$imagens_removidas = isset($_POST['imagens_removidas']) ? explode(',', $_POST['imagens_removidas']) : [];

// Debug: verificar o conteúdo dos arrays
echo "Imagens existentes (antes de remover): " . implode(',', $imagens_existentes) . "<br>"; // Debug

// Verifica se não há imagens existentes e garante que seja uma string vazia se não houver
if (empty($imagens_existentes) || (count($imagens_existentes) === 0 && $imagens_existentes[0] === '')) {
    $imagens_existentes = []; // Garantindo que seja um array vazio
}

// Debug: verificar novamente o conteúdo do array após a verificação
echo "Imagens existentes (após a verificação): " . implode(',', $imagens_existentes) . "<br>"; // Debug

    // Remove as imagens marcadas para remoção
    $imagens = array_diff($imagens_existentes, $imagens_removidas);

    // Inicializa o array de imagens para novas imagens
    $novas_imagens = []; // Array para armazenar as novas imagens

    // Diretório de upload das imagens
    $upload_dir = 'img_produtos/';
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

    // Processa as novas imagens enviadas
    if (isset($_FILES['produtoImagens']) && count($_FILES['produtoImagens']['name']) > 0) {
        for ($i = 0; $i < count($_FILES['produtoImagens']['name']); $i++) {
            $imagem = $_FILES['produtoImagens']['name'][$i];
            $tmp_name = $_FILES['produtoImagens']['tmp_name'][$i];

            if ($imagem && is_uploaded_file($tmp_name)) {
                // Verifica a extensão do arquivo
                $extensao = pathinfo($imagem, PATHINFO_EXTENSION);
                if (in_array(strtolower($extensao), $allowed_extensions)) {
                    // Renomeia a imagem para garantir um nome único
                    $novo_nome_imagem = uniqid() . '.' . $extensao;
                    $upload_file = $upload_dir . $novo_nome_imagem;

                    // Tenta mover o arquivo carregado para o diretório de destino
                    if (move_uploaded_file($tmp_name, $upload_file)) {
                        $novas_imagens[] = $novo_nome_imagem; // Adiciona a nova imagem ao array de novas imagens
                        echo "Imagem carregada com sucesso: " . $novo_nome_imagem . "<br>"; // Depuração
                    } else {
                        echo "Erro ao mover o arquivo para " . $upload_file . "<br>"; // Depuração
                    }
                } else {
                    echo "Extensão de arquivo inválida: " . htmlspecialchars($extensao) . "<br>"; // Depuração
                }
            } else {
                echo "Nenhum arquivo foi carregado para o índice: " . $i . "<br>"; // Depuração
            }
        }
    } else {
        echo "Nenhuma imagem enviada.<br>"; // Depuração
    }

    // Debug: verificar o conteúdo das novas imagens
    echo "Novas imagens: " . implode(',', $novas_imagens) . "<br>"; // Debug

    // Junta imagens existentes com novas imagens
    $imagens = array_merge($imagens, $novas_imagens);
    $imagens_string = implode(',', $imagens);
    echo "Imagens finais (para salvar no BD): " . $imagens_string . "<br>"; // Depuração

    // Verificação do estado da conexão com o banco de dados
    if ($mysqli->connect_error) {
        die("Erro de conexão: " . $mysqli->connect_error . "<br>"); // Depuração
    }

    // Atualiza o produto no banco de dados
    $sql = "UPDATE produtos SET nome_produto = ?, descricao_produto = ?, valor_produto = ?, valor_produto_taxa = ?, frete_gratis = ?, valor_frete = ?, imagens = ? WHERE id_produto = ?";
    $stmt = $mysqli->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("ssdsdsdi", $nome_produto, $descricao_produto, $valor_produto, $valor_produto_taxa, $frete_gratis, $valor_frete, $imagens_string, $id_produto);

        if ($stmt->execute()) {
            echo "Produto atualizado com sucesso!<br>"; // Depuração
        } else {
            echo "Erro ao executar a atualização: " . $stmt->error . "<br>"; // Depuração
        }
    } else {
        echo "Erro na preparação da consulta: " . $mysqli->error . "<br>"; // Depuração
    }
} else {
    echo "Método de solicitação não permitido.";
}
?>
