<?php
    session_start();
    include('../../../conexao.php'); // Conexão com o banco

    // Verificação de sessão
    if (!isset($_SESSION['id'])) {
        header("Location: ../../../../index.php");
        exit;
    }

    $id_session = $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['senha_cliente']) && isset($_POST['senha_compra']))) {

    $bd_cliente = $mysqli->query("SELECT senha_login FROM meus_clientes WHERE id = $id_session") or die($mysqli->error);
    $dados = $bd_cliente->fetch_assoc();
    $senha_compra = $dados['senha_login'];

        // Verificação e sanitização dos dados recebidos
        $tipo_compra = 'crediario';
        $id_cliente = isset($_POST['id_cliente']) ? intval($_POST['id_cliente']) : 0;
        $id_parceiro = isset($_POST['id_parceiro']) ? intval($_POST['id_parceiro']) : 0;
        $valor_frete = isset($_POST['valor_frete']) ? floatval(str_replace(',', '.', $_POST['valor_frete'])) : 0.0;
        $valor_total_sem_crediario = isset($_POST['valor_total_sem_crediario']) ? floatval(str_replace(',', '.', $_POST['valor_total_sem_crediario'])) : 0.0;
        $valor_total_crediario = isset($_POST['valor_total_crediario']) ? floatval(str_replace(',', '.', $_POST['valor_total_crediario'])) : 0.0;
        $detalhes_produtos = isset($_POST['detalhes_produtos']) ? $_POST['detalhes_produtos'] : '';
        $entrega = isset($_POST['entrega']) ? $_POST['entrega'] : '';
        $rua = isset($_POST['rua']) ? $_POST['rua'] : '';
        $bairro = isset($_POST['bairro']) ? $_POST['bairro'] : '';
        $numero = isset($_POST['numero']) ? $_POST['numero'] : '';
        $contato = isset($_POST['contato']) ? $_POST['contato'] : '';
        $entrada = isset($_POST['entrada']) ? floatval(str_replace(',', '.', $_POST['entrada'])) : 0.0;
        $restante = isset($_POST['restante']) ? floatval(str_replace(',', '.', $_POST['restante'])) : 0.0;
        $tipo_entrada_crediario = isset($_POST['tipo_entrada_crediario']) ? $_POST['tipo_entrada_crediario'] : '';
        $bandeiras_aceitas = isset($_POST['bandeiras_aceita']) ? $_POST['bandeiras_aceita'] : '';
        $comentario = isset($_POST['comentario']) ? $_POST['comentario'] : '';
        $maior_parcelas = isset($_POST['maiorParcelas']) ? intval($_POST['maiorParcelas']) : 1;

        // Formatação para moeda
        $valor_total_crediario_formatado = number_format($valor_total_crediario, 2, ',', '.');
        $entrada_formatado = number_format($entrada, 2, ',', '.');
        $restante_formatado = number_format($restante, 2, ',', '.');
        
        $senha_compra = $_POST['senha_compra'];
        $senha_cliente = $_POST['senha_cliente'];

// Verifica se a senha foi preenchida
if (empty($senha_cliente)) {
    $erro = 'Senha não informada';
}

// Verifica se a senha está correta usando password_verify
elseif (!password_verify($senha_cliente, $senha_compra)) {
    $erro = 'Senha incorreta';
}

// Se houver erro, exibe o popup com a mensagem
if (isset($erro)) {
    echo "<script>
            document.addEventListener('DOMContentLoaded', function () {
                const popupSenha = document.getElementById('popup-senha');
                const msgErro = document.getElementById('msg_erro');
                if (popupSenha && msgErro) {
                    popupSenha.style.display = 'block';
                    msgErro.textContent = '$erro';
                    msgErro.style.display = 'block';
                }
            });
          </script>";
    exit;
}

// Aqui você pode continuar com o processamento da compra, como inserir os dados no banco de dados



        echo json_encode(['success' => true, 'message' => 'Senha correta.']);
        // Aqui você pode continuar com o processamento da compra, como inserir os dados no banco de dados
        // Exemplo de inserção no banco de dados

    }
?>