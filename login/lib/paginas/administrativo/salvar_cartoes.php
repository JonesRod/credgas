<?php

include('../../conexao.php');

// Inicia a sessão
if (!isset($_SESSION)) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recebe os dados do formulário
    $id_cartao =  $_GET['editar'];
    $id_admin = $_POST['id_admin'];
    $nome_cartao = $_POST['novoCartao'];

    // Recebe os valores das parcelas de 1x até 12x
    $parcelas = [
        '1x' => $_POST['1x'] ?? null,
        '2x' => $_POST['2x'] ?? null,
        '3x' => $_POST['3x'] ?? null,
        '4x' => $_POST['4x'] ?? null,
        '5x' => $_POST['5x'] ?? null,
        '6x' => $_POST['6x'] ?? null,
        '7x' => $_POST['7x'] ?? null,
        '8x' => $_POST['8x'] ?? null,
        '9x' => $_POST['9x'] ?? null,
        '10x' => $_POST['10x'] ?? null,
        '11x' => $_POST['11x'] ?? null,
        '12x' => $_POST['12x'] ?? null
    ];

    // Validação: Verifique se os campos de parcelas estão preenchidos ou têm o valor 0
    foreach ($parcelas as $parcela => $valor) {
        if ($valor === null || $valor === '') {
            echo "Erro: O campo {$parcela} está vazio. Por favor, preencha todos os campos de parcelas.";
            exit;
        }
    }

    // Insere ou atualiza o cartão e as parcelas no banco de dados
    if (isset($_POST['alterar'])) {
        // Caso seja alteração, a query será para UPDATE
        $sql = "UPDATE cartoes SET id_admin = ?, nome = ?, 1x = ?, 2x = ?, 3x = ?, 4x = ?, 5x = ?, 6x = ?, 7x = ?, 8x = ?, 9x = ?, 10x = ?, 11x = ?, 12x = ? WHERE id = ?";
        // Incluindo o ID para atualizar
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('isssssssssssssi',
            $id_admin,
            $nome_cartao,
            $parcelas['1x'],
            $parcelas['2x'],
            $parcelas['3x'],
            $parcelas['4x'],
            $parcelas['5x'],
            $parcelas['6x'],
            $parcelas['7x'],
            $parcelas['8x'],
            $parcelas['9x'],
            $parcelas['10x'],
            $parcelas['11x'],
            $parcelas['12x'],
            $id_cartao // O ID para a atualização
        );
        //echo ('certo');
        //die();
    } else {
        // Caso seja adição, a query será para INSERT
        $sql = "INSERT INTO cartoes (id_admin, nome, 1x, 2x, 3x, 4x, 5x, 6x, 7x, 8x, 9x, 10x, 11x, 12x) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        // Prepara a consulta SQL
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('isiiiiiiiiiiii',
            $id_admin,
            $nome_cartao,
            $parcelas['1x'],
            $parcelas['2x'],
            $parcelas['3x'],
            $parcelas['4x'],
            $parcelas['5x'],
            $parcelas['6x'],
            $parcelas['7x'],
            $parcelas['8x'],
            $parcelas['9x'],
            $parcelas['10x'],
            $parcelas['11x'],
            $parcelas['12x']
        );
    }

    // Executa a consulta
    if ($stmt->execute()) {
        echo "Cartão adicionado/alterado com sucesso!";
    } else {
        echo "Erro ao adicionar/alterar o cartão: " . $mysqli->error;
    }

    // Fecha a consulta
    $stmt->close();
} else {
    echo "Método inválido.";
}

header("Refresh:0; url=lista_cartoes.php"); // Atualiza a página
exit;


?>
