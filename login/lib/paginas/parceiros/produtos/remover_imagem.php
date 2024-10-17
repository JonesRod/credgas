<?php
// Inclui a conexão com o banco de dados
include('../../../conexao.php');

// Verifica se o índice da imagem e o ID do produto foram enviados
if (isset($_POST['index']) && isset($_POST['produto_id'])) {
    $index = intval($_POST['index']);
    $produto_id = intval($_POST['produto_id']);

    // Busca as imagens do produto no banco de dados
    $sql = "SELECT imagens FROM produtos WHERE id_produto = ?";
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $produto_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $produto = $result->fetch_assoc();
            $imagens = explode(',', $produto['imagens']); // Converte a string de imagens para array

            // Remove a imagem do array
            if (isset($imagens[$index])) {
                unset($imagens[$index]);
                $imagens = array_values($imagens); // Reorganiza o array

                // Atualiza o campo de imagens no banco de dados
                $imagens_atualizadas = implode(',', $imagens);
                $update_sql = "UPDATE produtos SET imagens = ? WHERE id_produto = ?";
                $update_stmt = $mysqli->prepare($update_sql);

                if ($update_stmt) {
                    $update_stmt->bind_param("si", $imagens_atualizadas, $produto_id);
                    if ($update_stmt->execute()) {
                        echo "success"; // Envia sucesso
                    } else {
                        echo "Erro ao atualizar o banco de dados: " . $mysqli->error;
                    }
                } else {
                    echo "Erro ao preparar consulta de atualização: " . $mysqli->error;
                }
            } else {
                echo "Imagem não encontrada no array.";
            }
        } else {
            echo "Produto não encontrado.";
        }

        $stmt->close();
    } else {
        echo "Erro na preparação da consulta: " . $mysqli->error;
    }
} else {
    echo "Dados inválidos.";
}
?>
