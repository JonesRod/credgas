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

        // Validações básicas
        if (empty($data['id_cliente']) || empty($data['id_parceiro']) || empty($data['detalhes_produtos'])) {
            throw new Exception('Dados obrigatórios ausentes.');
        }

        $data_hora = isset($data['data_hora']) ? $data['data_hora'] : '';
        $id_cliente = isset($data['id_cliente']) ? intval($data['id_cliente']) : 0;
        $id_parceiro = isset($data['id_parceiro']) ? intval($data['id_parceiro']) : 0;
        $detalhes_produtos = isset($data['detalhes_produtos']) ? $data['detalhes_produtos'] : '';

        $valor_frete = isset($data['valor_frete']) ? floatval($data['valor_frete']) : 0.0;
        $valor_total = isset($data['total_compra']) ? floatval($data['total_compra']) : 0.0;
        $saldo_usado = isset($data['saldo_usado']) ? floatval($data['saldo_usado']) : 0.0;
        $total_compra_sem_frete = $valor_total - $valor_frete + $saldo_usado;
        $taxa_crediario = isset($data['taxa_crediario']) ? floatval($data['taxa_crediario']) : 0.0;
        $momen_pagamento = isset($data['momen_pagamento']) ? $data['momen_pagamento'] : '';
        $tipo_pagamento = isset($data['tipo_pagamento']) ? $data['tipo_pagamento'] : '';
        $entrega = isset($data['tipo_entrega']) ? $data['tipo_entrega'] : '';

        $entrada = $valor_total;

        $num_cartao = isset($data['num_cartao']) ? $data['num_cartao'] : '';
        $nome_cartao = isset($data['nome_cartao']) ? $data['nome_cartao'] : '';
        $validade = isset($data['validade']) ? $data['validade'] : '';
        $cod_seguranca = isset($data['cod_seguranca']) ? $data['cod_seguranca'] : '';
        $valorParcela_entrada = isset($data['valor_parcela']) ? floatval($data['valor_parcela']) : 0.0;
        $qt_parcelas_entrada = isset($data['qt_parcelas_entrada']) ? intval($data['qt_parcelas_entrada']) : 0;

        $bandeiras_aceitas = isset($data['bandeiras_aceitas']) ? $data['bandeiras_aceitas'] : '';
        $salvar_cartao = isset($data['salvar_cartao']) ? $data['salvar_cartao'] : '';
        $tipo_compra = isset($data['tipo_compra']) ? $data['tipo_compra'] : '';
        $restante = $valor_total - $entrada;

        $valor_restante = isset($data['valor_restante']) ? floatval($data['valor_restante']) : 0.0;
        $forma_pg_restante = isset($data['forma_pg_restante']) ? $data['forma_pg_restante'] : '';
        $qt_parcelas = isset($data['qt_parcelas']) ? intval($data['qt_parcelas']) : 0;
        $valor_parcela_restante = isset($data['valor_parcela_restante']) ? floatval($data['valor_parcela_restante']) : 0.0;

        $rua = isset($data['rua']) ? $data['rua'] : '';
        $numero = isset($data['numero']) ? $data['numero'] : '';
        $bairro = isset($data['bairro']) ? $data['bairro'] : '';
        $contato = isset($data['contato']) ? $data['contato'] : '';
        $comentario = isset($data['comentario']) ? $data['comentario'] : '';

        $status_cliente = 0;
        $status_parceiro = 0;

        if ($tipo_pagamento === '1') {
            $tipo_pagamento = 'pix';
        } else if ($tipo_pagamento === '2') {
            $tipo_pagamento = 'credito';
        } else if ($tipo_pagamento === '3') {
            $tipo_pagamento = 'debito';
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
            data, codigo_retirada, id_cliente, id_parceiro, produtos, valor_frete, valor_produtos, saldo_usado, taxa_crediario, formato_compra, entrada, forma_pg_entrada, qt_parcela_entrada, valor_parcela_entrada, valor_restante, forma_pg_restante, qt_parcelas, valor_parcela, tipo_entrega, endereco_entrega, num_entrega, bairro_entrega, contato_recebedor, comentario, status_cliente, status_parceiro
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if (!$stmt) {
            throw new Exception('Erro ao preparar a consulta: ' . $mysqli->error);
        }

        $stmt->bind_param(
            "ssiissssssssisssssssssssii",
            $data_hora,
            $codigo_retirada,
            $id_cliente,
            $id_parceiro,
            $detalhes_produtos,
            $valor_frete,
            $total_compra_sem_frete,
            $saldo_usado,
            $taxa_crediario,
            $momen_pagamento,
            $entrada,
            $tipo_pagamento,
            $qt_parcelas_entrada,
            $valorParcela_entrada,
            $restante,
            $forma_pg_restante,
            $qt_parcelas,
            $valor_parcela_restante,
            $entrega,
            $rua,
            $numero,
            $bairro,
            $contato,
            $comentario,
            $status_cliente,
            $status_parceiro
        );

        if ($stmt->execute()) {
            $num_pedido = $stmt->insert_id;
            $stmt->close();

            // Buscar cartões do cliente usando prepared statements
            $stmt = $mysqli->prepare("SELECT * FROM cartoes_clientes WHERE id_cliente = ?");
            if ($stmt) {
                $stmt->bind_param("i", $id_cliente);
                $stmt->execute();
                $result = $stmt->get_result();

                $cartoes = array();
                $cartoes_credito = 0;
                $cartoes_debito = 0;
                while ($row = $result->fetch_assoc()) {
                    $cartoes[] = $row;
                    if ($row['tipo'] === 'credito') {
                        $cartoes_credito++;
                    } elseif ($row['tipo'] === 'debito') {
                        $cartoes_debito++;
                    }
                }

                $stmt->close();
            } else {
                die("Erro na preparação da consulta: " . $mysqli->error);
            }
            
            // manda a notificação para o parceiro
            $stmt = $mysqli->prepare("INSERT INTO contador_notificacoes_parceiro (data, id_parceiro, pedidos) VALUES (?, ?, 1)");
            $stmt->bind_param("si", $data_hora, $id_parceiro);
            $stmt->execute();
            $stmt->close();

            // salvar o cartão de crédito ou débito se necessário
            if ($salvar_cartao == 1) {
                // Criptografar o código de segurança
                $cod_seguranca_criptografado = password_hash($cod_seguranca, PASSWORD_DEFAULT);

                // Verificar se o cartão já está cadastrado
                $stmt = $mysqli->prepare("SELECT id FROM cartoes_clientes WHERE id_cliente = ? AND num_cartao = ? AND tipo = ?");

                if ($stmt) {
                    $stmt->bind_param("iss", $id_cliente, $num_cartao, $tipo_pagamento);
                    $stmt->execute();
                    $stmt->store_result();

                    if ($stmt->num_rows > 0) {
                        $stmt->close();
                    } else {
                        $stmt->close();

                        // Verificar se o limite de cartões foi atingido
                        if (($tipo_pagamento === 'credito' && $cartoes_credito >= 5) || ($tipo_pagamento === 'debito' && $cartoes_debito >= 5)) {
                        } else {
                            // Salvar o novo cartão no banco de dados
                            $stmt = $mysqli->prepare("INSERT INTO cartoes_clientes (id_cliente, num_cartao, validade, cod_seguranca, tipo, nome) VALUES (?, ?, ?, ?, ?, ?)");
                            if ($stmt) {
                                $stmt->bind_param("isssss", $id_cliente, $num_cartao, $validade, $cod_seguranca_criptografado, $tipo_pagamento, $nome_cartao);
                                $stmt->execute();
                                $stmt->close();
                            } else {
                                die("Erro ao salvar o cartão: " . $mysqli->error);
                            }
                        }
                    }
                } else {
                    die("Erro na preparação da consulta: " . $mysqli->error);
                }
            }

            // Salvar notificação
            $msg = "Pedido #$num_pedido em Análise.";
            $stmt_notificacao = $mysqli->prepare("INSERT INTO contador_notificacoes_cliente (data, id_cliente, msg, referente, lida) VALUES (?, ?, ?, 'pedido', 1)");
            if (!$stmt_notificacao) {
                throw new Exception("Erro ao salvar a notificação: " . $mysqli->error);
            }
            $stmt_notificacao->bind_param("sis", $data_hora, $id_cliente, $msg);
            $stmt_notificacao->execute();
            $stmt_notificacao->close();

            echo json_encode(['success' => true, 'message' => 'Compra finalizada com sucesso.']);
        } else {
            throw new Exception('Erro ao executar a consulta: ' . $stmt->error);
        }
    } else {
        throw new Exception('Método inválido.');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>