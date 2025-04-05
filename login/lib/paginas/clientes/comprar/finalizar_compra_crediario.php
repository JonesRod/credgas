<?php
session_start();
include('../../../conexao.php'); // Conexão com o banco

// Configurar cabeçalhos para JSON
header('Content-Type: application/json; charset=utf-8');

// Verificação de sessão
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Sessão expirada.']);
    exit;
}

$id_session = $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Receber os dados JSON
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (isset($data['senha_cliente']) && isset($data['senha_compra'])) {
        // Buscar a senha do cliente no banco
        $bd_cliente = $mysqli->query("SELECT senha_login FROM meus_clientes WHERE id = $id_session") or die(json_encode(['success' => false, 'message' => 'Erro ao buscar dados do cliente.']));
        $dados = $bd_cliente->fetch_assoc();
        $senha_compra = $dados['senha_login'];

        // Verificar e sanitizar os dados recebidos
        $tipo_compra = 'crediario';
        $id_cliente = isset($data['id_cliente']) ? intval($data['id_cliente']) : 0;
        $id_parceiro = isset($data['id_parceiro']) ? intval($data['id_parceiro']) : 0;
        $valor_frete = isset($data['valor_frete']) ? floatval($data['valor_frete']) : 0.0;
        $valor_total_sem_crediario = isset($data['valor_total_sem_crediario']) ? floatval($data['valor_total_sem_crediario']) : 0.0;
        $valor_total_crediario = isset($data['valor_total_crediario']) ? floatval($data['valor_total_crediario']) : 0.0;
        $detalhes_produtos = isset($data['detalhes_produtos']) ? $data['detalhes_produtos'] : '';
        $entrega = isset($data['entrega']) ? $data['entrega'] : '';
        $rua = isset($data['rua']) ? $data['rua'] : '';
        $bairro = isset($data['bairro']) ? $data['bairro'] : '';
        $numero = isset($data['numero']) ? $data['numero'] : '';
        $contato = isset($data['contato']) ? $data['contato'] : '';
        $entrada = isset($data['entrada']) ? floatval($data['entrada']) : 0.0;
        $restante = isset($data['restante']) ? floatval($data['restante']) : 0.0;
        $tipo_entrada_crediario = isset($data['tipo_entrada_crediario']) ? $data['tipo_entrada_crediario'] : '';
        $bandeiras_aceitas = isset($data['bandeiras_aceitas']) ? $data['bandeiras_aceitas'] : '';
        $comentario = isset($data['comentario']) ? $data['comentario'] : '';
        $parcelas = isset($data['parcelas']) ? intval($data['parcelas']) : 1;
        $valor_parcela = isset($data['valor_parcela']) ? floatval($data['valor_parcela']) : 0.0;
        $senha_cliente = $data['senha_cliente'];

        $tota_compra = $valor_total_crediario + $valor_frete + $valor_total_sem_crediario;
        $status_cliente = 0; // Status do cliente
        $status_parceiro = 0; // Status do parceiro
        $dataFormatada = $data['dataFormatada'] ?? date('Y-m-d H:i:s');

        // Verifica se a senha foi preenchida
        if (empty($senha_cliente)) {
            echo json_encode(['success' => false, 'message' => 'Senha não informada.']);
            exit;
        }

        // Verifica se a senha está correta usando password_verify
        if (!password_verify($senha_cliente, $senha_compra)) {
            echo json_encode(['success' => false, 'message' => 'Senha incorreta.']);
            exit;
        }

        // Processar a compra (exemplo de inserção no banco de dados)
        $stmt = $mysqli->prepare("INSERT INTO pedidos (
        data,
        id_cliente, 
        id_parceiro, 
        produtos, 
        valor_frete,
        valor,
        tipo_compra, 
        entrada, 
        forma_pg_entrada, 
        valor_restante, 
        forma_pg_restante, 
        qt_parcelas, 
        valor_parcela,
        tipo_entrega, 
        endereco_entrega,
        num_entrega,
        bairro_entrega,
        contato_recebedor,
        comentario, 
        status_cliente, 
        status_parceiro) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param(
            "siiiddsddsissssssiii", // Tipos de dados: s = string, i = inteiro, d = double
            $dataFormatada,         // s: data
            $id_cliente,            // i: id_cliente
            $id_parceiro,           // i: id_parceiro
            $detalhes_produtos,     // s: produtos
            $valor_frete,           // d: valor_frete
            $tota_compra,           // d: valor
            $tipo_compra,           // s: tipo_compra
            $entrada,               // d: entrada
            $tipo_entrada_crediario,// s: forma_pg_entrada
            $restante,              // d: valor_restante
            $tipo_compra,           // s: forma_pg_restante
            $parcelas,              // i: qt_parcelas
            $valor_parcela,         // d: valor_parcela
            $entrega,               // s: tipo_entrega
            $rua,                   // s: endereco_entrega
            $numero,                // s: num_entrega
            $bairro,                // s: bairro_entrega
            $contato,               // s: contato_recebedor
            $comentario,            // s: comentario
            $status_cliente,        // i: status_cliente
            $status_parceiro        // i: status_parceiro
        );

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Compra finalizada com sucesso.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao finalizar a compra.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Dados incompletos.']);
    }
}
?>