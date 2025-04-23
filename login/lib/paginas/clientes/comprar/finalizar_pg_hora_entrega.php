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

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Receber os dados JSON
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Erro ao decodificar JSON: ' . json_last_error_msg());
        }

        if (isset($data['senha_cliente']) && isset($data['senha_cliente'])) {
            // Buscar a senha do cliente no banco
            $bd_cliente = $mysqli->query("SELECT senha_login FROM meus_clientes WHERE id = $id_session");
            if (!$bd_cliente) {
                throw new Exception('Erro ao buscar dados do cliente.');
            }
            $dados = $bd_cliente->fetch_assoc();
            $senha_compra = $dados['senha_login'];

            // Verificar se a senha foi preenchida
            if (empty($data['senha_cliente'])) {
                echo json_encode(['success' => false, 'message' => 'Senha não informada.']);
                exit;
            }

            // Verifica se a senha está correta usando password_verify
            if (!password_verify($data['senha_cliente'], $senha_compra)) {
                echo json_encode(['success' => false, 'message' => 'Senha incorreta.']);
                exit;
            }

            // Verificar e sanitizar os dados recebidos
            $tipo_compra = 'Pagar na Retirada ou entrega.';
            $id_cliente = isset($data['id_cliente']) ? intval($data['id_cliente']) : 0;
            $id_parceiro = isset($data['id_parceiro']) ? intval($data['id_parceiro']) : 0;
            $valor_frete = isset($data['valor_frete']) ? floatval($data['valor_frete']) : 0.0;
            $valor_total = isset($data['valor_total']) ? floatval($data['valor_total']) : 0.0;
            $entrada_saldo = isset($data['entrada_saldo']) ? floatval($data['entrada_saldo']) : 0.0;
            $detalhes_produtos = isset($data['detalhes_produtos']) ? $data['detalhes_produtos'] : '';
            $entrega = isset($data['entrega']) ? $data['entrega'] : '';
            $rua = isset($data['rua']) ? $data['rua'] : '';
            $bairro = isset($data['bairro']) ? $data['bairro'] : '';
            $numero = isset($data['numero']) ? $data['numero'] : '';
            $contato = isset($data['contato']) ? $data['contato'] : '';
            $comentario = isset($data['comentario']) ? $data['comentario'] : '';
            $hora_data = isset($data['data_hora']) ? $data['data_hora'] : null;
            $bandeiras_outros_aceitos = isset($data['bandeiras_outros_aceitos']) ? $data['bandeiras_outros_aceitos'] : '';
            $taxa_crediario = isset($data['taxa_crediario']) ? floatval($data['taxa_crediario']) : 0.0;

            $total_compra_sem_frete = $valor_total - $valor_frete;

            $status_cliente = 0; // Status do cliente
            $status_parceiro = 0; // Status do parceiro

            // Validar se $hora_data foi preenchido
            if (empty($hora_data)) {
                echo json_encode(['success' => false, 'message' => 'Erro: A data e hora não foram fornecidas.']);
                exit;
            }

            // Gerador do código de retirada de 6 dígitos
            $codigo_retirada = mt_rand(100000, 999999);
            $stmt = $mysqli->prepare("SELECT COUNT(*) FROM pedidos WHERE codigo_retirada = ? AND id_cliente = ?");
            if (!$stmt) {
                throw new Exception('Erro ao preparar a consulta: ' . $mysqli->error);
            }
            $stmt->bind_param("si", $codigo_retirada, $id_cliente);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            // Gera um novo código se já existir
            while ($count > 0) {
                $codigo_retirada = mt_rand(100000, 999999);
                $stmt = $mysqli->prepare("SELECT COUNT(*) FROM pedidos WHERE codigo_retirada = ? AND id_cliente = ?");
                if (!$stmt) {
                    throw new Exception('Erro ao preparar a consulta: ' . $mysqli->error);
                }
                $stmt->bind_param("si", $codigo_retirada, $id_cliente);
                $stmt->execute();
                $stmt->bind_result($count);
                $stmt->fetch();
                $stmt->close();
            }

            // Processar a compra
            $stmt = $mysqli->prepare("INSERT INTO pedidos (
                data, 
                codigo_retirada, 
                id_cliente, 
                id_parceiro, 
                produtos, 

                valor_frete, 
                valor_produtos, 
                saldo_usado, 
                taxa_crediario, 
                formato_compra,

                forma_pg_entrada,  
                tipo_entrega, 
                endereco_entrega, 
                num_entrega, 
                bairro_entrega, 

                contato_recebedor, 
                comentario, 
                status_cliente, 
                status_parceiro
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            if (!$stmt) {
                throw new Exception('Erro ao preparar a consulta: ' . $mysqli->error);
            }

            $stmt->bind_param(
                "ssiisssssssssssssii", // Tipos de dados: s = string, i = inteiro, d = double
                $hora_data,            // s: data
                $codigo_retirada,      // s: código de retirada
                $id_cliente,           // i: id_cliente
                $id_parceiro,          // i: id_parceiro
                $detalhes_produtos,    // s: produtos

                $valor_frete,          // d: valor_frete
                $total_compra_sem_frete, // d: valor_produtos
                $entrada_saldo,        // d: saldo_usado
                $taxa_crediario,       // d: taxa_crediario
                $tipo_compra,          // s: tipo_compra

                $bandeiras_outros_aceitos, // s: forma_pg_entrada
                $entrega,              // s: tipo_entrega
                $rua,                  // s: endereco_entrega
                $numero,               // s: num_entrega
                $bairro,               // s: bairro_entrega

                $contato,              // s: contato_recebedor
                $comentario,           // s: comentario
                $status_cliente,       // i: status_cliente
                $status_parceiro       // i: status_parceiro
            );

            if ($stmt->execute()) {
                $num_pedido = $stmt->insert_id;
                $stmt->close();

                // Salvar notificação
                $msg = "Pedido #$num_pedido em Análise.";
                $stmt_notificacao = $mysqli->prepare("INSERT INTO contador_notificacoes_cliente (data, id_cliente, msg, referente, lida) VALUES (?, ?, ?, 'pedido', 1)");
                if (!$stmt_notificacao) {
                    throw new Exception("Erro ao salvar a notificação: " . $mysqli->error);
                }
                $stmt_notificacao->bind_param("sis", $hora_data, $id_cliente, $msg);
                $stmt_notificacao->execute();
                $stmt_notificacao->close();

                // manda a notificação para o parceiro
                $stmt = $mysqli->prepare("INSERT INTO contador_notificacoes_parceiro (data, id_parceiro, pedidos) VALUES (?, ?, 1)");
                $stmt->bind_param("si", $data_hora, $id_parceiro);
                $stmt->execute();
                $stmt->close();

                // Excluir o pedido do carrinho
                $stmt_carrinho = $mysqli->prepare("DELETE FROM carrinho WHERE id_cliente = ? AND id_parceiro = ?");
                if ($stmt_carrinho) {
                    $stmt_carrinho->bind_param("ii", $id_cliente, $id_parceiro);
                    $stmt_carrinho->execute();
                    $stmt_carrinho->close();
                } else {
                    throw new Exception("Erro ao excluir do carrinho: " . $mysqli->error);
                }

                echo json_encode(['success' => true, 'message' => 'Compra finalizada com sucesso.']);
            } else {
                throw new Exception('Erro ao executar a consulta: ' . $stmt->error);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Dados incompletos.']);
        }
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>