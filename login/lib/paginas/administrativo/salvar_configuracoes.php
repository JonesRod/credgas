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
        $sql_query = $mysqli->query("SELECT * FROM config_admin WHERE id_cliente = '$id'") or die($mysqli->error);
        $admin = $sql_query->fetch_assoc();

    } else {
        session_unset();
        session_destroy(); 
        header("Location: ../../../../index.php");
        exit();
    }


    // Obter os dados enviados pelo formulário
    $id_admin = $_POST['id_admin'];
    $idmc = $_POST['idmc'];
    $idsc = $_POST['idsc'];
    $tc = $_POST['tc'];
    $tcr = $_POST['tcr'];
    $tp = $_POST['tp'];
    $tpr = $_POST['tpr'];

    // Formas de recebimento
    $formas_recebimento = isset($_POST['formas_recebimento']) ? implode(',', $_POST['formas_recebimento']) : '';
    $descricao_outros_forma = $_POST['descricao_outros_forma'] ?? '';

    // Campos para cartões de débito e crédito
    $cartoes_debito = isset($_POST['cartoes_debito']) ? implode(',', $_POST['cartoes_debito']) : '';
    $cartoes_credito = isset($_POST['cartoes_credito']) ? implode(',', $_POST['cartoes_credito']) : '';

    // Taxas
    $taxa_padrao = $_POST['taxa_padrao'];
    $taxa_pix = $_POST['taxa_pix'];
    $taxa_crediario = $_POST['taxa_crediario'];
    $taxa_outros = $_POST['taxa_outros'];

    // Multas e juros
    $dias_inclu_spc = $_POST['dias_inclu_spc'];
    $multa_inclu_spc = $_POST['multa_inclu_spc'];
    $juro_inclu_spc = $_POST['juro_inclu_spc'];

    // Desconto cliente fiel
    $dias_cli_fiel = $_POST['dias_cli_fiel'];
    $valor_dias_cli_fiel = $_POST['valor_dias_cli_fiel'];

    // Desconto cliente pontual
    $dias_cli_pontual = $_POST['dias_cli_pontual'];
    $valor_dias_cli_pontual = $_POST['valor_dias_cli_pontual'];

    // Bônus de indicação
    $bonus_indicacao = $_POST['bonus_indicacao'];

    // Bônus para aniversariantes
    $bonus_aniversariante = $_POST['bonus_aniversariante'];

    $data = date('Y-m-d H:i:s'); // Define a data atual

    $sql = "INSERT INTO config_admin (
        id_cliente, data_alteracao, idade_min_cadastro, idade_min_crediario, termos_cliente_vista, 
        termos_cliente_crediario, termos_parceiro, termos_privacidade, formas_recebimento, cartoes_debito, cartoes_credito, outras_formas, 
        taxa_padrao, taxa_pix, taxa_crediario, taxa_outros, dias_inclu_spc, 
        multa_inclu_spc, juro_inclu_spc, dias_cli_fiel, valor_dias_cli_fiel, dias_cli_pontual, 
        valor_dias_cli_pontual, bonus_indicacao, bonus_aniversariante
        ) VALUES (
            '$id_admin', '$data', '$idmc', '$idsc', '$tc', '$tcr', '$tp', '$tpr',
            '$formas_recebimento', '$cartoes_debito', '$cartoes_credito', '$descricao_outros_forma', '$taxa_padrao', '$taxa_pix',
            '$taxa_crediario', '$taxa_outros', '$dias_inclu_spc', '$multa_inclu_spc',
            '$juro_inclu_spc', '$dias_cli_fiel', '$valor_dias_cli_fiel', '$dias_cli_pontual',
            '$valor_dias_cli_pontual', '$bonus_indicacao', '$bonus_aniversariante'
        )
    ";

    // Executando diretamente
    if ($mysqli->query($sql)) {
        $message = "<div class='msg-container'>Dados salvos com sucesso!</div>";
    } else {
        $message = "Erro ao salvar dados: " . $mysqli->error;
    }

    $mysqli->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações</title>
    <script>
        // Redirecionar após 3 segundos com o ID
        setTimeout(function() {
            const idAdmin = "<?php echo $id_admin; ?>"; // Passa o valor do PHP para o JS
            if (idAdmin) {
                window.location.href = `configuracoes.php?id_admin=${idAdmin}`;
            } else {
                window.location.href = "configuracoes.php";
            }
        }, 3000);
    </script>

    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 20%;
        }
        .msg-container {
            font-size: 1.5rem;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="msg-container">
        <?php echo $message; ?>
    </div>
</body>
</html>

