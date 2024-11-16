<?php
    include('../../conexao.php');

    // Inicia a sessão
    if (!isset($_SESSION)) {
        session_start(); 
    }

    // Verifica se o usuário está logado
    if (isset($_SESSION['id'])) {
        $id = $_SESSION['id'];

        // Consulta para buscar o parceiro
        $sql_query = $mysqli->query("SELECT * FROM meus_parceiros WHERE id = '$id'") or die($mysqli->error);
        $parceiro = $sql_query->fetch_assoc();

        // Verifica e ajusta a logo
        if (isset($parceiro['logo'])) {
            $minhaLogo = $parceiro['logo'];

            if ($minhaLogo != '') {
                // Se existe e não está vazio, atribui o valor à variável logo
                $logo = 'arquivos/' . $parceiro['logo'];
            }
        } else {
            $logo = '../arquivos_fixos/icone_loja.jpg';
        }
    } else {
        session_unset();
        session_destroy(); 
        header("Location: ../../../../index.php");
        exit();
    }

    // Salvando os dados enviados pelo formulário
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $id_parceiro = intval($_POST['id_parceiro']);
        $horarios = $_POST['horarios'];
        $formas_recebimento = isset($_POST['formas_recebimento']) ? implode(',', $_POST['formas_recebimento']) : '';
        $valor_minimo = isset($_POST['valor_minimo']) ? floatval($_POST['valor_minimo']) : 0;

        // Atualizar ou inserir os dados no banco de dados
        $sql = "UPDATE parceiros 
                SET horarios_funcionamento = ?, formas_recebimento = ?, valor_minimo_pedido = ? 
                WHERE id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('ssdi', json_encode($horarios), $formas_recebimento, $valor_minimo, $id_parceiro);

        if ($stmt->execute()) {
            $msg = "Dados salvos com sucesso!";
        } else {
            $msg = "Erro ao salvar os dados: " . $stmt->error;
        }
    }

    // Obter os dados existentes
    $id_parceiro = intval($_GET['id_parceiro']);
    $sql = "SELECT horarios_funcionamento, formas_recebimento, valor_minimo_pedido FROM meus_parceiros WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $id_parceiro);
    $stmt->execute();
    $stmt->bind_result($horarios_funcionamento, $formas_recebimento, $valor_minimo_pedido);
    $stmt->fetch();
    $horarios_funcionamento = json_decode($horarios_funcionamento, true) ?? [];
    $formas_recebimento = explode(',', $formas_recebimento ?? '');
    $valor_minimo_pedido = $valor_minimo_pedido ?? 0;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações de Horários</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
        }
        h2 {
            text-align: center; /* Centraliza o texto */
            margin-bottom: 20px;
        }
        form {
            max-width: 600px;
            margin: auto;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
        }
        label {
            display: block;
            margin-top: 10px;
        }
        input[type="time"],
        input[type="number"] {
            margin-right: 10px;
        }
        fieldset {
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 15px;
            padding: 10px;
        }
        legend {
            font-weight: bold;
            padding: 0 10px;
        }
        .buttons {
            text-align: center;
        }
        .buttons button, .buttons a {
            padding: 10px 20px;
            margin: 5px;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .buttons button {
            background-color: #28a745;
            color: #fff;
        }
        .buttons a {
            background-color: #dc3545;
            color: #fff;
        }
    </style>
</head>
<body>
    <h2>Minhas Configurações</h2>

    <form method="POST">
        <input type="hidden" name="id_parceiro" value="<?php echo $id_parceiro; ?>">

        <!-- Horários de Funcionamento -->
        <fieldset>
            <legend>Horários de Funcionamento</legend>
            <?php
            $dias = ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'];
            foreach ($dias as $dia) {
                $abertura = $horarios_funcionamento[$dia]['abertura'] ?? '';
                $fechamento = $horarios_funcionamento[$dia]['fechamento'] ?? '';
                $almoco_inicio = $horarios_funcionamento[$dia]['almoco_inicio'] ?? '';
                $almoco_fim = $horarios_funcionamento[$dia]['almoco_fim'] ?? '';
                echo "<label>$dia:</label>";
                echo "<input type='time' name='horarios[$dia][abertura]' value='$abertura'> - ";
                echo "<input type='time' name='horarios[$dia][fechamento]' value='$fechamento'>";
                echo " | Almoço: ";
                echo "<input type='time' name='horarios[$dia][almoco_inicio]' value='$almoco_inicio'> - ";
                echo "<input type='time' name='horarios[$dia][almoco_fim]' value='$almoco_fim'>";
            }
            ?>
        </fieldset>

        <!-- Formas de Recebimento -->
        <fieldset>
            <legend>Formas de Recebimento</legend>
            <label>
                <input type="checkbox" name="formas_recebimento[]" value="Dinheiro" 
                    <?php echo in_array('Dinheiro', $formas_recebimento) ? 'checked' : ''; ?>>
                Dinheiro
            </label>
            <label>
                <input type="checkbox" name="formas_recebimento[]" value="Cartão de Crédito" 
                    <?php echo in_array('Cartão de Crédito', $formas_recebimento) ? 'checked' : ''; ?>>
                Cartão de Crédito
            </label>
            <label>
                <input type="checkbox" name="formas_recebimento[]" value="Cartão de Débito" 
                    <?php echo in_array('Cartão de Débito', $formas_recebimento) ? 'checked' : ''; ?>>
                Cartão de Débito
            </label>
            <label>
                <input type="checkbox" name="formas_recebimento[]" value="Pix" 
                    <?php echo in_array('Pix', $formas_recebimento) ? 'checked' : ''; ?>>
                Pix
            </label>
        </fieldset>

        <!-- Valor Mínimo de Pedido -->
        <fieldset>
            <legend>Valor Mínimo de Pedido</legend>
            <label>Informe o valor mínimo:</label>
            <input type="number" step="0.01" name="valor_minimo" value="<?php echo htmlspecialchars($valor_minimo_pedido); ?>" required>
        </fieldset>

        <!-- Botões -->
        <div class="buttons">
            <button type="submit">Salvar</button>
            <a href="parceiro_home.php">Voltar</a>
        </div>
    </form>
</body>
</html>
